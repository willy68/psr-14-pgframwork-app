<?php

namespace App\Api\Client;

use App\Models\Client;
use GuzzleHttp\Psr7\Response;
use App\Api\AbstractApiController;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ClientController extends AbstractApiController
{

    /**
     * Model class
     *
     * @var string
     */
    protected $model = Client::class;

    public function create(ServerRequestInterface $request): ResponseInterface
    {
        // Get body params
        $params = $this->getParams($request, $this->attributes);
        $conditions = ['code_client' => $params['code_client']];
        if ($request->getAttribute($this->foreignKey)) {
            $conditions[$this->foreignKey] = $request->getAttribute($this->foreignKey);
        }
        $client = $this->model::find('last', $conditions);
        if ($client) {
            return new Response(400);
        }
        return parent::create($request);
    }
}
