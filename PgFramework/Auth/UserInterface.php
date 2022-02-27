<?php

namespace PgFramework\Auth;

interface UserInterface
{
    /**
     *
     * @return int
     */
    public function getId(): int;

    /**
     *
     * @return string
     */
    public function getEmail(): string;

    /**
     *
     * @return string
     */
    public function getUsername(): string;

    /**
     *
     *
     * @return string
     */
    public function getPassword(): string;

    /**
     *
     * @return string[]
     */
    public function getRoles(): array;
}
