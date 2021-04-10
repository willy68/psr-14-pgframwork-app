<?php

namespace PgFramework\Security\Csrf\TokenStorage;

use PgFramework\Session\SessionInterface;

class TokenSessionStorage implements TokenStorageInterface
{

    /**
     * @var array|\ArrayAccess|SessionInterface
     */
    private $session;

    /**
     * @var string
     */
    private $sessionKey;

    /**
     * @var int
     */
    private $limit;

    /**
     * CsrfMiddleware constructor.
     *
     * @param array|\ArrayAccess|SessionInterface $session
     * @param int                $limit      Limit the number of token to store in the session
     * @param string             $sessionKey
     * @param string             $formKey
     */
    public function __construct(
        SessionInterface &$session,
        int $limit = 50,
        string $sessionKey = 'csrf.tokens'
    ) {
        $this->testSession($session);
        $this->session = &$session;
        $this->sessionKey = $sessionKey;
        $this->limit = $limit;
    }

    public function hasToken(string $tokenId): bool
    {
        return isset($this->session[$this->sessionKey][$tokenId]);
    }

    public function getToken(string $tokenId): ?string
    {
        if (!$this->hasToken($tokenId)) {
            return null;
        }

        return (string) $this->session[$this->sessionKey][$tokenId];
    }

    public function setToken(string $tokenId, string $token): void
    {
        $tokens = $this->session[$this->sessionKey] ?? [];
        $tokens[$tokenId] = $token;
        $this->session[$this->sessionKey] = $this->limitTokens($tokens);
    }

    public function removeToken(string $tokenId): ?string
    {
        if (!$this->hasToken($tokenId)) {
            return null;
        }

        $token = (string) $this->session[$this->sessionKey][$tokenId];
        $tokens = $this->session[$this->sessionKey];
        unset($tokens[$tokenId]);
        $this->session[$this->sessionKey] = $tokens;
        return $token;
    }

    /**
     * Test if the session acts as an array.
     *
     * @param $session
     *
     * @throws \TypeError
     */
    private function testSession($session): void
    {
        if (!\is_array($session) && !$session instanceof \ArrayAccess) {
            throw new \TypeError('session is not an array');
        }
    }

    /**
     * @return string
     */
    public function getSessionKey(): string
    {
        return $this->sessionKey;
    }

    /**
     * Limit the number of tokens.
     *
     * @param array $tokens
     *
     * @return array
     */
    private function limitTokens(array $tokens): array
    {
        if (\count($tokens) > $this->limit) {
            array_shift($tokens);
        }

        return $tokens;
    }

}
