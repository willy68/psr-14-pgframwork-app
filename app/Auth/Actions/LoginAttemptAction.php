<?php

namespace App\Auth\Actions;

use PgFramework\Auth\AuthSession;
use Mezzio\Router\RouterInterface;
use PgFramework\Session\FlashService;
use PgFramework\Router\Annotation\Route;
use PgFramework\Session\SessionInterface;
use PgFramework\Actions\RouterAwareAction;
use PgFramework\Response\ResponseRedirect;
use PgFramework\Renderer\RendererInterface;
use Psr\Http\Message\ServerRequestInterface;
use PgFramework\Auth\RememberMe\RememberMeInterface;

/**
 * @Route("/login", methods={"POST"})
 */
class LoginAttemptAction
{
    use RouterAwareAction;

    /**
     * Undocumented variable
     *
     * @var RendererInterface
     */
    private $renderer;

    /**
     * Undocumented variable
     *
     * @var AuthSession
     */
    private $auth;

    /**
     *
     *
     * @var RememberMeInterface
     */
    private $cookie;

    /**
     * Undocumented variable
     *
     * @var SessionInterface
     */
    private $session;

    /**
     * Undocumented variable
     *
     * @var RouterInterface
     */
    private $router;

    public function __construct(
        RendererInterface $renderer,
        AuthSession $auth,
        RememberMeInterface $cookie,
        SessionInterface $session,
        RouterInterface $router
    ) {
        $this->renderer = $renderer;
        $this->auth = $auth;
        $this->cookie = $cookie;
        $this->session = $session;
        $this->router = $router;
    }

    public function __invoke(ServerRequestInterface $request)
    {
        $params = $request->getParsedBody();
        $user = $this->auth->login($params['username'], $params['password']);
        if ($user) {
            $path = $this->session->get('auth.redirect')  ?: $this->router->generateUri('admin');
            $this->session->delete('auth.redirect');
            $response = new ResponseRedirect($path);
            if ($params['rememberMe']) {
                $response = $this->cookie->onLogin(
                    $response,
                    $user
                );
            }
            return $response;
        } else {
            (new FlashService($this->session))->error('Identifiant ou mot de passe incorrect');
            return $this->redirect('auth.login');
        }
    }
}
