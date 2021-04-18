<?php

namespace PgFramework\EventListener;

use PgFramework\Event\RequestEvent;

class ActiveRecordListener
{

    public function __invoke(RequestEvent $event): void
    {
        $event->getApp()->getContainer()->get('ActiveRecord');
    }
}
