<?php

namespace Tests\PgFramework\Controller;

use App\Entity\Post;
use Pagerfanta\Pagerfanta;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Psr7\ServerRequest;
use Pagerfanta\Adapter\ArrayAdapter;
use PgFramework\Session\FlashService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use PgFramework\Controller\CrudController;
use PgFramework\Database\Doctrine\PaginatedEntityRepository;
use PgFramework\Renderer\RendererInterface;
use PgFramework\Database\Doctrine\PaginatedQueryBuilder;
use PgRouter\Router;
use stdClass;

class CrudControllerTest extends TestCase
{
    private $flash;
    private $renderer;
    private $queryBuilder;
    private $repository;
    private $em;
    private $om;

    public function setUp(): void
    {
        $this->flash = $this->getMockBuilder(FlashService::class)->disableOriginalConstructor()->getMock();
        $this->renderer = $this->getMockBuilder(RendererInterface::class)->getMock();
        $this->queryBuilder = $this->getMockBuilder(PaginatedQueryBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->em = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $this->om = $this->getMockBuilder(ManagerRegistry::class)->disableOriginalConstructor()->getMock();
        $this->om->method('getManagerForClass')->willReturn(EntityManagerInterface::class);
        $this->repository = $this->getMockBuilder(PaginatedEntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->repository->method('find')->willReturnCallback(function ($id) {
            $object = new \stdClass();
            $object->id = (int)$id;
            return $object;
        });
        $this->em->method('getRepository')->willReturn($this->repository);
    }

    private function makeCrudAction(): CrudController
    {
        $this->renderer->method('render')->willReturn('');
        $router = $this->getMockBuilder(Router::class)->disableOriginalConstructor()->getMock();
        $router->method('generateUri')->willReturnCallback(function ($url) {
            return $url;
        });
        /**
         * @var Router $router
         * @var ManagerRegistry $om
         */
        $action = new CrudController($this->renderer, $this->om, $router, $this->flash);
        $property = (new \ReflectionClass($action))->getProperty('viewPath');
        $property->setAccessible(true);
        $property->setValue($action, '@demo');
        $property = (new \ReflectionClass($action))->getProperty('em');
        $property->setAccessible(true);
        $property->setValue($action, $this->em);
        return $action;
    }

    public function testIndex()
    {
        $request = new ServerRequest('GET', '/demo');
        $pager = new Pagerfanta(new ArrayAdapter([1, 2]));
        $this->repository->method('buildFindAll')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('paginate')->willReturn($pager);
        $this->renderer
            ->expects($this->once())
            ->method('render')
            ->with('@demo/index', ['items' => $pager]);
        call_user_func([$this->makeCrudAction(), 'index'], $request);
    }

    public function testEdit()
    {
        $id = 3;
        $request = (new ServerRequest('GET', '/demo'))->withAttribute('id', $id);
        $this->renderer
            ->expects($this->once())
            ->method('render')
            ->with(
                '@demo/edit',
                $this->callback(function ($params) use ($id) {
                    $this->assertEquals($id, $params['item']->id);
                    return true;
                })
            );
        call_user_func([$this->makeCrudAction(), 'edit'], $request);
    }

    public function testDelete()
    {
        $id = 3;
        $request = (new ServerRequest('DELETE', '/demo'))
            ->withAttribute('id', $id);
        $this->repository->expects($this->once())->method('find');
        $this->em
            ->expects($this->once())
            ->method('remove')
            ->with(
                $this->callback(function ($item) use ($id) {
                    $this->assertEquals($id, $item->id);
                    return true;
                })
            );
        $response = call_user_func([$this->makeCrudAction(), 'delete'], $request);
        $this->assertEquals(['.index'], $response->getHeader('Location'));
    }

    public function testCreate()
    {
        $id = 3;
        $request = (new ServerRequest('GET', '/new'))->withAttribute('id', $id);
        $this->renderer
            ->expects($this->once())
            ->method('render')
            ->with(
                '@demo/create',
                $this->callback(function ($params) use ($id) {
                    $this->assertInstanceOf(\stdClass::class, $params['item']);
                    return true;
                })
            );
        call_user_func([$this->makeCrudAction(), 'create'], $request);
    }
}
