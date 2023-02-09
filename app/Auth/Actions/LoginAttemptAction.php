<?php

declare(strict_types=1);

namespace App\Auth\Actions;

use PgFramework\Auth\AuthSession;
use Mezzio\Router\RouterInterface;
use PgFramework\Session\FlashService;
use PgFramework\Router\Annotation\Route;
use Mezzio\Session\SessionInterface;
use PgFramework\Actions\RouterAwareAction;
use PgFramework\Auth\Middleware\AuthenticationMiddleware;
use PgFramework\Auth\Middleware\RehashPasswordMiddleware;
use PgFramework\Response\ResponseRedirect;
use PgFramework\Renderer\RendererInterface;
use Psr\Http\Message\ServerRequestInterface;
use PgFramework\Auth\RememberMe\RememberMeInterface;

/**
 * @Route("/login", methods={"POST"}, name="auth.login.post", middlewares={RehashPasswordMiddleware::class, AuthenticationMiddleware::class})
 */
#[Route('/login', methods:['POST'], name:'auth.login.post', middlewares:[RehashPasswordMiddleware::class,AuthenticationMiddleware::class])]
class LoginAttemptAction
{
    use RouterAwareAction;

    /**
     * @var RendererInterface
     */
    private $renderer;

    /**
     * @var AuthSession
     */
    private $auth;

    /**
     * @var RememberMeInterface
     */
    private $cookie;

    /**
     * @var SessionInterface
     */
    private $session;

    /**
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
            $path = $this->session->get('auth.redirect')  ?: $this->router->generateUri('account');
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
