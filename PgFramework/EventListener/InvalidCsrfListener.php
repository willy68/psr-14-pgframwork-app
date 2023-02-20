<?php

declare(strict_types=1);

namespace PgFramework\EventListener;

use GuzzleHttp\Psr7\Response;
use PgFramework\Event\ExceptionEvent;
use PgFramework\Session\FlashService;
use PgFramework\HttpUtils\RequestUtils;
use PgFramework\Response\ResponseRedirect;
use Grafikart\Csrf\InvalidCsrfException;
use League\Event\ListenerPriority;
use PgFramework\Event\Events;
use PgFramework\EventDispatcher\EventSubscriberInterface;

class InvalidCsrfListener implements EventSubscriberInterface
{
    private $flashService;

    public function __construct(FlashService $flashService)
    {
        $this->flashService = $flashService;
    }

    public function __invoke(ExceptionEvent $event)
    {
        $e = $event->getException();
        $request = $event->getRequest();

        if ($e instanceof InvalidCsrfException) {
            if (RequestUtils::isJson($request)) {
                $event->setResponse(new Response(403, [], json_encode($e->getMessage() . ' ' . $e->getCode())));
                return;
            }
            $this->flashService->error('Vous n\'avez pas de token valid pour executer cette action');
            $event->setResponse(new ResponseRedirect('/'));
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::EXCEPTION => ListenerPriority::HIGH
        ];
    }
}
