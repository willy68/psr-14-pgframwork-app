<?php

namespace App\Auth\Actions;

use App\Auth\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Mezzio\Router\RouterInterface;
use Mezzio\Session\SessionInterface;
use PgFramework\Actions\RouterAwareAction;
use PgFramework\Auth\AuthSession;
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

class RegisterAction
{
    use RouterAwareAction;

    private RendererInterface $renderer;

    private AuthSession $auth;

    private SessionInterface $session;

    private RouterInterface $router;

    private EntityManagerInterface $em;

    private PasswordHasherInterface $hasher;

    protected array $messages = [
        'create' => "Votre compte à bien été créé"
    ];

    public function __construct(
        RendererInterface $renderer,
        EntityManagerInterface $em,
        PasswordHasherInterface $hasher,
        AuthSession $auth,
        SessionInterface $session,
        RouterInterface $router
    ) {
        $this->renderer = $renderer;
        $this->em = $em;
        $this->hasher = $hasher;
        $this->auth = $auth;
        $this->session = $session;
        $this->router = $router;
    }

    /**
     * @Route("/register", name="auth.register", methods={"GET"})
     * @Route("/register", methods={"POST"})
     */
    #[Route(path: "/register", name: "auth.register", methods: ['GET'])]
    #[Route(path: "/register", methods: ['POST'])]
    public function __invoke(ServerRequestInterface $request): ResponseInterface|string
    {
        $user = new User();
        $errors = false;
        $submitted = false;

        if ($request->getMethod() === 'POST') {
            $params = $request->getParsedBody();

            $user->setUsername($params['username']);
            $user->setEmail($params['email']);

            $validator = $this->getValidator($request);
            if ($validator->isValid()) {
                $password = $this->hasher->hash($params['password']);
                $user->setPassword($password);
                $user->setRoles(['ROLE_USER']);
                $this->em->persist($user);
                $this->em->flush();

                if ($user->getId()) {
                    (new FlashService($this->session))->success($this->messages['create']);
                    $path = $this->router->generateUri('account');
                    $response = new ResponseRedirect($path);
                    if ($params['connect']) {
                        $this->auth->setUser($user);
                    }
                    return $response;
                }
                (new FlashService($this->session))->error('Un problème est survenu, réessayer de vous enregistrer');
            } else {
                $submitted = true;
                $errors = $validator->getErrors();
            }
        }
        return $this->renderer->render(
            '@auth/register',
            compact('user', 'errors', 'submitted')
        );
    }

    /**
     * @param ServerRequestInterface $request
     * @return Validator
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getValidator(ServerRequestInterface $request): Validator
    {
        return (new Validator($request->getParsedBody()))
            ->required('username', 'email', 'password')
            ->addRules([
                'username' => 'min:2|unique:App\Auth\Entity\User,username,,Cet utilisateur existe déjà',
                'email' => 'email|unique:App\Auth\Entity\User,email,,Cet Email existe déjà',
                'password' => 'min:4',
                'password_confirm' => 'password|confirm:password',
            ]);
    }
}
