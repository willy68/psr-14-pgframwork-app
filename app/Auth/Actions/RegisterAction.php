<?php

namespace App\Auth\Actions;

use App\Auth\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use PgFramework\Auth\AuthSession;
use Mezzio\Router\RouterInterface;
use PgFramework\Validator\Validator;
use PgFramework\Session\FlashService;
use PgFramework\Router\Annotation\Route;
use Mezzio\Session\SessionInterface;
use PgFramework\Actions\RouterAwareAction;
use PgFramework\Response\ResponseRedirect;
use PgFramework\Renderer\RendererInterface;
use Psr\Http\Message\ServerRequestInterface;
use PgFramework\Security\Hasher\PasswordHasherInterface;

class RegisterAction
{
    use RouterAwareAction;

    /**
     * @var RendererInterface
     */
    private $renderer;

    /**
     * @var AuthSession
     */
    private $auth;

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var PasswordHasherInterface
     */
    private $hasher;

    /**
     * @var array
     */
    protected $messages = [
        'create' => "Utilisateur à bien été créé"
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
     * @Route("/register", name="auth.register")
     */
    #[Route(path: "/register", name: "auth.register", methods: ['GET'])]
    #[Route(path: "/register", methods: ['POST'])]
    public function __invoke(ServerRequestInterface $request)
    {
        $user = new User();
        $errors = false;
        $submited = false;

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
                    $path = $this->session->get('auth.redirect') ?: $this->router->generateUri('admin');
                    $this->session->unset('auth.redirect');
                    $response = new ResponseRedirect($path);
                    if ($params['connect']) {
                        $user = $this->auth->login($user->getUsername(), $params['password']);
                    }
                    return $response;
                }
                (new FlashService($this->session))->error('Un problème est survenu, réessayer de vous enregistrer');
            } else {
                $submited = true;
                $errors = $validator->getErrors();
            }
        }
        return $this->renderer->render(
            '@auth/register',
            compact('user', 'errors', 'submited')
        );
    }

    /**
     * @param Request $request
     * @return Validator
     */
    protected function getValidator(ServerRequestInterface $request): Validator
    {
        $validator = (new Validator($request->getParsedBody()))
            ->required('username', 'email', 'password')
            ->addRules([
                'username' => 'min:2|unique:App\Auth\Entity\User,username,,Cet utilisateur existe déjà',
                'email'    => 'email|unique:App\Auth\Entity\User,email,,Cet Email existe déjà',
                'email_confirm' => 'email|confirm:email',
                'password'      => 'min:4',
            ]);
        return $validator;
    }
}
