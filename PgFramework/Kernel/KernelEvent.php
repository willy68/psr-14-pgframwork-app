<?php

namespace PgFramework\Kernel;

use Exception;
use Mezzio\Router\RouteResult;
use PgFramework\Event\ViewEvent;
use PgFramework\Event\RequestEvent;
use PgFramework\Event\ResponseEvent;
use PgFramework\Event\ExceptionEvent;
use PgFramework\Event\ControllerEvent;
use Psr\Http\Message\ResponseInterface;
use PgFramework\Event\ControllerParamsEvent;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

class KernelEvent implements KernelInterface
{
    protected $request;

    protected $dispatcher;

    protected $container;

    public function __construct(ContainerInterface $container, EventDispatcherInterface $dispatcher)
    {
        $this->container = $container;
        $this->dispatcher = $dispatcher;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->request = $request;

        $event = new RequestEvent($this, $request);
        $event = $this->dispatcher->dispatch($event);

        if ($event->hasResponse()) {
            return $this->filterResponse($event->getResponse(), $event->getRequest());
        }

        /** @var RouteResult $result */
        $result = $event->getRequest()->getAttribute(RouteResult::class);
        $controller = $result->getMatchedRoute()->getCallback();
        $params = $result->getMatchedParams();

        $event = new ControllerEvent($this, $controller, $event->getRequest());
        $event = $this->dispatcher->dispatch($event);
        $controller = $event->getController();

        // controller arguments
        if ($this->container instanceof \DI\Container) {
            $this->container->set(ServerRequestInterface::class, $event->getRequest());
        } else {
            // Limitation: $request must be named "$request"
            $params = array_merge(["request" => $event->getRequest()], $params);
        };

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
            return $this->filterResponse($response, $event->getRequest());
        } catch (\Exception $e) {
            return $response;
        }
    }

    public function getContainer(): ContainerInterface
    {
        return $this->container;
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
}
