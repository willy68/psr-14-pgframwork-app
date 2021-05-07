<?php

namespace PgFramework\Security\Hasher;

class DefaultPasswordHasher implements PasswordHasherInterface
{
    protected $config = [
        'algo' => \PASSWORD_BCRYPT,
        'options' => [
            'cost' => 10,
            'memory_cost' => \PASSWORD_ARGON2_DEFAULT_MEMORY_COST,
            'time_cost' => \PASSWORD_ARGON2_DEFAULT_TIME_COST,
            'threads' => PASSWORD_ARGON2_DEFAULT_THREADS
        ]
    ];

    public function __construct(array $config = [])
    {
        if (!empty($config)) {
            $this->config = array_merge($this->config, $config);
        }
        if (!in_array($this->config['algo'], \password_algos())) {
            $this->config['algo'] = \PASSWORD_DEFAULT;
        }
    }

    public function hash(string $plainPassword)
    {
        return \password_hash($plainPassword, $this->config['algo'], $this->config['options']);
    }

    public function verify(string $hashedPassword, string $plainPassword): bool
    {
        return \password_verify($plainPassword, $hashedPassword);
    }

    public function needsRehash(string $hashedPassword): bool
    {
        return \password_needs_rehash(
            $hashedPassword,
            $this->config['algo'],
            $this->config['options']
        );
    }
}
