<?php

namespace App\Auth;

use App\Auth\Actions\LoginAction;
use App\Auth\Actions\LoginAttemptAction;
use App\Auth\Actions\LogoutAction;
use App\Auth\Actions\PasswordForgetController;
use App\Auth\Actions\PasswordResetController;
use PgFramework\Module;
use PgFramework\Renderer\RendererInterface;

class AuthModule extends Module
{
    public const DEFINITIONS = __DIR__ . '/config.php';

    public const MIGRATIONS = __DIR__ . '/db/migrations';

    public const SEEDS = __DIR__ . '/db/seeds';

    public const ANNOTATIONS = [
        LoginAction::class,
        LoginAttemptAction::class,
        LogoutAction::class,
        PasswordForgetController::class,
        PasswordResetController::class,
    ];

    public function __construct(RendererInterface $renderer)
    {
        $renderer->addPath('auth', __DIR__ . '/views');
    }
}
