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

abstract class AbstractVoter implements VoterInterface
{
    /**
    * {@inheritdoc}
    */
    public function vote(Auth $auth, array $attributes, $subject = null)
    {
        // abstain vote by default in case none of the attributes are supported
        $vote = self::ACCESS_ABSTAIN;

        foreach ($attributes as $attribute) {
            try {
                if (!$this->canVote($attribute, $subject)) {
                    continue;
                }
            } catch (\TypeError $e) {
                if (\PHP_VERSION_ID < 80000) {
                    if (
                        0 === strpos($e->getMessage(), 'Argument 1 passed to')
                        && false !== strpos($e->getMessage(), '::canVote() must be of the type string')
                    ) {
                        continue;
                    }
                } elseif (false !== strpos($e->getMessage(), 'canVote(): Argument #1')) {
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
