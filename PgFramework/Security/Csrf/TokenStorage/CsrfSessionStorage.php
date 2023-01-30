<?php

namespace PgFramework\Security\Csrf\TokenStorage;

use Mezzio\Session\SessionInterface;

class CsrfSessionStorage implements CsrfStorageInterface
{
    private $session;
    private $sessionKey;
    private $limit;

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

    public function hasToken(string $token): bool
    {
        return \in_array($token, $this->session->toArray()[$this->sessionKey] ?? []);
    }

    public function setToken(string $token): void
    {
        $tokens = $this->session->get($this->sessionKey) ?? [];
        $tokens[] = $token;
        $this->session->set($this->sessionKey, $this->limitTokens($tokens));
    }

    public function removeToken(string $token): string
    {
        $tokens = array_filter(
            $this->session->toArray()[$this->sessionKey] ?? [],
            function ($t) use ($token) {
                return $token !== $t;
            }
        );
        $this->session->set($this->sessionKey, $tokens);

        return $token;
    }

    /**
     * @return string
     */
    public function getSessionKey(): string
    {
        return $this->sessionKey;
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
