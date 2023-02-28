<?php

namespace PgFramework\Security\Csrf;

use PgFramework\Security\Csrf\TokenGenerator\CsrfGeneratorInterface;
use PgFramework\Security\Csrf\TokenStorage\CsrfStorageInterface;

class CsrfManager implements CsrfManagerInterface
{
    private CsrfStorageInterface $storage;
    private CsrfGeneratorInterface $generator;
    private string $sessionKey;
    private string $formKey;

    public function __construct(
        CsrfStorageInterface $storage,
        CsrfGeneratorInterface $generator,
        string $sessionKey = 'csrf.tokens',
        string $formKey = '_csrf'
    ) {
        $this->storage = $storage;
        $this->generator = $generator;
        $this->sessionKey = $sessionKey;
        $this->formKey = $formKey;
    }

    public function getToken(): string
    {
        return $this->generateToken();
    }

    public function removeToken(string $token): string
    {
        return $this->storage->removeToken($token);
    }

    public function generateToken(): string
    {
        $token = $this->generator->generateToken();
        $this->storage->setToken($token);
        return $token;
    }

    public function isTokenValid(string $token): bool
    {
        if (!$this->storage->hasToken($token)) {
            return false;
        }
        return true;
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
