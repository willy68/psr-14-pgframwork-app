<?php

declare(strict_types=1);

namespace PgFramework\Security\Hasher;

interface PasswordHasherInterface
{
    /**
     * Generate password hash
     *
     * @param string $plainPassword
     * @return string
     */
    public function hash(string $plainPassword): string;

    /**
     * Verify password with hashed user password
     *
     * @param string $hashedPassword
     * @param string $plainPassword
     * @return bool
     */
    public function verify(string $hashedPassword, string $plainPassword): bool;

    /**
     * Returns true if the password need to be rehashed, due to the password being
     * created with anything else than the passwords generated by this class.
     *
     * @param string $hashedPassword
     * @return bool
     */
    public function needsRehash(string $hashedPassword): bool;
}
