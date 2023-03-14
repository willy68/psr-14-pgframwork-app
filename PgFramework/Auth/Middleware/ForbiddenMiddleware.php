<?php

declare(strict_types=1);

namespace PgFramework\Auth\Middleware;

use GuzzleHttp\Psr7\Response;
use PgFramework\Response\JsonResponse;
use PgFramework\Session\FlashService;
use PgFramework\HttpUtils\RequestUtils;
use Psr\Http\Message\ResponseInterface;
use PgFramework\Auth\ForbiddenException;
use Psr\Http\Server\MiddlewareInterface;
use Mezzio\Session\SessionInterface;
use PgFramework\Response\ResponseRedirect;
use PgFramework\Auth\FailedAccessException;
use PgFramework\Auth\UserInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TypeError;

class ForbiddenMiddleware implements MiddlewareInterface
{
    private string $loginPath;

    private SessionInterface $session;

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
            if (RequestUtils::isJson($request) || RequestUtils::wantJson($request)) {
                return new JsonResponse(401, json_encode($e->getMessage() . ' ' . $e->getCode()));
            }
            return $this->redirectLogin($request);
        } catch (FailedAccessException $e) {
            if (RequestUtils::isJson($request) || RequestUtils::wantJson($request)) {
                return new JsonResponse(403, json_encode($e->getMessage() . ' ' . $e->getCode()));
            }
            return $this->redirectAdminHome($request);
        } catch (TypeError $error) {
            if (str_contains($error->getMessage(), UserInterface::class)) {
                if (RequestUtils::isJson($request) || RequestUtils::wantJson($request)) {
                    return new JsonResponse(403, json_encode($error->getMessage() . ' ' . $error->getCode()));
                }
                return $this->redirectLogin($request);
            }
            throw $error;
        }
    }

    public function redirectLogin(ServerRequestInterface $request): ResponseInterface
    {
        $this->session->set('auth.redirect', $request->getUri()->getPath());
        (new FlashService($this->session))->error('Vous devez posséder un compte pour accéder à cette page');
        return new ResponseRedirect($this->loginPath);
    }

    protected function redirectAdminHome(ServerRequestInterface $request): ResponseInterface
    {
        $uri = $this->loginPath;
        $server = $request->getServerParams();
        if (isset($server['HTTP_REFERER'])) {
            $uri = $server['HTTP_REFERER'];
        }
        (new FlashService($this->session))->error('Vous n\'avez pas l\'authorisation pour exécuter cette action');
        return new ResponseRedirect($uri);
    }
}
