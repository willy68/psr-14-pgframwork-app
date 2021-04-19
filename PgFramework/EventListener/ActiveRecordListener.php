<?php

namespace PgFramework\EventListener;

use League\Event\ListenerPriority;
use PgFramework\Event\RequestEvent;
use PgFramework\EventDispatcher\EventSubscriberInterface;

class ActiveRecordListener implements EventSubscriberInterface
{

    public function __invoke(RequestEvent $event): void
    {
        $event->getApp()->getContainer()->get('ActiveRecord');
    }

    public static function getSubscribedEvents()
    {
        return [
            RequestEvent::class => ListenerPriority::HIGH
        ];
    }
}
