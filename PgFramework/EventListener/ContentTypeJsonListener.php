<?php

namespace PgFramework\EventListener;

use PgFramework\Event\ResponseEvent;

class ContentTypeJsonListener
{
    public function __invoke(ResponseEvent $event)
    {
        $response = $event->getResponse();
        $event->setResponse($response->withAddedHeader('Content-type', 'application/json;charset=UTF-8'));
    }
}
