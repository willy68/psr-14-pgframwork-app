<?php

namespace PgFramework\Actions;

use PgFramework\Database\NoRecordException;
use PgFramework\Database\Table;
use Mezzio\Router\RouterInterface;
use PgFramework\Database\Hydrator;
use PgFramework\Validator\Validator;
use PgFramework\Session\FlashService;
use Psr\Http\Message\ResponseInterface;
use PgFramework\Renderer\RendererInterface;
use Psr\Http\Message\ServerRequestInterface as Request;

class CrudAction
{
    use RouterAwareAction;

    private RendererInterface $renderer;

    private RouterInterface $router;

    protected Table $table;

    private FlashService $flash;

    protected string $viewPath;

    protected string $routePrefix;

    protected array $messages = [
        'create' => "L'élément a bien été créé",
        'edit'   => "L'élément a bien été modifié"
    ];

    protected array $acceptedParams = [];

    public function __construct(
        RendererInterface $renderer,
        RouterInterface $router,
        Table $table,
        FlashService $flash
    ) {
        $this->renderer = $renderer;
        $this->router = $router;
        $this->table = $table;
        $this->flash = $flash;
    }

    public function __invoke(Request $request): string|ResponseInterface
    {
        $this->renderer->addGlobal('viewPath', $this->viewPath);
        $this->renderer->addGlobal('routePrefix', $this->routePrefix);
        if ($request->getMethod() === 'DELETE') {
            return $this->delete($request);
        }
        if (substr((string)$request->getUri(), -3) === 'new') {
            return $this->create($request);
        }
        if ($request->getAttribute('id')) {
            return $this->edit($request);
        }
        return $this->index($request);
    }

    /**
     * Affiche la liste des éléments
     * @param Request $request
     * @return string
     */
    public function index(Request $request): string
    {
        $params = $request->getQueryParams();
        $items = $this->table->findAll()->paginate(12, $params['p'] ?? 1);

        return $this->renderer->render($this->viewPath . '/index', compact('items'));
    }

    /**
     * Edite un élément
     * @param Request $request
     * @return ResponseInterface|string
     * @throws NoRecordException
     */
    public function edit(Request $request): string|ResponseInterface
    {
        $id = (int)$request->getAttribute('id');
        $item = $this->table->find($id);
        $errors = null;

        if ($request->getMethod() === 'POST') {
            $validator = $this->getValidator($request);
            if ($validator->isValid()) {
                $this->table->update($id, $this->prePersist($request, $item));
                $this->postPersist($request, $item);
                $this->flash->success($this->messages['edit']);
                return $this->redirect($this->routePrefix . '.index');
            }
            $errors = $validator->getErrors();
            Hydrator::hydrate($request->getParsedBody(), $item);
        }

        return $this->renderer->render(
            $this->viewPath . '/edit',
            $this->formParams(compact('item', 'errors'))
        );
    }

    /**
     * Crée un nouvel élément
     * @param Request $request
     * @return ResponseInterface|string
     */
    public function create(Request $request): string|ResponseInterface
    {
        $item = $this->getNewEntity();
        $errors = null;
        if ($request->getMethod() === 'POST') {
            $validator = $this->getValidator($request);
            if ($validator->isValid()) {
                $this->table->insert($this->prePersist($request, $item));
                $this->postPersist($request, $item);
                $this->flash->success($this->messages['create']);
                return $this->redirect($this->routePrefix . '.index');
            }
            Hydrator::hydrate($request->getParsedBody(), $item);
            $errors = $validator->getErrors();
        }
        return $this->renderer->render(
            $this->viewPath . '/create',
            $this->formParams(compact('item', 'errors'))
        );
    }

    /**
     * Action de suppression
     *
     * @param Request $request
     * @return ResponseInterface
     */
    public function delete(Request $request): ResponseInterface
    {
        $this->table->delete($request->getAttribute('id'));
        return $this->redirect($this->routePrefix . '.index');
    }

    /**
     * Filtre les paramètres reçu par la requête
     *
     * @param Request $request
     * @param $item
     * @return array
     */
    protected function prePersist(Request $request, $item): array
    {
        return array_filter(array_merge($request->getParsedBody(), $request->getUploadedFiles()), function ($key) {
            return in_array($key, $this->acceptedParams);
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * Permet d’effectuer un traitement après la persistence
     * @param Request $request
     * @param $item
     */
    protected function postPersist(Request $request, $item): void
    {
    }

    /**
     * Génère le validateur pour valider les données
     *
     * @param Request $request
     * @return Validator
     */
    protected function getValidator(Request $request): Validator
    {
        return new Validator(array_merge($request->getParsedBody(), $request->getUploadedFiles()));
    }

    /**
     * Génère une nouvelle entité pour l’action de création
     */
    protected function getNewEntity()
    {
        $entity = $this->table->getEntity();
        return new $entity();
    }

    /**
     * Permet de traiter les paramètres à envoyer à la vue
     *
     * @param array $params
     * @return array
     */
    protected function formParams(array $params): array
    {
        return $params;
    }
}
