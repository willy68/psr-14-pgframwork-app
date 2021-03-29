<?php

namespace App\Api;

use Framework\Module;
use Mezzio\Router\RouteGroup;
use Mezzio\Router\FastRouteRouter;
use Mezzio\Router\RouterInterface;
use App\Api\Client\ClientController;
use Tuupola\Middleware\JwtAuthentication;
use App\Api\Client\Adresse\AdresseController;
use App\Api\Client\Civilite\CiviliteController;
use Framework\Middleware\ContentTypeJsonMiddleware;
use Framework\Middleware\CorsAllowOriginMiddleware;
use App\Api\Client\AdresseType\AdresseTypeController;

class ApiClientModule extends Module
{
    public function __construct(RouterInterface $router)
    {

        /** @var FastRouteRouter $router */
        $router->group('/api', function (RouteGroup $route) {
            // Client
            $route->get(
                '/entreprise/{entreprise_id:\d+}/client/{id:\d+}',
                ClientController::class . '::get',
                'client.get'
            );
            $route->get(
                '/clients',
                ClientController::class . '::list',
                'client.all'
            );
            $route->get(
                '/entreprise/{entreprise_id:\d+}/client/list',
                ClientController::class . '::list',
                'client.list'
            );
            $route->post(
                '/entreprise/{entreprise_id:\d+}/client',
                ClientController::class . '::create',
                'client.create'
            );
            $route->put(
                '/entreprise/{entreprise_id:\d+}/client/{id:\d+}',
                ClientController::class . '::update',
                'client.update'
            );
            $route->delete(
                '/entreprise/{entreprise_id:\d+}/client/{id:\d+}',
                ClientController::class . '::delete',
                'client.delete'
            );

            // Civilite
            $route->get(
                '/entreprise/{entreprise_id:\d+}/civilite/{id:\d+}',
                CiviliteController::class . '::get',
                'civilite.get'
            );
            $route->get(
                '/civilites',
                CiviliteController::class . '::list',
                'civilite.all'
            );
            $route->get(
                '/entreprise/{entreprise_id:\d+}/civilite/list',
                CiviliteController::class . '::list',
                'civilite.list'
            );
            $route->post(
                '/entreprise/{entreprise_id:\d+}/civilite',
                CiviliteController::class . '::create',
                'civilite.create'
            );
            $route->put(
                '/entreprise/{entreprise_id:\d+}/civilite/{id:\d+}',
                CiviliteController::class . '::update',
                'civilite.update'
            );
            $route->delete(
                '/entreprise/{entreprise_id:\d+}/civilite/{id:\d+}',
                CiviliteController::class . '::delete',
                'civilite.delete'
            );

            // Adresse
            $route->get(
                '/client/{client_id:\d+}/adresse/{id:\d+}',
                AdresseController::class . '::get',
                'adresse.get'
            );
            $route->get(
                '/adresses',
                AdresseController::class . '::list',
                'adresse.all'
            );
            $route->get(
                '/client/{client_id:\d+}/adresse/list',
                AdresseController::class . '::list',
                'adresse.list'
            );
            $route->post(
                '/client/{client_id:\d+}/adresse',
                AdresseController::class . '::create',
                'adresse.create'
            );
            $route->put(
                '/client/{client_id:\d+}/adresse/{id:\d+}',
                AdresseController::class . '::update',
                'adresse.update'
            );
            $route->delete(
                '/client/{client_id:\d+}/adresse/{id:\d+}',
                AdresseController::class . '::delete',
                'adresse.delete'
            );

            // Adresse_type
            $route->get(
                '/adresse_type/{id:\d+}',
                AdresseTypeController::class . '::get',
                'adresse_type.get'
            );
            $route->get(
                '/adresse_types',
                AdresseTypeController::class . '::list',
                'adresse_type.all'
            );
            $route->get(
                '/adresse_type/list',
                AdresseTypeController::class . '::list',
                'adresse_type.list'
            );
            $route->post(
                '/adresse_type',
                AdresseTypeController::class . '::create',
                'adresse_type.create'
            );
            $route->put(
                '/adresse_type/{id:\d+}',
                AdresseTypeController::class . '::update',
                'adresse_type.update'
            );
            $route->delete(
                '/adresse_type/{id:\d+}',
                AdresseTypeController::class . '::delete',
                'adresse_type.delete'
            );
        })
            // ->middleware(JwtAuthentication::class)
            ->middleware(CorsAllowOriginMiddleware::class)
            ->middleware(ContentTypeJsonMiddleware::class);
    }
}
