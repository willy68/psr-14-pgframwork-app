<?php

declare(strict_types=1);

namespace PgFramework\Middleware;

use PgFramework\HttpUtils\RequestUtils;
use PgFramework\Response\JsonResponse;
use PgFramework\Response\ResponseRedirect;
use PgFramework\Security\Csrf\CsrfTokenManagerInterface;
use PgFramework\Session\FlashService;
use Grafikart\Csrf\InvalidCsrfException;
use Dflydev\FigCookies\SetCookie;
use Dflydev\FigCookies\FigResponseCookies;
use Psr\Http\Message\{
    ResponseInterface,
    ServerRequestInterface
};
use Psr\Http\Server\{
    RequestHandlerInterface,
    MiddlewareInterface
};

use function array_merge;

class InvalidCsrfMiddleware implements MiddlewareInterface
{
    protected array $config = [
        'cookieName' => 'XSRF-TOKEN',
        'header' => 'X-CSRF-TOKEN',
        'field' => '_csrf',
        'expiry' => null,
        'secure' => false,
        'httponly' => true,
        'samesite' => null,
    ];

    private FlashService $flashService;
    private CsrfTokenManagerInterface $tokenManager;

    /**
     * InvalidCsrfMiddleware constructor.
     * @param FlashService $flashService
     * @param array $config
     */
    public function __construct(CsrfTokenManagerInterface $tokenManager, FlashService $flashService, array $config = [])
    {
        $this->tokenManager = $tokenManager;
        $this->flashService = $flashService;
        $this->config = array_merge($this->config, $config);
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (InvalidCsrfException $e) {
            $token = $request->getAttribute($this->config['field']);
            $tokenId = null;

            if ($token) {
                [$tokenId] = explode(CsrfTokenManagerInterface::DELIMITER, $token->getValue());
            }
            if ($tokenId) {
                $this->tokenManager->removeToken($tokenId);
            }

            if (RequestUtils::isJson($request) || RequestUtils::wantJson($request)) {
                $response = new JsonResponse(403, json_encode($e->getMessage()));
            } else {
                $this->flashService->error('Vous n\'avez pas de token valid pour exécuter cette action');
                $response = new ResponseRedirect('/');
            }

            $setCookie = $this->deleteCookie(time() - 3600);
            return FigResponseCookies::set($response, $setCookie);
        }
    }

    private function deleteCookie(?int $expiry = null): SetCookie
    {
        return SetCookie::create($this->config['cookieName'])
            ->withValue('')
            ->withExpires(($expiry === null) ? $this->config['expiry'] : $expiry)
            ->withPath('/')
            ->withDomain()
            ->withSecure($this->config['secure'])
            ->withHttpOnly($this->config['httponly']);
    }
}
