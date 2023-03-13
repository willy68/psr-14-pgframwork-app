<?php

declare(strict_types=1);

namespace PgFramework\EventListener;

use ArrayAccess;
use PgFramework\Event\Events;
use Dflydev\FigCookies\SetCookie;
use League\Event\ListenerPriority;
use PgFramework\Event\RequestEvent;
use PgFramework\Event\ResponseEvent;
use PgFramework\Event\ExceptionEvent;
use PgFramework\Response\JsonResponse;
use PgFramework\Session\FlashService;
use PgFramework\HttpUtils\RequestUtils;
use Grafikart\Csrf\InvalidCsrfException;
use Dflydev\FigCookies\FigRequestCookies;
use Dflydev\FigCookies\FigResponseCookies;
use PgFramework\Response\ResponseRedirect;
use PgFramework\Security\Csrf\CsrfTokenManagerInterface;
use PgFramework\EventDispatcher\EventSubscriberInterface;

use function array_merge;
use function explode;
use function hash_equals;
use function in_array;
use function is_array;

class CsrfCookieListener implements EventSubscriberInterface
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

    public function __construct(CsrfTokenManagerInterface $tokenManager, FlashService $flashService, array $config = [])
    {
        $this->tokenManager = $tokenManager;
        $this->flashService = $flashService;
        $this->config = array_merge($this->config, $config);
    }

    /**
     * @param RequestEvent $event
     * @return void
     * @throws InvalidCsrfException
     */
    public function onRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $method = $request->getMethod();

        $cookie = FigRequestCookies::get($request, $this->config['cookieName'])->getValue();

        if (in_array($method, ['GET', 'HEAD'], true)) {
            if (null === $cookie || !$this->tokenManager->isTokenValid($cookie)) {
                $token = $this->tokenManager->getToken();
                $request = $request->withAttribute($this->config['field'], $this->createCookie($token));
            } else {
                $request = $request->withAttribute($this->config['field'], $this->createCookie($cookie));
            }
        }

        if (in_array($method, ['DELETE', 'PATCH', 'POST', 'PUT'], true)) {
            $body = $request->getParsedBody() ?: [];

            if ((is_array($body) || $body instanceof ArrayAccess) && !empty($body)) {
                $token = $body[$this->config['field']] ?? null;
            } elseif (!$request->hasHeader($this->config['header'])) {
                throw new InvalidCsrfException('Le cookie Csrf n\'existe pas ou est incorrect');
            } else {
                $token = $request->getHeaderLine($this->config['header']);
            }
            $this->validateToken($token, $cookie);

            [$tokenId] = explode(CsrfTokenManagerInterface::DELIMITER, $cookie);
            $token = $this->tokenManager->refreshToken($tokenId);
            $request = $request->withAttribute($this->config['field'], $this->createCookie($token));
        }
        $event->setRequest($request);
    }

    /**
     * @param ResponseEvent $event
     * @return void
     */
    public function onResponse(ResponseEvent $event): void
    {
        $response = $event->getResponse();
        $request = $event->getRequest();
        $cookie = $request->getAttribute($this->config['field']);

        if (null !== $cookie) {
            $response = FigResponseCookies::set($response, $cookie);
            $event->setResponse($response);
        }
    }

    /**
     * @param ExceptionEvent $event
     * @return void
     */
    public function onException(ExceptionEvent $event): void
    {
        $e = $event->getException();

        if ($e instanceof InvalidCsrfException) {
            $request = $event->getRequest();
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

            $setCookie = $this->createCookie('', time() - 3600);
            $response = FigResponseCookies::set($response, $setCookie);

            $event->setResponse($response);
        }
    }

    /**
     * @param string|null $token
     * @param string|null $cookie
     * @return void
     * @throws InvalidCsrfException
     */
    protected function validateToken(?string $token = null, ?string $cookie = null): void
    {
        if (!$token) {
            throw new InvalidCsrfException('Le token Csrf n\'existe pas');
        }

        if (!$cookie) {
            throw new InvalidCsrfException('Le cookie Csrf n\'existe pas');
        }

        if (!$this->tokenManager->isTokenValid($token)) {
            throw new InvalidCsrfException('Le token Csrf est incorrect');
        }

        if (!hash_equals($token, $cookie)) {
            throw new InvalidCsrfException('Le cookie et le token Csrf ne correspondent pas');
        }
    }

    public function getFormKey(): string
    {
        return $this->config['field'];
    }

    private function createCookie(string $token, ?int $expiry = null): SetCookie
    {
        return SetCookie::create($this->config['cookieName'])
            ->withValue($token)
            ->withExpires(($expiry === null) ? $this->config['expiry'] : $expiry)
            ->withPath('/')
            ->withDomain()
            ->withSecure($this->config['secure'])
            ->withHttpOnly($this->config['httponly']);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::REQUEST => ['onRequest', 400],
            Events::RESPONSE => ['onResponse', ListenerPriority::LOW],
            Events::EXCEPTION => ['onException', ListenerPriority::NORMAL]
        ];
    }
}
