<?php

namespace App\Account\Action;

use App\Auth\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use PgFramework\Auth\Auth;
use PgFramework\Auth\LoggedInMiddleware;
use PgFramework\Renderer\RendererInterface;
use PgFramework\Response\ResponseRedirect;
use PgFramework\Router\Annotation\Route;
use PgFramework\Security\Hasher\PasswordHasherInterface;
use PgFramework\Session\FlashService;
use PgFramework\Validator\Validator;
use Psr\Http\Message\ServerRequestInterface;

#[Route('/mon-profil', methods:['POST'], middlewares:[LoggedInMiddleware::class])]
class AccountEditController
{
    private RendererInterface $renderer;
    private FlashService $flashService;
    private EntityManagerInterface $em;
    private Auth $auth;
    private PasswordHasherInterface $hasher;

    public function  __construct(
        RendererInterface $renderer,
        FlashService $flashService,
        Auth $auth,
        EntityManagerInterface $em,
        PasswordHasherInterface $hasher
    ) {
        $this->renderer = $renderer;
        $this->flashService = $flashService;
        $this->em = $em;
        $this->auth = $auth;
        $this->hasher = $hasher;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseRedirect|string
     */
    public function __invoke(ServerRequestInterface $request): ResponseRedirect|string
    {
        $user = $this->em->find(User::class, $this->auth->getUser()->getId());
        $params = $request->getParsedBody();
        $validator = (new Validator($params))
            ->confirm('password')
            ->required('username', 'firstname', 'lastname');
        if ($validator->isValid()) {
            $user->setUsername($params['username']);
            $user->setFirstname($params['firstname']);
            $user->setLastname($params['lastname']);
            if (!empty($params['email'])) {
                $user->setEmail($params['email']);
            }
            if (!empty($params['password'])) {
                $user->setPassword($this->hasher->hash($params['password']));
            }
            $this->em->persist($user);
            $this->em->flush();
            $this->flashService->success('Votre compte a bien été mis à jour');
            return new ResponseRedirect($request->getUri()->getPath());
        }
        $errors = $validator->getErrors();
        return $this->renderer->render('@account/account', compact('user', 'errors'));
    }
}