<?php

namespace App\Auth\Models;

use ActiveRecord\Model;
use DateTime;
use PgFramework\Auth\TokenInterface;

class UserToken extends Model implements TokenInterface
{
    public static $connection = 'blog';

    public static $table_name = 'user_tokens';

    /**
     * get token id
     */
    public function getId(): int
    {
        return (int)$this->id;
    }

    /**
     * get the unique user credential (ex. username or email)
     */
    public function getSeries(): string
    {
        return $this->series;
    }

    /**
     * get the unique user credential (ex. username or email)
     */
    public function getCredential(): string
    {
        return $this->credential;
    }

    /**
     * Get the random pasword hash
     */
    public function getRandomPassword(): string
    {
        return $this->random_password;
    }
    /**
     * Get the expiration date
     */
    public function getExpirationDate(): DateTime
    {
        return $this->expiration_date;
    }

    /**
     * Get is_expired field as bool
     *
     * @return bool
     */
    public function getIsExpired(): bool
    {
        return (bool)$this->is_expired;
    }
}
