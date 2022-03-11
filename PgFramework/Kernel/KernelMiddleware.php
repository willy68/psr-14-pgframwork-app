<?php

namespace PgFramework\Kernel;

use Exception;
use Throwable;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use PgFramework\Middleware\Stack\MiddlewareAwareStackTrait;

class KernelMiddleware implements KernelInterface, RequestHandlerInterface
{
    use MiddlewareAwareStackTrait;

    /**
     * Actual Request
     *
     * @var ServerRequestInterface
     */
    protected $request;

    /**
     * Injection Container
     *
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $c)
    {
        $this->container = $c;
    }

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

    public function handleException(Throwable $e, ServerRequestInterface $request): ResponseInterface
    {
        return $this->handle($request);
    }

    /**
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
     *
     * @param string[]|MiddlewareInterface[]|callable[]
     * @return self
     */
    public function setCallbacks(array $callbacks): self
    {
        if (empty($callbacks)) {
            throw new InvalidArgumentException("Une liste de listeners doit être passer à ce Kernel");
        }

        $this->middlewares($callbacks);
        return $this;
    }

    /**
     *
     * @return object
     * @throws Exception
     */
    private function getMiddleware()
    {
        return $this->shiftMiddleware($this->getContainer());
    }

    public function getContainer(): ContainerInterface
    {
        return $this->container;
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
