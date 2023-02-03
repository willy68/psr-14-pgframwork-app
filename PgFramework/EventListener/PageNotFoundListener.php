<?php

declare(strict_types=1);

namespace PgFramework\EventListener;

use DebugBar\DebugBar;
use GuzzleHttp\Psr7\Response;
use PgFramework\Event\Events;
use PgFramework\Event\ExceptionEvent;
use PgFramework\Renderer\RendererInterface;
use PgFramework\Router\Exception\PageNotFoundException;
use PgFramework\EventDispatcher\EventSubscriberInterface;

class PageNotFoundListener implements EventSubscriberInterface
{
    /**
     *
     * @var RendererInterface
     */
    private $renderer;

    public function __construct(RendererInterface $renderer)
    {
        $this->renderer = $renderer;
    }

    public function __invoke(ExceptionEvent $event): void
    {
        $e = $event->getException();
        if ($e instanceof PageNotFoundException) {
            $c = $event->getKernel()->getContainer();
            $appEnv = $c->get('env');
            if ($appEnv === 'dev') {
                $debugBar = $c->get(DebugBar::class);
                $exceptionsCollector = $debugBar->getCollector('exceptions');
                $exceptionsCollector->addThrowable($e);
            }
            $event->setResponse(new Response(404, [], $this->renderer->render('error404')));
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            Events::EXCEPTION => 500
        ];
    }
}
