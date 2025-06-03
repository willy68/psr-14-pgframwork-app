<?php

declare(strict_types=1);

namespace PgFramework\DebugBar\Middleware;

use DebugBar\DebugBar;
use DebugBar\DebugBarException;
use Pg\Router\RouteResult;
use Pg\Router\RouterInterface;
use PgFramework\DebugBar\PgDebugBar;
use PgFramework\ApplicationInterface;
use PgFramework\HttpUtils\RequestUtils;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Mezzio\Session\SessionInterface;
use PgFramework\Environnement\Environnement;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use PgFramework\DebugBar\DataCollector\RouteCollector;
use PgFramework\DebugBar\DataCollector\RequestCollector;

class DebugBarMiddleware implements MiddlewareInterface
{
    protected PgDebugBar|DebugBar $debugBar;
    protected SessionInterface $session;

    public function __construct(DebugBar $debugBar, SessionInterface $session)
    {
        $this->debugBar = $debugBar;
        $this->session = $session;
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     * @throws DebugBarException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (Environnement::getEnv('APP_ENV', 'prod') !== 'dev' || RequestUtils::isAjax($request)) {
            return $handler->handle($request);
        }

        $response = $handler->handle($request);

        /** @var ApplicationInterface $app*/
        $app = $request->getAttribute(ApplicationInterface::class);

        $this->debugBar->addCollector(
            (new RequestCollector($request, $response, $app->getContainer()->get(SessionInterface::class)))
                ->useHtmlVarDumper()
        );

        $routeResult = $request->getAttribute(RouteResult::class);
        $this->debugBar->addCollector(
            new RouteCollector(
                $app->getContainer()->get(RouterInterface::class),
                $routeResult
            )
        );

        return $this->debugBar->injectDebugbar($response);
    }
}
