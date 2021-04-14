<?php

namespace App\Auth\Middleware;

use PgFramework\Auth\User;
use PgFramework\Session\FlashService;
use Psr\Http\Message\ResponseInterface;
use PgFramework\Auth\ForbiddenException;
use Psr\Http\Server\MiddlewareInterface;
use PgFramework\Session\SessionInterface;
use PgFramework\Response\ResponseRedirect;
use PgFramework\Auth\FailedAccessException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ForbidenMiddleware implements MiddlewareInterface
{

    private $loginPath;

    /**
     * Undocumented variable
     *
     * @var SessionInterface
     */
    private $session;

    public function __construct(string $loginPath, SessionInterface $session)
    {
        $this->loginPath = $loginPath;
        $this->session = $session;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (ForbiddenException $e) {
            return $this->redirectLogin($request);
        } catch (FailedAccessException $e) {
            return $this->redirectAdminHome($request);
        } catch (\TypeError $error) {
            if (strpos($error->getMessage(), User::class) !== false) {
                return $this->redirectLogin($request);
            }
        }
    }

    public function redirectLogin(ServerRequestInterface $request): ResponseInterface
    {
        $this->session->set('auth.redirect', $request->getUri()->getPath());
        (new FlashService($this->session))->error('Vous devez posseder un compte pour accéder à cette page');
        return new ResponseRedirect($this->loginPath);
    }

    protected function redirectAdminHome(ServerRequestInterface $request): ResponseInterface
    {
        $uri = $this->loginPath;
        $server = $request->getServerParams();

        if (isset($server['HTTP_REFERER'])) {
            $uri = $server['HTTP_REFERER'];
        }

        (new FlashService($this->session))->error('Vous n\'avez pas l\'authorisation pour executer cette action');
        return new ResponseRedirect($uri);
    }
}
