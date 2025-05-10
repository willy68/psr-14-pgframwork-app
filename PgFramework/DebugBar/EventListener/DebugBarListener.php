<?php

declare(strict_types=1);

namespace PgFramework\DebugBar\EventListener;

use DebugBar\{DataCollector\ExceptionsCollector, DebugBar, DebugBarException};
use Pg\Router\RouteResult;
use Pg\Router\RouterInterface;
use PgFramework\DebugBar\PgDebugBar;
use PgFramework\Response\JsonResponse;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Mezzio\Session\SessionInterface;
use PgFramework\DebugBar\DataCollector\AuthCollector;
use PgFramework\DebugBar\DataCollector\RequestCollector;
use PgFramework\DebugBar\DataCollector\RouteCollector;
use PgFramework\Environnement\Environnement;
use PgFramework\Event\Events;
use PgFramework\Event\ExceptionEvent;
use PgFramework\Event\ResponseEvent;
use PgFramework\EventDispatcher\EventSubscriberInterface;
use PgFramework\HttpUtils\RequestUtils;

class DebugBarListener implements EventSubscriberInterface
{
    protected PgDebugBar|DebugBar $debugBar;

    protected SessionInterface $session;

    public function __construct(DebugBar $debugBar, SessionInterface $session)
    {
        $this->debugBar = $debugBar;
        $this->session = $session;
    }

    /**
     * @param ResponseEvent $event
     * @return void
     * @throws DebugBarException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function onResponse(ResponseEvent $event): void
    {
        if (Environnement::getEnv('APP_ENV', 'prod') !== 'dev') {
            return;
        }

        $response = $event->getResponse();
        $request = $event->getRequest();

        if (RequestUtils::isAjax($request)) {
            return;
        }

        if ($response instanceof JsonResponse) {
            return;
        }

        if (1 === preg_match('{^application/(?:\w+\++)*json}i', $response->getHeaderLine('content-type'))) {
            return;
        }

        $c = $event->getKernel()->getContainer();

        $this->debugBar->addCollector(
            (new RequestCollector($request, $response, $this->session))
                ->useHtmlVarDumper()
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

    /**
     * @param ExceptionEvent $event
     * @return void
     * @throws DebugBarException
     */
    public function onException(ExceptionEvent $event): void
    {
        if (Environnement::getEnv('APP_ENV', 'prod') !== 'dev') {
            return;
        }

        $e = $event->getException();
        /** @var ExceptionsCollector $exceptionsCollector*/
        $exceptionsCollector = $this->debugBar->getCollector('exceptions');
        $exceptionsCollector->addThrowable($e);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::RESPONSE => ['onResponse', -1000],
            Events::EXCEPTION => ['onException',1000]
        ];
    }
}
