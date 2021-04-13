<?php

namespace PgFramework\Auth\RememberMe;

use PgFramework\Auth\User;
use Dflydev\FigCookies\SetCookie;
use Psr\Http\Message\ResponseInterface;
use Dflydev\FigCookies\FigRequestCookies;
use Dflydev\FigCookies\FigResponseCookies;
use Psr\Http\Message\ServerRequestInterface;
use PgFramework\Auth\Repository\UserRepositoryInterface;
use PgFramework\Auth\Repository\TokenRepositoryInterface;

class RememberMeDatabase extends AbstractRememberMe
{

    /**
     * Token Repository
     *
     * @var TokenRepositoryInterface
     */
    private $tokenRepository;

    /**
     * Constructeur: ajoute l'option pour le nom du cookie du mot de passe aléatoire
     *
     * @param UserRepositoryInterface $userRepository
     * @param TokenRepositoryInterface $tokenRepository
     */
    public function __construct(
        UserRepositoryInterface $userRepository,
        TokenRepositoryInterface $tokenRepository,
        string $salt
    ) {
        parent::__construct($userRepository, $salt);
        $this->tokenRepository = $tokenRepository;
    }

    /**
     * Crée un cookie d'authentification,
     * un token en base données et un cookie avec un mot de passe aléatoire
     *
     * @param ResponseInterface $response
     * @param User $user
     * @return ResponseInterface
     */
    public function onLogin(ResponseInterface $response, User $user): ResponseInterface
    {

        $series = base64_encode(random_bytes(64));
        $randomPassword = base64_encode(random_bytes(64));

        //["series', 'credential', 'random_password', 'expiration_date']
        $this->tokenRepository->saveToken(
            [
                'series' => $series,
                'credential' => $user->getUsername(),
                'random_password' => $randomPassword,
                'expiration_date' => new \DateTime()
            ]
        );

        // Create random password cookie [$series, $username, $randomPassword]
        $cookie = SetCookie::create($this->options['name'])
            ->withValue($this->encodeCookie([$series, $user->getUsername(), $randomPassword]))
            ->withExpires(time() +  $this->options['lifetime'])
            ->withPath($this->options['path'])
            ->withDomain($this->options['domain'])
            ->withSecure($this->options['secure'])
            ->withHttpOnly($this->options['httpOnly']);
        return FigResponseCookies::set($response, $cookie);
    }

    /**
     * Connecte l'utilisateur automatiquement avec le cookie reçu de la requète et
     * vérifie le token en base de données s'il est valide
     *
     * @param ServerRequestInterface $request
     * @return ServerRequestInterface
     */
    public function autoLogin(ServerRequestInterface $request): ServerRequestInterface
    {
        $authenticate = true;

        $cookie = FigRequestCookies::get($request, $this->options['name']);
        if (!$cookie->getValue()) {
            return $request->withAttribute('cancel.rememberme.cookie', true);
        }

        try {
            $cookieParts = $this->decodeCookie($cookie->getValue());
            if (3 !== \count($cookieParts)) {
                throw new \Exception();
            }

            [$series,, $randomPassword] = $cookieParts;
            $token = $this->tokenRepository->getTokenBySeries($series);

            if (!$token) {
                $authenticate = false;
            }
        } catch (\Exception $e) {
            $authenticate = false;
        } catch (\TypeError $e) {
            $authenticate = false;
        }

        if (!$authenticate) {
            return $request->withAttribute('cancel.rememberme.cookie', true);
        }

        $user = $this->userRepository->getUser($this->options['field'], $token->getCredential());
        if ($user) {

            //password corrupted
            if (!hash_equals($token->getRandomPassword(), $randomPassword)) {
                $authenticate = false;
            }
            // expiration outdated
            if ($token->getExpirationDate()->getTimestamp() + $this->options['lifetime'] < time()) {
                $authenticate = false;
            }
            // Remove token from database
            if (!$authenticate) {
                $this->tokenRepository->deleteToken($token->getId());
                return $request->withAttribute('cancel.rememberme.cookie', true);
            }

            //["series', 'credential', 'random_password', 'expiration_date']
            $randomPassword = base64_encode(random_bytes(64));
            $this->tokenRepository->updateToken(
                [
                    'random_password' => $randomPassword,
                    'expiration_date' => new \DateTime()
                ],
                $token->getId()
            );

            $cookie = SetCookie::create($this->options['name'])
            ->withValue($this->encodeCookie([$series, $user->getUsername(), $randomPassword]))
            ->withExpires(time() +  $this->options['lifetime'])
            ->withPath($this->options['path'])
            ->withDomain($this->options['domain'])
            ->withSecure($this->options['secure'])
            ->withHttpOnly($this->options['httpOnly']);

            $request = $request->withAttribute($this->options['attribute'], $cookie);
        }
        /** @var ServerRequestInterface $request */
        return $request->withAttribute('_user', $user);
    }

    /**
     * Déconnecte l'utilisateur et invalide le cookie dans la response et
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
            if (3 === \count($cookieParts)) {

                [$series] = $cookieParts;

                $token = $this->tokenRepository->getTokenBySeries($series);

                if ($token) {
                    // Delete token from database
                    $this->tokenRepository->deleteToken($token->getId());
                }
                // Delete cookie
                $cookiePassword = SetCookie::create($this->options['name'])
                    ->withValue('')
                    ->withExpires(time() - 3600)
                    ->withPath($this->options['path'])
                    ->withDomain($this->options['domain'])
                    ->withSecure($this->options['secure'])
                    ->withHttpOnly($this->options['httpOnly']);
                $response = FigResponseCookies::set($response, $cookiePassword);
            }
        }
        return $response;
    }

    /**
     * Renouvelle la date d'expiration du cookie dans la response et le token en base de données
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function resume(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        // Get $cookie
        $cookie = $request->getAttribute($this->options['attribute']);

        if ($cookie && $cookie->getValue()) {
            // Set new random password cookie
            $response = FigResponseCookies::set($response, $cookie);
        }
        return $response;
    }
}
