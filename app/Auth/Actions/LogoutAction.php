<?php

namespace App\Auth\Actions;

use PgFramework\Auth\AuthSession;
use PgFramework\Session\FlashService;
use PgFramework\Router\Annotation\Route;
use PgFramework\Response\ResponseRedirect;

/**
 * @Route("/logout", name="auth.logout", methods={"POST"})
 */
class LogoutAction
{

    /**
     * Undocumented variable
     *
     * @var AuthSession
     */
    private $auth;

    /**
     * Undocumented variable
     *
     * @var FlashService
     */
    private $flashService;

    public function __construct(
        AuthSession $auth,
        FlashService $flashService
    ) {
        $this->auth = $auth;
        $this->flashService = $flashService;
    }

    public function __invoke()
    {
        $this->auth->logout();
        $this->flashService->success('Vous êtes maintenant déconnecté');
        return new ResponseRedirect('/blog');
    }
}
