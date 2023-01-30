<?php

namespace PgFramework\Security\Csrf;

use PgFramework\Security\Csrf\TokenStorage\TokenStorageInterface;
use PgFramework\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class CsrfManager implements CsrfTokenManagerInterface
{
    private $storage;
    private $generator;
    private $sessionKey;
    private $formKey;

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

    public function getToken(?string $tokenId = null): string
    {
        if (null !== $tokenId) {
            if ($this->storage->hasToken($tokenId)) {
                return $this->storage->getToken($tokenId);
            }
            // Create new one for this id
            return $this->refreshToken($tokenId);
        }
        return $this->generateToken();
    }

    /**
     * {@inheritdoc}
     */
    public function refreshToken(string $tokenId): string
    {
        return $this->generateToken($tokenId);
    }

    /**
     * {@inheritdoc}
     */
    public function removeToken(string $tokenId): string
    {
        $this->storage->removeToken($tokenId);
        return $tokenId;
    }

    /**
     * {@inheritdoc}
     */
    public function isTokenValid(string $token): bool
    {
        [$tokenId, $token] = explode(self::DELIMITER, $token);
        if (!$this->storage->hasToken($tokenId)) {
            return false;
        }
        [, $knownToken] = explode(self::DELIMITER, $this->storage->getToken($tokenId));

        return ($token === $knownToken);
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

    public function generateId(): string
    {
        return bin2hex(random_bytes(8));
    }

    private function generateToken(?string $tokenId = null): string
    {
        if (null !== $tokenId) {
            $token = $tokenId . self::DELIMITER . $this->generator->generateToken();
        } else {
            $tokenId = $this->generateId();
            $token = $tokenId . self::DELIMITER . $this->generator->generateToken();
        }

        $this->storage->setToken($tokenId, $token);
        return $token;
    }
}
