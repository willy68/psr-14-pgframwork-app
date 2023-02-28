<?php

declare(strict_types=1);

namespace PgFramework\Security\Hasher;

use PgFramework\Security\Security;

use function array_merge;
use function hash_hmac;
use function in_array;
use function password_algos;
use function password_hash;
use function password_needs_rehash;
use function password_verify;

use const PASSWORD_ARGON2_DEFAULT_MEMORY_COST;
use const PASSWORD_ARGON2_DEFAULT_THREADS;
use const PASSWORD_ARGON2_DEFAULT_TIME_COST;
use const PASSWORD_BCRYPT;
use const PASSWORD_DEFAULT;

class PepperPasswordHasher implements PasswordHasherInterface
{
    protected array $config = [
        'algo' => PASSWORD_BCRYPT,
        'options' => [
            'cost' => 10,
            'memory_cost' => PASSWORD_ARGON2_DEFAULT_MEMORY_COST,
            'time_cost' => PASSWORD_ARGON2_DEFAULT_TIME_COST,
            'threads' => PASSWORD_ARGON2_DEFAULT_THREADS
        ]
    ];

    public function __construct(array $config = [])
    {
        if (!empty($config)) {
            $this->config = array_merge($this->config, $config);
        }

        if (!in_array($this->config['algo'], password_algos())) {
            $this->config['algo'] = PASSWORD_DEFAULT;
        }
    }

    public function hash(string $plainPassword): string
    {
        $plainPassword = hash_hmac("sha256", $plainPassword, Security::getSalt());
        return password_hash($plainPassword, $this->config['algo'], $this->config['options']);
    }

    public function verify(string $hashedPassword, string $plainPassword): bool
    {
        $plainPassword = hash_hmac("sha256", $plainPassword, Security::getSalt());
        return password_verify($plainPassword, $hashedPassword);
    }

    public function needsRehash(string $hashedPassword): bool
    {
        return password_needs_rehash(
            $hashedPassword,
            $this->config['algo'],
            $this->config['options']
        );
    }
}
