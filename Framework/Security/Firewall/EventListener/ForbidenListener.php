<?php

namespace Framework\Security\Firewall\EventListener;

use Framework\Event\ExceptionEvent;
use Framework\Session\FlashService;
use Framework\Auth\ForbiddenException;
use Framework\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Framework\Response\ResponseRedirect;
use Framework\Auth\FailedAccessException;
use Psr\Http\Message\ServerRequestInterface;

class ForbidenListener
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

    public function onException(ExceptionEvent $event)
    {
        $e = $event->getException();
        $request = $event->getRequest();
        if ($e instanceof ForbiddenException) {
            $event->setResponse($this->redirectLogin($request));
            return;
        }

        if ($e instanceof FailedAccessException) {
            $event->setResponse($this->redirectAdminHome($request));
            return;
        }
    }

    public function redirectLogin(ServerRequestInterface $request): ResponseInterface
    {
        $this->session->set('auth.redirect', $request->getUri()->getPath());
        (new FlashService($this->session))->error('Vous devez posseder un compte pour accéder à cette page');
        return new ResponseRedirect($this->loginPath);
    }

    public function redirectAdminHome(ServerRequestInterface $request): ResponseInterface
    {
        $uri = '/admin';
        $server = $request->getServerParams();

        if (isset($server['HTTP_REFERER'])) {
            $uri = $server['HTTP_REFERER'];
        }

        (new FlashService($this->session))->error('Vous n\'avez pas l\'authorisation pour executer cette action');
        return new ResponseRedirect($uri);
    }
}