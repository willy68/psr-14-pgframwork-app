<?php

declare(strict_types=1);

namespace PgFramework\EventListener;

use GuzzleHttp\Psr7\Response;
use League\Event\ListenerPriority;
use PgFramework\Event\ViewEvent;
use PgFramework\EventDispatcher\EventSubscriberInterface;

class StringResponseListener implements EventSubscriberInterface
{
    public function __invoke(ViewEvent $event)
    {
        $result = $event->getResult();

        if (is_string($result)) {
            $event->setResponse(new Response(200, [], $result));
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ViewEvent::class => ListenerPriority::HIGH
        ];
    }
}
