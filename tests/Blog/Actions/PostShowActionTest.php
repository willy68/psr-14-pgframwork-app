<?php

namespace Tests\App\Blog\Actions;

use App\Entity\Post;
use PHPUnit\Framework\TestCase;
use Mezzio\Router\RouterInterface;
use Prophecy\PhpUnit\ProphecyTrait;
use App\Blog\Actions\PostShowAction;
use App\Repository\PostRepository;
use PgFramework\Renderer\RendererInterface;

class PostShowActionTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var PostShowActionTest
     */
    private $action;

    private $renderer;

    private $postRepository;

    private $router;

    public function setUp(): void
    {
        $this->renderer = $this->prophesize(RendererInterface::class);
        $this->postRepository = $this->prophesize(PostRepository::class);
        $this->router = $this->prophesize(RouterInterface::class);
        $this->action = new PostShowAction(
            $this->renderer->reveal(),
            $this->router->reveal()
        );
    }

    public function makePost(int $id, string $slug): Post
    {
        // Article
        $post = new Post();
        $post->setId($id)
            ->setSlug($slug);
        return $post;
    }

    public function testShowRedirect()
    {
        $post = $this->makePost(9, "azezae-azeazae");

        $this->router->generateUri(
            'blog.show',
            ['id' => $post->getId(), 'slug' => $post->getSlug()]
        )->willReturn('/demo2');
        $this->postRepository->findWithCategory($post->getId())->willReturn($post);
        $this->renderer->render('@blog/show', ['post' => $post])->willReturn('');

        /** @var callable $callable */
        $callable = $this->action;
        $response = call_user_func_array($callable, ['slug', $post]);
        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals(['/demo2'], $response->getHeader('location'));
    }

    public function testShowRender()
    {
        $post = $this->makePost(9, "azezae-azeazae");
        $this->postRepository->findWithCategory($post->getId())->willReturn($post);
        $this->renderer->render('@blog/show', ['post' => $post])->willReturn($post->getSlug());

        /** @var callable $callable */
        $callable = $this->action;
        $response = call_user_func_array($callable, ['azezae-azeazae', $post]);
        $this->assertEquals($post->getSlug(), $response);
    }
}
