<?php

namespace PgFramework\Security\Firewall\EventListener;

use App\Auth\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use League\Event\ListenerPriority;
use PgFramework\Security\Firewall\FirewallEvents;
use PgFramework\Security\Hasher\PasswordHasherInterface;
use PgFramework\EventDispatcher\EventSubscriberInterface;
use PgFramework\Security\Firewall\Event\LoginSuccessEvent;

class RehashPasswordListener implements EventSubscriberInterface
{
    protected PasswordHasherInterface $hasher;

    protected EntityManagerInterface $em;

    public function __construct(PasswordHasherInterface $hasher, EntityManagerInterface $em)
    {
        $this->hasher = $hasher;
        $this->em = $em;
    }

    public function onLoginSuccess(LoginSuccessEvent $event)
    {
        $result = $event->getResult();
        /** @var User $user */
        $user = $result->getUser();
        $credentials = $result->getCredentials();
        $plainPassword = $credentials['password'] ?? null;

        if ($plainPassword && $this->hasher->needsRehash($user->getPassword())) {
            $password = $this->hasher->hash($plainPassword);
            $user->setPassword($password);
            $this->em->persist($user);
            $this->em->flush();

            $event->setResult($result->setUser($user));
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            FirewallEvents::LOGIN_SUCCESS =>  ['onLoginSuccess', ListenerPriority::HIGH]
        ];
    }
}
