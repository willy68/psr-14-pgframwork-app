<?php

use PgFramework\{
    Auth,
    Auth\AuthSession,
    Auth\User
};
use App\Auth\{
    ActiveRecordUserRepository,
    Twig\AuthTwigExtension,
    Middleware\ForbidenMiddleware,
    UserTokenRepository
};
use PgFramework\Auth\Repository\UserRepositoryInterface;
use PgFramework\Auth\RememberMe\RememberMeDatabase;
use PgFramework\Auth\RememberMe\RememberMeInterface;
use PgFramework\Auth\Repository\TokenRepositoryInterface;
use PgFramework\Auth\Service\UtilToken;
use PgFramework\Auth\Service\UtilTokenInterface;
use PgFramework\Environnement\Environnement;
use PgFramework\Security\Firewall\EventListener\ForbidenListener;

use function DI\{
    add,
    get,
    factory
};

return [
    'auth.login' => '/login',
    'twig.extensions' => add([
        get(AuthTwigExtension::class)
    ]),
    Auth::class => \DI\get(AuthSession::class),
    User::class => factory(function (Auth $auth) {
        return $auth->getUser();
    })->parameter('auth', get(Auth::class)),
    RememberMeInterface::class => \DI\get(RememberMeDatabase::class),
    RememberMeDatabase::class =>
    \DI\autowire()->constructorParameter('salt', Environnement::getEnv('APP_KEY', 'abcdefghijklmnop123456789')),
    UtilTokenInterface::class => \DI\get(UtilToken::class),
    UserRepositoryInterface::class => \DI\get(ActiveRecordUserRepository::class),
    TokenRepositoryInterface::class => \DI\get(UserTokenRepository::class),
    ForbidenMiddleware::class => \DI\autowire()->constructorParameter('loginPath', \DI\get('auth.login')),
    ForbidenListener::class => \DI\autowire()->constructorParameter('loginPath', \DI\get('auth.login')),
];
