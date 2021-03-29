<?php

namespace App\Api\Client\Adresse;

use App\Models\Adresse;
use GuzzleHttp\Psr7\Response;
use App\Api\AbstractApiController;
use Psr\Http\Message\ResponseInterface;
use ActiveRecord\Exceptions\RecordNotFound;
use Psr\Http\Message\ServerRequestInterface;

class AdresseController extends AbstractApiController
{
    /**
     * Model class
     *
     * @var string
     */
    protected $model = Adresse::class;

    /**
     * Default to 'entreprise_id'
     * @var string
     */
    protected $foreignKey = 'client_id';

    public function list(ServerRequestInterface $request): ResponseInterface
    {
        $options = [];
        $adresse_type_id = $this->getQueryOption($request, [], ['adresse_type_id']);
        $attributes = $request->getAttributes();
        $conditions = [];
        if (isset($adresse_type_id['adresse_type_id'])) {
            $conditions = ['adresse_type_id = ?', $adresse_type_id['adresse_type_id']];
        }
        if (!empty($this->foreignKey) && (isset($attributes[$this->foreignKey]))) {
            if (isset($adresse_type_id['adresse_type_id'])) {
                $conditions = [
                    $this->foreignKey . ' = ? AND adresse_type_id = ?',
                    $attributes[$this->foreignKey],
                    $adresse_type_id['adresse_type_id']
                ];
            } else {
                $conditions = [$this->foreignKey . ' = ?', $attributes[$this->foreignKey]];
            }
        }
        if (!empty($conditions)) {
            $options['conditions'] = $conditions;
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
        $json = $this->jsonArray($models);
        return new Response(200, [], $json);
    }
}
