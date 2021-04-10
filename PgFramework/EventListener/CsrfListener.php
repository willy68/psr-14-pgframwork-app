<?php

namespace PgFramework\EventListener;

use PgFramework\Event\RequestEvent;
use PgFramework\Security\Security;
use Grafikart\Csrf\NoCsrfException;
use Grafikart\Csrf\InvalidCsrfException;
use PgFramework\Security\Csrf\CsrfTokenManagerInterface;

class CsrfListener
{
    /**
     * @var string
     */
    private $sessionKey;

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
     * CsrfMiddleware constructor.
     *
     * @param array|\ArrayAccess $session
     * @param int                $limit      Limit the number of token to store in the session
     * @param string             $sessionKey
     * @param string             $formKey
     */
    public function __construct(
        CsrfTokenManagerInterface $tokenManager,
        string $sessionKey = 'csrf.tokens',
        string $formKey = '_csrf'
    )
    {
        $this->tokenManager = $tokenManager;
        $this->sessionKey = $sessionKey;
        $this->formKey = $formKey;
    }

    /**
     * 
     *
     * @param object $event
     * @return void
     */
    public function onRequestEvent(RequestEvent $event)
    {
        $request = $event->getRequest();
        $method = $request->getMethod();

        if (\in_array($method, ['DELETE', 'PATCH', 'POST', 'PUT'], true)) {
            $params = $request->getParsedBody() ?: [];
            if (!array_key_exists($this->formKey, $params)) {
                throw new NoCsrfException();
            }

            if (!$this->tokenManager->isTokenValid($params[$this->formKey])) {
                throw new InvalidCsrfException();
            }

            [$tokenId, ] = explode(CsrfTokenManagerInterface::delimiter, $params[$this->formKey]);
            $this->tokenManager->removeToken($tokenId);
        }
    }

    /**
     * @return string
     */
    public function getSessionKey(): string
    {
        return $this->sessionKey;
    }

    /**
     * @return string
     */
    public function getFormKey(): string
    {
        return $this->formKey;
    }

    public function getToken(): string
    {
        $tokenId = bin2hex(Security::randomBytes(8));
        return $this->tokenManager->getToken($tokenId);
    }
}
