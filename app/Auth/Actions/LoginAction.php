<?php

namespace App\Auth\Actions;

use PgFramework\Renderer\RendererInterface;
use PgFramework\Router\Annotation\Route;

/**
 * @Route("/login", name="auth.login", methods={"GET"})
 */
#[Route('/login', name: 'auth.login', methods: ['GET'])]
class LoginAction
{
    private RendererInterface $renderer;

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
