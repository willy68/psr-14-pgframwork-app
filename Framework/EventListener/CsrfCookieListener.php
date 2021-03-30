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
use Framework\Session\SessionInterface;
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
     * @var array|\ArrayAccess
     */
    private $session;

    protected $tokenField;

    /**
     * CsrfMiddleware constructor.
     *
     * @param array|\ArrayAccess $session
     * @param int                $limit      Limit the number of token to store in the session
     * @param string             $sessionKey
     * @param string             $formKey
     */
    public function __construct(
        SessionInterface &$session,
        string $sessionKey = 'csrf.tokens'
    ) {
        $this->testSession($session);
        $this->session = &$session;
        $this->config['session.key'] = $sessionKey;
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
        $this->tokenField = $cookie;

        if (is_string($cookie) && strlen($cookie) > 0) {
            $request = $request->withAttribute($this->config['field'], $cookie);
            $event->setRequest($request);
        }

        if (\in_array($method, ['GET', 'HEAD'], true) && null === $cookie) {

            $token = Security::saltToken(Security::createToken());
            $this->tokenField = $token;
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

        }
        $this->session[$this->config['session.key']] = $this->tokenField;

    }

    public function onResponseEvent(object $event)
    {
        /** @var ResponseEvent $event */
        /** @var ResponseInterface $response */
        $response = $event->getResponse();
        
        $setCookie = SetCookie::create('XSRF-TOKEN')
            ->withValue($this->tokenField)
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

        $cookie = Security::unsaltToken($cookie);
        if (!Security::verifyToken($cookie)) {
            throw new InvalidCsrfException('Le cookie Csrf est incorrect');
        }

        $csrfField = Security::unsaltToken($token);
        if (!hash_equals($csrfField, $cookie)) {
            throw new InvalidCsrfException('Le cookie Csrf est incorrect');
        }
    }

    /**
     * Test if the session acts as an array.
     *
     * @param $session
     *
     * @throws \TypeError
     */
    private function testSession($session): void
    {
        if (!\is_array($session) && !$session instanceof \ArrayAccess) {
            throw new \TypeError('session is not an array');
        }
    }

    public function getFormKey(): string
    {
        return $this->config['field'];
    }

    public function generateToken(): string
    {
        return $this->session[$this->config['session.key']];
    }
}
