<?php

declare(strict_types=1);

namespace PgFramework\Middleware;

use Dflydev\FigCookies\SetCookie;
use Psr\Http\Message\ResponseInterface;
use Grafikart\Csrf\InvalidCsrfException;
use Psr\Http\Server\MiddlewareInterface;
use Dflydev\FigCookies\FigRequestCookies;
use Dflydev\FigCookies\FigResponseCookies;
use PgFramework\Response\ResponseRedirect;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use PgFramework\Security\Csrf\CsrfTokenManagerInterface;

class CsrfCookieMiddleware implements MiddlewareInterface
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

    public function __construct(CsrfTokenManagerInterface $tokenManager, $config = [])
    {
        $this->tokenManager = $tokenManager;
        if (!empty($config)) {
            $this->config = array_merge($this->config, $config);
        }
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

        if (\in_array($method, ['GET', 'HEAD'], true)) {
            if (null === $cookie || !$this->tokenManager->isTokenValid($cookie)) {
                $token = $this->tokenManager->getToken();
                return FigResponseCookies::set(
                    new ResponseRedirect($request->getUri()->getPath()),
                    $this->createCookie($token)
                );
            }
        }

        if (\in_array($method, ['DELETE', 'PATCH', 'POST', 'PUT'], true)) {
            $body = $request->getParsedBody() ?: [];

            if ((\is_array($body) || $body instanceof \ArrayAccess) && !empty($body)) {
                $token = $body[$this->config['field']] ?? null;
            } elseif (!$request->hasHeader($this->config['header'])) {
                throw new InvalidCsrfException('Le cookie Csrf n\'existe pas ou est incorrect');
            } else {
                $token = $request->getHeaderLine($this->config['header']);
            }
            $this->validateToken($token, $cookie);

            [$tokenId] = \explode(CsrfTokenManagerInterface::DELIMITER, $cookie);
            $token = $this->tokenManager->refreshToken($tokenId);
            $request = $request->withAttribute($this->config['field'], $token);

            $response = $handler->handle($request);

            return $this->setCookie($token, $response);
        }
        return $handler->handle($request);
    }

    protected function validateToken(?string $token = null, ?string $cookie = null)
    {
        if (!$token) {
            throw new InvalidCsrfException('Le cookie Csrf n\'existe pas ou est incorrect');
        }

        if (!$cookie) {
            throw new InvalidCsrfException('Le cookie Csrf n\'existe pas ou est incorrect');
        }

        if (!$this->tokenManager->isTokenValid($token)) {
            throw new InvalidCsrfException('Le Csrf est incorrect');
        }

        if (!\hash_equals($token, $cookie)) {
            throw new InvalidCsrfException('Le cookie Csrf est incorrect');
        }
    }

    public function getFormKey(): string
    {
        return $this->config['field'];
    }

    protected function setCookie(string $token, ResponseInterface $response): ResponseInterface
    {
        $setCookie = SetCookie::create($this->config['cookieName'])
            ->withValue($token)
            ->withExpires($this->config['expiry'])
            ->withPath('/')
            ->withDomain(null)
            ->withSecure($this->config['secure'])
            ->withHttpOnly($this->config['httponly']);
        return FigResponseCookies::set($response, $setCookie);
    }

    private function createCookie(string $token, ?int $expiry = null): SetCookie
    {
        return SetCookie::create($this->config['cookieName'])
            ->withValue($token)
            ->withExpires(($expiry === null) ? $this->config['expiry'] : $expiry)
            ->withPath('/')
            ->withDomain(null)
            ->withSecure($this->config['secure'])
            ->withHttpOnly($this->config['httponly']);
    }
}
