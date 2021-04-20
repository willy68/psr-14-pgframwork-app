<?php

namespace PgFramework;

use Exception;
use DI\ContainerBuilder;
use Invoker\CallableResolver;
use PgFramework\Event\ViewEvent;
use Mezzio\Router\RouteResult;
use PgFramework\Event\RequestEvent;
use Mezzio\Router\RouteCollector;
use PgFramework\Event\ResponseEvent;
use GuzzleHttp\Psr7\ServerRequest;
use League\Event\ListenerPriority;
use PgFramework\Event\ExceptionEvent;
use PgFramework\Event\ControllerEvent;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use PgFramework\Environnement\Environnement;
use PgFramework\Event\ControllerParamsEvent;
use Invoker\Reflection\CallableReflection;
use PgFramework\Router\Loader\DirectoryLoader;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Invoker\ParameterResolver\ParameterResolver;
use Psr\EventDispatcher\EventDispatcherInterface;
use PgFramework\Middleware\Stack\MiddlewareAwareStackTrait;

/**
 * Application
 */
class App extends AbstractApplication implements RequestHandlerInterface
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
     *
     * @var CallableResolver
     */
    private $callableResolver;

    /**
     *
     * @var ParameterResolver
     */
    private $paramsResolver;

    /**
     *
     * @var EventDispatcherInterface
     */
    private $dispatcher;

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
        ?EventDispatcherInterface $dispatcher = null,
        ?CallableResolver $callableResolver = null,
        ?ParameterResolver $paramsResolver = null
    ) {
        $this->config[] = __DIR__ . '/Container/config/config.php';
        $this->config = \array_merge($this->config, $config);

        self::$app = $this;

        $this->dispatcher = $dispatcher;
        $this->callableResolver = $callableResolver;
        $this->paramsResolver = $paramsResolver;
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
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws Exception
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $middleware = $this->getMiddleware();
        if (is_null($middleware)) {
            throw new Exception('Aucun middleware n\'a intercepté cette requête');
        } elseif ($middleware instanceof MiddlewareInterface) {
            return $middleware->process($request, $this);
        } elseif (is_callable($middleware)) {
            return call_user_func_array($middleware, [$request, [$this, 'handle']]);
        }
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
        $this->request = $request;

        $container = $this->getContainer();

        if (!$this->callableResolver) {
            $this->callableResolver = $container->get(CallableResolver::class);
        }

        if (!$this->paramsResolver) {
            $this->paramsResolver = $container->get(ParameterResolver::class);
        }

        if (!$this->dispatcher) {
            $this->dispatcher = $container->get(EventDispatcherInterface::class);
        }

        foreach ($this->listeners as $listener => $eventName) {
            $priority = ListenerPriority::NORMAL;
            if (is_array($eventName)) {
                [$eventName, $priority] = $eventName;
            }
            $this->dispatcher->subscribeTo(
                $eventName,
                $this->callableResolver->resolve($listener),
                $priority
            );
            //$this->dispatcher->addSubscriber($listener);
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

        if ($request === null) {
            $this->request = ServerRequest::fromGlobals();
        }

        try {
            return $this->handleEvent($this->request);
        } catch (\Exception $e) {
            return $this->handleException($e, $this->request);
        }

        //return $this->handle($this->request);
    }

    private function handleEvent(ServerRequestInterface $request): ResponseInterface
    {
        $event = new RequestEvent($this, $request);
        $event = $this->dispatcher->dispatch($event);

        if ($event->hasResponse()) {
            return $this->filterResponse($event->getResponse(), $event->getRequest());
        }

        /** @var RouteResult $result */
        $result = $event->getRequest()->getAttribute(RouteResult::class);
        $controller = $result->getMatchedRoute()->getCallback();
        $params = $result->getMatchedParams();

        $controller = $this->callableResolver->resolve($controller);

        $event = new ControllerEvent($this, $controller, $event->getRequest());
        $event = $this->dispatcher->dispatch($event);
        $controller = $event->getController();

        $container = $this->getContainer();

        // controller arguments
        if ($container instanceof \DI\Container) {
            $container->set(ServerRequestInterface::class, $event->getRequest());
        } else {
            // Limitation: $request must be named "$request"
            $params = array_merge(["request" => $event->getRequest()], $params);
        }

        $callableReflection = CallableReflection::create($controller);
        $params = $this->paramsResolver->getParameters($callableReflection, $params, []);

        $event = new ControllerParamsEvent($this, $controller, $params, $event->getRequest());
        $event = $this->dispatcher->dispatch($event);
        $controller = $event->getController();
        $params = $event->getParams();

        // call controller
        $response = $controller(...$params);

        // view
        if (!$response instanceof ResponseInterface) {
            $event = new ViewEvent($this, $event->getRequest(), $response);
            $event = $this->dispatcher->dispatch($event);

            if ($event->hasResponse()) {
                $response = $event->getResponse();
            } else {
                $msg = sprintf('The controller must return a "Response" object but it returned %s.', $response);

                // the user may have forgotten to return something
                if (null === $response) {
                    $msg .= ' Did you forget to add a return statement somewhere in your controller?';
                }

                throw new Exception($msg . get_class($controller) . ' ' . __FILE__ . ' ' . (__LINE__ - 17));
            }
        }

        return $this->filterResponse($response, $event->getRequest());
    }
    /**
     * Filters a response object.
     *
     * @throws \RuntimeException if the passed object is not a Response instance
     */
    private function filterResponse(ResponseInterface $response, ServerRequestInterface $request): ResponseInterface
    {
        $event = new ResponseEvent($this, $request, $response);

        $event = $this->dispatcher->dispatch($event);

        return $event->getResponse();
    }

    private function handleException(\Throwable $e, ServerRequestInterface $request): ResponseInterface
    {
        $event = new ExceptionEvent($this, $request, $e);
        $event = $this->dispatcher->dispatch($event);

        // a listener might have replaced the exception
        $e = $event->getException();

        if (!$event->hasResponse()) {
            //$this->finishRequest($request, $type);

            throw $e;
        }

        $response = $event->getResponse();

        try {
            return $this->filterResponse($response, $event->getRequest());
        } catch (\Exception $e) {
            return $response;
        }
    }

    /**
     * Undocumented function
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

    public function getDispatcher()
    {
        return $this->dispatcher;
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
