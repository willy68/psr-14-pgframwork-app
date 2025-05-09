<?php

namespace App\Account;

use App\Account\Action\AccountAction;
use App\Account\Action\AccountEditController;
use App\Account\Action\SignupController;
use PgFramework\Module;
use PgFramework\Renderer\RendererInterface;

class AccountModule extends Module
{
    public const MIGRATIONS = __DIR__ . '/migrations';

    public const DEFINITIONS = __DIR__ . '/definitions.php';

    public const ANNOTATIONS = [
        AccountAction::class,
        AccountEditController::class,
        SignupController::class
    ];

    public function __construct(RendererInterface $renderer)
    {
        $renderer->addPath('account', __DIR__ . '/views');
    }
}
