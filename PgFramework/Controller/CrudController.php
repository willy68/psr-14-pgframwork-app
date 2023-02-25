<?php

declare(strict_types=1);

namespace PgFramework\Controller;

use Psr\Http\Message\ResponseInterface;
use stdClass;
use Mezzio\Router\RouterInterface;
use PgFramework\Database\Hydrator;
use PgFramework\Validator\Validator;
use PgFramework\Session\FlashService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use PgFramework\Actions\RouterAwareAction;
use PgFramework\Database\Doctrine\PaginatedEntityRepository;
use PgFramework\Renderer\RendererInterface;
use Psr\Http\Message\ServerRequestInterface;

class CrudController
{
    use RouterAwareAction;

    private RendererInterface $renderer;

    protected ManagerRegistry $om;

    protected ?EntityManagerInterface $em;

    protected string $entity = stdClass::class;

    private RouterInterface $router;

    private FlashService $flash;

    protected string $viewPath = '';

    protected string $routePrefix = '';

    protected array $messages = [
        'create' => "L'élément a bien été créé",
        'edit' => "L'élément a bien été modifié",
        'delete' => "L'élément a bien été supprimé"
    ];

    /**
     * @param RendererInterface $renderer
     * @param ManagerRegistry $om
     * @param RouterInterface $router
     * @param FlashService $flash
     */
    public function __construct(
        RendererInterface $renderer,
        ManagerRegistry $om,
        RouterInterface $router,
        FlashService $flash
    ) {
        $this->renderer = $renderer;
        $this->om = $om;
        $this->em = $this->om->getManagerForClass($this->entity);
        $this->router = $router;
        $this->flash = $flash;

        $this->renderer->addGlobal('viewPath', $this->viewPath);
        $this->renderer->addGlobal('routePrefix', $this->routePrefix);
    }

    /**
     * Liste-les entities Method GET
     *
     * @param ServerRequestInterface $request
     * @return string
     */
    public function index(ServerRequestInterface $request): string
    {
        $params = $request->getQueryParams();
        /** @var PaginatedEntityRepository $repo*/
        $repo = $this->em->getRepository($this->entity);
        $items = $repo->buildFindAll()->paginate(12, (int)($params['p'] ?? 1));

        return $this->renderer->render($this->viewPath . '/index', compact('items'));
    }

    /**
     * Edite un entity Method POST
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface|string
     */
    public function edit(ServerRequestInterface $request): string|ResponseInterface
    {
        $repo = $this->em->getRepository($this->entity);
        $item = $repo->find($request->getAttribute('id'));
        $errors = false;
        $submitted = false;

        if ($request->getMethod() === 'POST') {
            $validator = $this->getValidator($request);
            if ($validator->isValid()) {
                Hydrator::hydrate($this->getParams($request, $item), $item);
                $this->em->persist($item);
                $this->em->flush();
                $this->flash->success($this->messages['edit']);
                return $this->redirect($this->routePrefix . '.index');
            }
            $submitted = true;
            Hydrator::hydrate($request->getParsedBody(), $item);
            $errors = $validator->getErrors();
        }

        return $this->renderer->render(
            $this->viewPath . '/edit',
            $this->formParams(compact('item', 'errors', 'submitted'))
        );
    }

    /**
     * Crée un entity Method POST
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface|string
     */
    public function create(ServerRequestInterface $request): string|ResponseInterface
    {
        $item = $this->getNewEntity();
        $errors = false;
        $submitted = false;
        if ($request->getMethod() === 'POST') {
            $validator = $this->getValidator($request);
            if ($validator->isValid()) {
                Hydrator::hydrate($this->getParams($request, $item), $item);
                $this->em->persist($item);
                $this->em->flush();
                $this->flash->success($this->messages['create']);
                return $this->redirect($this->routePrefix . '.index');
            }
            $submitted = true;
            Hydrator::hydrate($request->getParsedBody(), $item);
            $errors = $validator->getErrors();
        }

        return $this->renderer->render(
            $this->viewPath . '/create',
            $this->formParams(compact('item', 'errors', 'submitted'))
        );
    }

    /**
     * Supprime un entity Method POST
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface|string
     */
    public function delete(ServerRequestInterface $request): string|ResponseInterface
    {
        $item = $this->em->getRepository($this->entity)->find($request->getAttribute('id'));
        $this->em->remove($item);
        $this->em->flush();
        $this->flash->success($this->messages['delete']);
        return $this->redirect($this->routePrefix . '.index');
    }

    /**
     * Filtre les paramètres reçu par la requête
     *
     * @param ServerRequestInterface $request
     * @param mixed|null $item
     * @return array
     */
    protected function getParams(ServerRequestInterface $request, mixed $item = null): array
    {
        return array_filter(array_merge($request->getParsedBody(), $request->getUploadedFiles()), function ($key) {
            return in_array($key, []);
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * get filtered Post params
     *
     * @param ServerRequestInterface $request
     * @param array $filter
     * @param bool $useKey
     * @return array
     */
    protected function getFilteredParams(ServerRequestInterface $request, array $filter, bool $useKey = false): array
    {
        if ($useKey) {
            $filter = array_keys($filter);
        }
        return array_filter(
            array_merge($request->getParsedBody(), $request->getUploadedFiles()),
            function ($key) use ($filter) {
                return in_array($key, $filter);
            },
            ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * @param array $params
     * @return array
     */
    protected function formParams(array $params): array
    {
        return $params;
    }

    /**
     * Get validator form fields
     *
     * @param ServerRequestInterface $request
     * @return Validator
     */
    protected function getValidator(ServerRequestInterface $request): Validator
    {
        return new Validator(array_merge($request->getParsedBody(), $request->getUploadedFiles()));
    }

    /**
     * @return mixed
     */
    protected function getNewEntity(): mixed
    {
        return new $this->entity();
    }
}
