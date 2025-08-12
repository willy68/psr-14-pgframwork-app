<?php

declare(strict_types=1);

namespace App\Auth\Actions;

use Exception;
use Pg\Router\RouterInterface;
use Mezzio\Session\SessionInterface;
use PgFramework\Actions\RouterAwareAction;
use PgFramework\Auth\AuthSession;
use PgFramework\Auth\Middleware\AuthenticationMiddleware;
use PgFramework\Auth\RememberMe\RememberMeInterface;
use PgFramework\Renderer\RendererInterface;
use PgFramework\Response\ResponseRedirect;
use PgFramework\Router\Annotation\Route;
use PgFramework\Session\FlashService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @Route("/login", methods={"POST"}, name="auth.login.post", middlewares={AuthenticationMiddleware::class})
 */
#[Route(
    '/login',
    name: 'auth.login.post',
    methods: ['POST'],
    middlewares: [AuthenticationMiddleware::class]
)]
class LoginAttemptAction
{
    use RouterAwareAction;

    private RendererInterface $renderer;

    private AuthSession $auth;

    private RememberMeInterface $cookie;

    private SessionInterface $session;

    private RouterInterface $router;

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

    /**
     * @throws Exception
     */
    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $params = $request->getParsedBody();
        $user = $this->auth->login($params['username'], $params['password']);
        if ($user) {
            $path = $this->session->get('auth.redirect') ?: $this->router->generateUri('account');
            $this->session->unset('auth.redirect');
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
