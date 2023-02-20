<?php

declare(strict_types=1);

namespace PgFramework\EventListener;

use PgFramework\Event\Events;
use PgFramework\Event\RequestEvent;
use PgFramework\ApplicationInterface;
use PgFramework\EventDispatcher\EventSubscriberInterface;

class ActiveRecordListener implements EventSubscriberInterface
{
    public function __invoke(RequestEvent $event): void
    {
        $request = $event->getRequest();
        /** @var ApplicationInterface */
        $app = $request->getAttribute(ApplicationInterface::class);
        $app->getContainer()->get('ActiveRecord');
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::REQUEST => 500
        ];
    }
}
