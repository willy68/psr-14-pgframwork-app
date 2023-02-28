<?php

declare(strict_types=1);

namespace PgFramework\Security\Authorization;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class VoterManagerFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $c): VoterManager
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
