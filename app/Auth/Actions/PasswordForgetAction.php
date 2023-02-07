<?php

namespace App\Auth\Actions;

use App\Auth\UserTable;
use PgFramework\Validator\Validator;
use PgFramework\Session\FlashService;
use App\Auth\Mailer\PasswordResetMailer;
use Mezzio\Router\RouterInterface;
use PgFramework\Router\Annotation\Route;
use PgFramework\Response\ResponseRedirect;
use PgFramework\Database\NoRecordException;
use PgFramework\Renderer\RendererInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @Route("/password", name="auth.password")
 */
#[Route('/password', name:'auth.password')]
class PasswordForgetAction
{
    private $renderer;
    private $userTable;
    private $router;
    private $mailer;
    private $flashService;

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

    public function __invoke(ServerRequestInterface $request)
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
                $user = $this->userTable->findBy('email', $params['email']);
                $token = $this->userTable->resetPassword($user->id);
                $this->mailer->send($user->email, [
                    'id' => $user->id,
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
