<?php

namespace Framework\Security\Authorization;

use Framework\Auth;
use Framework\Auth\ForbiddenException;

class AuthorizationChecker implements AuthorizationCheckerInterface
{
    private $auth;
    private $voterManager;
    private $exceptionOnNoUser;

    public function __construct(Auth $auth, VoterManagerInterface $voterManager, bool $exceptionOnNoUser = false)
    {
        $this->auth = $auth;
        $this->voterManager = $voterManager;
        $this->exceptionOnNoUser = $exceptionOnNoUser;
    }

    public function isGranted($attribute, $subject = null): bool
    {
        if (null === $this->auth->getUser()) {
            if ($this->exceptionOnNoUser) {
                throw new ForbiddenException('User not found.');
            }
            return false;
        }

        return $this->voterManager->decide($this->auth, [$attribute], $subject);
    }
}
