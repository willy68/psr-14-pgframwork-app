<?php

namespace PgFramework\EventListener;

use Dflydev\FigCookies\SetCookie;
use League\Event\ListenerPriority;
use PgFramework\Security\Security;
use PgFramework\Event\RequestEvent;
use PgFramework\Event\ResponseEvent;
use Grafikart\Csrf\InvalidCsrfException;
use Dflydev\FigCookies\FigRequestCookies;
use Dflydev\FigCookies\FigResponseCookies;
use PgFramework\Event\Events;
use PgFramework\Security\Csrf\CsrfTokenManagerInterface;
use PgFramework\EventDispatcher\EventSubscriberInterface;

class CsrfCookieListener implements EventSubscriberInterface
{
    protected $config = [
        'cookieName' => 'XSRF-TOKEN',
        'header' => 'X-CSRF-TOKEN',
        'session.key' => 'csrf.tokens',
        'field' => '_csrf',
        'expiry' => 0,
        'secure' => false,
        'httponly' => true,
        'samesite' => null,
    ];

    /**
     *
     * @var CsrfTokenManagerInterface
     */
    private $tokenManager;

    /**
     *
     * @param CsrfTokenManagerInterface $tokenManager
     */
    public function __construct(CsrfTokenManagerInterface $tokenManager)
    {
        $this->tokenManager = $tokenManager;
    }

    /**
     *
     * @param object $event
     * @return void
     */
    public function onRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        $method = $request->getMethod();

        $cookie = FigRequestCookies::get($request, $this->config['cookieName'])->getValue();

        if (is_string($cookie) && strlen($cookie) > 0) {
            [$tokenId] = explode(CsrfTokenManagerInterface::delimiter, $cookie);
            $cookie = $this->tokenManager->getToken($tokenId); 
            $request = $request->withAttribute($this->config['field'], $cookie);
        }

        if (\in_array($method, ['GET', 'HEAD'], true) && strlen($cookie) === 0) {
            $token = $this->getToken();
            $request = $request->withAttribute($this->config['field'], $token);
        }

        if (\in_array($method, ['DELETE', 'PATCH', 'POST', 'PUT'], true)) {
            $body = $request->getParsedBody() ?: [];
            if ((\is_array($body) || $body instanceof \ArrayAccess) && !empty($body)) {
                $token = $body[$this->config['field']];
                $this->validateToken($token, $cookie);
            } else if (!$request->hasHeader($this->config['header'])) {
                throw new InvalidCsrfException('Le cookie Csrf n\'existe pas ou est incorrect');
            } else {
                $headerCookie = $request->getHeaderLine($this->config['header']);
                $this->validateToken($headerCookie, $cookie);
            }

            [$tokenId] = explode(CsrfTokenManagerInterface::delimiter, $cookie);
            $token = $this->tokenManager->refreshToken($tokenId);
            $request = $request->withAttribute($this->config['field'], $token);
        }
        $event->setRequest($request);
    }

    /**
     *
     * @param object $event
     * @return void
     */
    public function onResponse(ResponseEvent $event)
    {
        $response = $event->getResponse();
        $request = $event->getRequest();
        $token = $request->getAttribute($this->config['field']);

        if (null !== $token) {
            $setCookie = SetCookie::create('XSRF-TOKEN')
                ->withValue($token)
                // ->withExpires(time() + 3600)
                ->withPath('/')
                ->withDomain(null)
                ->withSecure(false)
                ->withHttpOnly(false);
            $response = FigResponseCookies::set($response, $setCookie);
            $event->setResponse($response);
        }
    }

    protected function validateToken($token, $cookie)
    {
        if (!$cookie) {
            throw new InvalidCsrfException('Le cookie Csrf n\'existe pas ou est incorrect');
        }
        if (!$this->tokenManager->isTokenValid($token)) {
            throw new InvalidCsrfException('Le Csrf est incorrect');
        }
        if (!hash_equals($token, $cookie)) {
            throw new InvalidCsrfException('Le cookie Csrf est incorrect');
        }
    }

    public function getFormKey(): string
    {
        return $this->config['field'];
    }

    public function getToken(): string
    {
        $tokenId = bin2hex(Security::randomBytes(8));
        return $this->tokenManager->getToken($tokenId);
    }

    public static function getSubscribedEvents()
    {
        return [
            Events::REQUEST => ['onRequest', ListenerPriority::HIGH],
            Events::RESPONSE => ['onResponse', ListenerPriority::LOW]
        ];
    }
}
