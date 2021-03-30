<?php

namespace Framework\Security\Csrf;

use Framework\Security\Csrf\TokenStorage\TokenStorageInterface;
use Framework\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class CsrfTokenManager implements CsrfTokenManagerInterface
{
    private $storage;
    private $generator;
    private $sessionKey;

    public function __construct(
        TokenStorageInterface $storage,
        TokenGeneratorInterface $generator,
        string $sessionKey = 'csrf.tokens'
    ) {
        $this->storage = $storage;
        $this->generator = $generator;
        $this->sessionKey = $sessionKey;
    }

    public function getToken(): string
    {
        if ($this->storage->hasToken($this->sessionKey)) {
            return $this->storage->getToken($this->sessionKey);
        }

        $token = $this->generator->generateToken();
        $this->storage->setToken($token, $this->sessionKey);
        return $token;
    }

    /**
     * Generate and store a random token.
     *
     * @throws \Exception
     *
     * @return string
     */
    public function generateToken(): string
    {
        $token = bin2hex(random_bytes(16));
        $tokens = $this->session[$this->sessionKey] ?? [];
        $tokens[] = $token;
        $this->session[$this->sessionKey] = $this->limitTokens($tokens);

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
     * Remove a token from session.
     *
     * @param string $token
     */
    private function removeToken(string $token): void
    {
        $this->session[$this->sessionKey] = array_filter(
            $this->session[$this->sessionKey] ?? [],
            function ($t) use ($token) {
                return $token !== $t;
            }
        );
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
