<?php

declare(strict_types=1);

use DebugBar\DebugBar;
use Doctrine\DBAL\Configuration as DbalConfiguration;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Grafikart\Csrf\CsrfMiddleware;
use Invoker\CallableResolver;
use Invoker\Invoker;
use Invoker\ParameterResolver\ParameterResolver;
use Mezzio\Session\SessionInterface;
use Mezzio\Session\SessionPersistenceInterface;
use Pg\Router\RouteCollectionInterface;
use Pg\Router\RouteCollector;
use Pg\Router\Router;
use Pg\Router\RouterInterface;
use PgFramework\Auth\Auth;
use PgFramework\Auth\Middleware\AuthenticationMiddleware;
use PgFramework\Database\ActiveRecord\ActiveRecordFactory;
use PgFramework\Database\Doctrine\Bridge\DebugStack;
use PgFramework\Database\Doctrine\Bridge\DebugStackInterface;
use PgFramework\Database\Doctrine\ConnectionConfigFactory;
use PgFramework\Database\Doctrine\DbalConnectionFactory;
use PgFramework\Database\Doctrine\DoctrineConfigFactory;
use PgFramework\Database\Doctrine\EntityManagerFactory;
use PgFramework\Database\Doctrine\OrmManagerFactory;
use PgFramework\DebugBar\DebugBarFactory;
use PgFramework\Environnement\Environnement;
use PgFramework\EventDispatcher\EventDispatcher;
use PgFramework\Invoker\CallableResolverFactory;
use PgFramework\Invoker\InvokerFactory;
use PgFramework\Invoker\ResolverChainFactory;
use PgFramework\Jwt\JwtMiddlewareFactory;
use PgFramework\Kernel\KernelEvent;
use PgFramework\Mailer\MailerFactory;
use PgFramework\Renderer\RendererInterface;
use PgFramework\Renderer\TwigRendererFactory;
use PgFramework\Router\Command\RouteListCommand;
use PgFramework\Router\RequestMatcher;
use PgFramework\Router\RequestMatcherInterface;
use PgFramework\Router\RouterFactory;
use PgFramework\Router\RouterTwigExtension;
use PgFramework\Router\RoutesMapFactory;
use PgFramework\Router\RoutesMapInterface;
use PgFramework\Security\Authorization\AuthorizationChecker;
use PgFramework\Security\Authorization\AuthorizationCheckerInterface;
use PgFramework\Security\Authorization\VoterManagerFactory;
use PgFramework\Security\Authorization\VoterManagerInterface;
use PgFramework\Security\Csrf\CsrfTokenManager;
use PgFramework\Security\Csrf\CsrfTokenManagerInterface;
use PgFramework\Security\Csrf\TokenGenerator\TokenGenerator;
use PgFramework\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use PgFramework\Security\Csrf\TokenStorage\TokenSessionStorage;
use PgFramework\Security\Csrf\TokenStorage\TokenStorageInterface;
use PgFramework\Security\Firewall\AccessMapFactory;
use PgFramework\Security\Firewall\AccessMapInterface;
use PgFramework\Security\Firewall\EventListener\AuthenticationListener;
use PgFramework\Security\Firewall\FirewallMapFactory;
use PgFramework\Security\Firewall\FirewallMapInterface;
use PgFramework\Security\Hasher\DefaultPasswordHasher;
use PgFramework\Security\Hasher\PasswordHasherInterface;
use PgFramework\Serializer\NormalizerFactory;
use PgFramework\Serializer\SerializerFactory;
use PgFramework\Session\PHPSession;
use PgFramework\Session\SessionFactory;
use PgFramework\Session\SessionInterface as PgSessionInterface;
use PgFramework\Session\SessionPersistenceFactory;
use PgFramework\Twig\{CsrfExtension,
    FlashExtension,
    FormExtension,
    PagerFantaExtension,
    TextExtension,
    TimeExtension,
    WebpackExtension
};
use PgFramework\Validator\Filter\StriptagsFilter;
use PgFramework\Validator\Filter\TrimFilter;
use PgFramework\Validator\Rules\{ConfirmValidation,
    DateFormatValidation,
    EmailValidation,
    ExistsValidation,
    ExtensionValidation,
    MaxValidation,
    MinValidation,
    NotEmptyValidation,
    RangeValidation,
    RequiredValidation,
    SlugValidation,
    UniqueValidation,
    UploadedValidation
};
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Tuupola\Middleware\JwtAuthentication;

use function DI\add;
use function DI\autowire;
use function DI\create;
use function DI\factory;
use function DI\get;

return [
    'env' => Environnement::getEnv('APP_ENV', 'dev'),
    //'env' => env('ENV', 'production'),
    'app' => Environnement::getEnv('APP', 'web'),
    'jwt.secret' => Environnement::getEnv('APP_KEY', 'abcdefghijklmnop123456789'),
    'twig.entrypoints' => '',
    WebpackExtension::class => autowire()
        ->constructorParameter('entryPoints', get('twig.entrypoints')),
    //CsrfExtension::class => create()
    //    ->constructor(get(CsrfListenerInterface::class)),
    'twig.extensions' => add([
        get(RouterTwigExtension::class),
        get(PagerFantaExtension::class),
        get(TextExtension::class),
        get(TimeExtension::class),
        get(FlashExtension::class),
        get(FormExtension::class),
        get(CsrfExtension::class),
        get(WebpackExtension::class),
    ]),
    'form.validations' => add([
        'required' => RequiredValidation::class,
        'min' => MinValidation::class,
        'max' => MaxValidation::class,
        'date' => DateFormatValidation::class,
        'email' => EmailValidation::class,
        'confirm' => ConfirmValidation::class,
        'notEmpty' => NotEmptyValidation::class,
        'range' => RangeValidation::class,
        'filetype' => ExtensionValidation::class,
        'uploaded' => UploadedValidation::class,
        'slug' => SlugValidation::class,
        'exists' => ExistsValidation::class,
        'unique' => UniqueValidation::class
    ]),
    'form.filters' => add([
        'trim' => TrimFilter::class,
        'striptags' => StriptagsFilter::class
    ]),
    FirewallMapInterface::class => factory(FirewallMapFactory::class),
    'security.firewall.rules' => add([]),
    'security.voters' => add([]),
    AccessMapInterface::class => factory(AccessMapFactory::class),
    'security.voters.strategy' => VoterManagerInterface::STRATEGY_AFFIRMATIVE,
    VoterManagerInterface::class => factory(VoterManagerFactory::class),
    'session.persistence.ext' => add([
        'non_locking' => false,
        'delete_cookie_on_empty_session' => false
    ]),
    SessionPersistenceInterface::class => factory(SessionPersistenceFactory::class),
    SessionInterface::class => factory(SessionFactory::class),
    PgSessionInterface::class => create(PHPSession::class),
    RequestMatcherInterface::class => create(RequestMatcher::class),
    CsrfMiddleware::class =>
        create()->constructor(get(SessionInterface::class)),
    TokenStorageInterface::class =>
        create(TokenSessionStorage::class)->constructor(get(SessionInterface::class)),
    TokenGeneratorInterface::class => create(TokenGenerator::class),
    CsrfTokenManagerInterface::class =>
        create(CsrfTokenManager::class)->constructor(
            get(TokenStorageInterface::class),
            get(TokenGeneratorInterface::class)
        ),
        PasswordHasherInterface::class =>
        autowire(DefaultPasswordHasher::class)
            ->constructorParameter('config', get('password.hasher.config')),
        AuthenticationListener::class => autowire()
        ->constructorParameter('authenticators', get('security.authenticators')),
        AuthenticationMiddleware::class => autowire()
        ->constructorParameter('authenticators', get('security.authenticators')),
        AuthorizationCheckerInterface::class => autowire(AuthorizationChecker::class)
        ->constructorParameter('exceptionOnNoUser', true),
        JwtAuthentication::class => factory(JwtMiddlewareFactory::class),
        Invoker::class => factory(InvokerFactory::class),
        ParameterResolver::class => factory(ResolverChainFactory::class),
        CallableResolver::class => factory(CallableResolverFactory::class),
        EventDispatcherInterface::class => function (ContainerInterface $c): EventDispatcherInterface {
            return new EventDispatcher($c->get(CallableResolver::class));
        },
    KernelEvent::class => function (ContainerInterface $c): KernelEvent {
        return new KernelEvent(
            $c->get(EventDispatcherInterface::class),
            $c->get(CallableResolver::class),
            $c->get(ParameterResolver::class),
            $c
        );
    },
    'routes.listeners' => add([]),
    RoutesMapInterface::class => factory(RoutesMapFactory::class),
    RouterInterface::class => factory(RouterFactory::class),
    Router::class => factory(RouterFactory::class),
    'duplicate.route' => true,
    RouteCollector::class => autowire(),
    RouteCollectionInterface::class => get(RouteCollector::class),
    RendererInterface::class => factory(TwigRendererFactory::class),
    'database.sgdb' => Environnement::getEnv('DATABASE_SGDB', 'mysql'),
    'database.host' => Environnement::getEnv('DATABASE_HOST', 'localhost'),
    'database.user' => Environnement::getEnv('DATABASE_USER', 'root'),
    'database.password' => Environnement::getEnv('DATABASE_PASSWORD', 'root'),
    'database.name' => Environnement::getEnv('DATABASE_NAME', 'my_database'),
    'database.driver' => Environnement::getEnv('DATABASE_DRIVER'),
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
    },
    'mail.to' => 'admin@admin.fr',
    'mail.from' => 'noreply@admin.fr',
    MailerInterface::class => factory(MailerFactory::class),
    Configuration::class => factory(DoctrineConfigFactory::class),
    'doctrine.proxies.dir' => __DIR__ . '/app/Proxies',
    'doctrine.proxies.namespace' => 'App\Proxies',
    'doctrine.entity.path' => add([]),
    'doctrine.entity.namespace' => add([]),
    'doctrine.connection.default.url' => function (ContainerInterface $c): array {
        return [
            'url' => $c->get('database.sgdb') . "://" .
                $c->get('database.user') . ":" .
                $c->get('database.password') . "@" .
                $c->get('database.host') . "/" .
                $c->get('database.name') . "?charset=utf8",
        ];
    },
    'doctrine.connections' => add([
        'default' => 'doctrine.connection.default',
    ]),
    'doctrine.connection.default' => factory(DbalConnectionFactory::class)
        ->parameter('url', 'doctrine.connection.default.url')
        ->parameter('connectionName', 'default'),
    DbalConfiguration::class => factory(ConnectionConfigFactory::class),
    'doctrine.manager.default' => function (ContainerInterface $c): EntityManagerInterface {
        return $c->get(EntityManagerInterface::class);
    },
    EntityManagerInterface::class => factory(EntityManagerFactory::class)
        ->parameter('connectionEntry', 'doctrine.connection.default'),
    'doctrine.managers' => add([
        'default' => 'doctrine.manager.default',
    ]),
    ManagerRegistry::class => factory(OrmManagerFactory::class),
    DebugStackInterface::class => get(DebugStack::class),
    DebugBar::class => factory(DebugBarFactory::class),
    'serializer.normalizers' => factory(NormalizerFactory::class),
    SerializerInterface::class => factory(SerializerFactory::class),
    'console.commands' => add(
        [
            'route:list' => RouteListCommand::class,
        ]
    ),
];
