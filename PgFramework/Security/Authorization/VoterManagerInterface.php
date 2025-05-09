<?php

declare(strict_types=1);

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PgFramework\Security\Authorization;

use PgFramework\Auth\Auth;

/**
 * VoterManagerInterface makes authorization decisions.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author William Lety <william.lety@gmail.com>
 */
interface VoterManagerInterface
{
    public const STRATEGY_AFFIRMATIVE = 'affirmative';
    public const STRATEGY_CONSENSUS = 'consensus';
    public const STRATEGY_UNANIMOUS = 'unanimous';
    public const STRATEGY_PRIORITY = 'priority';


    /**
     * Decides whether the access is possible or not.
     *
     * @param Auth $auth
     * @param array $attributes An array of attributes associated with the method being invoked
     * @param mixed $subject
     * @return bool True if the access is granted, false otherwise
     */
    public function decide(Auth $auth, array $attributes, mixed $subject = null): bool;
}
