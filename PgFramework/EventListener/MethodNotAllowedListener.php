<?php

declare(strict_types=1);

namespace PgFramework\EventListener;

use GuzzleHttp\Psr7\Response;
use Pg\Router\RouteResult;
use PgFramework\Event\RequestEvent;
use Fig\Http\Message\StatusCodeInterface as StatusCode;
use PgFramework\Event\Events;
use PgFramework\EventDispatcher\EventSubscriberInterface;
use PgFramework\HttpUtils\RequestUtils;
use PgFramework\Response\JsonResponse;

class MethodNotAllowedListener implements EventSubscriberInterface
{
    public function __invoke(RequestEvent $event): void
    {
        $request = $event->getRequest();

        /** @var RouteResult $routeResult */
        $routeResult = $request->getAttribute(RouteResult::class);

        if (null === $routeResult) {
            return;
        }

        if ($routeResult->isMethodFailure()) {
            if (RequestUtils::isJson($event->getRequest()) || RequestUtils::wantJson($event->getRequest())) {
                $event->setResponse(
                    new JsonResponse(
                        statusCode::STATUS_METHOD_NOT_ALLOWED,
                        json_encode(
                            "Method not Allowed. Allowed methods: " .
                            implode(',', $routeResult->getAllowedMethods())
                        )
                    )
                );
                return;
            }
            $event->setResponse((new Response())
                ->withStatus(StatusCode::STATUS_METHOD_NOT_ALLOWED)
                ->withHeader('Allow', implode(',', $routeResult->getAllowedMethods())));
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::REQUEST => 600
        ];
    }
}
