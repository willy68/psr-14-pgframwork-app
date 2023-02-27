<?php

declare(strict_types=1);

namespace PgFramework\EventListener;

use PgFramework\Event\Events;
use PgFramework\Event\RequestEvent;
use PgFramework\ApplicationInterface;
use PgFramework\EventDispatcher\EventSubscriberInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class ActiveRecordListener implements EventSubscriberInterface
{
    /**
     * @param RequestEvent $event
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(RequestEvent $event): void
    {
        $request = $event->getRequest();
        /** @var ApplicationInterface $app*/
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
