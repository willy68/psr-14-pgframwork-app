<?php

namespace App\Api;

use ActiveRecord\Model;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use ActiveRecord\Exceptions\RecordNotFound;
use Psr\Http\Message\ServerRequestInterface;

class AbstractApiController
{

    /**
     * Model class
     *
     * @var string
     */
    protected $model = Model::class;

    /**
     * Attributs du model
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * Default to 'entreprise_id'
     * @var string
     */
    protected $foreignKey = 'entreprise_id';

    /**
     * AbstractApiController constructor.
     */
    public function __construct()
    {
        $this->attributes = $this->getModelAttributes();
    }

    /**
     * Get list of record
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function list(ServerRequestInterface $request): ResponseInterface
    {
        $options = [];
        $attributes = $request->getAttributes();

        if (!empty($this->foreignKey) && (isset($attributes[$this->foreignKey]))) {
            $options['conditions'] = [$this->foreignKey . ' = ?', $attributes[$this->foreignKey]];
        }

        $options = $this->getQueryOption($request, $options);
        try {
            if (!empty($options)) {
                $models = $this->model::all($options);
            } else {
                $models = $this->model::all();
            }
        } catch (RecordNotFound $e) {
            return new Response(404);
        }
        if (empty($models)) {
            return new Response(404);
        }
        $json = $this->jsonArray(
            $models,
            isset($options['include']) ? ['include' => $options['include']] : []
        );
        return new Response(200, [], $json);
    }

    /**
     * Get model by id
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function get(ServerRequestInterface $request): ResponseInterface
    {
        $options = [];
        $options = $this->getQueryOption($request, $options);
        $id = $request->getAttribute('id', 0);
        try {
            $model = $this->model::find($id);
        } catch (RecordNotFound $e) {
            return new Response(404);
        }
        return new Response(
            200,
            [],
            $model->to_json(isset($options['include']) ? ['include' => $options['include']] : [])
        );
    }
    /**
     * Update model by id
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function update(ServerRequestInterface $request): ResponseInterface
    {
        $id = (int) $request->getAttribute('id', 0);
        try {
            $model = $this->model::find($id);
        } catch (RecordNotFound $e) {
            return new Response(404);
        }
        $params = $this->getParams($request, $this->attributes);
        if ($model->update_attributes($params)) {
            return new Response(200, [], $model->to_json());
        } else {
            return new Response(400);
        }
    }

    /**
     * Create record
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function create(ServerRequestInterface $request): ResponseInterface
    {
        // Get body params
        $params = $this->getParams($request, $this->attributes);

        // Get route params
        if (!empty($this->foreignKey)) {
            if ($request->getAttribute($this->foreignKey)) {
                $params[$this->foreignKey] = $request->getAttribute($this->foreignKey);
            }
        }
        $model = new $this->model();
        $model->set_attributes($params);

        if ($model->save()) {
            return (new Response(200, [], $model->to_json()));
        } else {
            return new Response(400);
        }
    }

    /**
     * Delete model by id
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function delete(ServerRequestInterface $request): ResponseInterface
    {
        $id = $request->getAttribute('id', 0);

        try {
            $model = $this->model::find($id);
        } catch (RecordNotFound $e) {
            return new Response(404);
        }

        if ($model->delete()) {
            return new Response(200);
        } else {
            return new Response(400);
        }
    }

    /**
     * Return filter Post body from SPA
     *
     * @param ServerRequestInterface $request
     * @param array $fields
     * @return array
     */
    protected function getParams(ServerRequestInterface $request, array $fields): array
    {
        $params = json_decode((string) $request->getBody(), true);
        if (is_null($params)) {
            return [];
        }
        return array_filter($params, function ($key) use ($fields) {
            return in_array($key, $fields);
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * Get fields from model
     *
     * @return array
     */
    protected function getModelAttributes(): array
    {
        return array_filter(
            array_keys((new $this->model())->attributes()),
            function ($field) {
                /* 'id', 'created_at', 'updated_at' sont gérées par ActiveRecord */
                return !in_array($field, ['id', 'created_at', 'updated_at']);
            }
        );
    }

    /**
     * Récupère les queryParams de la requète filtrer par les clés
     * du tableau $filter
     *
     * @param ServerRequestInterface $request
     * @param array $options
     * @param array|null $filter
     * @return array
     */
    protected function getQueryOption(ServerRequestInterface $request, array $options, ?array $filter = []): array
    {
        if (empty($filter)) {
            $filter = ['limit', 'offset', 'order', 'include'];
        }
        $queryOptions = $request->getQueryParams();
        if (!empty($queryOptions)) {
            array_walk($queryOptions, function ($value, $key) use (&$options, $filter) {
                if (in_array($key, $filter)) {
                    if ($key === 'include') {
                        $options[$key] = [$value];
                    } else {
                        $options[$key] = $value;
                    }
                }
            });
        }
        return $options;
    }

    /**
     *
     * Transform le tableau $record
     * en un tableau d'objets json
     *
     * @param array $records
     * @param array $include
     * @return string
     */
    public function jsonArray(array $records, $include = []): string
    {
        $json = join(',', array_map(function ($record) use ($include) {
            return $record->to_json($include);
        }, $records));
        return '[' . $json . ']';
    }
}
