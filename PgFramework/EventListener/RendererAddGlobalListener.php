<?php

namespace PgFramework\EventListener;

use PgFramework\Event\Events;
use PgFramework\Event\RequestEvent;
use PgFramework\Renderer\RendererInterface;
use PgFramework\EventDispatcher\EventSubscriberInterface;

class RendererAddGlobalListener implements EventSubscriberInterface
{
    /**
     * @var RendererInterface
     */
    private RendererInterface $renderer;

    public function __construct(RendererInterface $renderer)
    {
        $this->renderer = $renderer;
    }

    public function __invoke(RequestEvent $e)
    {
        $request = $e->getRequest();

        $domain = sprintf(
            '%s://%s%s',
            $request->getUri()->getScheme(),
            $request->getUri()->getHost(),
            $request->getUri()->getPort() ? ':' . $request->getUri()->getPort() : ''
        );
        $this->renderer->addGlobal('domain', $domain);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::REQUEST => 850
        ];
    }
}
