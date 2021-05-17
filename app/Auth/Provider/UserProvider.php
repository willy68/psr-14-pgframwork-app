<?php

namespace App\Auth\Provider;

use App\Auth\Entity\User;
use Doctrine\ORM\EntityManager;
use PgFramework\Auth\UserInterface;
use PgFramework\Auth\Provider\UserProviderInterface;

class UserProvider implements UserProviderInterface
{
    /**
     * @var string
     */
    protected $entity;

    /**
     *
     * @var EntityManager
     */
    protected $em;

    public function __construct(EntityManager $em, string $entity = User::class)
    {
        $this->em = $em;
        $this->entity = $entity;
    }

    public function getUser(string $field, $value): ?UserInterface
    {
        try {
            $repo = $this->em->getRepository($this->entity);
            /** @var User $user */
            $user = $repo->findOneBy([$field => $value]);
        } catch (\Exception $e) {
            return null;
        }
        return $user;
    }

    public function updateUser(UserInterface $user): ?UserInterface
    {
        
        try {
            $dbUser = $this->em->find($this->entity, $user->getId());
        } catch (\Exception $e) {
            return null;
        }

        if (null === $dbUser) {
            return null;
        }

        $this->em->persist($user);
        $this->em->flush();
        return $user;
    }
}
