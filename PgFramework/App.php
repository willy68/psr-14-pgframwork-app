<?php

declare(strict_types=1);

namespace PgFramework;

use App\Auth\AuthModule;
use Exception;
use Invoker\Exception\NotCallableException;
use Pg\Router\RouteCollector;
use PgFramework\Router\Loader\RouteLoader;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Server\MiddlewareInterface;
use ReflectionException;
use RuntimeException;
use DI\ContainerBuilder;
use PgFramework\File\FileUtils;
use GuzzleHttp\Psr7\ServerRequest;
use PgFramework\Kernel\KernelEvent;
use Psr\Container\ContainerInterface;
use PgFramework\Kernel\KernelInterface;
use Psr\Http\Message\ResponseInterface;
use PgFramework\Kernel\KernelMiddleware;
use PgFramework\Router\RoutesMapInterface;
use PgFramework\Environnement\Environnement;
use Psr\Http\Message\ServerRequestInterface;
use PgFramework\Annotation\AnnotationsLoader;
use PgFramework\Router\Loader\DirectoryLoader;
use Throwable;

use function dirname;

/**
 * Application
 */
class App extends AbstractApplication
{
	public const PROXY_DIRECTORY = '/tmp/proxies';

	public const COMPILED_CONTAINER_DIRECTORY = '/tmp/di';

	private ?ContainerInterface $container = null;

	private ?KernelInterface $kernel;

	private array $config = [];

	private array $modules = [];

	private array $middlewares = [];

	private array $listeners = [];

	private ServerRequestInterface $request;

	private string $projectDir;

	private string $configDir;

	public function __construct(?KernelInterface $kernel = null)
	{
		$this->config[] = __DIR__ . '/Container/config/config.php';

		self::$app = $this;

		$this->kernel = $kernel;
	}

	/**
	 *
	 * @param string $module
	 * @return self
	 */
	public function addModule(string $module): self
	{
		$this->modules[] = $module;
		return $this;
	}

	/**
	 *
	 * @param array $modules
	 * @return self
	 */
	public function addModules(array $modules): self
	{
		foreach ($modules as $module) {
			$this->addModule($module);
		}
		return $this;
	}

	/**
	 * @param string $listener
	 * @return $this
	 */
	public function addListener(string $listener): self
	{
		$this->listeners[] = $listener;
		return $this;
	}

	/**
	 *
	 * @param array $listeners
	 * @return self
	 */
	public function addListeners(array $listeners): self
	{
		$this->listeners = array_merge($this->listeners, $listeners);
		return $this;
	}

	/**
	 * @param string|callable|MiddlewareInterface $middleware
	 * @return $this
	 */
	public function addMiddleware(string|callable|MiddlewareInterface $middleware): self
	{
		$this->middlewares[] = $middleware;
		return $this;
	}

	/**
	 *
	 * @param array $middlewares
	 * @return self
	 */
	public function addMiddlewares(array $middlewares): self
	{
		$this->middlewares = array_merge($this->middlewares, $middlewares);
		return $this;
	}

	/**
	 * @throws NotFoundExceptionInterface
	 * @throws ContainerExceptionInterface
	 * @throws NotCallableException
	 * @throws ReflectionException
	 * @throws Exception
	 */
	public function init(?ServerRequestInterface $request = null): static
	{
		if ($request === null) {
			$request = ServerRequest::fromGlobals();
		}

		/** @var ServerRequestInterface $request */
		$this->request = $request->withAttribute(ApplicationInterface::class, $this);

		$container = $this->getContainer();

		/** @var Module $module*/
		foreach ($this->modules as $module) {
			if (!empty($module::ANNOTATIONS)) {
				$loader = new RouteLoader(
					$container->get(RouteCollector::class),
					$container->get(AnnotationsLoader::class)
				);
				foreach ($module::ANNOTATIONS as $dir) {
					$loader->load($dir);
				}
			}
			$container->get($module);
		}

		if (!empty($this->listeners)) {
			if (!$this->kernel) {
				$this->kernel = $container->get(KernelEvent::class);
			}
			if (!$this->kernel instanceof KernelEvent) {
				throw new RuntimeException('Aucun Kernel ou le Kernel ne gère pas les listeners');
			}
			$map = $container->get(RoutesMapInterface::class);
			[$listeners] = $map->getListeners($this->request);
			if (null !== $listeners) {
				$this->listeners = array_merge($this->listeners, $listeners);
			}
			$this->kernel->setCallbacks($this->listeners);
		} else {
			if (!$this->kernel) {
				$this->kernel = $container->get(KernelMiddleware::class);
			}
			if (!$this->kernel instanceof KernelMiddleware) {
				throw new RuntimeException('Aucun Kernel ou le Kernel ne gère pas les middlewares');
			}
			$this->kernel->setCallbacks($this->middlewares);
		}
		return $this;
	}

	/**
	 *
	 * @return ResponseInterface
	 */
	public function run(): ResponseInterface
	{
		try {
			return $this->kernel->handle($this->request);
		} catch (Throwable $e) {
			return $this->kernel->handleException($e, $this->kernel->getRequest());
		}
	}

	/**
	 * Get Injection Container
	 *
	 * @return ContainerInterface
	 * @throws Exception
	 */
	public function getContainer(): ContainerInterface
	{
		if ($this->container === null) {
			$builder = new ContainerBuilder();

			$env = Environnement::getEnv('APP_ENV', 'prod');
			if ($env === 'prod') {
				$projectDir = realpath($this->getProjectDir()) ?: $this->getProjectDir();
				$builder->enableCompilation($projectDir . self::COMPILED_CONTAINER_DIRECTORY);
				$builder->writeProxiesToFile(true, $projectDir . self::PROXY_DIRECTORY);
			}

			$builder->addDefinitions($this->getRunTimeDefinitions());
			foreach ($this->config as $config) {
				$builder->addDefinitions($config);
			}

			/** @var Module $module*/
			foreach ($this->modules as $module) {
				if ($module::DEFINITIONS) {
					$builder->addDefinitions($module::DEFINITIONS);
				}
			}
			$this->container = $builder->build();
		}
		return $this->container;
	}

	protected function getRunTimeDefinitions(): array
	{
		// Get all config file definitions
		$config = FileUtils::getFiles($this->getConfigDir(), 'php', '.dist.');
		$this->config = array_merge($this->config, array_keys($config));

		return [
			ApplicationInterface::class => $this,
			'app.project.dir' => $this->projectDir,
			'app.cache.dir'   => $this->projectDir . '/tmp/cache',
		];
	}

	/**
	 *
	 * @return KernelInterface|null
	 */
	public function getKernel(): ?KernelInterface
	{
		return $this->kernel;
	}

	/**
	 *
	 * @return array
	 */
	public function getModules(): array
	{
		return $this->modules;
	}

	/**
	 * Get the value of request
	 *
	 * @return  ServerRequestInterface
	 */
	public function getRequest(): ServerRequestInterface
	{
		return $this->request;
	}

	/**
	 * Set the value of request
	 *
	 * @param  ServerRequestInterface  $request
	 *
	 * @return  self
	 */
	public function setRequest(ServerRequestInterface $request): self
	{
		$this->request = $request;

		return $this;
	}

	/**
	 * Gets the application root dir (path of the project composer file).
	 *
	 * https://github.com/symfony/symfony/blob/6.0/src/Symfony/Component/HttpKernel/Kernel.php#method_getProjectDir
	 */
	public function getProjectDir(): string
	{
		if (!isset($this->projectDir)) {
			$dir = $rootDir = dirname(__DIR__);
			while (!is_file($dir . '/composer.json')) {
				if ($dir === dirname($dir)) {
					return $this->projectDir = $rootDir;
				}
				$dir = dirname($dir);
			}
			$this->projectDir = $dir;
		}
		return $this->projectDir;
	}

	public function getConfigDir(): string
	{
		if (!isset($this->configDir)) {
			$projectDir = realpath($this->getProjectDir()) ?: $this->getProjectDir();
			$this->configDir = $projectDir . '/config';
		}
		return $this->configDir;
	}
}