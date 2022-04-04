<?php

declare(strict_types=1);

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Modify for this PgFramework by William Lety
 */

namespace PgFramework\Security\Authorization;

use PgFramework\Auth;
use InvalidArgumentException;
use PgFramework\Security\Authorization\Voter\VoterInterface;

class VoterManager implements VoterManagerInterface
{
    private $voters;
    private $strategy;
    private $allowIfAllAbstainDecisions;
    private $allowIfEqualGrantedDeniedDecisions;

    /**
     * @param iterable|VoterInterface[] $voters An array or an iterator of VoterInterface instances
     * @param string $strategy The vote strategy
     * @param bool $allowIfAllAbstainDecisions Whether to grant access if all voters abstained or not
     * @param bool $allowIfEqualGrantedDeniedDecisions Whether to grant access if result are equals
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(
        iterable $voters = [],
        string $strategy = self::STRATEGY_AFFIRMATIVE,
        bool $allowIfAllAbstainDecisions = false,
        bool $allowIfEqualGrantedDeniedDecisions = true
    ) {
        $strategyMethod = 'decide' . ucfirst($strategy);
        if ('' === $strategy || !\is_callable([$this, $strategyMethod])) {
            throw new InvalidArgumentException(sprintf('The strategy "%s" is not supported.', $strategy));
        }

        $this->voters = $voters;
        $this->strategy = $strategyMethod;
        $this->allowIfAllAbstainDecisions = $allowIfAllAbstainDecisions;
        $this->allowIfEqualGrantedDeniedDecisions = $allowIfEqualGrantedDeniedDecisions;
    }

    /**
     *
     * {@inheritdoc}
     */
    public function decide(Auth $auth, array $attributes, $subject = null)
    {
        return $this->{$this->strategy}($auth, $attributes, $subject);
    }

    /**
     * Grants access if any voter returns an affirmative response.
     *
     * If all voters abstained from voting, the decision will be based on the
     * allowIfAllAbstainDecisions property value (defaults to false).
     */
    private function decideAffirmative(Auth $auth, array $attributes, $subject = null): bool
    {
        $deny = 0;
        foreach ($this->voters as $voter) {
            $result = $voter->vote($auth, $attributes, $subject);

            if (VoterInterface::ACCESS_GRANTED === $result) {
                return true;
            }

            if (VoterInterface::ACCESS_DENIED === $result) {
                ++$deny;
            } elseif (VoterInterface::ACCESS_ABSTAIN !== $result) {
                trigger_deprecation(
                    'symfony/security-core',
                    '5.3',
                    'Returning "%s" in "%s::vote()" is deprecated, return one of "%s" constants: "ACCESS_GRANTED",
                    "ACCESS_DENIED" or "ACCESS_ABSTAIN".',
                    var_export($result, true),
                    get_debug_type($voter),
                    VoterInterface::class
                );
            }
        }

        if ($deny > 0) {
            return false;
        }

        return $this->allowIfAllAbstainDecisions;
    }

    /**
     * Grants access if there is consensus of granted against denied responses.
     *
     * Consensus means majority-rule (ignoring abstains) rather than unanimous
     * agreement (ignoring abstains). If you require unanimity, see
     * UnanimousBased.
     *
     * If there were an equal number of grant and deny votes, the decision will
     * be based on the allowIfEqualGrantedDeniedDecisions property value
     * (defaults to true).
     *
     * If all voters abstained from voting, the decision will be based on the
     * allowIfAllAbstainDecisions property value (defaults to false).
     */
    private function decideConsensus(Auth $auth, array $attributes, $subject = null): bool
    {
        $grant = 0;
        $deny = 0;
        foreach ($this->voters as $voter) {
            $result = $voter->vote($auth, $attributes, $subject);

            if (VoterInterface::ACCESS_GRANTED === $result) {
                ++$grant;
            } elseif (VoterInterface::ACCESS_DENIED === $result) {
                ++$deny;
            } elseif (VoterInterface::ACCESS_ABSTAIN !== $result) {
                trigger_deprecation(
                    'symfony/security-core',
                    '5.3',
                    'Returning "%s" in "%s::vote()" is deprecated, return one of "%s" constants: "ACCESS_GRANTED",
                    "ACCESS_DENIED" or "ACCESS_ABSTAIN".',
                    var_export($result, true),
                    get_debug_type($voter),
                    VoterInterface::class
                );
            }
        }

        if ($grant > $deny) {
            return true;
        }

        if ($deny > $grant) {
            return false;
        }

        if ($grant > 0) {
            return $this->allowIfEqualGrantedDeniedDecisions;
        }

        return $this->allowIfAllAbstainDecisions;
    }

    /**
     * Grants access if only grant (or abstain) votes were received.
     *
     * If all voters abstained from voting, the decision will be based on the
     * allowIfAllAbstainDecisions property value (defaults to false).
     */
    private function decideUnanimous(Auth $auth, array $attributes, $subject = null): bool
    {
        $grant = 0;
        foreach ($this->voters as $voter) {
            foreach ($attributes as $attribute) {
                $result = $voter->vote($auth, [$attribute], $subject);

                if (VoterInterface::ACCESS_DENIED === $result) {
                    return false;
                }

                if (VoterInterface::ACCESS_GRANTED === $result) {
                    ++$grant;
                } elseif (VoterInterface::ACCESS_ABSTAIN !== $result) {
                    trigger_deprecation(
                        'symfony/security-core',
                        '5.3',
                        'Returning "%s" in "%s::vote()" is deprecated, return one of "%s" constants: "ACCESS_GRANTED",
                        "ACCESS_DENIED" or "ACCESS_ABSTAIN".',
                        var_export($result, true),
                        get_debug_type($voter),
                        VoterInterface::class
                    );
                }
            }
        }

        // no deny votes
        if ($grant > 0) {
            return true;
        }

        return $this->allowIfAllAbstainDecisions;
    }

    /**
     * Grant or deny access depending on the first voter that does not abstain.
     * The priority of voters can be used to overrule a decision.
     *
     * If all voters abstained from voting, the decision will be based on the
     * allowIfAllAbstainDecisions property value (defaults to false).
     */
    private function decidePriority(Auth $auth, array $attributes, $subject = null)
    {
        foreach ($this->voters as $voter) {
            $result = $voter->vote($auth, $attributes, $subject);

            if (VoterInterface::ACCESS_GRANTED === $result) {
                return true;
            }

            if (VoterInterface::ACCESS_DENIED === $result) {
                return false;
            }

            if (VoterInterface::ACCESS_ABSTAIN !== $result) {
                trigger_deprecation(
                    'symfony/security-core',
                    '5.3',
                    'Returning "%s" in "%s::vote()" is deprecated, return one of "%s" constants: "ACCESS_GRANTED",
                    "ACCESS_DENIED" or "ACCESS_ABSTAIN".',
                    var_export($result, true),
                    get_debug_type($voter),
                    VoterInterface::class
                );
            }
        }

        return $this->allowIfAllAbstainDecisions;
    }
}
