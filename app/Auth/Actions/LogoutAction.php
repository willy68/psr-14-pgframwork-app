<?php

namespace App\Auth\Actions;

use PgFramework\Auth\AuthSession;
use PgFramework\Auth\Middleware\CookieLogoutMiddleware;
use PgFramework\Response\ResponseRedirect;
use PgFramework\Router\Annotation\Route;
use PgFramework\Session\FlashService;
use Psr\Http\Message\ResponseInterface;

/**
 * @Route("/logout", name="auth.logout", methods={"POST"}, middlewares={CookieLogoutMiddleware::class})
 */
#[Route('/logout', name: 'auth.logout', methods: ['POST'], middlewares: [CookieLogoutMiddleware::class])]
class LogoutAction
{
    private AuthSession $auth;

    private FlashService $flashService;

    public function __construct(
        AuthSession $auth,
        FlashService $flashService
    ) {
        $this->auth = $auth;
        $this->flashService = $flashService;
    }

    public function __invoke(): ResponseInterface
    {
        $this->auth->logout();
        $this->flashService->success('Vous êtes maintenant déconnecté');
        return new ResponseRedirect('/blog');
    }
}
