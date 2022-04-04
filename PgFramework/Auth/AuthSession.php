<?php

declare(strict_types=1);

namespace PgFramework\Auth;

use PgFramework\Auth;
use PgFramework\Auth\UserInterface;
use PgFramework\Session\SessionInterface;
use PgFramework\Auth\Provider\UserProviderInterface;

class AuthSession implements Auth
{
    /**
     * Cookie options
     *
     * @var array
     */
    private $options = [
        'sessionName' => 'auth.user',
        'field' => 'username'
    ];

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var User
     */
    private $user;

    /**
     * @var UserProviderInterface
     */
    protected $userProvider;

    public function __construct(
        SessionInterface $session,
        UserProviderInterface $userProvider,
        array $options = []
    ) {
        $this->session = $session;
        $this->userProvider = $userProvider;
        if (!empty($options)) {
            $this->options = array_merge($this->options, $options);
        }
    }

    /**
     *
     * @param string $username
     * @param string $password
     * @return UserInterface|null
     */
    public function login(string $identifier, string $password): ?UserInterface
    {
        if (empty($identifier) || empty($password)) {
            return null;
        }

        /** @var UserInterface $user */
        $user = $this->userProvider->getUser($this->options['field'], $identifier);
        if ($user && password_verify($password, $user->getPassword())) {
            $this->setUser($user);
            return $user;
        }
        return null;
    }

    public function logout(): void
    {
        $this->session->delete($this->options['sessionName']);
        $this->user = null;
    }

    public function getUser(): ?UserInterface
    {
        $userId = $this->session->get($this->options['sessionName']);

        if ($userId) {
            if ($this->user && (int) $this->user->getId() === (int) $userId) {
                return $this->user;
            }
            try {
                $this->user = $this->userProvider->getUser('id', $userId);
                return $this->user;
            } catch (\Exception $e) {
                $this->session->delete($this->options['sessionName']);
                return null;
            }
        }
        return null;
    }

    /**
     *
     * @param User $user
     * @return Auth
     */
    public function setUser(UserInterface $user): Auth
    {
        $this->session->set($this->options['sessionName'], $user->getId());
        $this->user = $user;
        return $this;
    }
}
