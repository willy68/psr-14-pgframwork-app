<?php

namespace PgFramework\DebugBar\Middleware;

use DebugBar\DebugBar;
use Mezzio\Router\RouteResult;
use Mezzio\Router\RouterInterface;
use PgFramework\DebugBar\PgDebugBar;
use PgFramework\ApplicationInterface;
use PgFramework\HttpUtils\RequestUtils;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use PgFramework\Session\SessionInterface;
use PgFramework\Environnement\Environnement;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use PgFramework\DebugBar\DataCollector\RouteCollector;
use PgFramework\DebugBar\DataCollector\RequestCollector;

class DebugBarMiddleware implements MiddlewareInterface
{
    /**
     * @var PgDebugBar
     */
    protected $debugBar;

    /**
     * @var SessionInterface
     */
    protected $session;

    public function __construct(DebugBar $debugBar, SessionInterface $session)
    {
        $this->debugBar = $debugBar;
        $this->session = $session;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (Environnement::getEnv('APP_ENV', 'prod') !== 'dev' || RequestUtils::isAjax($request)) {
            return $handler->handle($request);
        }

        $response = $handler->handle($request);

        /** @var ApplicationInterface */
        $app = $request->getAttribute(ApplicationInterface::class);

        $this->debugBar->addCollector(
            (new RequestCollector($request, $response, $app->getContainer()->get(SessionInterface::class)))
                ->useHtmlVarDumper(true)
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
