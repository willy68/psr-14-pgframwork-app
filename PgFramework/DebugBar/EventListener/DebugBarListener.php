<?php

declare(strict_types=1);

namespace PgFramework\DebugBar\EventListener;

use DebugBar\DebugBar;
use PgFramework\Event\Events;
use Mezzio\Router\RouteResult;
use Mezzio\Router\RouterInterface;
use PgFramework\ApplicationInterface;
use PgFramework\DebugBar\DataCollector\AuthCollector;
use PgFramework\DebugBar\PgDebugBar;
use PgFramework\Event\ResponseEvent;
use PgFramework\HttpUtils\RequestUtils;
use Mezzio\Session\SessionInterface;
use PgFramework\DebugBar\DataCollector\RequestCollector;
use PgFramework\DebugBar\DataCollector\RouteCollector;
use PgFramework\Environnement\Environnement;
use PgFramework\EventDispatcher\EventSubscriberInterface;

class DebugBarListener implements EventSubscriberInterface
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

    public function onResponse(ResponseEvent $event)
    {
        if (Environnement::getEnv('APP_ENV', 'prod') !== 'dev') {
            return;
        }

        $response = $event->getResponse();
        $request = $event->getRequest();

        if (RequestUtils::isAjax($request)) {
            return;
        }

        /** @var ApplicationInterface */
        $app = $request->getAttribute(ApplicationInterface::class);

        $this->debugBar->addCollector(
            (new RequestCollector($request, $response, $this->session))
                ->useHtmlVarDumper(true)
        );

        $routeResult = $request->getAttribute(RouteResult::class);
        $this->debugBar->addCollector(
            new RouteCollector(
                $app->getContainer()->get(RouterInterface::class),
                $routeResult
            )
        );

        $this->debugBar->addCollector($app->getContainer()->get(AuthCollector::class));

        $event->setResponse($this->debugBar->injectDebugbar($response));
    }

    public static function getSubscribedEvents()
    {
        return [
            Events::RESPONSE => ['onResponse', -1000]
        ];
    }
}
