<?php

declare(strict_types=1);

namespace PgFramework\DebugBar\EventListener;

use DebugBar\{DebugBar, DataCollector\ExceptionsCollector};
use Mezzio\{Router\RouteResult, Router\RouterInterface, Session\SessionInterface};
use PgFramework\{Event\Events,
    DebugBar\PgDebugBar,
    Event\ResponseEvent,
    Event\ExceptionEvent,
    HttpUtils\RequestUtils,
    Environnement\Environnement,
    DebugBar\DataCollector\AuthCollector,
    DebugBar\DataCollector\RouteCollector,
    DebugBar\DataCollector\RequestCollector,
    EventDispatcher\EventSubscriberInterface};

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

        $c = $event->getKernel()->getContainer();

        $this->debugBar->addCollector(
            (new RequestCollector($request, $response, $this->session))
                ->useHtmlVarDumper(true)
        );

        $routeResult = $request->getAttribute(RouteResult::class);
        $this->debugBar->addCollector(
            new RouteCollector(
                $c->get(RouterInterface::class),
                $routeResult
            )
        );

        $this->debugBar->addCollector($c->get(AuthCollector::class));

        $event->setResponse($this->debugBar->injectDebugbar($response));
    }

    public function onException(ExceptionEvent $event)
    {
        if (Environnement::getEnv('APP_ENV', 'prod') !== 'dev') {
            return;
        }

        $e = $event->getException();
        /** @var ExceptionsCollector */
        $exceptionsCollector = $this->debugBar->getCollector('exceptions');
        $exceptionsCollector->addThrowable($e);
    }

    public static function getSubscribedEvents()
    {
        return [
            Events::RESPONSE => ['onResponse', -1000],
            Events::EXCEPTION => ['onException',1000]
        ];
    }
}
