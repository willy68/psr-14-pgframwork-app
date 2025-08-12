<?php

namespace App\Auth\Actions;

use App\Auth\Entity\User;
use App\Auth\Mailer\PasswordResetMailer;
use Exception;
use Pg\Router\RouterInterface;
use PgFramework\Auth\Provider\UserProviderInterface;
use PgFramework\Renderer\RendererInterface;
use PgFramework\Response\ResponseRedirect;
use PgFramework\Router\Annotation\Route;
use PgFramework\Session\FlashService;
use PgFramework\Validator\Validator;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

/**
 * @Route("/password", name="auth.password")
 */
#[Route('/password', name: 'auth.password')]
class PasswordForgetController
{
    private RendererInterface $renderer;
    private RouterInterface $router;
    private PasswordResetMailer $mailer;
    private FlashService $flashService;
    private UserProviderInterface $userProvider;

    public function __construct(
        RendererInterface $renderer,
        UserProviderInterface $userProvider,
        RouterInterface $router,
        PasswordResetMailer $mailer,
        FlashService $flashService
    ) {
        $this->renderer = $renderer;
        $this->userProvider = $userProvider;
        $this->router = $router;
        $this->mailer = $mailer;
        $this->flashService = $flashService;
    }

    /**
     * @throws TransportExceptionInterface
     * @throws Exception
     */
    public function __invoke(ServerRequestInterface $request): ResponseRedirect|string
    {
        if ($request->getMethod() === 'GET') {
            return $this->renderer->render('@auth/password');
        }
        $params = $request->getParsedBody();
        $validator = (new Validator($params))
            ->notEmpty('email')
            ->email('email');
        if ($validator->isValid()) {
            /** @var User $user */
            if (($user = $this->userProvider->getUser('email', $params['email']))) {
                $token = $this->userProvider->resetPassword($user);
                $this->mailer->send($user->getEmail(), [
                    'id' => $user->getId(),
                    'token' => $token
                ]);
                $this->flashService->success('Un email vous a été envoyé');
                return new ResponseRedirect($this->router->generateUri('blog.index'));
            } else {
                $errors = ['email' => 'Aucun utilisateur ne correspond à cet email'];
            }
        } else {
            $errors = $validator->getErrors();
        }
        return $this->renderer->render('@auth/password', compact('errors'));
    }
}
