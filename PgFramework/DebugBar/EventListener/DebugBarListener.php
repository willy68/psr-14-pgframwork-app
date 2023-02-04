<?php

declare(strict_types=1);

namespace PgFramework\DebugBar\EventListener;

use DebugBar\DebugBar;
use PgFramework\Event\Events;
use Mezzio\Router\RouteResult;
use Mezzio\Router\RouterInterface;
use Mezzio\Session\SessionInterface;
use PgFramework\DebugBar\PgDebugBar;
use PgFramework\Event\ResponseEvent;
use PgFramework\Event\ExceptionEvent;
use PgFramework\HttpUtils\RequestUtils;
use PgFramework\Environnement\Environnement;
use DebugBar\DataCollector\ExceptionsCollector;
use PgFramework\DebugBar\DataCollector\AuthCollector;
use PgFramework\DebugBar\DataCollector\RouteCollector;
use PgFramework\DebugBar\DataCollector\RequestCollector;
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
