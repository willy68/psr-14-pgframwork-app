<?php

namespace App\Blog\db\Fixtures;

use App\Auth\Entity\User;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;

class UserFixtures implements FixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setUsername('admin')
            ->setEmail('admin@admin.fr')
            ->setPassword(password_hash('admin', PASSWORD_DEFAULT, []))
            ->setRoles(['ROLE_USER', 'ROLE_ADMIN']);
        $manager->persist($user);

        $user = new User();
        $user->setUsername('willy')
            ->setEmail('willy@willy.fr')
            ->setPassword(password_hash('willy', PASSWORD_DEFAULT, []))
            ->setRoles(['ROLE_USER']);
        $manager->persist($user);

        $manager->flush();
    }
}
