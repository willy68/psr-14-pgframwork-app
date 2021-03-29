<?php

namespace App\Auth\Middleware;

use Framework\Auth\User;
use Framework\Session\FlashService;
use Framework\Auth\ForbiddenException;
use Framework\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Framework\Response\ResponseRedirect;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ForbidenMiddleware implements MiddlewareInterface
{

    private $loginPath;

    /**
     * Undocumented variable
     *
     * @var SessionInterface
     */
    private $session;

    public function __construct(string $loginPath, SessionInterface $session)
    {
        $this->loginPath = $loginPath;
        $this->session = $session;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (ForbiddenException $e) {
            return $this->redirectLogin($request);
        } catch (\TypeError $error) {
            if (strpos($error->getMessage(), User::class) !== false) {
                return $this->redirectLogin($request);
            }
        }
    }

    public function redirectLogin(ServerRequestInterface $request): ResponseInterface
    {
        $this->session->set('auth.redirect', $request->getUri()->getPath());
        (new FlashService($this->session))->error('Vous devez posseder un compte pour accéder à cette page');
        return new ResponseRedirect($this->loginPath);
    }
}
