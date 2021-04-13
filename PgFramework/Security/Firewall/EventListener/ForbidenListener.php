<?php

namespace PgFramework\Security\Firewall\EventListener;

use Dflydev\FigCookies\FigRequestCookies;
use Dflydev\FigCookies\SetCookie;
use PgFramework\Event\ExceptionEvent;
use PgFramework\Session\FlashService;
use Psr\Http\Message\ResponseInterface;
use PgFramework\Auth\ForbiddenException;
use PgFramework\Session\SessionInterface;
use Dflydev\FigCookies\FigResponseCookies;
use PgFramework\Response\ResponseRedirect;
use PgFramework\Auth\FailedAccessException;
use Psr\Http\Message\ServerRequestInterface;

class ForbidenListener
{

    /**
     * Cookie options
     *
     * @var array
     */
    protected $options = [
        'name' => 'auth_login',
        'path' => '/',
        'domain' => null,
        'secure' => false,
        'httpOnly' => true,
        'samesite' => null,
    ];

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
            $response = $this->redirectLogin($request);

            // Todo create CancelRememberMeCookieListener
            if ($request->getAttribute('cancel.rememberme.cookie')) {
                $response = $this->cancelCookie($response);
                $event->setRequest(FigRequestCookies::remove($request, $this->options['name']));
            }
            $event->setResponse($response);
            return;
        }

        if ($e instanceof FailedAccessException) {
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

    protected function cancelCookie(ResponseInterface $response): ResponseInterface
    {
        // Delete cookie
        $cookiePassword = SetCookie::create($this->options['name'])
            ->withValue('')
            ->withExpires(time() - 3600)
            ->withPath($this->options['path'])
            ->withDomain($this->options['domain'])
            ->withSecure($this->options['secure'])
            ->withHttpOnly($this->options['httpOnly']);
        return FigResponseCookies::set($response, $cookiePassword);

    }
}