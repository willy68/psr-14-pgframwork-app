<?php

declare(strict_types=1);

namespace PgFramework\Security\Authorization\Voter;

use PgFramework\Auth\Auth;

class VoterRoles implements VoterInterface
{
    private $prefix;

    public function __construct(string $prefix = 'ROLE_')
    {
        $this->prefix = $prefix;
    }

    public function vote(Auth $auth, array $attributes, $subject = null)
    {
        $result = VoterInterface::ACCESS_ABSTAIN;
        $roles = $auth->getUser()->getRoles();

        foreach ($attributes as $attribute) {
            if (!\is_string($attribute) || 0 !== strpos($attribute, $this->prefix)) {
                continue;
            }

            $result = VoterInterface::ACCESS_DENIED;
            foreach ($roles as $role) {
                if ($attribute === $role) {
                    return VoterInterface::ACCESS_GRANTED;
                }
            }
        }
        return $result;
    }
}
