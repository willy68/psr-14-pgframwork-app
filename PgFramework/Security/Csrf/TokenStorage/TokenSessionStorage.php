<?php

declare(strict_types=1);

namespace PgFramework\Security\Csrf\TokenStorage;

use Mezzio\Session\SessionInterface;

class TokenSessionStorage implements TokenStorageInterface
{
    /**
     * @var SessionInterface
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

    private $lastIdField = 'csrf.lastid';

    /**
     * CsrfMiddleware constructor.
     *
     * @param SessionInterface   $session
     * @param int                $limit      Limit the number of token to store in the session
     * @param string             $sessionKey
     * @param string             $formKey
     */
    public function __construct(
        SessionInterface $session,
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
        return isset($this->session->toArray()[$this->sessionKey][$tokenId]);
    }

    public function getToken(?string $tokenId = null): ?string
    {
        if (null !== $tokenId) {
            if (!$this->hasToken($tokenId)) {
                return null;
            }
            return (string) $this->session->toArray()[$this->sessionKey][$tokenId];
        }

        // Try last insert id
        $tokenId = $this->session->get($this->lastIdField) ?? null;
        if ($tokenId) {
            return (string) $this->session->toArray()[$this->sessionKey][$tokenId];
        }

        return null;
    }

    public function setToken(string $tokenId, string $token): void
    {
        $tokens = $this->session->toArray()[$this->sessionKey] ?? [];
        $tokens[$tokenId] = $token;
        $this->session->set($this->sessionKey, $this->limitTokens($tokens));
        $this->session->set($this->lastIdField, $tokenId);
    }

    public function removeToken(string $tokenId): ?string
    {
        if (!$this->hasToken($tokenId)) {
            return null;
        }

        $tokens = $this->session->toArray()[$this->sessionKey];
        $token = $tokens[$tokenId];
        unset($tokens[$tokenId]);
        $this->session->set($this->sessionKey, $tokens);

        if ($tokenId === $this->session->toArray()[$this->lastIdField]) {
            $this->session->unset($this->lastIdField);
        }

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
        if (!\is_array($session->toArray()) && !$session->toArray() instanceof \ArrayAccess) {
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
