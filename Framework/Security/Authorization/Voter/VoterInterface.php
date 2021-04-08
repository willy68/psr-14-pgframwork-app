<?php

namespace Framework\Security\Authorization\Voter;

use Framework\Auth;

interface VoterInterface
{

    public const ACCESS_GRANTED = 1;
    public const ACCESS_ABSTAIN = 0;
    public const ACCESS_DENIED = -1;

    public function vote(Auth $auth, array $attributes, $subject = null);
}
