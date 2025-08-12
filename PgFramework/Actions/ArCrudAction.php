<?php

declare(strict_types=1);

namespace PgFramework\Actions;

use ActiveRecord\Model;
use PgFramework\Database\Table;
use PgFramework\Validator\Validator;
use Pg\Router\RouterInterface;
use PgFramework\Session\FlashService;
use Psr\Http\Message\ResponseInterface;
use PgFramework\Database\NoRecordException;
use PgFramework\Renderer\RendererInterface;
use Psr\Http\Message\ServerRequestInterface as Request;

class ArCrudAction
{
    use RouterAwareAction;

    /**
     * @var RendererInterface
     */
    private $renderer;

    /**
     * @var Table
     */
    protected $table;

    /**
     * Model class
     *
     * @var string
     */
    protected $model = Model::class;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var FlashService
     */
    private $flash;

    /**
     * @var string
     */
    protected $viewPath;

    /**
     * @var string
     */
    protected $routePrefix;

    /**
     * @var array
     */
    protected $messages = [
        'create' => "L'élément a bien été créé",
        'edit' => "L'élément a bien été modifié",
        'delete' => "L'élément a bien été supprimé"
    ];

    /**
     * @param RendererInterface $renderer
     * @param Table $table
     * @param RouterInterface $router
     * @param FlashService $flash
     */
    public function __construct(
        RendererInterface $renderer,
        Table $table,
        RouterInterface $router,
        FlashService $flash
    ) {
        $this->renderer = $renderer;
        $this->table = $table;
        $this->router = $router;
        $this->flash = $flash;

        $this->renderer->addGlobal('viewPath', $this->viewPath);
        $this->renderer->addGlobal('routePrefix', $this->routePrefix);
    }

    /**
     * Liste les entitys Method GET
     *
     * @param Request $request
     * @return string
     */
    public function index(Request $request): string
    {
        $params = $request->getQueryParams();
        // $items = $this->table->findAll()->paginate(12, $params['p'] ?? 1);
        $items = $this->model::setPaginatedQuery($this->model::findAll())
                ::paginate(12, $params['p'] ?? 1);

        return $this->renderer->render($this->viewPath . '/index', compact('items'));
    }

    /**
     * Edite un entity Method POST
     *
     * @param Request $request
     * @return ResponseInterface|string
     * @throws NoRecordException
     */
    public function edit(Request $request)
    {
        // $item = $this->table->find($request->getAttribute('id'));
        /** @var \ActiveRecord\Model */
        $item = $this->model::find($request->getAttribute('id'));
        $errors = false;
        $submited = false;

        if ($request->getMethod() === 'POST') {
            $validator = $this->getValidator($request);
            if ($validator->isValid()) {
                $item->update_attributes($this->getParams($request, $item));
                // $this->table->update($item->id, $this->getParams($request, $item));
                $this->flash->success($this->messages['edit']);
                return $this->redirect($this->routePrefix . '.index');
            }
            $submited = true;
            // Hydrator::hydrate($request->getParsedBody(), $item);
            $item->set_attributes(
                $this->getFilteredParams($request, $item->attributes(), true)
            );
            $errors = $validator->getErrors();
        }

        return $this->renderer->render(
            $this->viewPath . '/edit',
            $this->formParams(compact('item', 'errors', 'submited'))
        );
    }

    /**
     * Crée un entity Method POST
     *
     * @param Request $request
     * @return ResponseInterface|string
     */
    public function create(Request $request)
    {
        // $item = $this->getNewEntity();
        /** @var \ActiveRecord\Model */
        $item = new $this->model();
        $errors = false;
        $submited = false;
        if ($request->getMethod() === 'POST') {
            $validator = $this->getValidator($request);
            if ($validator->isValid()) {
                // $this->table->insert($this->getParams($request, $item));
                $item->create($this->getParams($request, $item));
                $this->flash->success($this->messages['create']);
                return $this->redirect($this->routePrefix . '.index');
            }
            $submited = true;
            // Hydrator::hydrate($request->getParsedBody(), $item);
            $item->set_attributes(
                $this->getFilteredParams($request, $item->attributes(), true)
            );
            $errors = $validator->getErrors();
        }

        return $this->renderer->render(
            $this->viewPath . '/create',
            $this->formParams(compact('item', 'errors', 'submited'))
        );
    }

    /**
     * Supprime un entity Method POST
     *
     * @param Request $request
     * @return ResponseInterface|string
     */
    public function delete(Request $request)
    {
        /** @var \ActiveRecord\Model */
        $item = $this->model::find($request->getAttribute('id'));
        // $this->table->delete($request->getAttribute('id'));
        $item->delete($request->getAttribute('id'));
        return $this->redirect($this->routePrefix . '.index');
    }

    /**
     * Récupère les paramètres POST
     *
     * @param Request $request
     * @param mixed|null $item
     * @return array
     */
    protected function getParams(Request $request, $item = null): array
    {
        return array_filter($request->getParsedBody(), function ($key) {
            return in_array($key, []);
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * get filtered Post params
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param array $filter
     * @param bool $useKey
     * @return array
     */
    protected function getFilteredParams(Request $request, array $filter, bool $useKey = false): array
    {
        if ($useKey) {
            $filter = array_keys($filter);
        }
        return array_filter($request->getParsedBody(), function ($key) use ($filter) {
            return in_array($key, $filter);
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * Get validator form fields
     *
     * @param Request $request
     * @return Validator
     */
    protected function getValidator(Request $request): Validator
    {
        return new Validator(array_merge($request->getParsedBody(), $request->getUploadedFiles()));
    }

    /**
     * @return mixed
     */
    protected function getNewEntity()
    {
        return [];
    }

    /**
     * @param array $params
     * @return array
     */
    protected function formParams(array $params): array
    {
        return $params;
    }
}
