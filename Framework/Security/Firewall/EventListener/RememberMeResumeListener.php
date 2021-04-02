<?php

namespace Framework\Security\Firewall\EventListener;

use Framework\Auth\RememberMe\RememberMeInterface;
use Framework\Event\ResponseEvent;

class RememberMeResumeListener
{

    /**
     *
     *
     * @var RememberMeInterface
     */
    private $cookie;

    public function __construct(RememberMeInterface $cookie)
    {
        $this->cookie = $cookie;
    }

    public function onResponseEvent(ResponseEvent $event)
    {
        $request = $event->getRequest();
        $response = $event->getResponse();
        $event->setResponse($this->cookie->resume($request, $response));
    }
}
