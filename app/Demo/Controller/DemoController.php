<?php

declare(strict_types=1);

namespace App\Demo\Controller;

use App\Entity\Ville;
use DateTime;
use App\Entity\Post;
use App\Models\Client;
use App\Auth\Models\User;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use PDO;
use Psr\Container\ContainerInterface;
use PgFramework\Router\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use PgFramework\Renderer\RendererInterface;
use Psr\Http\Message\ServerRequestInterface;
use PgFramework\Invoker\Annotation\ParameterConverter;
use PgFramework\Database\ActiveRecord\ActiveRecordQuery;
use Symfony\Component\Serializer\SerializerInterface;

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
     * @param PDO $pdo
     * @param EntityManagerInterface $em
     * @param ContainerInterface $c
     * @param ManagerRegistry $managerRegistry
     * @param SerializerInterface $serializer
     * @return string
     */
    #[Route('/', name: 'demo.index', methods: ['GET'])]
    public function index(
        ServerRequestInterface $request,
        RendererInterface $renderer,
        PDO $pdo,
        EntityManagerInterface $em,
        ContainerInterface $c,
        ManagerRegistry $managerRegistry,
		SerializerInterface $serializer,
    ): string {
		$villeEm = $managerRegistry->getManager('communes');

		$repoVille = $villeEm->getRepository(Ville::class);
		$villes = $repoVille->find('24220');
		$villeJson = $serializer->serialize($villes, 'json');

        $conn = $managerRegistry->getManager();
        /** @var PostRepository $rp*/
        $rp = $conn->getRepository(Post::class);
        $pc = $rp->findWithCategory(15);

        $query = new ActiveRecordQuery();
        $query
            ->where('id = ?', 'user_id = ?')
            ->orWhere('created_at = now()')
            ->setWhereValue([2, 5, new DateTime()]);
        /** @var User $user */
        $user = User::find_by_username(['username' => 'admin']);
        $user_array = $user->to_array();

        /** @var PostRepository $repo*/
        $repo = $em->getRepository(Post::class);
        $doctrinePost = $repo->findWithCategory(16);

        $mysql_ver = $pdo->getAttribute(PDO::ATTR_SERVER_VERSION);
        $params = array_merge($request->getServerParams(), $user_array, [$mysql_ver], [$query], [$villeJson]);
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

    /**
     * @param string $name
     * @param int $years
     * @param RendererInterface $renderer
     * @return string
     */
    #[Route('/test/{name:\w+}/{years:\d+}', name:'blog.test', methods:['GET'])]
    public function testRequestParams(string $name, int $years, RendererInterface $renderer): string
    {
        $params = ['name' => $name, 'years' => $years];
        return $renderer->render('@demo/index', compact('params'));
    }
}
