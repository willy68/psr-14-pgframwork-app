<?php

declare(strict_types=1);

namespace PgFramework\EventListener;

use GuzzleHttp\Psr7\Response;
use PgFramework\Event\Events;
use PgFramework\Event\ExceptionEvent;
use PgFramework\HttpUtils\RequestUtils;
use PgFramework\Renderer\RendererInterface;
use PgFramework\Response\JsonResponse;
use PgFramework\Router\Exception\PageNotFoundException;
use PgFramework\EventDispatcher\EventSubscriberInterface;

class PageNotFoundListener implements EventSubscriberInterface
{
    /**
     *
     * @var RendererInterface
     */
    private RendererInterface $renderer;

    public function __construct(RendererInterface $renderer)
    {
        $this->renderer = $renderer;
    }

    public function __invoke(ExceptionEvent $event): void
    {
        $e = $event->getException();
        if ($e instanceof PageNotFoundException) {
            if (RequestUtils::isJson($event->getRequest()) || RequestUtils::wantJson($event->getRequest())) {
                $event->setResponse(new JsonResponse(404, json_encode($e->getMessage())));
                return;
            }
            $event->setResponse(new Response(404, [], $this->renderer->render('error404')));
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::EXCEPTION => 500
        ];
    }
}
