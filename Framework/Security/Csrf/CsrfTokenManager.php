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
        string $sessionKey = 'csrf.tokens',
        string $formKey = '_csrf'
    ) {
        $this->storage = $storage;
        $this->generator = $generator;
        $this->sessionKey = $sessionKey;
        $this->formKey = $formKey;
    }

    public function getToken(string $key): string
    {
        if ($this->storage->hasToken($this->sessionKey)) {
            return $this->storage->getToken($this->sessionKey);
        }

        $token = $this->generator->generateToken();
        $this->storage->setToken($token, $this->sessionKey);
        return $token;
    }

    /**
     * {@inheritdoc}
     */
    public function refreshToken(string $key)
    {
        $value = $this->generator->generateToken();

        $this->storage->setToken($key, $value);

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function removeToken(string $key)
    {
        return $this->storage->removeToken($key);
    }

    /**
     * {@inheritdoc}
     */
    public function isTokenValid(string $key, string $token)
    {
        if (!$this->storage->hasToken($key)) {
            return false;
        }

        return hash_equals($this->storage->getToken($key), $token);
    }

    /**
     * @return string
     */
    public function getSessionKey(): string
    {
        return $this->sessionKey;
    }

    /**
     * @return string
     */
    public function getFormKey(): string
    {
        return $this->formKey;
    }

}
