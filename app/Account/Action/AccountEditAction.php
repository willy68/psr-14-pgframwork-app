<?php

namespace App\Account\Action;

use App\Auth\UserTable;
use PgFramework\Auth\Auth;
use PgFramework\Auth\Middleware\LoggedInMiddleware;
use PgFramework\Renderer\RendererInterface;
use PgFramework\Response\ResponseRedirect;
use PgFramework\Router\Annotation\Route;
use PgFramework\Security\Hasher\PasswordHasherInterface;
use PgFramework\Session\FlashService;
use PgFramework\Validator\Validator;
use Psr\Http\Message\ServerRequestInterface;

#[Route('/mon-profil', methods:['POST'], middlewares:[LoggedInMiddleware::class])]
class AccountEditAction
{
    private RendererInterface $renderer;

    private Auth $auth;

    private FlashService $flashService;

    private UserTable $userTable;

    private PasswordHasherInterface $hasher;

    public function __construct(
        RendererInterface $renderer,
        Auth $auth,
        PasswordHasherInterface $hasher,
        FlashService $flashService,
        UserTable $userTable
    ) {
        $this->renderer = $renderer;
        $this->auth = $auth;
        $this->flashService = $flashService;
        $this->userTable = $userTable;
        $this->hasher = $hasher;
    }

    public function __invoke(ServerRequestInterface $request): ResponseRedirect|string
    {
        $user = $this->auth->getUser();
        $params = $request->getParsedBody();
        $validator = (new Validator($params))
            ->confirm('password')
            ->required('username', 'firstname', 'lastname');
        if ($validator->isValid()) {
            $userParams = [
                'username'  => $params['username'],
                'firstname' => $params['firstname'],
                'lastname'  => $params['lastname']
            ];
            if (!empty($params['email'])) {
                $userParams['email'] = $params['email'];
            }
            if (!empty($params['password'])) {
                $userParams['password'] = $this->hasher->hash($params['password']);
            }
            $this->userTable->update($user->getId(), $userParams);
            $this->flashService->success('Votre compte a bien été mis à jour');
            return new ResponseRedirect($request->getUri()->getPath());
        }
        $errors = $validator->getErrors();
        return $this->renderer->render('@account/account', compact('user', 'errors'));
    }
}
