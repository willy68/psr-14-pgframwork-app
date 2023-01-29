<?php

use PgFramework\Environnement\Environnement;
use PgFramework\Security\Firewall\EventListener\ForbiddenListener;
use PgFramework\Auth;
use PgFramework\Auth\{
    AuthSession,
    UserInterface,
    Provider\TokenProviderInterface,
    Provider\UserProviderInterface,
    RememberMe\RememberMeInterface,
    RememberMe\RememberMeDatabase,
    RememberMe\RememberMe,
    Middleware\ForbidenMiddleware
};
use PgFramework\Auth\Service\{
    UtilToken,
    UtilTokenInterface
};
use App\Auth\{
    Twig\AuthTwigExtension,
    Provider\UserProvider,
    Provider\UserTokenProvider
};
use PgFramework\Security\Authentication\FormAuthentication;

use function DI\{
    add,
    get,
    factory,
    autowire
};

return [
    'auth.login' => '/login',
    'twig.extensions' => add([
        get(AuthTwigExtension::class)
    ]),
    'doctrine.entity.path' => add([__DIR__ . '/Entity']),
    'doctrine.entity.namespace' => add(['App\Auth\Entity']),
    AuthSession::class => autowire()->constructorParameter('options', [
        'sessionName' => 'auth.user',
        'field' => 'username'
    ]),
    Auth::class => get(AuthSession::class),
    UserInterface::class => factory(function (Auth $auth) {
        return $auth->getUser();
    })->parameter('auth', get(Auth::class)),
    RememberMeInterface::class => get(RememberMeDatabase::class),
    RememberMeDatabase::class =>
    autowire()->constructorParameter('salt', Environnement::getEnv('APP_KEY', 'abcdefghijklmnop123456789')),
    RememberMe::class =>
    autowire()->constructorParameter('salt', Environnement::getEnv('APP_KEY', 'abcdefghijklmnop123456789')),
    UtilTokenInterface::class => get(UtilToken::class),
    UserProviderInterface::class => get(UserProvider::class),
    TokenProviderInterface::class => get(UserTokenProvider::class),
    ForbidenMiddleware::class => autowire()->constructorParameter('loginPath', get('auth.login')),
    ForbiddenListener::class => autowire()->constructorParameter('loginPath', get('auth.login')),
    FormAuthentication::class => autowire()->constructorParameter('options', [
        'identifier' => 'username',
        'password' => 'password',
        'rememberMe' => 'rememberMe',
        'auth.login' => 'auth.login',
        'redirect.success' => 'account',
        'matched.route.name' => 'auth.login.post'
    ]),
    'password.hasher.config' => add([
        'algo' => \PASSWORD_ARGON2I,
        'options' => [
            'cost' => 10,
            'memory_cost' => \PASSWORD_ARGON2_DEFAULT_MEMORY_COST,
            'time_cost' => \PASSWORD_ARGON2_DEFAULT_TIME_COST,
            'threads' => PASSWORD_ARGON2_DEFAULT_THREADS
        ]

    ])
];
