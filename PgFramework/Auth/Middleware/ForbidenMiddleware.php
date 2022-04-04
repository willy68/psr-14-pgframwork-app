<?php

declare(strict_types=1);

namespace PgFramework\Auth\Middleware;

use GuzzleHttp\Psr7\Response;
use PgFramework\Session\FlashService;
use PgFramework\HttpUtils\RequestUtils;
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
            if (RequestUtils::isJson($request)) {
                return new Response(403, [], $e->getMessage() . ' ' . $e->getCode());
            }
            return $this->redirectLogin($request);
        } catch (FailedAccessException $e) {
            if (RequestUtils::isJson($request)) {
                return new Response(403, [], $e->getMessage() . ' ' . $e->getCode());
            }
            return $this->redirectAdminHome($request);
        } catch (\TypeError $error) {
            if (strpos($error->getMessage(), User::class) !== false) {
                if (RequestUtils::isJson($request)) {
                    return new Response(403, [], $error->getMessage() . ' ' . $error->getCode());
                }
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
