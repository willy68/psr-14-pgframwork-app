<?php

declare(strict_types=1);

namespace PgFramework\Controller;

use stdClass;
use Mezzio\Router\RouterInterface;
use PgFramework\Database\Hydrator;
use PgFramework\Validator\Validator;
use PgFramework\Session\FlashService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use PgFramework\Actions\RouterAwareAction;
use PgFramework\Renderer\RendererInterface;
use Psr\Http\Message\ServerRequestInterface;

class CrudController
{
    use RouterAwareAction;

    /**
     * @var RendererInterface
     */
    private $renderer;

    /**
     * @var ManagerRegistry
     */
    protected $om;

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * Entity class
     *
     * @var string
     */
    protected $entity = stdClass::class;

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
        /** @var  EntityManagerInterface */
        $this->em = $this->om->getManagerForClass($this->entity);
        $this->router = $router;
        $this->flash = $flash;

        $this->renderer->addGlobal('viewPath', $this->viewPath);
        $this->renderer->addGlobal('routePrefix', $this->routePrefix);
    }

    /**
     * Liste les entitys Method GET
     *
     * @param ServerRequestInterface $request
     * @return string
     */
    public function index(ServerRequestInterface $request): string
    {
        $params = $request->getQueryParams();
        $repo = $this->em->getRepository($this->entity);
        $items = $repo->buildFindAll()->paginate(12, (int)($params['p'] ?? 1));

        return $this->renderer->render($this->viewPath . '/index', compact('items'));
    }

    /**
     * Edite un entity Method POST
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface|string
     * @throws NoRecordException
     */
    public function edit(ServerRequestInterface $request)
    {
        $repo = $this->em->getRepository($this->entity);
        $item = $repo->find($request->getAttribute('id'));
        $errors = false;
        $submited = false;

        if ($request->getMethod() === 'POST') {
            $validator = $this->getValidator($request);
            if ($validator->isValid()) {
                Hydrator::hydrate($this->getParams($request, $item), $item);
                try {
                    $this->em->persist($item);
                    $this->em->flush();
                } catch (ORMException $e) {
                    throw $e;
                }
                $this->flash->success($this->messages['edit']);
                return $this->redirect($this->routePrefix . '.index');
            }
            $submited = true;
            Hydrator::hydrate($request->getParsedBody(), $item);
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
     * @param ServerRequestInterface $request
     * @return ResponseInterface|string
     */
    public function create(ServerRequestInterface $request)
    {
        $item = $this->getNewEntity();
        $errors = false;
        $submited = false;
        if ($request->getMethod() === 'POST') {
            $validator = $this->getValidator($request);
            if ($validator->isValid()) {
                Hydrator::hydrate($this->getParams($request, $item), $item);
                try {
                    $this->em->persist($item);
                    $this->em->flush();
                } catch (ORMException $e) {
                    throw $e;
                }
                $this->flash->success($this->messages['create']);
                return $this->redirect($this->routePrefix . '.index');
            }
            $submited = true;
            Hydrator::hydrate($request->getParsedBody(), $item);
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
     * @param ServerRequestInterface $request
     * @return ResponseInterface|string
     */
    public function delete(ServerRequestInterface $request)
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
     * @param Request $request
     * @param mixed|null $item
     * @return array
     */
    protected function getParams(ServerRequestInterface $request, $item = null): array
    {
        return array_filter(array_merge($request->getParsedBody(), $request->getUploadedFiles()), function ($key) {
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
     * @param Request $request
     * @return Validator
     */
    protected function getValidator(ServerRequestInterface $request): Validator
    {
        return new Validator(array_merge($request->getParsedBody(), $request->getUploadedFiles()));
    }

    /**
     * @return mixed
     */
    protected function getNewEntity()
    {
        return new $this->entity();
    }
}
