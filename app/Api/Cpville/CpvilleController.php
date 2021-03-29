<?php

namespace App\Api\Cpville;

use App\Models\Cpville;
use GuzzleHttp\Psr7\Response;
use App\Api\AbstractApiController;
use Psr\Http\Message\ResponseInterface;
use ActiveRecord\Exceptions\RecordNotFound;
use Psr\Http\Message\ServerRequestInterface;

class CpvilleController extends AbstractApiController
{

    /**
     * Model class
     *
     * @var \Cpville
     */
    protected $model = Cpville::class;

    /**
     * Default to 'entreprise_id'
     * @var string
     */
    protected $foreignKey = '';

    public function search(ServerRequestInterface $request): ResponseInterface
    {
        $options = [];
        // Default to
        $col = 'cp';
        if ($request->getAttribute('ville')) {
            $col = 'ville';
        }
        // For limit, offset, order
        $options = $this->getQueryOption($request, $options);
        // POST method
        $params = $this->getParams($request, ['search']);
        if (empty($params)) {
            // GET method
            $params['search'] = $request->getAttribute('search');
            if (!$params) {
                return new Response(404);
            }
        }
        $options['conditions'] = [$col . ' LIKE ?', $params['search'] . '%'];

        try {
            if (!empty($options)) {
                $cpville = $this->model::all($options);
            } else {
                $cpville = $this->model::all();
            }
        } catch (RecordNotFound $e) {
            return new Response(404);
        }

        if (empty($cpville)) {
            return new Response(404);
        }

        $json = $this->jsonArray($cpville);
        return new Response(200, [], $json);
    }
}
