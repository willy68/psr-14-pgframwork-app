<?php

namespace Framework\Security\Firewall\EventListener;

use Framework\Session\FlashService;
use Framework\Auth\ForbiddenException;
use Framework\Event\ExceptionEvent;
use Framework\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Framework\Response\ResponseRedirect;
use Psr\Http\Message\ServerRequestInterface;

class ForbidenListener
{

    private $loginPath;

    /**
     * Undocumented variable
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
        }
    }

    public function redirectLogin(ServerRequestInterface $request): ResponseInterface
    {
        $this->session->set('auth.redirect', $request->getUri()->getPath());
        (new FlashService($this->session))->error('Vous devez posseder un compte pour accéder à cette page');
        return new ResponseRedirect($this->loginPath);
    }
}