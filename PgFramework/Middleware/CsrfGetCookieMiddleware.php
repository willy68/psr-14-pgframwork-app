<?php

namespace PgFramework\Middleware;

use Dflydev\FigCookies\SetCookie;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Dflydev\FigCookies\FigRequestCookies;
use Dflydev\FigCookies\FigResponseCookies;
use PgFramework\Security\Security;
use PgFramework\Session\SessionInterface;
use Grafikart\Csrf\InvalidCsrfException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CsrfGetCookieMiddleware implements MiddlewareInterface
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
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $method = $request->getMethod();

        $cookie = FigRequestCookies::get($request, $this->config['cookieName'])->getValue();
        $this->tokenField = $cookie;

        if (is_string($cookie) && strlen($cookie) > 0) {
            $request = $request->withAttribute($this->config['field'], $cookie);
        }

        if (\in_array($method, ['GET', 'HEAD'], true) && null === $cookie) {

            $token = Security::saltToken(Security::createToken());
            $this->tokenField = $token;
            $request = $request->withAttribute($this->config['field'], $token);

            $response = $handler->handle($request);

            $setCookie = SetCookie::create('XSRF-TOKEN')
                ->withValue($token)
                // ->withExpires(time() + 3600)
                ->withPath('/')
                ->withDomain(null)
                ->withSecure(false)
                ->withHttpOnly(false);
            return FigResponseCookies::set($response, $setCookie);
        }

        if (\in_array($method, ['DELETE', 'PATCH', 'POST', 'PUT'], true)) {
            $body = $request->getParsedBody() ?: [];
            if ((\is_array($body) || $body instanceof \ArrayAccess) && !empty($body)) {
                $token = $body[$this->config['field']];
                $this->validateToken($token, $cookie);
                return $handler->handle($request);
            }

            if (!$request->hasHeader($this->config['header'])) {
                throw new InvalidCsrfException('Le cookie Csrf n\'existe pas ou est incorrect');
            }
            $headerCookie = $request->getHeaderLine($this->config['header']);
            $this->validateToken($headerCookie, $cookie);
        }

        $this->session[$this->config['session.key']] = $this->tokenField;
        return $handler->handle($request);
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
