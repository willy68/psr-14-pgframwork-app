<?php

namespace App\Account\Action;

use App\Auth\Entity\User;
use App\Auth\UserTable;
use App\Auth\DatabaseAuth;
use Mezzio\Router\RouterInterface;
use PgFramework\Database\Hydrator;
use PgFramework\Security\Hasher\PasswordHasherInterface;
use PgFramework\Validator\Validator;
use PgFramework\Session\FlashService;
use PgFramework\Renderer\RendererInterface;
use PgFramework\Response\ResponseRedirect;
use PgFramework\Router\Annotation\Route;
use Psr\Http\Message\ServerRequestInterface;

#[Route('/inscription', name: 'account.signup', methods:['GET'])]
#[Route('/inscription', methods:['POST'])]
class SignupAction
{
    private RendererInterface $renderer;

    private UserTable $userTable;

    private RouterInterface $router;

    private DatabaseAuth $auth;

    private FlashService $flashService;

    private PasswordHasherInterface $hasher;

    public function __construct(
        RendererInterface $renderer,
        UserTable $userTable,
        RouterInterface $router,
        DatabaseAuth $auth,
        FlashService $flashService,
        PasswordHasherInterface $hasher
    ) {
        $this->renderer = $renderer;
        $this->userTable = $userTable;
        $this->router = $router;
        $this->auth = $auth;
        $this->flashService = $flashService;
        $this->hasher = $hasher;
    }

    public function __invoke(ServerRequestInterface $request): ResponseRedirect|string
    {
        if ($request->getMethod() === 'GET') {
            return $this->renderer->render('@account/signup');
        }
        $params = $request->getParsedBody();
        $validator = (new Validator($params))
            ->required('username', 'email', 'password', 'password_confirm')
            ->length('username', 4)
            ->email('email')
            ->confirm('password')
            ->length('password', 4)
            ->unique('username', $this->userTable)
            ->unique('email', $this->userTable);
        if ($validator->isValid()) {
            $userParams = [
                'username' => $params['username'],
                'email'    => $params['email'],
                'password' => $this->hasher->hash($params['password']),
                'roles'    => json_encode(['ROLE_USER'])
            ];
            $this->userTable->insert($userParams);
            /** @var User $user */
            $user = Hydrator::hydrate($userParams, User::class);
            $user->setId($this->userTable->getPdo()->lastInsertId());
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
