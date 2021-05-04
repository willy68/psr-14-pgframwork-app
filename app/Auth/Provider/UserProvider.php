<?php

namespace App\Auth\Provider;

use App\Auth\Entity\User;
use Doctrine\ORM\EntityManager;
use PgFramework\Auth\User as AuthUser;
use PgFramework\Auth\Provider\UserProviderInterface;

class UserProvider implements UserProviderInterface
{
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function getUser(string $field, $value): ?AuthUser
    {
        $repo = $this->em->getRepository(User::class);
        try {
            /** @var User $user */
            $user = $repo->findBy([$field, $value]);
        } catch (\Exception $e) {
            return null;
        }
        return $user;
    }
}
