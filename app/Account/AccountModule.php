<?php

namespace App\Account;

use PgFramework\Module;
use PgFramework\Renderer\RendererInterface;

class AccountModule extends Module
{
    public const MIGRATIONS = __DIR__ . '/migrations';

    public const DEFINITIONS = __DIR__ . '/definitions.php';

    public const ANNOTATIONS = [__DIR__ . '/Action'];

    public function __construct(RendererInterface $renderer)
    {
        $renderer->addPath('account', __DIR__ . '/views');
    }
}
