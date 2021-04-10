<?php

namespace PgFramework\EventListener;

use GuzzleHttp\Psr7\Response;
use PgFramework\Event\ViewEvent;

class StringResponseListener
{
    public function __invoke(ViewEvent $event)
    {
        $result = $event->getResult();

        if (is_string($result)) {
            $event->setResponse(new Response(200, [], $result));
        }
    }
}
