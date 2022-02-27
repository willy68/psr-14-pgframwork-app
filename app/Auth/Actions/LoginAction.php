<?php

namespace App\Auth\Actions;

use PgFramework\Router\Annotation\Route;
use PgFramework\Renderer\RendererInterface;

/**
 * @Route("/login", name="auth.login", methods={"GET"})
 */
class LoginAction
{
    private $renderer;

    public function __construct(RendererInterface $renderer)
    {
        $this->renderer = $renderer;
    }

    public function __invoke(): string
    {
        // $submited = false;
        return $this->renderer->render('@auth/login'/*, compact('submited')*/);
    }
}
