<?php

use PgFramework\Environnement\Environnement;
use PgFramework\Security\Firewall\EventListener\ForbidenListener;
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
    ForbidenListener::class => autowire()->constructorParameter('loginPath', get('auth.login')),
];
