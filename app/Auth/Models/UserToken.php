<?php

namespace App\Auth\Models;

use ActiveRecord\Model;
use Framework\Auth\TokenInterface;

class UserToken extends Model implements TokenInterface
{
    public static $connection = 'blog';

    public static $table_name = 'user_tokens';

    /**
     * get token id
     *
     * @return int
     */
    public function getId(): int
    {
        return (int)$this->id;
    }

    /**
     * get the unique user credential (ex. username or email)
     *
     * @return string
     */
    public function getSeries(): string
    {
        return $this->series;
    }

    /**
     * get the unique user credential (ex. username or email)
     *
     * @return string
     */
    public function getCredential(): string
    {
        return $this->credential;
    }

    /**
     * get the random pasword hash
     *
     * @return string
     */
    public function getRandomPassword(): string
    {
        return $this->random_password;
    }
    /**
     * get the expiration date
     *
     * @return \DateTime
     */
    public function getExpirationDate(): \DateTime
    {
        return $this->expiration_date;
    }

    /**
     * get is_expired field as bool
     *
     * @return bool
     */
    public function getIsExpired(): bool
    {
        return (bool)$this->is_expired;
    }
}
