<?php

namespace Framework\Security\Authorization;

use Psr\Container\ContainerInterface;
use Framework\Security\Authorization\VoterManagerInterface;

class VoterManagerFactory
{

    public function __invoke(ContainerInterface $c)
    {
        $voters = [];
        $strategy = VoterManagerInterface::STRATEGY_AFFIRMATIVE;

        if ($c->has('security.voters')) {
            $voters = $c->get('security.voters');
        }

        if ($c->has('security.voters.strategy')) {
            $strategy = $c->get('security.voters.strategy');
        }

        return new VoterManager($voters, $strategy);
    }
}
