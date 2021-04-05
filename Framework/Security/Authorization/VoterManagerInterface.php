<?php

namespace Framework\Security\Authorization;

use Framework\Auth;

interface VoterManagerInterface
{
    public const STRATEGY_AFFIRMATIVE = 'affirmative';
    public const STRATEGY_CONSENSUS = 'consensus';
    public const STRATEGY_UNANIMOUS = 'unanimous';
    public const STRATEGY_PRIORITY = 'priority';

    public function decide(Auth $auth, array $attributes, $subject = null);
}
