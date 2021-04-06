<?php

use Framework\Jwt\JwtMiddlewareFactory;
use Psr\Container\ContainerInterface;
use Grafikart\Csrf\CsrfMiddleware;
use Framework\Twig\{
    CsrfExtension,
    FormExtension,
    TextExtension,
    TimeExtension,
    FlashExtension,
    PagerFantaExtension,
    WebpackExtension
};
use Framework\Router\FastRouteRouterFactory;
use Framework\Router\RouterTwigExtension;
use Framework\Session\PHPSession;
use Framework\Session\SessionInterface;
use Framework\Renderer\RendererInterface;
use Framework\Renderer\TwigRendererFactory;
use Framework\ActiveRecord\ActiveRecordFactory;
use Framework\Environnement\Environnement;
use Framework\Invoker\CallableResolverFactory;
use Framework\Invoker\InvokerFactory;
use Framework\Invoker\ResolverChainFactory;
use Framework\Router\RequestMatcher;
use Framework\Router\RequestMatcherInterface;
use Framework\Security\Csrf\CsrfTokenManager;
use Framework\Security\Csrf\CsrfTokenManagerInterface;
use Framework\Security\Csrf\TokenGenerator\TokenGenerator;
use Framework\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Framework\Security\Csrf\TokenStorage\TokenSessionStorage;
use Framework\Security\Csrf\TokenStorage\TokenStorageInterface;
use Framework\Validator\Filter\StriptagsFilter;
use Framework\Validator\Filter\TrimFilter;
use Framework\Validator\Rules\{
    DateFormatValidation,
    EmailConfirmValidation,
    EmailValidation,
    ExistsValidation,
    ExtensionValidation,
    MaxValidation,
    MinValidation,
    RangeValidation,
    RequiredValidation,
    SlugValidation,
    UniqueValidation,
    UploadedValidation,
    NotEmptyValidation
};
use Invoker\CallableResolver;
use Invoker\Invoker;
use Invoker\ParameterResolver\ParameterResolver;
use League\Event\EventDispatcher;
use Mezzio\Router\FastRouteRouter;
use Mezzio\Router\RouteCollector;
use Mezzio\Router\RouterInterface;
use Tuupola\Middleware\JwtAuthentication;
use Psr\EventDispatcher\EventDispatcherInterface;

use function DI\create;
use function DI\get;
use function DI\factory;

return [
    'env' => Environnement::getEnv('APP_ENV', 'dev'),
    //'env' => env('ENV', 'production'),
    'app' => Environnement::getEnv('APP', 'web'),
    'jwt.secret' => Environnement::getEnv('APP_KEY', 'abcdefghijklmnop123456789'),
    'twig.extensions' => [
        get(RouterTwigExtension::class),
        get(PagerFantaExtension::class),
        get(TextExtension::class),
        get(TimeExtension::class),
        get(FlashExtension::class),
        get(FormExtension::class),
        get(CsrfExtension::class),
        get(WebpackExtension::class),
    ],
    'form.validations' => \DI\add([
        'required' => RequiredValidation::class,
        'min' => MinValidation::class,
        'max' => MaxValidation::class,
        'date' => DateFormatValidation::class,
        'email' => EmailValidation::class,
        'emailConfirm' => EmailConfirmValidation::class,
        'notEmpty' => NotEmptyValidation::class,
        'range' => RangeValidation::class,
        'filetype' => ExtensionValidation::class,
        'uploaded' => UploadedValidation::class,
        'slug' => SlugValidation::class,
        'exists' => ExistsValidation::class,
        'unique' => UniqueValidation::class
    ]),
    'form.filters' => \DI\add([
        'trim' => TrimFilter::class,
        'striptags' => StriptagsFilter::class
    ]),
    'security.voters' => \DI\add([]),
    SessionInterface::class => create(PHPSession::class),
    RequestMatcherInterface::class => create(RequestMatcher::class),
    CsrfMiddleware::class =>
    create()->constructor(get(SessionInterface::class)),
    TokenStorageInterface::class => 
    create(TokenSessionStorage::class)->constructor(get(SessionInterface::class)),
    TokenGeneratorInterface::class => create(TokenGenerator::class),
    CsrfTokenManagerInterface::class => 
    create(CsrfTokenManager::class)->constructor(
        get(TokenStorageInterface::class), 
        get(TokenGeneratorInterface::class)),
    JwtAuthentication::class => factory(JwtMiddlewareFactory::class),
    Invoker::class => factory(InvokerFactory::class),
    ParameterResolver::class => factory(ResolverChainFactory::class),
    CallableResolver::class => factory(CallableResolverFactory::class),
    EventDispatcherInterface::class => function ():  EventDispatcherInterface {
        return new EventDispatcher();
    },
    RouterInterface::class => factory(FastRouteRouterFactory::class),
    FastRouteRouter::class => factory(FastRouteRouterFactory::class),
    'duplicate.route' => true,
    RouteCollector::class => \DI\autowire()
        ->constructorParameter("detectDuplicates", \DI\get('duplicate.route')),
    RendererInterface::class => factory(TwigRendererFactory::class),
    'database.sgdb' => Environnement::getEnv('DATABASE_SGDB', 'mysql'),
    'database.host' => Environnement::getEnv('DATABASE_HOST', 'localhost'),
    'database.user' => Environnement::getEnv('DATABASE_USER', 'root'),
    'database.password' => Environnement::getEnv('DATABASE_PASSWORD', 'root'),
    'database.name' => Environnement::getEnv('DATABASE_NAME', 'my_database'),
    'ActiveRecord' => factory(ActiveRecordFactory::class),
    'ActiveRecord.connections' => function (ContainerInterface $c): array {
        return [
            'development' => $c->get('database.sgdb') . "://" .
                $c->get('database.user') . ":" .
                $c->get('database.password') . "@" .
                $c->get('database.host') . "/" .
                $c->get('database.name') . "?charset=utf8"
        ];
    },
    PDO::class => function (ContainerInterface $c) {
        return new PDO(
            $c->get('database.sgdb') . ":host=" . $c->get('database.host') . ";dbname=" . $c->get('database.name'),
            $c->get('database.user'),
            $c->get('database.password'),
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
            ]
        );
    }
];
