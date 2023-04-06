<?php

declare(strict_types=1);

namespace PgFramework\Auth\RememberMe;

use DateTime;
use Exception;
use PgFramework\Auth\UserInterface;
use Dflydev\FigCookies\SetCookie;
use Psr\Http\Message\ResponseInterface;
use Dflydev\FigCookies\FigRequestCookies;
use Dflydev\FigCookies\FigResponseCookies;
use Psr\Http\Message\ServerRequestInterface;
use PgFramework\Auth\Provider\UserProviderInterface;
use PgFramework\Auth\Provider\TokenProviderInterface;
use TypeError;

use function count;

class RememberMeDatabase extends AbstractRememberMe
{
    private TokenProviderInterface $tokenProvider;

    public function __construct(
        UserProviderInterface $userProvider,
        TokenProviderInterface $tokenProvider,
        string $salt
    ) {
        parent::__construct($userProvider, $salt);
        $this->tokenProvider = $tokenProvider;
    }

    /**
     * Crée un cookie d’authentification,
     * un token en base donnée et un cookie avec un mot de passe aléatoire
     *
     * @param ResponseInterface $response
     * @param UserInterface $user
     * @return ResponseInterface
     * @throws Exception
     */
    public function onLogin(ResponseInterface $response, UserInterface $user): ResponseInterface
    {

        $series = base64_encode(random_bytes(64));
        $randomPassword = base64_encode(random_bytes(64));

        //["series', 'credential', 'random_password', 'expiration_date']
        $this->tokenProvider->saveToken(
            [
                'series' => $series,
                'credential' => $user->getUsername(),
                'random_password' => $randomPassword,
                'expiration_date' => (new DateTime())
                    ->setTimestamp(time() +  $this->options['lifetime'])
            ]
        );

        // Create random password cookie [$series, $username, $randomPassword]
        return FigResponseCookies::set($response, $this->createCookie($series, $user, $randomPassword));
    }

    /**
     * Connecte l’utilisateur automatiquement avec le cookie reçu de la request et
     * vérifie le token en base de données s’il est valide
     *
     * @param ServerRequestInterface $request
     * @return ServerRequestInterface
     * @throws Exception
     */
    public function autoLogin(ServerRequestInterface $request): ServerRequestInterface
    {
        $authenticate = true;

        $cookie = FigRequestCookies::get($request, $this->options['name']);
        if (!$cookie->getValue()) {
            return $request;
        }

        try {
            $cookieParts = $this->decodeCookie($cookie->getValue());
            if (3 !== count($cookieParts)) {
                throw new Exception();
            }

            [$series,, $randomPassword] = $cookieParts;
            $token = $this->tokenProvider->getTokenBySeries($series);

            if (!$token) {
                throw new Exception();
            }
        } catch (Exception | TypeError $e) {
            return $request->withAttribute($this->options['attribute'], $this->cancelCookie());
        }

        $user = $this->userProvider->getUser($this->options['field'], $token->getCredential());
        if ($user) {
            //password corrupted
            if (!hash_equals(base64_decode($token->getRandomPassword()), base64_decode($randomPassword))) {
                $authenticate = false;
            }
            // expiration outdated
            if ($token->getExpirationDate()->getTimestamp() < time()) {
                $authenticate = false;
            }
            // Remove token from database
            if (!$authenticate) {
                $this->tokenProvider->deleteToken($token->getId());
                return $request->withAttribute($this->options['attribute'], $this->cancelCookie());
            }

            // Update cookie and database token
            //['series', 'credential', 'random_password', 'expiration_date']
            $randomPassword = base64_encode(random_bytes(64));
            $this->tokenProvider->updateToken(
                [
                    'random_password' => $randomPassword,
                    'expiration_date' => (new DateTime())
                        ->setTimestamp(time() +  $this->options['lifetime'])
                ],
                $token->getId()
            );

            return $request->withAttribute('_user', $user)
                ->withAttribute($this->options['attribute'], $this->createCookie($series, $user, $randomPassword));
        }
        return $request;
    }

    /**
     * Déconnecte l’utilisateur et invalide le cookie dans la response et
     * marque le token en base de données expiré
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function onLogout(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $cookie = FigRequestCookies::get($request, $this->options['name']);
        if ($cookie->getValue()) {
            $cookieParts = $this->decodeCookie($cookie->getValue());
            if (3 === count($cookieParts)) {
                [$series] = $cookieParts;

                $token = $this->tokenProvider->getTokenBySeries($series);

                if ($token) {
                    // Delete token from database
                    $this->tokenProvider->deleteToken($token->getId());
                }
                // Delete cookie
                $response = FigResponseCookies::set($response, $this->cancelCookie());
            }
        }
        return $response;
    }

    /**
     * Renouvelle la date d’expiration du cookie dans la response et le token en base de données
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
            // Set new expiration date
            assert($cookie instanceof SetCookie);
            $cookie->withExpires(time() +  $this->options['lifetime']);
            $response = FigResponseCookies::set($response, $cookie);
        }
        return $response;
    }

    protected function createCookie(string $series, UserInterface $user, string $randomPassword): SetCookie
    {
        return SetCookie::create($this->options['name'])
            ->withValue($this->encodeCookie([$series, $user->getUsername(), $randomPassword]))
            ->withExpires(time() +  $this->options['lifetime'])
            ->withPath($this->options['path'])
            ->withDomain($this->options['domain'])
            ->withSecure($this->options['secure'])
            ->withHttpOnly($this->options['httpOnly']);
    }

    protected function cancelCookie(): SetCookie
    {
        return SetCookie::create($this->options['name'])
            ->withValue('')
            ->withExpires(time() - 3600)
            ->withPath($this->options['path'])
            ->withDomain($this->options['domain'])
            ->withSecure($this->options['secure'])
            ->withHttpOnly($this->options['httpOnly']);
    }
}
