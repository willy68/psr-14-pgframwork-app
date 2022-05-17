<?php

namespace App\Account\Action;

use App\Auth\User;
use App\Auth\UserTable;
use App\Auth\DatabaseAuth;
use Mezzio\Router\RouterInterface;
use PgFramework\Database\Hydrator;
use PgFramework\Validator\Validator;
use PgFramework\Session\FlashService;
use PgFramework\Renderer\RendererInterface;
use PgFramework\Response\ResponseRedirect;
use Psr\Http\Message\ServerRequestInterface;

class SignupAction
{
    /**
     * @var RendererInterface
     */
    private $renderer;
    /**
     * @var UserTable
     */
    private $userTable;
    /**
     * @var Router
     */
    private $router;
    /**
     * @var DatabaseAuth
     */
    private $auth;
    /**
     * @var FlashService
     */
    private $flashService;

    public function __construct(
        RendererInterface $renderer,
        UserTable $userTable,
        RouterInterface $router,
        DatabaseAuth $auth,
        FlashService $flashService
    ) {
        $this->renderer = $renderer;
        $this->userTable = $userTable;
        $this->router = $router;
        $this->auth = $auth;
        $this->flashService = $flashService;
    }

    public function __invoke(ServerRequestInterface $request)
    {
        if ($request->getMethod() === 'GET') {
            return $this->renderer->render('@account/signup');
        }
        $params = $request->getParsedBody();
        $validator = (new Validator($params))
            ->required('username', 'email', 'password', 'password_confirm')
            ->length('username', 5)
            ->email('email')
            ->confirm('password')
            ->length('password', 4)
            ->unique('username', $this->userTable)
            ->unique('email', $this->userTable);
        if ($validator->isValid()) {
            $userParams = [
                'username' => $params['username'],
                'email'    => $params['email'],
                'password' => password_hash($params['password'], PASSWORD_DEFAULT)
            ];
            $this->userTable->insert($userParams);
            $user = Hydrator::hydrate($userParams, User::class);
            $user->id = $this->userTable->getPdo()->lastInsertId();
            $this->auth->setUser($user);
            $this->flashService->success('Votre compte a bien été créé');
            return new ResponseRedirect($this->router->generateUri('account'));
        }
        $errors = $validator->getErrors();
        return $this->renderer->render('@account/signup', [
            'errors' => $errors,
            'user'   => [
                'username' => $params['username'],
                'email'    => $params['email']
            ]
        ]);
    }
}
