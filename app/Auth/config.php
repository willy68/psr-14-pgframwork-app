<?php

use PgFramework\{
    Auth,
    Auth\AuthSession,
    Auth\User,
    Auth\Provider\UserProviderInterface
};

use App\Auth\{
    Twig\AuthTwigExtension,
    Provider\UserProvider
};

use function DI\{
    add,
    get,
    factory,
    autowire
};

use PgFramework\Auth\Service\UtilToken;
use App\Auth\Provider\UserTokenProvider;
use PgFramework\Auth\RememberMe\RememberMe;
use PgFramework\Environnement\Environnement;
use PgFramework\Auth\Service\UtilTokenInterface;
use PgFramework\Auth\Middleware\ForbidenMiddleware;
use PgFramework\Auth\RememberMe\RememberMeDatabase;
use PgFramework\Auth\RememberMe\RememberMeInterface;
use PgFramework\Auth\Provider\TokenProviderInterface;
use PgFramework\Security\Firewall\EventListener\ForbidenListener;

return [
    'auth.login' => '/login',
    'twig.extensions' => add([
        get(AuthTwigExtension::class)
    ]),
    'doctrine.entity.path' => add([__DIR__ . '/Entity']),
    Auth::class => get(AuthSession::class),
    User::class => factory(function (Auth $auth) {
        return $auth->getUser();
    })->parameter('auth', get(Auth::class)),
    RememberMeInterface::class => get(RememberMeDatabase::class),
    RememberMeDatabase::class =>
    \DI\autowire()->constructorParameter('salt', Environnement::getEnv('APP_KEY', 'abcdefghijklmnop123456789')),
    RememberMe::class =>
    \DI\autowire()->constructorParameter('salt', Environnement::getEnv('APP_KEY', 'abcdefghijklmnop123456789')),
    UtilTokenInterface::class => get(UtilToken::class),
    UserProviderInterface::class => get(UserProvider::class),
    TokenProviderInterface::class => get(UserTokenProvider::class),
    ForbidenMiddleware::class => autowire()->constructorParameter('loginPath', get('auth.login')),
    ForbidenListener::class => autowire()->constructorParameter('loginPath', get('auth.login')),
];
