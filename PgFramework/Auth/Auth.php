<?php

declare(strict_types=1);

namespace PgFramework\Auth;

interface Auth
{
    /**
     * @return UserInterface|null
     */
    public function getUser(): ?UserInterface;

    /**
     * @param UserInterface $user
     * @return Auth
     */
    public function setUser(UserInterface $user): self;
}
