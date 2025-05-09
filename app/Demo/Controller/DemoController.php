<?php

declare(strict_types=1);

namespace App\Demo\Controller;

use App\Entity\Ville;
use DateTime;
use App\Entity\Post;
use App\Models\Client;
use App\Auth\Models\User;
use PDO;
use PgFramework\Router\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use PgFramework\Renderer\RendererInterface;
use Psr\Http\Message\ServerRequestInterface;
use PgFramework\Database\ActiveRecord\ActiveRecordQuery;

class DemoController
{
    /**
     * Montre l’index de l’application
     * $renderer est injecté automatiquement, comme toutes les classes
     * renseignées dans config/config.php
     * Il est possible d’injecter la ServerRequestInterface
     * et les paramètres de la route (ex. $id).
     * Ce type d'injection est possible avec \DI\Container de PHP-DI
     *
     * @Route("/", name="demo.index", methods={"GET"})
     *
     * @param ServerRequestInterface $request
     * @param RendererInterface $renderer
     * @param PDO $pdo
     * @param ManagerRegistry $managerRegistry
     * @return string
     */
    #[Route('/', name: 'demo.index', methods: ['GET'])]
    public function index(
        ServerRequestInterface $request,
        RendererInterface $renderer,
        PDO $pdo,
        ManagerRegistry $managerRegistry
    ): string {
        $conn = $managerRegistry->getManager();
        $post = $conn->getRepository(Post::class)->find(1);
        $communes = $managerRegistry->getManager('communes')->getRepository(Ville::class);
        $villes = $communes->findOneBy(['ville' => 'Meyrals']);

        $query = new ActiveRecordQuery();
        $query
            ->where('id = ?', 'user_id = ?')
            ->orWhere('created_at = now()')
            ->setWhereValue([2, 5, new DateTime()]);
        /** @var User $user */
        $user = User::find_by_username(['username' => 'admin']);
        $user_array = $user->to_array();

        $mysql_ver = $pdo->getAttribute(PDO::ATTR_SERVER_VERSION);
        $params = array_merge(
            $request->getServerParams(),
            $user_array,
            [$mysql_ver],
            [$query],
            [serialize($post)],
            [json_encode($villes)]
        );
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
     * @param Client $client
     * @param RendererInterface $renderer
     * @return string
     */
    #[Route('/demo/client/{id:\d+}', name: 'demo.client', methods: ['GET'])]
    public function demoClient(Client $client, RendererInterface $renderer): string
    {
        $client = $client->to_array(['include' => 'adresses']);
        return $renderer->render('@demo/client', compact('client'));
    }
}
