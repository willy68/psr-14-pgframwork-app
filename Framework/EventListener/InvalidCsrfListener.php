<?php

namespace Framework\EventListener;

use GuzzleHttp\Psr7\Response;
use Framework\Event\ExceptionEvent;
use Framework\Session\FlashService;
use Framework\HttpUtils\RequestUtils;
use Framework\Response\ResponseRedirect;
use Grafikart\Csrf\InvalidCsrfException;

class InvalidCsrfListener
{
    /**
     * Undocumented variable
     *
     * @var FlashService
     */
    private $flashService;

    /**
     * InvalidCsrfListener constructor.
     * @param FlashService $flashService
     */
    public function __construct(FlashService $flashService)
    {
        $this->flashService = $flashService;
    }

    public function onException(ExceptionEvent $event)
    {
        $e = $event->getException();
        $request = $event->getRequest();

        if ($e instanceof InvalidCsrfException) {
            if (RequestUtils::isJson($request)) {
                $event->setResponse(new Response(403, [], $e->getMessage() . ' ' . $e->getCode()));
                return;
            }
            $this->flashService->error('Vous n\'avez pas de token valid pour executer cette action');
            $event->setResponse(new ResponseRedirect('/'));
        }
    }
}
