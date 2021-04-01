<?php

namespace Framework\EventListener;

use GuzzleHttp\Psr7\Response;
use Framework\Event\ViewEvent;

class StringResponseListener
{
    public function onView(ViewEvent $event)
    {
        $result = $event->getResult();

        if (is_string($result)) {
            $event->setResponse(new Response(200, [], $result));
        }
    }
}
