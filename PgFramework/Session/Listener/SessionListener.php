<?php

namespace PgFramework\Session\Listener;

use PgFramework\Event\Events;
use League\Event\ListenerPriority;
use Mezzio\Session\SessionInterface;
use PgFramework\Event\RequestEvent;
use PgFramework\Event\ResponseEvent;
use Mezzio\Session\SessionPersistenceInterface;
use PgFramework\EventDispatcher\EventSubscriberInterface;

class SessionListener implements EventSubscriberInterface
{
    public const SESSION_ATTRIBUTE = '_session';

    private $persistence;

    private $session;

    public function __construct(SessionPersistenceInterface $persistence, SessionInterface $session)
    {
        $this->persistence = $persistence;
        $this->session = $session;
    }

    public function onRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        $event->setRequest(
            $request
                ->withAttribute(self::SESSION_ATTRIBUTE, $this->session)
                ->withAttribute(SessionInterface::class, $this->session)
        );
    }

    public function onResponse(ResponseEvent $event)
    {
        $response = $event->getResponse();
        $event->setResponse($this->persistence->persistSession($this->session, $response));
    }

    public static function getSubscribedEvents()
    {
        return [
            Events::REQUEST => ['onRequest', 1000],
            Events::RESPONSE => ['onResponse', ListenerPriority::LOW],
        ];
    }
}
