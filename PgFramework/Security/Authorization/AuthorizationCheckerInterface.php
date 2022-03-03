<?php

namespace PgFramework\Security\Authorization;

interface AuthorizationCheckerInterface
{
    /**
     * Checks if the attribute is granted against the current auth and optionally supplied subject.
     * $attribute A single attribute to vote on (can be of any type,
     * string and instance of Expression are supported by the core)
     *
     * @param mixed $attribute
     * @param mixed $subject
     *
     * @return bool
     */
    public function isGranted($attribute, $subject = null);
}
