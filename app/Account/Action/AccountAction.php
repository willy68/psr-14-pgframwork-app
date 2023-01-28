<?php

namespace App\Account\Action;

use PgFramework\Auth;
use PgFramework\Auth\LoggedInMiddleware;
use PgFramework\Renderer\RendererInterface;
use PgFramework\Router\Annotation\Route;
use Psr\Http\Message\ServerRequestInterface;

#[Route('/mon-profil', name:'account', methods:['GET'], middlewares:[LoggedInMiddleware::class])]
class AccountAction
{
    /**
     * @var RendererInterface
     */
    private $renderer;
    /**
     * @var Auth
     */
    private $auth;

    public function __construct(
        RendererInterface $renderer,
        Auth $auth
    ) {
        $this->renderer = $renderer;
        $this->auth = $auth;
    }

    public function __invoke(ServerRequestInterface $request)
    {
        $user = $this->auth->getUser();
        return $this->renderer->render('@account/account', compact('user'));
    }
}
