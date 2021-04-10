<?php

namespace PgFramework\EventListener;

use GuzzleHttp\Psr7\Response;
use PgFramework\Renderer\RendererInterface;
use ActiveRecord\Exceptions\RecordNotFound;
use PgFramework\Event\ExceptionEvent;

class RecordNotFoundListener
{

    /**
     * Renderer de vue
     *
     * @var RendererInterface
     */
    private $renderer;

    /**
     *
     * @param RendererInterface $renderer
     */
    public function __construct(RendererInterface $renderer)
    {
        $this->renderer = $renderer;
    }

    public function onException(ExceptionEvent $event)
    {
        $e = $event->getException();
        if ($e instanceof RecordNotFound) {
            $event->setResponse(new Response(404, [], $this->renderer->render(
                'error404',
                ['message' => $e->getMessage()]
            )));
        }
    }
}
