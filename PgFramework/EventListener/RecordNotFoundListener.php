<?php

declare(strict_types=1);

namespace PgFramework\EventListener;

use GuzzleHttp\Psr7\Response;
use PgFramework\Event\Events;
use League\Event\ListenerPriority;
use PgFramework\Event\ExceptionEvent;
use PgFramework\HttpUtils\RequestUtils;
use ActiveRecord\Exceptions\RecordNotFound;
use PgFramework\Database\NoRecordException;
use PgFramework\Renderer\RendererInterface;
use PgFramework\EventDispatcher\EventSubscriberInterface;
use PgFramework\Response\JsonResponse;

class RecordNotFoundListener implements EventSubscriberInterface
{
    /**
     * Renderer de vue
     *
     * @var RendererInterface
     */
    private RendererInterface $renderer;

    /**
     *
     * @param RendererInterface $renderer
     */
    public function __construct(RendererInterface $renderer)
    {
        $this->renderer = $renderer;
    }

    public function __invoke(ExceptionEvent $event)
    {
        $e = $event->getException();
        if ($e instanceof RecordNotFound || $e instanceof NoRecordException) {
            if (RequestUtils::isJson($event->getRequest()) || RequestUtils::wantJson($event->getRequest())) {
                $event->setResponse(new JsonResponse(404, json_encode($e->getMessage())));
                return;
            }
            $event->setResponse(new Response(404, [], $this->renderer->render(
                'error404',
                ['message' => $e->getMessage()]
            )));
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::EXCEPTION => ListenerPriority::HIGH
        ];
    }
}
