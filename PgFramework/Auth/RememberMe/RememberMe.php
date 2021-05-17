<?php

namespace PgFramework\Auth\RememberMe;

use PgFramework\Auth\UserInterface;
use Dflydev\FigCookies\SetCookie;
use Psr\Http\Message\ResponseInterface;
use Dflydev\FigCookies\FigRequestCookies;
use Dflydev\FigCookies\FigResponseCookies;
use Psr\Http\Message\ServerRequestInterface;

class RememberMe extends AbstractRememberMe
{

    /**
     * Crée un cookie d'authentification
     *
     * @param ResponseInterface $response
     * @param UserInterface $user
     * @return ResponseInterface
     */
    public function onLogin(ResponseInterface $response, UserInterface $user): ResponseInterface
    {

        $cookieValue = $this->getCookieHash(
            $user->getUsername(),
            $user->getPassword(),
            get_class($user),
            time() + $this->options['lifetime'],
            $this->salt
        );

        $cookie = SetCookie::create($this->options['name'])
            ->withValue($cookieValue)
            ->withExpires(time() + $this->options['lifetime'])
            ->withPath($this->options['path'])
            ->withDomain($this->options['domain'])
            ->withSecure($this->options['secure'])
            ->withHttpOnly($this->options['httpOnly']);
        return FigResponseCookies::set($response, $cookie);
    }

    /**
     * Connecte l'utilisateur automatiquement avec le cookie reçu de la requète
     *
     * @param ServerRequestInterface $request
     * @return ServerRequestInterface
     */
    public function autoLogin(ServerRequestInterface $request): ServerRequestInterface
    {
        $cookie = FigRequestCookies::get($request, $this->options['name']);
        if (($cookieValue = $cookie->getValue())) {
            $cookieParts = $this->decodeCookie($cookieValue);

            if (4 !== \count($cookieParts)) {
                return $this->cancelCookie($request);
            }

            [$username,, $expires, $hash] = $cookieParts;

            if (false === $username = base64_decode($username, true)) {
                return $this->cancelCookie($request);
            }

            $user = $this->userProvider->getUser($this->options['field'], $username);

            if (true === $this->validateToken($user, $expires, $hash)) {
                $cookieValue = $this->getCookieHash(
                    $user->getUsername(),
                    $user->getPassword(),
                    get_class($user),
                    time() + $this->options['lifetime'],
                    $this->salt
                );

                $cookie = SetCookie::create($this->options['name'])
                    ->withValue($cookieValue)
                    ->withExpires(time() + $this->options['lifetime'])
                    ->withPath($this->options['path'])
                    ->withDomain($this->options['domain'])
                    ->withSecure($this->options['secure'])
                    ->withHttpOnly($this->options['httpOnly']);

                return $request->withAttribute('_user', $user)
                    ->withAttribute($this->options['attribute'], $cookie);
            }
        }
        return $this->cancelCookie($request);
    }

    /**
     * Déconnecte l'utilisateur et invalide le cookie dans la response
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function onLogout(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $cookie = FigRequestCookies::get($request, $this->options['name']);
        if ($cookie->getValue()) {
            $cookie = SetCookie::create($this->options['name'])
                ->withValue('')
                ->withExpires(time() - 3600)
                ->withPath($this->options['path'])
                ->withDomain($this->options['domain'])
                ->withSecure($this->options['secure'])
                ->withHttpOnly($this->options['httpOnly']);
            $response = FigResponseCookies::set($response, $cookie);
        }
        return $response;
    }

    /**
     * Renouvelle la date d'expiration du cookie dans la response
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function resume(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        // Get $cookie
        $cookie = $request->getAttribute($this->options['attribute']);

        if ($cookie) {
            // Set new random password cookie
            $response = FigResponseCookies::set($response, $cookie);
        } else {
            $cookie = FigRequestCookies::get($request, $this->options['name']);

            if ($cookie->getValue()) {
                $setCookie = SetCookie::create($this->options['name'])
                    ->withValue($cookie->getValue())
                    ->withExpires(time() + $this->options['lifetime'])
                    ->withPath($this->options['path'])
                    ->withDomain($this->options['domain'])
                    ->withSecure($this->options['secure'])
                    ->withHttpOnly($this->options['httpOnly']);
                $response = FigResponseCookies::set($response, $setCookie);
            }
        }
        return $response;
    }

    protected function getCookieHash(
        string $credential,
        string $password,
        string $userClass,
        int $expires
    ): string {
        return $this->encodeCookie([
            base64_encode($credential),
            $userClass,
            $expires,
            hash_hmac($this->algo, $credential . $password . $userClass . $expires, $this->salt)
        ]);
    }

    protected function validateToken($user, $expires, $hash): bool
    {
        return true === hash_equals(
            hash_hmac(
                $this->algo,
                $user->getUsername() . $user->getPassword() . get_class($user) . $expires,
                $this->salt
            ),
            $hash
        );
    }

    protected function cancelCookie(ServerRequestInterface $request): ServerRequestInterface
    {
        $cookie = SetCookie::create($this->options['name'])
            ->withValue('')
            ->withExpires(time() - 3600)
            ->withPath($this->options['path'])
            ->withDomain($this->options['domain'])
            ->withSecure($this->options['secure'])
            ->withHttpOnly($this->options['httpOnly']);
        return $request->withAttribute($this->options['attribute'], $cookie);
    }
}
