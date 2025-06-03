<?php

declare(strict_types=1);

namespace PgFramework\Security\Csrf;

use Exception;
use PgFramework\Security\Security;
use PgFramework\Security\Csrf\TokenStorage\TokenStorageInterface;
use PgFramework\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class CsrfTokenManager implements CsrfTokenManagerInterface
{
    private TokenStorageInterface $storage;
    private TokenGeneratorInterface $generator;
    private string $sessionKey;
	private string $formKey;

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

    /**
     * @throws Exception
     */
    public function getToken(?string $tokenId = null): string
    {
        if (null !== $tokenId) {
            if ($this->storage->hasToken($tokenId)) {
                return $this->storage->getToken($tokenId);
            }
            // Create new one for this id
            return $this->generateToken($tokenId);
        }

        // Get last token
        $token = $this->storage->getToken();
        if (null === $token) {
            return $this->generateToken();
        }
        return $token;
    }

    /**
     * {@inheritdoc}
     * @throws Exception
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
     * @throws Exception
     */
    public function isTokenValid(string $token): bool
    {
        [$tokenId, $token] = explode(self::DELIMITER, $token);
        if (!$this->storage->hasToken($tokenId)) {
            return false;
        }
        [, $knownToken] = explode(self::DELIMITER, $this->storage->getToken($tokenId));

        $knownToken = Security::unsaltToken($knownToken);
        $token = Security::unsaltToken($token);
        if (false === Security::verifyToken($token)) {
            return false;
        }

        return hash_equals(base64_decode($knownToken), base64_decode($token));
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

    /**
     * @throws Exception
     */
    public function generateId(): string
    {
        return bin2hex(Security::randomBytes(8));
    }

    /**
     * @throws Exception
     */
    public function generateToken(?string $tokenId = null): string
    {
        if (null === $tokenId) {
            $tokenId = $this->generateId();
        }
        $token = $tokenId . self::DELIMITER . $this->generator->generateToken();

        $this->storage->setToken($tokenId, $token);
        return $token;
    }
}
