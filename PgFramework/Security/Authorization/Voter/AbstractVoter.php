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

namespace PgFramework\Security\Authorization\Voter;

use PgFramework\Auth\Auth;
use TypeError;

abstract class AbstractVoter implements VoterInterface
{
    public function vote(Auth $auth, array $attributes, $subject = null): int
    {
        // abstain vote by default in case none of the attributes are supported
        $vote = self::ACCESS_ABSTAIN;

        foreach ($attributes as $attribute) {
            try {
                if (!$this->canVote($attribute, $subject)) {
                    continue;
                }
            } catch (TypeError $e) {
                if (
                    str_starts_with($e->getMessage(), 'Argument 1 passed to')
                    && str_contains($e->getMessage(), '::canVote() must be of the type string')
                ) {
                    continue;
                }
                throw $e;
            }

            // as soon as at least one attribute is supported, default is to deny access
            $vote = self::ACCESS_DENIED;

            if ($this->voteOnAttribute($auth, $attribute, $subject)) {
                // grant access as soon as at least one attribute returns a positive response
                return self::ACCESS_GRANTED;
            }
        }

        return $vote;
    }

    abstract public function canVote(string $attribute, $subject = null): bool;

    abstract public function voteOnAttribute(Auth $auth, string $attribute, $subject = null);
}
