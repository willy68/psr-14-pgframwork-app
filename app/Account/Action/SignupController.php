<?php

namespace App\Account\Action;

use App\Auth\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Mezzio\Router\RouterInterface;
use PgFramework\Auth\Auth;
use PgFramework\Renderer\RendererInterface;
use PgFramework\Response\ResponseRedirect;
use PgFramework\Router\Annotation\Route;
use PgFramework\Security\Hasher\PasswordHasherInterface;
use PgFramework\Session\FlashService;
use PgFramework\Validator\Validator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

#[Route('/inscription', name: 'account.signup', methods:['GET'])]
#[Route('/inscription', methods:['POST'])]
class SignupController
{
    private RendererInterface $renderer;

    private RouterInterface $router;

    private EntityManagerInterface $em;

    private Auth $auth;

    private FlashService $flashService;

    private PasswordHasherInterface $passwordHasher;

    public function __construct(
        RendererInterface $renderer,
        RouterInterface $router,
        EntityManagerInterface $em,
        Auth $auth,
        FlashService $flashService,
        PasswordHasherInterface $passwordHasher
    ) {
        $this->renderer = $renderer;
        $this->router = $router;
        $this->em = $em;
        $this->auth = $auth;
        $this->flashService = $flashService;
        $this->passwordHasher = $passwordHasher;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ServerRequestInterface $request): ResponseInterface|string
    {
        if ($request->getMethod() === 'GET') {
            return $this->renderer->render('@account/signup');
        }

        $params = $request->getParsedBody();
        $validator = $this->getValidator($params);
        if ($validator->isValid()) {
            $user = new User();
            $user->setUsername($params['username']);
            $user->setEmail($params['email']);
            $password = $this->passwordHasher->hash($params['password']);
            $user->setPassword($password);
            $user->setRoles(['ROLE_USER']);
            $this->em->persist($user);
            $this->em->flush();

            if ($user->getId()) {
                $this->auth->setUser($user);
                $this->flashService->success('Votre compte a bien été créé');
                return new ResponseRedirect($this->router->generateUri('account'));
            }
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

    /**
     * @param array $params
     * @return Validator
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getValidator(array $params): Validator
    {
        return (new Validator($params))
            ->required('username', 'email', 'password')
            ->addRules([
                'username' => 'min:2|unique:' . User::class . ',username,,Cet utilisateur existe déjà',
                'email'    => 'email|unique:' . User::class . ',email,,Cet Email existe déjà',
                'password' => 'min:4',
                'password_confirm' => 'confirm:password',
            ]);
    }
}
