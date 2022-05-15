<?php

declare(strict_types=1);

namespace PgFramework\Security\Firewall\EventListener;

use GuzzleHttp\Psr7\Response;
use PgFramework\Event\Events;
use League\Event\ListenerPriority;
use PgFramework\Event\ExceptionEvent;
use PgFramework\Session\FlashService;
use PgFramework\HttpUtils\RequestUtils;
use Psr\Http\Message\ResponseInterface;
use PgFramework\Auth\ForbiddenException;
use Mezzio\Session\SessionInterface;
use PgFramework\Response\ResponseRedirect;
use PgFramework\Auth\FailedAccessException;
use Psr\Http\Message\ServerRequestInterface;
use PgFramework\EventDispatcher\EventSubscriberInterface;

class ForbiddenListener implements EventSubscriberInterface
{
    private $loginPath;

    /**
     *
     * @var SessionInterface
     */
    private $session;

    public function __construct(string $loginPath, SessionInterface $session)
    {
        $this->loginPath = $loginPath;
        $this->session = $session;
    }

    public function __invoke(ExceptionEvent $event)
    {
        $e = $event->getException();
        $request = $event->getRequest();

        if ($e instanceof ForbiddenException) {
            if (RequestUtils::isJson($request)) {
                $event->setResponse(new Response(403, [], json_encode($e->getMessage() . ' ' . $e->getCode())));
                return;
            }
            $event->setResponse($this->redirectLogin($request));
            return;
        }

        if ($e instanceof FailedAccessException) {
            if (RequestUtils::isJson($request)) {
                $event->setResponse(new Response(403, [], json_encode($e->getMessage() . ' ' . $e->getCode())));
                return;
            }
            $event->setResponse($this->redirectAdminHome($request));
            return;
        }
    }

    protected function redirectLogin(ServerRequestInterface $request): ResponseInterface
    {
        $this->session->set('auth.redirect', $request->getUri()->getPath());
        (new FlashService($this->session))->error('Vous devez posseder un compte pour accéder à cette page');
        return new ResponseRedirect($this->loginPath);
    }

    protected function redirectAdminHome(ServerRequestInterface $request): ResponseInterface
    {
        $uri = $this->loginPath;
        $server = $request->getServerParams();

        if (isset($server['HTTP_REFERER'])) {
            $uri = $server['HTTP_REFERER'];
        }

        (new FlashService($this->session))->error('Vous n\'avez pas l\'authorisation pour executer cette action');
        return new ResponseRedirect($uri);
    }

    public static function getSubscribedEvents()
    {
        return [
            Events::EXCEPTION => ListenerPriority::HIGH
        ];
    }
}
