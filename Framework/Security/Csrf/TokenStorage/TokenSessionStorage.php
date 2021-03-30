<?php

namespace Framework\Security\Csrf\TokenStorage;

class TokenSessionStorage implements TokenStorageInterface
{

    /**
     * @var array|\ArrayAccess
     */
    private $session;

    /**
     * @var string
     */
    private $sessionKey;

    /**
     * @var string
     */
    private $formKey;

    /**
     * @var int
     */
    private $limit;

    /**
     * CsrfMiddleware constructor.
     *
     * @param array|\ArrayAccess $session
     * @param int                $limit      Limit the number of token to store in the session
     * @param string             $sessionKey
     * @param string             $formKey
     */
    public function __construct(
        &$session
    ) {
        $this->testSession($session);
        $this->session = &$session;
    }

    public function hasToken(string $key): bool
    {
        return array_key_exists($key, $this->session);
    }

    public function getToken(string $key): string
    {
        return $this->session[$this->sessionKey];
    }

    public function setToken(string $token, string $key): void
    {
        $this->session[$key] = $token;
    }

    public function removeToken(string $key): void
    {
        $this->session[$key] = null;
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

}
