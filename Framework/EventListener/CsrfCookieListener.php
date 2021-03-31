<?php

namespace Framework\EventListener;

use Framework\Security\Security;
use Dflydev\FigCookies\SetCookie;
use Framework\Event\RequestEvent;
use Framework\Event\ResponseEvent;
use Psr\Http\Message\ResponseInterface;
use Grafikart\Csrf\InvalidCsrfException;
use Dflydev\FigCookies\FigRequestCookies;
use Dflydev\FigCookies\FigResponseCookies;
use Framework\Security\Csrf\CsrfTokenManagerInterface;
use Psr\Http\Message\ServerRequestInterface;

class CsrfCookieListener
{
    protected $config = [
        'cookieName' => 'XSRF-TOKEN',
        'header' => 'X-CSRF-TOKEN',
        'session.key' => 'csrf.tokens',
        'field' => '_csrf',
        'expiry' => 0,
        'secure' => false,
        'httponly' => false,
        'samesite' => null,
    ];

    /**
     *
     * @var string
     */
    protected $tokenId;

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
    public function __construct(CsrfTokenManagerInterface $tokenManager)
    {
        $this->tokenManager = $tokenManager;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function onRequestEvent(object $event)
    {
        /** @var RequestEvent $event */
        /** @var ServerRequestInterface $request */
        $request = $event->getRequest();
        $method = $request->getMethod();

        $cookie = FigRequestCookies::get($request, $this->config['cookieName'])->getValue();

        if (is_string($cookie) && strlen($cookie) > 0) {
            $request = $request->withAttribute($this->config['field'], $cookie);
            $event->setRequest($request);
            [$this->tokenId, ] = explode(CsrfTokenManagerInterface::delimiter, $cookie);
        }

        if (\in_array($method, ['GET', 'HEAD'], true) && strlen($cookie) === 0) {
            $token = $this->generateToken();
            $request = $request->withAttribute($this->config['field'], $token);
            $event->setRequest($request);
        }

        if (\in_array($method, ['DELETE', 'PATCH', 'POST', 'PUT'], true)) {
            $body = $request->getParsedBody() ?: [];
            if ((\is_array($body) || $body instanceof \ArrayAccess) && !empty($body)) {
                $token = $body[$this->config['field']];
                $this->validateToken($token, $cookie);
            }

            else if (!$request->hasHeader($this->config['header'])) {
                throw new InvalidCsrfException('Le cookie Csrf n\'existe pas ou est incorrect');
            } else {
                $headerCookie = $request->getHeaderLine($this->config['header']);
                $this->validateToken($headerCookie, $cookie);
            }

            [$this->tokenId, ] = explode(CsrfTokenManagerInterface::delimiter, $cookie);
            $token = $this->tokenManager->refreshToken($this->tokenId);
            $request = $request->withAttribute($this->config['field'], $token);
            $event->setRequest($request);
        }

    }

    public function onResponseEvent(object $event)
    {
        /** @var ResponseEvent $event */
        /** @var ResponseInterface $response */
        $response = $event->getResponse();
        /** @var ServerRequestInterface $request */
        $request = $event->getRequest();
        $token = $this->tokenManager->getToken($this->tokenId);
        
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

    protected function validateToken($token, $cookie)
    {
        if (!$cookie) {
            throw new InvalidCsrfException('Le cookie Csrf n\'existe pas ou est incorrect');
        }
        $this->tokenManager->isTokenValid($token);
    }

    public function getFormKey(): string
    {
        return $this->config['field'];
    }

    public function generateToken(): string
    {
        $this->tokenId = bin2hex(Security::randomBytes(8));
        return $this->tokenManager->getToken($this->tokenId);
    }

    public function getToken(): string
    {
        if (null === $this->tokenId) {
            $token = $this->generateToken();
        }
        return $this->tokenManager->getToken($this->tokenId); 
    }
}
