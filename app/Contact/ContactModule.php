<?php

namespace App\Contact;

use PgFramework\Module;
use PgFramework\Renderer\RendererInterface;

class ContactModule extends Module
{
    public const DEFINITIONS = __DIR__ . '/definitions.php';
    public const ANNOTATIONS = [__DIR__];

    public function __construct(RendererInterface $renderer)
    {
        $renderer->addPath('contact', __DIR__);
        //$router->get('/contact', ContactAction::class, 'contact');
        //$router->post('/contact', ContactAction::class);
    }
}
