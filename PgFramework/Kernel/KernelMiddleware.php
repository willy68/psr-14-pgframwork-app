<?php

declare(strict_types=1);

namespace PgFramework\Kernel;

use Exception;
use Throwable;
use InvalidArgumentException;
use PgFramework\Middleware\CombinedMiddleware;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
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

    private $index = 0;

    public function __construct(ContainerInterface $c)
    {
        $this->container = $c;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->request = $request;

        $this->index++;
        if ($this->index > 1) {
            throw new Exception('Aucun middleware n\'a intercepté cette requête');
        }

        return (new CombinedMiddleware($this->container, (array)$this->getMiddlewareStack(), $this))
            ->process($request, $this);
    }

    public function handleException(Throwable $e, ServerRequestInterface $request): ResponseInterface
    {
        throw $e;
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
        return $this->lazyPipe($this->getContainer(), $routePrefix, $middleware);
    }

    /**
     *
     * @param string[]|MiddlewareInterface[]|callable[]
     * @return self
     */
    public function setCallbacks(array $callbacks): self
    {
        if (empty($callbacks)) {
            throw new InvalidArgumentException("Une liste de middlewares doit être passer à ce Kernel");
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
