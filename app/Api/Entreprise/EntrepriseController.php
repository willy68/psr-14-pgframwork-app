<?php

namespace App\Api\Entreprise;

use App\Models\Entreprise;
use GuzzleHttp\Psr7\Response;
use App\Models\Administrateur;
use App\Api\AbstractApiController;
use Psr\Http\Message\ResponseInterface;
use ActiveRecord\Exceptions\RecordNotFound;
use Psr\Http\Message\ServerRequestInterface;

class EntrepriseController extends AbstractApiController
{

    /**
     * Model class
     *
     * @var string
     */
    protected $model = Entreprise::class;

    /**
     * Default to 'entreprise_id'
     * @var string
     */
    protected $foreignKey = 'user_id';

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function list(ServerRequestInterface $request): ResponseInterface
    {
        $options = [];
        $params = $request->getAttributes();
        $options = $this->getQueryOption($request, $options);
        if (isset($params[$this->foreignKey])) {
            $options['joins'] = ['administrateurs'];
            $options['conditions'] = [
                "`administrateur`." . $this->foreignKey . " = ?",
                [$params[$this->foreignKey]]
            ];
        }
        try {
            if (!empty($options)) {
                $entreprises = $this->model::all($options);
            } else {
                $entreprises = $this->model::all();
            }
        } catch (RecordNotFound $e) {
            return new Response(404);
        }
        if (empty($entreprises)) {
            return new Response(404);
        }
        $json = $this->jsonArray($entreprises);
        return new Response(200, [], $json);
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function create(ServerRequestInterface $request): ResponseInterface
    {
        if (!$request->getAttribute($this->foreignKey)) {
            return new Response(400);
        }
        $params = $this->getParams($request, $this->attributes);
        if (empty($params)) {
            return new Response(400);
        }
        try {
            $entreprise = $this->model::find_by_siret(array('siret' => $params['siret']));
            if ($entreprise) {
                return new Response(400);
            }
        } catch (RecordNotFound $e) {
        } catch (\Exception $e) {
            return new Response(404);
        }
        $entreprise = new $this->model();
        $entreprise->set_attributes($params);
        try {
            if ($entreprise->save()) {
                $admin = new Administrateur();
                $admin->set_attributes(array(
                    'user_id' => $request->getAttribute('user_id'),
                    'entreprise_id' => $entreprise->id
                ));
                $admin->save();
            } else {
                return new Response(400);
            }
        } catch (\Exception $e) {
            if (isset($entreprise->id)) {
                $entreprise->delete();
            }
            return new Response(400);
        }
        return new Response(200, [], $entreprise->to_json());
    }
}
