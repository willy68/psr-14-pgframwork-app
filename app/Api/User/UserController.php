<?php

namespace App\Api\User;

use App\Models\User;
use Firebase\JWT\JWT;
use GuzzleHttp\Psr7\Response;
use App\Models\Administrateur;
use App\Api\AbstractApiController;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use ActiveRecord\Exceptions\RecordNotFound;
use Psr\Http\Message\ServerRequestInterface;

class UserController extends AbstractApiController
{

    /**
     * \DI\Container
     *
     * @var ContainerInterface $container
     */
    private $container;

    /**
     * Model class
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * UserController constructor.
     * @param ContainerInterface $c
     */
    public function __construct(ContainerInterface $c)
    {
        parent::__construct();
        $this->container = $c;
    }

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
                $users = $this->model::all($options);
            } else {
                $users = $this->model::all();
            }
        } catch (RecordNotFound $e) {
            return new Response(404);
        }
        if (empty($users)) {
            return new Response(404);
        }
        $json = $this->jsonArray($users);
        return new Response(200, [], $json);
    }

    /**
     * create user
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function create(ServerRequestInterface $request): ResponseInterface
    {
        $params = $this->getParams($request, $this->attributes);
        if (empty($params)) {
            return new Response(400);
        }
        try {
            $user = $this->model::find_by_email(['email' => $params['email']]);
        } catch (RecordNotFound $e) {
        } catch (\Exception $e) {
            return new Response(404);
        }

        if ($user) {
            return new Response(400);
        }
        if (isset($params['password']) && isset($params['username'])) {
            $pwd = password_hash(
                $params['username'] . $params['password'],
                PASSWORD_BCRYPT,
                ["cost" => 8]
            );
        }
        $params['password'] = $pwd;
        $user = new $this->model();
        $user->set_attributes($params);
        if ($user->save()) {
            $attributes = $request->getAttributes();
            if (isset($attributes[$this->foreignKey])) {
                $admin = new Administrateur();
                $admin->set_attributes(
                    [
                        'user_id' => $user->id,
                        $this->foreignKey => $attributes[$this->foreignKey]
                    ]
                );
                $admin->save();
            }
            return (new Response(200, [], $user->to_json()));
        } else {
            return new Response(400);
        }
    }

    /**
     * login
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws \Exception
     */
    public function login(ServerRequestInterface $request): ResponseInterface
    {
        $params = $this->getParams($request, ['email']);
        $queryParams = $request->getQueryParams();
        $options = [];
        /*SELECT `user`.* FROM `user`
        INNER JOIN `administrateur` ON(`administrateur`.user_id = `user`.id)
        WHERE `administrateur`.`entreprise_id` = $queryParams['entreprise_id'] AND
        `user`.`email` = $params['email']*/
        if (isset($queryParams['entreprise_id'])) {
            $options['joins'] = ['administrateurs'];
            $options['conditions'] = [
                "`user`.email = ? AND `administrateur`.entreprise_id = ?",
                $params['email'],
                $queryParams['entreprise_id']
            ];
        } else {
            $options['conditions'] = ["`user`.email = ?", $params['email']];
        }

        try {
            $user = $this->model::find($options);
        } catch (RecordNotFound $e) {
            return new Response(404);
        }
        if (!$user) {
            return new Response(404);
        }
        $token = $this->authenticate($request, $user->to_array());
        if ($token) {
            $userJwt = [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->role,
                'token' => $token
            ];
            $json = json_encode($userJwt);
            return new Response(200, [], $json);
        } else {
            return new Response(401);
        }
    }

    /**
     * Authenticate user
     *
     * @param ServerRequestInterface $request
     * @param array $user
     * @param int $exp
     * @param int $nbf
     * @return string
     * @throws \Exception
     */
    protected function authenticate(
        ServerRequestInterface $request,
        array $user,
        int $exp = 3600,
        int $nbf = 0
    ): string {
        $jwt = null;
        $params = $this->getParams($request, ['password']);
        $passCrypt = $user['username'] . $params['password'];

        if (password_verify($passCrypt, $user['password'])) {
            $jwt = $this->createJwt($user, $exp, $nbf);
        }
        return $jwt;
    }

    /**
     * Create Jwt token
     *
     * @param array $user
     * @param int $exp
     * @param int $nbf
     * @return string
     * @throws \Exception
     */
    protected function createJwt(array $user, int $exp = 3600, int $nbf = 0): string
    {
        $jwt = null;

        $key = $this->container->get('jwt.secret');

        $tokenId = base64_encode(random_bytes(32));
        $issuedAt = time();
        $notBefore = $issuedAt + $nbf;            // Adding 0 seconds
        $expire = $notBefore + $exp;           // Adding 3600 seconds
        $serverName = $_SERVER['SERVER_NAME'];     // Retrieve the server name from config file

        /*
        * Create the token as an array
        */
        $data = [
            'iat' => $issuedAt,         // Issued at: time when the token was generated
            'jti' => $tokenId,          // Json Token Id: an unique identifier for the token
            'iss' => $serverName,       // Issuer
            'nbf' => $notBefore,        // Not before
            'exp' => $expire,           // Expire
            'data' => [                  // Data related to the signer user
                'username' => $user['username'], // username from the users table
                'email' => $user['email'],       // User email
                'role' => $user['role']         // User role (user,admin)
            ]
        ];

        try {
            $jwt = JWT::encode($data, $key);
        } catch (\UnexpectedValueException $e) {
            return null;
        } catch (\DomainException $e) {
            return null;
        }

        return $jwt;
    }
}
