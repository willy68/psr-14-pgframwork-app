<?php

declare(strict_types=1);

namespace PgFramework\Kernel;

use Exception;
use InvalidArgumentException;
use Invoker\CallableResolver;
use Mezzio\Router\RouteResult;
use PgFramework\Event\ViewEvent;
use PgFramework\Event\RequestEvent;
use PgFramework\Event\ResponseEvent;
use PgFramework\ApplicationInterface;
use PgFramework\Event\ExceptionEvent;
use Psr\Container\ContainerInterface;
use PgFramework\Event\ControllerEvent;
use Psr\Http\Message\ResponseInterface;
use Invoker\Reflection\CallableReflection;
use PgFramework\Event\ControllerParamsEvent;
use Psr\Http\Message\ServerRequestInterface;
use Invoker\ParameterResolver\ParameterResolver;
use Psr\EventDispatcher\EventDispatcherInterface;
use RuntimeException;

class KernelEvent implements KernelInterface
{
    protected $request;

    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var CallableResolver
     */
    private $callableResolver;

    /**
     * @var ParameterResolver
     */
    private $paramsResolver;


    public function __construct(
        ?EventDispatcherInterface $dispatcher = null,
        ?CallableResolver $callableResolver = null,
        ?ParameterResolver $paramsResolver = null
    ) {
        $this->dispatcher = $dispatcher;
        $this->callableResolver = $callableResolver;
        $this->paramsResolver = $paramsResolver;
    }

    /**
     * @inheritDoc
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->request = $request;

        /** @var ContainerInterface $container */
        $container = $request->getAttribute(ApplicationInterface::class)->getContainer();

        if (!$this->dispatcher) {
            $this->dispatcher = $container->get(EventDispatcherInterface::class);
        }

        if (!$this->callableResolver) {
            $this->callableResolver = $container->get(CallableResolver::class);
        }

        if (!$this->paramsResolver) {
            $this->paramsResolver = $container->get(ParameterResolver::class);
        }


        $event = new RequestEvent($this, $request);
        $event = $this->dispatcher->dispatch($event);

        if ($event->hasResponse()) {
            return $this->filterResponse($event->getResponse(), $this->getRequest());
        }

        /** @var RouteResult $result */
        $result = $event->getRequest()->getAttribute(RouteResult::class);
        $controller = $result->getMatchedRoute()->getCallback();
        $params = $result->getMatchedParams();

        $controller = $this->callableResolver->resolve($controller);

        $event = new ControllerEvent($this, $controller, $this->getRequest());
        $event = $this->dispatcher->dispatch($event);
        $controller = $event->getController();

        // controller arguments
        if ($container instanceof \DI\Container) {
            $container->set(ServerRequestInterface::class, $this->getRequest());
        } else {
            // Limitation: $request must be named "$request"
            $params = array_merge(["request" => $event->getRequest()], $params);
        }

        $callableReflection = CallableReflection::create($controller);
        $params = $this->paramsResolver->getParameters($callableReflection, $params, []);

        $event = new ControllerParamsEvent($this, $controller, $params, $this->getRequest());
        $event = $this->dispatcher->dispatch($event);
        $controller = $event->getController();
        $params = $event->getParams();

        // call controller
        $response = $controller(...$params);

        // view
        if (!$response instanceof ResponseInterface) {
            $event = new ViewEvent($this, $this->getRequest(), $response);
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

        return $this->filterResponse($response, $this->getRequest());
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

    /**
     * @inheritDoc
     */
    public function handleException(\Throwable $e, ServerRequestInterface $request): ResponseInterface
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
            return $this->filterResponse($response, $this->getRequest());
        } catch (\Exception $e) {
            return $response;
        }
    }

    /**
     *
     * @param array $callbacks
     * @return self
     */
    public function setCallbacks(array $callbacks): self
    {
        if (empty($callbacks)) {
            throw new InvalidArgumentException("Une liste de listeners doit être passer à ce Kernel");
        }

        if (! $this->dispatcher) {
            throw new RuntimeException("Aucun dispatcher d'évennement, veuillez en fournir un au constructeur");
        }

        /** @var mixed */
        $dispatcher = $this->dispatcher;
        foreach ($callbacks as $callback) {
            $dispatcher->addSubscriber($callback);
        }
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getDispatcher(): EventDispatcherInterface
    {
        return $this->dispatcher;
    }

    /**
     * @inheritDoc
     */
    public function getRequest(): ServerRequestInterface
    {
        return $this->request;
    }

    /**
     * @inheritDoc
     */
    public function setRequest(ServerRequestInterface $request): self
    {
        $this->request = $request;

        return $this;
    }
}
