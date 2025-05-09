<?php

namespace App\Auth;

use Mezzio\Session\SessionInterface;
use PgFramework\Auth\Auth;
use PgFramework\Auth\UserInterface;
use PgFramework\Database\NoRecordException;

class DatabaseAuth implements Auth
{
    private UserTable $userTable;

    private SessionInterface $session;

    private ?UserInterface $user = null;

    public function __construct(UserTable $userTable, SessionInterface $session)
    {
        $this->userTable = $userTable;
        $this->session = $session;
    }

    /**
     * @param string $username
     * @param string $password
     * @return UserInterface|null
     * @throws NoRecordException
     */
    public function login(string $username, string $password): ?UserInterface
    {
        if (empty($username) || empty($password)) {
            return null;
        }

        /** @var UserInterface $user */
        $user = $this->userTable->findBy('username', $username);
        if ($user && password_verify($password, $user->getPassword())) {
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
     * @return UserInterface|null
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
        $this->session->set('auth.user', $user->getId());
        $this->user = $user;
        return $this;
    }
}
