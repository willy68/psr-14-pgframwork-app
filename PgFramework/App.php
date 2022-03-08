<?php

namespace PgFramework;

use Exception;
use DI\ContainerBuilder;
use Mezzio\Router\RouteCollector;
use GuzzleHttp\Psr7\ServerRequest;
use Psr\Container\ContainerInterface;
use PgFramework\Kernel\KernelInterface;
use Psr\Http\Message\ResponseInterface;
use PgFramework\Router\RoutesMapInterface;
use PgFramework\Environnement\Environnement;
use Psr\Http\Message\ServerRequestInterface;
use PgFramework\Router\Loader\DirectoryLoader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use PgFramework\Middleware\Stack\MiddlewareAwareStackTrait;

/**
 * Application
 */
class App extends AbstractApplication
{
    use MiddlewareAwareStackTrait;

    public const PROXY_DIRECTORY = 'tmp/proxies';

    public const COMPILED_CONTAINER_DIRECTORY = 'tmp/di';

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
     * Undocumented variable
     *
     * @var array
     */
    private $config = [];

    /**
     * Undocumented modules
     *
     * @var array
     */
    private $modules = [];

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
     * Undocumented function
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
     * Undocumented function
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

    public function addListeners(array $listeners): self
    {
        $this->listeners = array_merge($this->listeners, $listeners);
        return $this;
    }

    /**
     * Undocumented function
     *
     * @param string $routePrefix
     * @param string|null $middleware
     * @return self
     */
    public function pipe(string $routePrefix, ?string $middleware = null): self
    {
        /** MiddlewareAwareStackTrait::lazyPipe */
        return $this->lazyPipe($routePrefix, $middleware, $this->getContainer());
    }

    /**
     * Undocumented function
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
                    $container->get(RouteCollector::class)
                );
                foreach ($module::ANNOTATIONS as $dir) {
                    $loader->load($dir);
                }
            }
            $module = $container->get($module);
        }

        if (!empty($this->listeners)) {
            if (!$this->kernel) {
                $this->kernel = $container->get(KernelInterface::class);
            }

            $map = $container->get(RoutesMapInterface::class);
            [$listeners] = $map->getListeners($this->request);
            if (null !== $listeners) {
                $this->listeners = array_merge($this->listeners, $listeners);
            }

            foreach ($this->listeners as $listener) {
                $this->kernel->getDispatcher()->addSubscriber($listener);
            }
        }

        try {
            return $this->kernel->handle($this->request);
        } catch (\Exception $e) {
            return $this->kernel->handleException($e, $this->request);
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
            $env = Environnement::getEnv('APP_ENV', 'production');
            if ($env === 'production') {
                $builder->enableCompilation(self::COMPILED_CONTAINER_DIRECTORY);
                $builder->writeProxiesToFile(true, self::PROXY_DIRECTORY);
            }
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

    /**
     * Undocumented function
     *
     * @return array
     */
    public function getModules(): array
    {
        return $this->modules;
    }

    /**
     * Undocumented function
     *
     * @return object
     * @throws Exception
     */

    private function getMiddleware()
    {
        return $this->shiftMiddleware($this->getContainer());
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
}
