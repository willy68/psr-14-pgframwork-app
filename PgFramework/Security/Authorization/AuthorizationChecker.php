<?php

declare(strict_types=1);

namespace PgFramework\Security\Authorization;

use PgFramework\Auth\Auth;
use PgFramework\Auth\ForbiddenException;

class AuthorizationChecker implements AuthorizationCheckerInterface
{
    private Auth $auth;
    private VoterManagerInterface $voterManager;
    private bool $exceptionOnNoUser;

    public function __construct(Auth $auth, VoterManagerInterface $voterManager, bool $exceptionOnNoUser = false)
    {
        $this->auth = $auth;
        $this->voterManager = $voterManager;
        $this->exceptionOnNoUser = $exceptionOnNoUser;
    }

    /**
     * @throws ForbiddenException
     */
    public function isGranted(mixed $attribute, mixed $subject = null): bool
    {
        if (null === $this->auth->getUser()) {
            if ($this->exceptionOnNoUser) {
                throw new ForbiddenException('User not found.');
            }
            return false;
        }

        return $this->voterManager->decide($this->auth, [$attribute], $subject);
    }

    public function setExceptionOnNoUser(bool $exceptionOnNoUser): static
    {
        $this->exceptionOnNoUser = $exceptionOnNoUser;
        return $this;
    }
}
