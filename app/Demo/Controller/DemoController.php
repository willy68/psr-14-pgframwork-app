<?php

namespace App\Demo\Controller;

use DateTime;
use App\Entity\Post;
use App\Models\Client;
use App\Auth\Models\User;
use Doctrine\ORM\EntityManager;
use App\Repository\PostRepository;
use Psr\Container\ContainerInterface;
use PgFramework\Router\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use PgFramework\Validator\ValidationRules;
use PgFramework\Renderer\RendererInterface;
use Psr\Http\Message\ServerRequestInterface;
use PgFramework\Invoker\Annotation\ParameterConverter;
use PgFramework\Database\ActiveRecord\ActiveRecordQuery;

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
     * @param ContainerInterface $c
     * @return string
     */
    #[Route('/', name: 'demo.index', methods: ['GET'])]
    public function index(
        ServerRequestInterface $request,
        RendererInterface $renderer,
        \PDO $pdo,
        EntityManager $em,
        ContainerInterface $c,
        ManagerRegistry $managerRegistry
    ): string {
        $conn = $managerRegistry->getManager();

        /** @var PostRepository */
        $rp = $conn->getRepository(Post::class);
        $pc = $rp->findWithCategory(122);

        $query = new ActiveRecordQuery();
        $query
            ->where('id = ?', 'user_id = ?')
            ->orWhere('created_at = now()')
            ->setWhereValue([2, 5, new DateTime()]);
        /** @var \App\Auth\Models\User $user */
        $user = User::find_by_username(['username' => 'admin']);
        $user_array = $user->to_array();

        /** @var PostRepository */
        $repo = $em->getRepository(Post::class);
        $doctrinePost = $repo->findWithCategory(122);

        $mysql_ver = $pdo->getAttribute(\PDO::ATTR_SERVER_VERSION);
        $params = array_merge($request->getServerParams(), $user_array, [$mysql_ver], [$query]);
        return $renderer->render('@demo/index', compact('params'));
    }

    /**
     * @Route("/react", name="demo.react", methods={"GET"})
     *
     * @param RendererInterface $renderer
     * @return string
     */
    #[Route('/react', name: 'demo.react', methods: ['GET'])]
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
     * @param \PgFramework\Renderer\RendererInterface $renderer
     * @return string
     */
    #[Route('/demo/client/{id:\d+}', name: 'demo.client', methods: ['GET'])]
    public function demoClient(Client $client, RendererInterface $renderer): string
    {
        $client = $client->to_array(['include' => 'adresses']);
        return $renderer->render('@demo/client', compact('client'));
    }
}
