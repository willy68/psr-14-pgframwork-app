<?php

namespace PgFramework\EventListener;

use GuzzleHttp\Psr7\Response;
use Mezzio\Router\RouteResult;
use PgFramework\Event\RequestEvent;
use PgFramework\Renderer\RendererInterface;

class PageNotFoundListener
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

    public function __invoke(RequestEvent $event): void
    {
        $request = $event->getRequest();

        $routeResult = $request->getAttribute(RouteResult::class);

        if (null === $routeResult) {
            $event->setResponse(new Response(404, [], $this->renderer->render('error404')));
        }
    }
}
