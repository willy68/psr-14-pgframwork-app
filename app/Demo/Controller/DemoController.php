<?php

namespace App\Demo\Controller;

use App\Models\Client;
use App\Auth\Models\User;
use Framework\Router\Annotation\Route;
use Framework\Validator\ValidationRules;
use Framework\Renderer\RendererInterface;
use Psr\Http\Message\ServerRequestInterface;
use Framework\Invoker\Annotation\ParameterConverter;
use Framework\Database\ActiveRecord\ActiveRecordQuery;

class DemoController
{

    /**
     * Montre l'index de l'application
     * $renderer est injecté automatiquement, comme toutes les classes
     * renseignées dans config/config.php
     * Il est possible d'injecter la ServerRequestInterface
     * et les paramètres de la route (ex. $id).
     * Ce type d'injection est possible avec \DI\Container de PHP-DI
     *
     * @Route("/", name="demo.index", methods={"GET"})
     *
     * @param ServerRequestInterface $request
     * @param RendererInterface $renderer
     * @param \PDO $pdo
     * @return string
     */
    public function index(
        ServerRequestInterface $request,
        RendererInterface $renderer,
        \PDO $pdo
    ): string {
        $validation = new ValidationRules('auteur', 'required|min:3|max:10|filter:trim');
        $validation->isValid('Willy ');
        $query = new ActiveRecordQuery();
        $query
            ->where('id = ?', 'user_id = ?')
            ->orWhere('created_at = now()')
            ->setWhereValue([2, 5, new \DateTime()]);
        /** @var \App\Auth\Models\User $user */
        $user = User::find_by_username(['username' => 'admin']);
        $user_array = $user->to_array();
        $mysql_ver = $pdo->getAttribute(\PDO::ATTR_SERVER_VERSION);
        $params = array_merge($request->getServerParams(), $user_array, [$mysql_ver], [$query->__toString()]);
        return $renderer->render('@demo/index', compact('params'));
    }

    /**
     * @Route("/react", name="demo.react", methods={"GET"})
     *
     * @param RendererInterface $renderer
     * @return string
     */
    public function demoReact(RendererInterface $renderer): string
    {
        return $renderer->render('@demo/react');
    }

    /**
     * @Route("/demo/client/{id:\d+}", name="demo.client", methods={"GET"})
     *
     * @ParameterConverter("client", options={"id"="id", "include"="adresses"})
     *
     * @param \App\Models\Client $client
     * @param \Framework\Renderer\RendererInterface $renderer
     * @return string
     */
    public function demoClient(Client $client, RendererInterface $renderer): string
    {
        $client = $client->to_array(['include' => 'adresses']);
        return $renderer->render('@demo/client', compact('client'));
    }
}
