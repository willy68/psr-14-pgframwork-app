<?php

declare(strict_types=1);

namespace PgFramework\Security\Authentication;

use Mezzio\Router\RouteResult;
use PgFramework\Auth;
use PgFramework\Auth\UserInterface;
use Mezzio\Router\RouterInterface;
use PgFramework\Session\FlashService;
use Psr\Http\Message\ResponseInterface;
use Mezzio\Session\SessionInterface;
use PgFramework\Actions\RouterAwareAction;
use PgFramework\Response\ResponseRedirect;
use Psr\Http\Message\ServerRequestInterface;
use PgFramework\Auth\Provider\UserProviderInterface;
use PgFramework\Security\Hasher\PasswordHasherInterface;
use PgFramework\Security\Authentication\Exception\AuthenticationFailureException;
use PgFramework\Security\Authentication\Result\AuthenticateResult;
use PgFramework\Security\Authentication\Result\AuthenticateResultInterface;

class FormAuthentication implements AuthenticationInterface
{
    use RouterAwareAction;

    protected $matchedRouteName = 'auth.login.post';

    protected $auth;

    protected $userProvider;

    protected $session;

    protected $router;

    protected $hasher;

    protected $options = [
        'identifier' => 'username',
        'password' => 'password',
        'rememberMe' => 'rememberMe',
        'auth.login' => 'auth.login',
        'redirect.success' => 'account'
    ];

    public function __construct(
        Auth $auth,
        UserProviderInterface $userProvider,
        SessionInterface $session,
        RouterInterface $router,
        PasswordHasherInterface $hasher,
        array $options = []
    ) {
        $this->auth = $auth;
        $this->userProvider = $userProvider;
        $this->session = $session;
        $this->router = $router;
        $this->hasher = $hasher;

        if (!empty($options)) {
            $this->options = array_merge($this->options, $options);
        }
    }

    public function supports(ServerRequestInterface $request): bool
    {
        /** @var RouteResult $routeResult */
        $routeResult = $request->getAttribute(RouteResult::class);
        return $routeResult->getMatchedRouteName() === $this->matchedRouteName;
    }

    public function authenticate(ServerRequestInterface $request): AuthenticateResultInterface
    {
        $credentials = $this->getCredentials($request);

        if (null === $credentials) {
            throw new AuthenticationFailureException('User credentials could not be null');
        }

        $user = $this->getUser($credentials);

        if (!$user || !$user instanceof UserInterface) {
            throw new AuthenticationFailureException('User not found');
        }

        if (!$this->hasher->verify($user->getPassword(), $credentials['password'])) {
            throw new AuthenticationFailureException('Bad password');
        }

        return new AuthenticateResult($credentials, $user);
    }

    public function getCredentials(ServerRequestInterface $request)
    {
        $params = $request->getParsedBody();

        $credentials['identifier'] = $params[$this->options['identifier']] ?? null;
        $credentials['password'] = $params[$this->options['password']] ?? null;
        $rememberMe = $params[$this->options['rememberMe']] ?? null;
        if ($rememberMe) {
            $credentials['rememberMe'] = true;
        }

        if (!\is_string($credentials['identifier'])) {
            return null;
        }

        return $credentials;
    }

    public function getUser($credentials)
    {
        return $this->userProvider->getUser($this->options['identifier'], $credentials['identifier']);
    }

    /**
     * @param UserInterface $user
     */
    public function onAuthenticateSuccess(ServerRequestInterface $request, $user): ?ResponseInterface
    {
        $this->auth->setUser($user);

        $path = $this->session->get('auth.redirect') ?: $this->router->generateUri($this->options['redirect.success']);
        $this->session->unset('auth.redirect');
        return new ResponseRedirect($path);
    }

    public function onAuthenticateFailure(
        ServerRequestInterface $request,
        AuthenticationFailureException $e
    ): ?ResponseInterface {
        (new FlashService($this->session))->error('Identifiant ou mot de passe incorrect');
        return $this->redirect($this->options['auth.login']);
    }

    public function supportsRememberMe(ServerRequestInterface $request): bool
    {
        /** @var AuthenticateResultInterface $result */
        if (($result = $request->getAttribute('auth.result'))) {
            $credentials = $result->getCredentials();
            return $credentials['rememberMe'] ?? false;
        }
        return false;
    }
}
