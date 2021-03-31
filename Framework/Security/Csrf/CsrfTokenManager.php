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

    public function getToken(string $tokenId): string
    {
        if ($this->storage->hasToken($tokenId)) {
            return $this->storage->getToken($tokenId);
        }

        $token = $tokenId . self::delimiter . $this->generator->generateToken();
        $this->storage->setToken($tokenId, $token);
        return $token;
    }

    /**
     * {@inheritdoc}
     */
    public function refreshToken(string $tokenId): string
    {
        $token = $tokenId . self::delimiter . $this->generator->generateToken();

        $this->storage->setToken($tokenId, $token);

        return $token;
    }

    /**
     * {@inheritdoc}
     */
    public function removeToken(string $tokenId): string
    {
        return $this->storage->removeToken($tokenId);
        return $tokenId;
    }

    /**
     * {@inheritdoc}
     */
    public function isTokenValid(string $token): bool
    {
        [$tokenId, ] = explode(self::delimiter, $token);
        if (!$this->storage->hasToken($tokenId)) {
            return false;
        }

        return hash_equals($this->storage->getToken($tokenId), $token);
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
