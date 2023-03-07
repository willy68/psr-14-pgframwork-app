<?php

namespace PgFramework\Security\Csrf\Listener;

use ArrayAccess;
use GuzzleHttp\Psr7\Response;
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
use PgFramework\Security\Csrf\CsrfManagerInterface;
use PgFramework\EventDispatcher\EventSubscriberInterface;

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
    private CsrfManagerInterface $csrfManager;

    public function __construct(CsrfManagerInterface $csrfManager, FlashService $flashService, array $config = [])
    {
        $this->csrfManager = $csrfManager;
        $this->flashService = $flashService;
        $this->config = array_merge($this->config, $config);
    }

    /**
     * @throws InvalidCsrfException
     */
    public function onRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        $method = $request->getMethod();

        $cookie = FigRequestCookies::get($request, $this->config['cookieName'])->getValue();

        if (in_array($method, ['GET', 'HEAD'], true) && null === $cookie) {
            $token = $this->csrfManager->getToken();
            $request = $request->withAttribute($this->config['field'], $token);
        }

        if (in_array($method, ['DELETE', 'PATCH', 'POST', 'PUT'], true)) {
            $body = $request->getParsedBody() ?: [];

            if ((is_array($body) || $body instanceof ArrayAccess) && !empty($body)) {
                $token = $body[$this->config['field']] ?? null;
                $this->validateToken($token, $cookie);
            } elseif (!$request->hasHeader($this->config['header'])) {
                throw new InvalidCsrfException('Le cookie Csrf n\'existe pas ou est incorrect');
            } else {
                $token = $request->getHeaderLine($this->config['header']);
                $this->validateToken($token, $cookie);
            }

            $this->csrfManager->removeToken($token);
            $token = $this->csrfManager->getToken();
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
                ->withDomain()
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
        if ($e instanceof InvalidCsrfException) {
            if ($token) {
                $this->csrfManager->removeToken($token);
            }

            if (RequestUtils::isJson($request)) {
                $response = new JsonResponse(403, [], json_encode($e->getMessage()));
            } else {
                $this->flashService->error('Vous n\'avez pas de token valid pour exécuter cette action');
                $response = new ResponseRedirect('/');
            }

            $setCookie = SetCookie::create($this->config['cookieName'])
                ->withValue('')
                ->withExpires(time() - 3600)
                ->withPath('/')
                ->withDomain()
                ->withSecure($this->config['secure'])
                ->withHttpOnly($this->config['httponly']);
            $response = FigResponseCookies::set($response, $setCookie);

            $event->setResponse($response);
        }
    }

    public function getFormKey(): string
    {
        return $this->config['field'];
    }

    /**
     * @throws InvalidCsrfException
     */
    protected function validateToken(?string $token = null, ?string $cookie = null)
    {
        if (!$token) {
            throw new InvalidCsrfException('Le cookie Csrf n\'existe pas ou est incorrect');
        }

        if (!$cookie) {
            throw new InvalidCsrfException('Le cookie Csrf n\'existe pas ou est incorrect');
        }

        if (!$this->csrfManager->isTokenValid($token)) {
            throw new InvalidCsrfException('Le Csrf est incorrect');
        }

        if ($token !== $cookie) {
            throw new InvalidCsrfException('Le cookie Csrf est incorrect');
        }
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
