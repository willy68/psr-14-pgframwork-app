<?php

namespace Framework\Security\Authorization\Voter;

use Framework\Auth;

class VoterRoles implements VoterInterface
{
    public function vote(Auth $auth, array $attributes, $subject = null)
    {
        $result = VoterInterface::ACCESS_ABSTAIN;
        $roles = $auth->getUser()->getRoles();

        foreach ($attributes as $attribute) {
            if (!\is_string($attribute)) {
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
