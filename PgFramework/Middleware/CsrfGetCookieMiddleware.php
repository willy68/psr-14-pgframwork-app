<?php

declare(strict_types=1);

namespace PgFramework\Middleware;

use ArrayAccess;
use Dflydev\FigCookies\SetCookie;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Dflydev\FigCookies\FigRequestCookies;
use Dflydev\FigCookies\FigResponseCookies;
use Grafikart\Csrf\InvalidCsrfException;
use PgFramework\Security\Csrf\CsrfTokenManagerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function in_array;
use function is_array;

class CsrfGetCookieMiddleware implements MiddlewareInterface
{
    protected array $config = [
        'cookieName' => 'XSRF-TOKEN',
        'header' => 'X-CSRF-TOKEN',
        'session.key' => 'csrf.tokens',
        'field' => '_csrf',
        'expiry' => 0,
        'secure' => false,
        'httponly' => true,
        'samesite' => null,
    ];

    private CsrfTokenManagerInterface $tokenManager;

    /**
     * CsrfMiddleware constructor.
     *
     * @param CsrfTokenManagerInterface $tokenManager
     */
    public function __construct(CsrfTokenManagerInterface $tokenManager)
    {
        $this->tokenManager = $tokenManager;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws InvalidCsrfException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $method = $request->getMethod();

        $cookie = FigRequestCookies::get($request, $this->config['cookieName'])->getValue();

        if (in_array($method, ['GET', 'HEAD'], true)) {
            if (null === $cookie) {
                $cookie = $this->tokenManager->generateToken();
                $request = $request->withAttribute($this->config['field'], $cookie);
            }

            $response = $handler->handle($request);

            return $this->setCookie($cookie, $response);
        }

        if (in_array($method, ['DELETE', 'PATCH', 'POST', 'PUT'], true)) {
            $body = $request->getParsedBody() ?: [];
            if ((is_array($body) || $body instanceof ArrayAccess) && !empty($body)) {
                $token = $body[$this->config['field']] ?? null;
                $this->validateToken($token, $cookie);
            } elseif (!$request->hasHeader($this->config['header'])) {
                throw new InvalidCsrfException('Le cookie Csrf n\'existe pas ou est incorrect');
            } else {
                $headerCookie = $request->getHeaderLine($this->config['header']);
                $this->validateToken($headerCookie, $cookie);
            }

            [$tokenId] = explode(CsrfTokenManagerInterface::DELIMITER, $cookie);
            $token = $this->tokenManager->refreshToken($tokenId);
            $request = $request->withAttribute($this->config['field'], $token);

            $response = $handler->handle($request);

            return $this->setCookie($token, $response);
        }
        return $handler->handle($request);
    }

    /**
     * @throws InvalidCsrfException
     */
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

        if (!hash_equals($token, $cookie)) {
            throw new InvalidCsrfException('Le cookie Csrf est incorrect');
        }
    }

    public function getFormKey(): string
    {
        return $this->config['field'];
    }

    protected function setCookie(string $token, ResponseInterface $response): ResponseInterface
    {
        $setCookie = SetCookie::create('XSRF-TOKEN')
            ->withValue($token)
            // ->withExpires(time() + 3600)
            ->withPath('/')
            ->withDomain()
            ->withSecure(false)
            ->withHttpOnly(false);
        return FigResponseCookies::set($response, $setCookie);
    }
}
