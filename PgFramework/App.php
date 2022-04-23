<?php

declare(strict_types=1);

namespace PgFramework;

use Exception;
use DI\ContainerBuilder;
use Mezzio\Router\RouteCollector;
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
use PgFramework\Middleware\PageNotFoundMiddleware;
use Doctrine\Common\Annotations\AnnotationRegistry;
use PgFramework\Middleware\DispatcherMiddleware;
use RuntimeException;

/**
 * Application
 */
class App extends AbstractApplication
{
    public const PROXY_DIRECTORY = '/tmp/proxies';

    public const COMPILED_CONTAINER_DIRECTORY = '/tmp/di';

    /**
     *
     * @var ContainerInterface
     */
    private $container = null;

    /**
     * Kernel type
     *
     * @var KernelInterface
     */
    private $kernel;

    /**
     *
     * @var array
     */
    private $config = [];

    /**
     *
     * @var array
     */
    private $modules = [];

    /**
     *
     * @var array
     */
    private $middlewares = [];

    /**
     *
     * @var array
     */
    private $listeners = [];

    /**
     *
     * @var ServerRequestInterface
     */
    private $request;

    /**
     * Dir where de composer.json file is located
     *
     * @var string
     */
    private $projectDir;

    /**
     * App constructor
     *
     * @param array $config
     */
    public function __construct(
        array $config,
        ?KernelInterface $kernel = null
    ) {
        $this->config[] = __DIR__ . '/Container/config/config.php';
        $this->config = \array_merge($this->config, $config);

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
     *
     * @param string $listener
     * @return self
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
     *
     * @param string $middleware
     * @return self
     */
    public function addMiddleware(string $middleware): self
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
     *
     * @param  ServerRequestInterface|null $request
     * @return ResponseInterface
     * @throws Exception
     */
    public function run(?ServerRequestInterface $request = null): ResponseInterface
    {
        if ($request === null) {
            $request = ServerRequest::fromGlobals();
        }

        /** @var ServerRequestInterface $request */
        $this->request = $request->withAttribute(ApplicationInterface::class, $this);

        $container = $this->getContainer();

        if (class_exists(AnnotationRegistry::class)) {
            AnnotationRegistry::registerLoader('class_exists');
        }

        foreach ($this->modules as $module) {
            if (!empty($module::ANNOTATIONS)) {
                $loader = new DirectoryLoader(
                    $container->get(RouteCollector::class),
                    $container->get(AnnotationsLoader::class)
                );
                foreach ($module::ANNOTATIONS as $dir) {
                    $loader->load($dir);
                }
            }
            $module = $container->get($module);
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
            $this->addMiddlewares(
                [
                    DispatcherMiddleware::class,
                    PageNotFoundMiddleware::class
                ]
            );
            $this->kernel->setCallbacks($this->middlewares);
        }

        try {
            return $this->kernel->handle($this->request);
        } catch (\Exception $e) {
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
        $projectDir = realpath($this->getProjectDir()) ?: $this->getProjectDir();
        return [
            ApplicationInterface::class => $this,
            'app.project.dir' => $projectDir,
            'app.cache.dir'   => $projectDir . '/tmp/cache',
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
    public function getRequest()
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
    public function setRequest(ServerRequestInterface $request)
    {
        $this->request = $request;

        return $this;
    }

    /**
     * Gets the application root dir (path of the project's composer file).
     *
     * https://github.com/symfony/symfony/blob/6.0/src/Symfony/Component/HttpKernel/Kernel.php#method_getProjectDir
     */
    public function getProjectDir(): string
    {
        if (!isset($this->projectDir)) {
            $r = new \ReflectionObject($this);

            if (!is_file($dir = $r->getFileName())) {
                throw new \LogicException(
                    sprintf(
                        'Cannot auto-detect project dir for kernel of class "%s".',
                        $r->name
                    )
                );
            }

            $dir = $rootDir = \dirname($dir);
            while (!is_file($dir . '/composer.json')) {
                if ($dir === \dirname($dir)) {
                    return $this->projectDir = $rootDir;
                }
                $dir = \dirname($dir);
            }
            $this->projectDir = $dir;
        }

        return $this->projectDir;
    }
}
