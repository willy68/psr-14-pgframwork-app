<?php

namespace App\Api\Controller;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface;

class ApiController
{
    /**
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @return Response
     */
    public function index(ServerRequestInterface $request): Response
    {
        $params = $request->getServerParams();

        return (new Response(200, [], json_encode($params)));
    }
}
