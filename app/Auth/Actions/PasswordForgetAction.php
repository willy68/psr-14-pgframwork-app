<?php

namespace App\Auth\Actions;

use App\Auth\Entity\User;
use App\Auth\Mailer\PasswordResetMailer;
use App\Auth\UserTable;
use Pg\Router\RouterInterface;
use PgFramework\Database\NoRecordException;
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
class PasswordForgetAction
{
    private RendererInterface $renderer;
    private UserTable $userTable;
    private RouterInterface $router;
    private PasswordResetMailer $mailer;
    private FlashService $flashService;

    public function __construct(
        RendererInterface $renderer,
        UserTable $userTable,
        RouterInterface $router,
        PasswordResetMailer $mailer,
        FlashService $flashService
    ) {
        $this->renderer = $renderer;
        $this->userTable = $userTable;
        $this->router = $router;
        $this->mailer = $mailer;
        $this->flashService = $flashService;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseRedirect|string
     * @throws TransportExceptionInterface
     * @throws \Exception
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
            try {
                /** @var User $user */
                $user = $this->userTable->findBy('email', $params['email']);
                $token = $this->userTable->resetPassword($user->getId());
                $this->mailer->send($user->getEmail(), [
                    'id' => $user->getId(),
                    'token' => $token
                ]);
                $this->flashService->success('Un email vous a été envoyé');
                return new ResponseRedirect($this->router->generateUri('blog.index'));
            } catch (NoRecordException $e) {
                $errors = ['email' => 'Aucun utilisateur ne correspond à cet email'];
            }
        } else {
            $errors = $validator->getErrors();
        }
        return $this->renderer->render('@auth/password', compact('errors'));
    }
}
