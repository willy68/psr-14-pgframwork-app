<?php

namespace App\Auth;

use PgFramework\Auth\Auth;
use PgFramework\Auth\User;
use PgFramework\Auth\UserInterface;
use Mezzio\Session\SessionInterface;
use PgFramework\Database\NoRecordException;

class DatabaseAuth implements Auth
{
    /**
     * @var UserTable
     */
    private $userTable;
    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var \App\Auth\User
     */
    private $user;

    public function __construct(UserTable $userTable, SessionInterface $session)
    {
        $this->userTable = $userTable;
        $this->session = $session;
    }

    public function login(string $username, string $password): ?UserInterface
    {
        if (empty($username) || empty($password)) {
            return null;
        }

        /** @var \App\Auth\User $user */
        $user = $this->userTable->findBy('username', $username);
        if ($user && password_verify($password, $user->password)) {
            $this->setUser($user);
            return $user;
        }

        return null;
    }

    public function logout(): void
    {
        $this->session->unset('auth.user');
    }

    /**
     * @return User|null
     */
    public function getUser(): ?UserInterface
    {
        if ($this->user) {
            return $this->user;
        }
        $userId = $this->session->get('auth.user');
        if ($userId) {
            try {
                $this->user = $this->userTable->find($userId);
                return $this->user;
            } catch (NoRecordException $exception) {
                $this->session->unset('auth.user');
                return null;
            }
        }
        return null;
    }

    public function setUser(UserInterface $user): Auth
    {
        $this->session->set('auth.user', $user->id);
        $this->user = $user;
        return $this;
    }
}
