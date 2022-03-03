<?php

namespace PgFramework\Middleware;

use Psr\Http\Message\ResponseInterface;
use Grafikart\Csrf\InvalidCsrfException;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use PgFramework\Security\Csrf\CsrfTokenManagerInterface;

class SessionCsrfMiddleware implements MiddlewareInterface
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
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $method = $request->getMethod();

        if (\in_array($method, ['DELETE', 'PATCH', 'POST', 'PUT'], true)) {
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
