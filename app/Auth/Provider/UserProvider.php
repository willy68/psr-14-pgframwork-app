<?php

namespace App\Auth\Provider;

use App\Auth\Entity\User;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use PgFramework\Auth\UserInterface;
use PgFramework\Auth\Provider\UserProviderInterface;
use PgFramework\Security\Hasher\PasswordHasherInterface;
//use Ramsey\Uuid\Uuid;

class UserProvider implements UserProviderInterface
{
    protected string $entity;
    protected EntityManagerInterface $em;
    private PasswordHasherInterface $hasher;

    public function __construct(
        EntityManagerInterface $em,
        PasswordHasherInterface $hasher,
        string $entity = User::class
    ) {
        $this->em = $em;
        $this->entity = $entity;
        $this->hasher = $hasher;
    }

    public function getUser(string $field, $value): ?UserInterface
    {
        $repo = $this->em->getRepository($this->entity);
        /** @var User $user */
        $user = $repo->findOneBy([$field => $value]);
        return $user;
    }

    public function updateUser(UserInterface $user): ?UserInterface
    {
        $dbUser = $this->em->find($this->entity, $user->getId());

        if (null === $dbUser) {
            return null;
        }

        $this->em->persist($user);
        $this->em->flush();
        return $user;
    }
    public function resetPassword(User $user): string
    {
        //$token = Uuid::uuid4()->toString();
		$token = random_bytes(64);
        $user->setPasswordReset($token);
        $user->setPasswordResetAt(new DateTime());
        $this->em->persist($user);
        $this->em->flush();
        return $token;
    }

    public function updatePassword(User $user, string $password): UserInterface
    {
        $user->setPassword($this->hasher->hash($password));
        $this->em->persist($user);
        $this->em->flush();
        return $user;
    }

    /**
     * @return EntityManagerInterface
     */
    public function getEntityManager(): EntityManagerInterface
    {
        return $this->em;
    }

    /**
     * @return string
     */
    public function getEntity(): string
    {
        return $this->entity;
    }
}
