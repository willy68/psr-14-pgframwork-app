<?php

namespace App\Api;

use Framework\Module;
use Mezzio\Router\RouteGroup;
use App\Api\User\UserController;
use Mezzio\Router\FastRouteRouter;
use Mezzio\Router\RouterInterface;
use App\Api\Controller\ApiController;
use App\Api\User\Role\RoleController;
use App\Api\Cpville\CpvilleController;
use Tuupola\Middleware\JwtAuthentication;
use App\Api\Entreprise\EntrepriseController;
use App\Api\DernierCode\DernierCodeController;
use Framework\Middleware\ContentTypeJsonMiddleware;
use Framework\Middleware\CorsAllowOriginMiddleware;

class ApiModule extends Module
{


    public const MIGRATIONS = __DIR__ . '/db/migrations';

    public const SEEDS = __DIR__ . '/db/seeds';

    /**
     * ApiModule constructor.
     * @param Router $router
     */
    public function __construct(RouterInterface $router)
    {
        /** @var FastRouteRouter $router */
        $router->get('/api', ApiController::class . '::index', 'api.index')
            ->middleware(CorsAllowOriginMiddleware::class)
            ->middleware(ContentTypeJsonMiddleware::class);

        // Route without JWT authentication
        $router->group('/api', function (RouteGroup $route) {
            $route->post(
                '/user/login',
                UserController::class . '::login',
                'user.login'
            );
            $route->post(
                '/user/create',
                UserController::class . '::create',
                'user.create'
            );
            $route->post(
                '/entreprise/{entreprise_id:\d+}/user',
                UserController::class . '::create',
                'user.register'
            );
            // Cpville
            $route->post(
                '/cpville/search/{ville:ville}',
                CpvilleController::class . '::search',
                'ville.post.search'
            );
            $route->post(
                '/cpville/search/{cp:cp}',
                CpvilleController::class . '::search',
                'cp.post.search'
            );
            $route->get(
                '/cpville/search/{ville:ville}/{search:[^\?]+}',
                CpvilleController::class . '::search',
                'ville.get.search'
            );
            $route->get(
                '/cpville/search/{cp:cp}/{search:\d+}',
                CpvilleController::class . '::search',
                'cp.get.search'
            );
        })
            ->middleware(CorsAllowOriginMiddleware::class)
            ->middleware(ContentTypeJsonMiddleware::class);

        // Route with JWT authentication
        $router->group('/api', function (RouteGroup $route) {
            // User
            $route->get(
                '/user/{id:\d+}',
                UserController::class . '::get',
                'user.get'
            );
            $route->get(
                '/entreprise/{entreprise_id:\d+}/user/list',
                UserController::class . '::list',
                'user.list'
            );
            $route->get(
                '/users',
                UserController::class . '::list',
                'user.all'
            );
            $route->put(
                '/user/{id:\d+}',
                UserController::class . '::update',
                'user.update'
            );
            $route->delete(
                '/user/{id:\d+}',
                UserController::class . '::delete',
                'user.delete'
            );

            // Entreprise
            $route->get(
                '/user/{user_id:\d+}/entreprise/{id:\d+}',
                EntrepriseController::class . '::get',
                'entreprise.get'
            );
            $route->get(
                '/entreprises',
                EntrepriseController::class . '::list',
                'entreprise.all'
            );
            $route->get(
                '/user/{user_id:\d+}/entreprise/list',
                EntrepriseController::class . '::list',
                'entreprise.list'
            );
            $route->post(
                '/user/{user_id:\d+}/entreprise',
                EntrepriseController::class . '::create',
                'entreprise.create'
            );
            $route->put(
                '/user/{user_id:\d+}/entreprise/{id:\d+}',
                EntrepriseController::class . '::update',
                'entreprise.update'
            );
            $route->delete(
                '/user/{user_id:\d+}/entreprise/{id:\d+}',
                EntrepriseController::class . '::delete',
                'entreprise.delete'
            );

            // Dernier_code
            $route->get(
                '/entreprise/{entreprise_id:\d+}/dernier_code/{id:\d+}',
                DernierCodeController::class . '::get',
                'dernier_code.get'
            );
            $route->get(
                '/dernier_codes',
                DernierCodeController::class . '::list',
                'dernier_code.all'
            );
            $route->get(
                '/entreprise/{entreprise_id:\d+}/dernier_code/list',
                DernierCodeController::class . '::list',
                'dernier_code.list'
            );
            $route->get(
                '/entreprise/{entreprise_id:\d+}/dernier_code/{table_nom:[0-9a-zA-Z_]+}',
                DernierCodeController::class . '::forTable',
                'dernier_code.forTable'
            );
            $route->post(
                '/entreprise/{entreprise_id:\d+}/dernier_code',
                DernierCodeController::class . '::create',
                'dernier_code.create'
            );
            $route->put(
                '/entreprise/{entreprise_id:\d+}/dernier_code/{id:\d+}',
                DernierCodeController::class . '::update',
                'dernier_code.update'
            );
            $route->delete(
                '/entreprise/{entreprise_id:\d+}/dernier_code/{id:\d+}',
                DernierCodeController::class . '::delete',
                'dernier_code.delete'
            );

            // Adresse_type
            $route->get(
                '/role/{id:\d+}',
                RoleController::class . '::get',
                'role.get'
            );
            $route->get(
                '/roles',
                RoleController::class . '::list',
                'role.all'
            );
            $route->get(
                '/role/list',
                RoleController::class . '::list',
                'role.list'
            );
            $route->post(
                '/role',
                RoleController::class . '::create',
                'role.create'
            );
            $route->put(
                '/role/{id:\d+}',
                RoleController::class . '::update',
                'role.update'
            );
            $route->delete(
                '/role/{id:\d+}',
                RoleController::class . '::delete',
                'role.delete'
            );
        })
            // ->middleware(JwtAuthentication::class)
            ->middleware(CorsAllowOriginMiddleware::class)
            ->middleware(ContentTypeJsonMiddleware::class);
    }
}
