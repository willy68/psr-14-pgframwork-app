<?php

declare(strict_types=1);

namespace PgFramework\EventListener;

use League\Event\ListenerPriority;
use PgFramework\Event\RequestEvent;
use Grafikart\Csrf\InvalidCsrfException;
use PgFramework\Event\Events;
use PgFramework\Security\Csrf\CsrfTokenManagerInterface;
use PgFramework\EventDispatcher\EventSubscriberInterface;
use function in_array;

class CsrfListener implements EventSubscriberInterface
{
    private string $formKey;

    private CsrfTokenManagerInterface $tokenManager;

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
     * @param RequestEvent $event
     * @return void
     * @throws InvalidCsrfException
     */
    public function __invoke(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $method = $request->getMethod();

        if (in_array($method, ['DELETE', 'PATCH', 'POST', 'PUT'], true)) {
            $params = $request->getParsedBody() ?: [];
            if (!array_key_exists($this->formKey, $params)) {
                throw new InvalidCsrfException();
            }

            if (!$this->tokenManager->isTokenValid($params[$this->formKey])) {
                throw new InvalidCsrfException();
            }

            [$tokenId] = explode(CsrfTokenManagerInterface::DELIMITER, $params[$this->formKey]);
            $this->tokenManager->removeToken($tokenId);
        }
    }

    public function getFormKey(): string
    {
        return $this->formKey;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::REQUEST => ListenerPriority::HIGH
        ];
    }
}
