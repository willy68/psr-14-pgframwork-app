<?php

namespace App\Auth\Action;

use App\Auth\UserTable;
use PgFramework\Validator\Validator;
use PgFramework\Session\FlashService;
use App\Auth\Mailer\PasswordResetMailer;
use PgFramework\Response\ResponseRedirect;
use ActiveRecord\Exceptions\RecordNotFound;
use PgFramework\Renderer\RendererInterface;
use Psr\Http\Message\ServerRequestInterface;

class PasswordForgetAction
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
     * @var PasswordResetMailer
     */
    private $mailer;
    /**
     * @var FlashService
     */
    private $flashService;

    public function __construct(
        RendererInterface $renderer,
        UserTable $userTable,
        PasswordResetMailer $mailer,
        FlashService $flashService
    ) {
        $this->renderer = $renderer;
        $this->userTable = $userTable;
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
                return new ResponseRedirect($request->getUri()->getPath());
            } catch (RecordNotFound $e) {
                $errors = ['email' => 'Aucun utilisateur ne correspond à cet email'];
            }
        } else {
            $errors = $validator->getErrors();
        }
        return $this->renderer->render('@auth/password', compact('errors'));
    }
}
