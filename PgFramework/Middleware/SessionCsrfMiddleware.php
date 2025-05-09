<?php

declare(strict_types=1);

namespace PgFramework\Middleware;

use Psr\Http\Message\ResponseInterface;
use Grafikart\Csrf\InvalidCsrfException;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use PgFramework\Security\Csrf\CsrfTokenManagerInterface;

use function in_array;

class SessionCsrfMiddleware implements MiddlewareInterface
{
    private string $formKey;

    private CsrfTokenManagerInterface $tokenManager;

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
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws InvalidCsrfException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
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
        return $handler->handle($request);
    }
}
