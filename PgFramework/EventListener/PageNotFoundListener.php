<?php

namespace PgFramework\EventListener;

use GuzzleHttp\Psr7\Response;
use PgFramework\Event\Events;
use League\Event\ListenerPriority;
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

    /**
     *
     * @param RendererInterface $renderer
     */
    public function __construct(RendererInterface $renderer)
    {
        $this->renderer = $renderer;
    }

    public function __invoke(ExceptionEvent $event): void
    {
        $e = $event->getException();
        if ($e instanceof PageNotFoundException) {
            $event->setResponse(new Response(404, [], $this->renderer->render('error404')));
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            Events::EXCEPTION => ListenerPriority::HIGH
        ];
    }
}
