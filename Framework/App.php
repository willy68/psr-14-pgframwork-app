<?php

namespace Framework;

use Exception;
use DI\ContainerBuilder;
use Framework\Event\Events;
use GuzzleHttp\Psr7\Response;
use Invoker\CallableResolver;
use Framework\Event\ViewEvent;
use Mezzio\Router\RouteResult;
use Framework\Event\RequestEvent;
use Mezzio\Router\RouteCollector;
use Framework\Event\ResponseEvent;
use GuzzleHttp\Psr7\ServerRequest;
use League\Event\ListenerPriority;
use Framework\Event\ControllerEvent;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Framework\Environnement\Environnement;
use Framework\Event\ControllerParamsEvent;
use Invoker\Reflection\CallableReflection;
use Framework\Router\Loader\DirectoryLoader;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Invoker\ParameterResolver\ParameterResolver;
use Psr\EventDispatcher\EventDispatcherInterface;
use Framework\Middleware\Stack\MiddlewareAwareStackTrait;

/**
 * Application
 */
class App implements RequestHandlerInterface
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
     * Self static
     *
     * @var App
     */
    private static $app = null;

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
    )
    {
        $this->config[] = __DIR__ . '/Container/config/config.php';
        $this->config = \array_merge($this->config, $config);
        self::$app = $this;

        if (null !== $dispatcher) {
            $this->dispatcher = $dispatcher;
        }

        if (null !== $callableResolver) {
            $this->callableResolver = $callableResolver;
        }

        if (null !== $paramsResolver) {
            $this->paramsResolver = $paramsResolver;
        }
    }

    /**
     * Get Self instance
     *
     * @return App|null
     */
    public static function getApp(): ?App
    {
        return self::$app;
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
        }

        foreach ($this->modules as $module) {
            if (!empty($module::ANNOTATIONS)) {
                $loader = new DirectoryLoader(
                    $container->get(RouteCollector::class));
                foreach ($module::ANNOTATIONS as $dir) {
                    $loader->load($dir);
                }
            }
            $module = $container->get($module);
        }

        if ($request === null) {
            $request = ServerRequest::fromGlobals();
        }

        return $this->handleEvent($request);
    }

    private function handleEvent(ServerRequestInterface $request): ResponseInterface
    {
        $event = new RequestEvent($this, $request);
        $event = $this->dispatcher->dispatch($event, Events::REQUEST);

        if ($event->hasResponse()) {
            return $event->getResponse();
        }

        /** @var RouteResult $result */
        $result = $event->getRequest()->getAttribute(RouteResult::class);
        $controller = $result->getMatchedRoute()->getCallback();
        $params = $result->getMatchedParams();

        $controller = $this->callableResolver->resolve($controller);

        $event = new ControllerEvent($this, $controller, $request);
        $event = $this->dispatcher->dispatch($event);
        $controller = $event->getController();

        $container = $this->getContainer();

        // controller arguments
        if ($container instanceof \DI\Container) {
            $container->set(ServerRequestInterface::class, $request);
        } else {
            // Limitation: $request must be named "$request"
            $params = array_merge(["request" => $request] , $params);
        }

        $callableReflection = CallableReflection::create($controller);
        $params = $this->paramsResolver->getParameters($callableReflection, $params, []);
        
        $event = new ControllerParamsEvent($this, $controller, $params, $request);
        $event = $this->dispatcher->dispatch($event);
        $controller = $event->getController();
        $params = $event->getParams();

        // call controller
        $response = $controller(...$params);

        if (is_string($response)) {
            $response = new Response(200, [], $response);
        }

        // view
        if (!$response instanceof Response) {
            $event = new ViewEvent($this, $request, $response);
            $event = $this->dispatcher->dispatch($event);

            if ($event->hasResponse()) {
                $response = $event->getResponse();
            } else {
                $msg = sprintf('The controller must return a "Response" object but it returned %s.', $response);

                // the user may have forgotten to return something
                if (null === $response) {
                    $msg .= ' Did you forget to add a return statement somewhere in your controller?';
                }

                //throw new ControllerDoesNotReturnResponseException($msg, $controller, __FILE__, __LINE__ - 17);
            }
        }

        return $this->filterResponse($response, $request);
    }
    /**
     * Filters a response object.
     *
     * @throws \RuntimeException if the passed object is not a Response instance
     */
    private function filterResponse(ResponseInterface $response, ServerRequestInterface $request): ResponseInterface
    {
        $event = new ResponseEvent($this, $request, $response);

        $event = $this->dispatcher->dispatch($event, Events::RESPONSE);

        return $event->getResponse();
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
}
