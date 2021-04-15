<?php

namespace PgFramework\EventListener;

use GuzzleHttp\Psr7\Response;
use PgFramework\Event\ExceptionEvent;
use PgFramework\Session\FlashService;
use PgFramework\HttpUtils\RequestUtils;
use PgFramework\Response\ResponseRedirect;
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
}
