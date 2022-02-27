<?php

namespace PgFramework;

use PgFramework\Auth\UserInterface;

interface Auth
{
    /**
     *
     * @return UserInterface|null
     */
    public function getUser(): ?UserInterface;

    /**
     *
     * @param UserInterface $user
     * @return Auth
     */
    public function setUser(UserInterface $user): self;
}
