<?php

namespace App\Account;

use App\Account\Action\AccountAction;
use App\Account\Action\AccountEditAction;
use App\Account\Action\SignupAction;
use Mezzio\Router\RouteCollector;
use PgFramework\Auth\LoggedInMiddleware;
use PgFramework\Module;
use PgFramework\Renderer\RendererInterface;

class AccountModule extends Module
{
    public const MIGRATIONS = __DIR__ . '/migrations';

    public const DEFINITIONS = __DIR__ . '/definitions.php';

    public function __construct(RouteCollector $router, RendererInterface $renderer)
    {
        $renderer->addPath('account', __DIR__ . '/views');
        $router->get('/inscription', SignupAction::class, 'account.signup');
        $router->post('/inscription', SignupAction::class);
        $router->get('/mon-profil', [LoggedInMiddleware::class, AccountAction::class], 'account');
        $router->post('/mon-profil', [LoggedInMiddleware::class, AccountEditAction::class]);
    }
}
