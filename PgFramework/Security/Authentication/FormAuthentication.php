<?php

namespace PgFramework\Security\Authentication;

use PgFramework\Auth;
use PgFramework\Auth\User;
use Mezzio\Router\RouterInterface;
use PgFramework\Session\FlashService;
use Psr\Http\Message\ResponseInterface;
use PgFramework\Session\SessionInterface;
use PgFramework\Actions\RouterAwareAction;
use PgFramework\Response\ResponseRedirect;
use Psr\Http\Message\ServerRequestInterface;
use PgFramework\Auth\Provider\UserProviderInterface;
use PgFramework\Security\Hasher\PasswordHasherInterface;
use PgFramework\Security\Authentication\Exception\AuthenticationFailureException;

class FormAuthentication implements AuthenticationInterface
{
    use RouterAwareAction;

    protected $auth;

    protected $userProvider;

    protected $session;

    protected $router;

    protected $hasher;

    protected $options = [
        'identifier' => 'username',
        'password' => 'password',
        'auth.login' => 'auth.login',
        'redirect.success' => 'admin'
    ];

    public function __construct(
        Auth $auth,
        UserProviderInterface $userProvider,
        SessionInterface $session,
        RouterInterface $router,
        PasswordHasherInterface $hasher,
        array $options = []
    ){
        $this->auth = $auth;
        $this->userProvider = $userProvider;
        $this->session = $session;
        $this->router = $router;
        $this->hasher = $hasher;

        if (!empty($options)) {
            $this->options = array_merge($this->options, $options);
        }
    }

    public function authenticate(ServerRequestInterface $request)
    {
        $credentials = $this->getCredentials($request);

        if (null === $credentials) {
            throw new AuthenticationFailureException('User credentials could not be null');
        }

        $user = $this->getUser($credentials);

        if (!$user || !$user instanceof User) {
            throw new AuthenticationFailureException('User not found');
        }
        
        if (!$this->hasher->verify($user->getPassword(), $credentials['password'])) {
            throw new AuthenticationFailureException('Bad password');
        }

        return $user;
    }

    public function getCredentials(ServerRequestInterface $request): ?array
    {
        $params = $request->getParsedBody();

        $credentials['identifier'] = $params[$this->options['identifier']] ?? '';
        $credentials['password'] = $params[$this->options['password']] ?? '';

        if (!\is_string($credentials['identifier']) || isEmpty($credentials['identifier'])) {
            return null;
        }

        return $credentials;
    }

    public function getUser($credentiels)
    {
        return $this->userProvider->getUser($this->options['identifier'], $credentiels['identifier']);
    }

    public function onAuthenticateSuccess(ServerRequestInterface $request, $user): ?ResponseInterface
    {
        $this->auth->setUser($user);

        $path = $this->session->get('auth.redirect')  ?: $this->router->generateUri($this->options['redirect.success']);
        $this->session->delete('auth.redirect');
        return  new ResponseRedirect($path);
    }

    public function onAuthenticateFailure(ServerRequestInterface $request, AuthenticationFailureException $e): ?ResponseInterface
    {
        (new FlashService($this->session))->error('Identifiant ou mot de passe incorrect');
        return $this->redirect($this->options['auth.login']);
    }
}
