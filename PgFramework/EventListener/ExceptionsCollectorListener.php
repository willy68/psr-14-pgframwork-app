<?php

namespace PgFramework\EventListener;

use DebugBar\DataCollector\ExceptionsCollector;
use DebugBar\DebugBar;
use PgFramework\Event\Events;
use PgFramework\Event\ExceptionEvent;
use PgFramework\EventDispatcher\EventSubscriberInterface;

class ExceptionsCollectorListener implements EventSubscriberInterface
{
    private $debugBar;

    public function __construct(DebugBar $debugBar)
    {
        $this->debugBar = $debugBar;
    }

    public function __invoke(ExceptionEvent $event)
    {
        $c = $event->getKernel()->getContainer();
        $appEnv = $c->get('env');
        if ($appEnv !== 'dev') {
            return;
        }

        $e = $event->getException();
        /** @var ExceptionsCollector */
        $exceptionsCollector = $this->debugBar->getCollector('exceptions');
        $exceptionsCollector->addThrowable($e);
    }

    public static function getSubscribedEvents()
    {
        return [
            Events::EXCEPTION => 1000
        ];
    }
}
