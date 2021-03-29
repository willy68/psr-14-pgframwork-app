<?php

namespace Framework\EventListener;

use League\Event\Listener;
use Framework\Event\RequestEvent;

class ActiveRecordListener implements Listener
{

    public function __invoke(object $event): void
    {
        /** @var RequestEvent $event */
        $event->getApp()->getContainer()->get('ActiveRecord');
    }
}
