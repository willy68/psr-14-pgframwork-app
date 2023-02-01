<?php

declare(strict_types=1);

namespace PgFramework\EventListener;

use GuzzleHttp\Psr7\Response;
use PgFramework\Event\Events;
use Dflydev\FigCookies\SetCookie;
use League\Event\ListenerPriority;
use PgFramework\Security\Security;
use PgFramework\Event\RequestEvent;
use PgFramework\Event\ResponseEvent;
use PgFramework\Event\ExceptionEvent;
use PgFramework\Session\FlashService;
use PgFramework\HttpUtils\RequestUtils;
use Grafikart\Csrf\InvalidCsrfException;
use Dflydev\FigCookies\FigRequestCookies;
use Dflydev\FigCookies\FigResponseCookies;
use PgFramework\Response\ResponseRedirect;
use PgFramework\Security\Csrf\CsrfTokenManagerInterface;
use PgFramework\EventDispatcher\EventSubscriberInterface;

class CsrfCookieListener implements EventSubscriberInterface
{
    protected $config = [
        'cookieName' => 'XSRF-TOKEN',
        'header' => 'X-CSRF-TOKEN',
        'field' => '_csrf',
        'expiry' => null,
        'secure' => false,
        'httponly' => true,
        'samesite' => null,
    ];

    /**
     * @var FlashService
     */
    private $flashService;

    /**
     *
     * @var CsrfTokenManagerInterface
     */
    private $tokenManager;

    public function __construct(CsrfTokenManagerInterface $tokenManager, FlashService $flashService, array $config = [])
    {
        $this->tokenManager = $tokenManager;
        $this->flashService = $flashService;
        $this->config = array_merge($this->config, $config);
    }

    public function onRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        $method = $request->getMethod();

        $cookie = FigRequestCookies::get($request, $this->config['cookieName'])->getValue();

        if (\in_array($method, ['GET', 'HEAD'], true) && null === $cookie) {
            $token = $this->tokenManager->generateToken();
            $request = $request->withAttribute($this->config['field'], $token);
        }

        if (\in_array($method, ['DELETE', 'PATCH', 'POST', 'PUT'], true)) {
            $body = $request->getParsedBody() ?: [];

            if ((\is_array($body) || $body instanceof \ArrayAccess) && !empty($body)) {
                $token = $body[$this->config['field']] ?? null;
                $this->validateToken($token, $cookie);
            } elseif (!$request->hasHeader($this->config['header'])) {
                throw new InvalidCsrfException('Le cookie Csrf n\'existe pas ou est incorrect');
            } else {
                $token = $request->getHeaderLine($this->config['header']);
                $this->validateToken($token, $cookie);
            }

            [$tokenId] = explode(CsrfTokenManagerInterface::DELIMITER, $cookie);
            $token = $this->tokenManager->refreshToken($tokenId);
            $request = $request->withAttribute($this->config['field'], $token);
        }
        $event->setRequest($request);
    }

    public function onResponse(ResponseEvent $event)
    {
        $response = $event->getResponse();
        $request = $event->getRequest();
        $token = $request->getAttribute($this->config['field']);

        if (null !== $token) {
            $setCookie = SetCookie::create($this->config['cookieName'])
                ->withValue($token)
                ->withExpires($this->config['expiry'])
                ->withPath('/')
                ->withDomain(null)
                ->withSecure($this->config['secure'])
                ->withHttpOnly($this->config['httponly']);
            $response = FigResponseCookies::set($response, $setCookie);
            $event->setResponse($response);
        }
    }

    public function onException(ExceptionEvent $event)
    {
        $e = $event->getException();
        $request = $event->getRequest();
        $token = $request->getAttribute($this->config['field']);
        $tokenId = '';
        if ($token) {
            [$tokenId] = explode(CsrfTokenManagerInterface::DELIMITER, $token);
        }

        if ($e instanceof InvalidCsrfException) {
            if ($token) {
                $this->tokenManager->removeToken($tokenId);
            }

            if (RequestUtils::isJson($request)) {
                $response = new Response(403, [], json_encode($e->getMessage()));
            } else {
                $this->flashService->error('Vous n\'avez pas de token valid pour executer cette action');
                $response = new ResponseRedirect('/');
            }

            $setCookie = SetCookie::create($this->config['cookieName'])
                ->withValue('')
                ->withExpires(time() - 3600)
                ->withPath('/')
                ->withDomain(null)
                ->withSecure($this->config['secure'])
                ->withHttpOnly($this->config['httponly']);
            $response = FigResponseCookies::set($response, $setCookie);

            $event->setResponse($response);
        }
    }

    protected function validateToken(?string $token = null, ?string $cookie = null)
    {
        if (!$token) {
            throw new InvalidCsrfException('Le cookie Csrf n\'existe pas ou est incorrect');
        }

        if (!$cookie) {
            throw new InvalidCsrfException('Le cookie Csrf n\'existe pas ou est incorrect');
        }

        if (!$this->tokenManager->isTokenValid($token)) {
            throw new InvalidCsrfException('Le Csrf est incorrect');
        }

        if (!hash_equals($token, $cookie)) {
            throw new InvalidCsrfException('Le cookie Csrf est incorrect');
        }
    }

    public function getFormKey(): string
    {
        return $this->config['field'];
    }

    public static function getSubscribedEvents()
    {
        return [
            Events::REQUEST => ['onRequest', 400],
            Events::RESPONSE => ['onResponse', ListenerPriority::LOW],
            Events::EXCEPTION => ['onException', ListenerPriority::NORMAL]
        ];
    }
}
