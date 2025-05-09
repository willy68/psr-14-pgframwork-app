<?php

declare(strict_types=1);

namespace PgFramework\Security\Authorization\Voter;

use PgFramework\Auth\Auth;

use function in_array;
use function is_string;

class VoterRoles implements VoterInterface
{
    private string $prefix;

    public function __construct(string $prefix = 'ROLE_')
    {
        $this->prefix = $prefix;
    }

    public function vote(Auth $auth, array $attributes, $subject = null): int
    {
        $result = VoterInterface::ACCESS_ABSTAIN;
        $roles = $auth->getUser()->getRoles();

        foreach ($attributes as $attribute) {
            if (!is_string($attribute) || !str_starts_with($attribute, $this->prefix)) {
                continue;
            }

            $result = VoterInterface::ACCESS_DENIED;
            if (in_array($attribute, $roles, true)) {
                return VoterInterface::ACCESS_GRANTED;
            }
        }
        return $result;
    }
}
