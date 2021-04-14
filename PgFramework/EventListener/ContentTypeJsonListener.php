<?php

namespace PgFramework\EventListener;

use PgFramework\Event\ResponseEvent;
use PgFramework\HttpUtils\RequestUtils;

class ContentTypeJsonListener
{
    public function __invoke(ResponseEvent $event)
    {
        $request = $event->getRequest();
        if (RequestUtils::isJson($request)) {
            $response = $event->getResponse();
            $event->setResponse($response->withAddedHeader('Content-type', 'application/json;charset=UTF-8'));
        }
    }
}
