<?php

namespace Framework\EventListener;

use GuzzleHttp\Psr7\Response;
use Framework\Renderer\RendererInterface;
use ActiveRecord\Exceptions\RecordNotFound;
use Framework\Event\ExceptionEvent;

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
