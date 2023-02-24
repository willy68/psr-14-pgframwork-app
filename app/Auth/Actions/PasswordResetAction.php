<?php

namespace App\Auth\Actions;

use App\Auth\Entity\User;
use App\Auth\UserTable;
use Mezzio\Router\RouterInterface;
use PgFramework\Database\NoRecordException;
use PgFramework\Validator\Validator;
use PgFramework\Session\FlashService;
use PgFramework\Router\Annotation\Route;
use PgFramework\Response\ResponseRedirect;
use PgFramework\Renderer\RendererInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @Route("/password/reset/{id:\d+}/{token}", name="auth.reset")
 */
#[Route('/password/reset/{id:\d+}/{token}', name:'auth.reset')]
class PasswordResetAction
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
     * @var RouterInterface
     */
    private $router;
    /**
     * @var FlashService
     */
    private $flashService;

    public function __construct(
        RendererInterface $renderer,
        UserTable $userTable,
        FlashService $flashService,
        RouterInterface $router
    ) {
        $this->renderer = $renderer;
        $this->userTable = $userTable;
        $this->router = $router;
        $this->flashService = $flashService;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseRedirect|string
     * @throws NoRecordException
     */
    public function __invoke(ServerRequestInterface $request): ResponseRedirect|string
    {
        /** @var User $user */
        $user = $this->userTable->find($request->getAttribute('id'));
        if (
            $user->getPasswordReset() !== null &&
            $user->getPasswordReset() === $request->getAttribute('token') &&
            time() - $user->getPasswordResetAt()->getTimestamp() < 600
        ) {
            if ($request->getMethod() === 'GET') {
                return $this->renderer->render('@auth/reset');
            } else {
                $params = $request->getParsedBody();
                $validator = (new Validator($params))
                    ->length('password', 4)
                    ->confirm('password');
                if ($validator->isValid()) {
                    $this->userTable->updatePassword($user->getId(), $params['password']);
                    $this->flashService->success('Votre mot de passe a bien été changé');
                    return new ResponseRedirect($this->router->generateUri('auth.login'));
                } else {
                    $errors = $validator->getErrors();
                    return $this->renderer->render('@auth/reset', compact('errors'));
                }
            }
        } else {
            $this->flashService->error('Token invalid');
            return new ResponseRedirect($this->router->generateUri('auth.password'));
        }
    }
}
