<?php

namespace Framework\EventListener;

use League\Event\Listener;
use GuzzleHttp\Psr7\Response;
use Mezzio\Router\RouteResult;
use Framework\Event\RequestEvent;
use Framework\Renderer\RendererInterface;
use Psr\Http\Message\ServerRequestInterface;

class PageNotFoundListener implements Listener
{
    /**
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

    public function __invoke(object $event): void
    {
        /** @var RequestEvent $event */
        /** @var ServerRequestInterface $request */
        $request = $event->getRequest();

        $routeResult = $request->getAttribute(RouteResult::class);

        if (null === $routeResult) {
            $event->setResponse(new Response(404, [], $this->renderer->render('error404')));
        }
    }
}
