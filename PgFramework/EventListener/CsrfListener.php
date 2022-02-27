<?php

namespace PgFramework\EventListener;

use League\Event\ListenerPriority;
use PgFramework\Event\RequestEvent;
use Grafikart\Csrf\InvalidCsrfException;
use PgFramework\Event\Events;
use PgFramework\Security\Csrf\CsrfTokenManagerInterface;
use PgFramework\EventDispatcher\EventSubscriberInterface;

class CsrfListener implements EventSubscriberInterface
{
    /**
     * @var string
     */
    private $formKey;

    /**
     *
     * @var CsrfTokenManagerInterface
     */
    private $tokenManager;

    /**
     * @param CsrfTokenManagerInterface $tokenManager
     * @param string             $formKey
     */
    public function __construct(
        CsrfTokenManagerInterface $tokenManager,
        string $formKey = '_csrf'
    ) {
        $this->tokenManager = $tokenManager;
        $this->formKey = $formKey;
    }

    /**
     *
     *
     * @param object $event
     * @return void
     */
    public function __invoke(RequestEvent $event)
    {
        $request = $event->getRequest();
        $method = $request->getMethod();

        if (\in_array($method, ['DELETE', 'PATCH', 'POST', 'PUT'], true)) {
            $params = $request->getParsedBody() ?: [];
            if (!array_key_exists($this->formKey, $params)) {
                throw new InvalidCsrfException();
            }

            if (!$this->tokenManager->isTokenValid($params[$this->formKey])) {
                throw new InvalidCsrfException();
            }

            [$tokenId] = explode(CsrfTokenManagerInterface::delimiter, $params[$this->formKey]);
            $this->tokenManager->removeToken($tokenId);
        }
    }

    /**
     * @return string
     */
    public function getFormKey(): string
    {
        return $this->formKey;
    }

    public static function getSubscribedEvents()
    {
        return [
            Events::REQUEST => ListenerPriority::HIGH
        ];
    }
}
