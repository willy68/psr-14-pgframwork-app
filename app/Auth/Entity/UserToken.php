<?php

namespace App\Auth\Entity;

use DateTime;
use Doctrine\ORM\Mapping\Id;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use PgFramework\Auth\TokenInterface;
use Doctrine\ORM\Mapping\GeneratedValue;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_tokens")
 */
#[Entity]
#[Table(name: 'user_tokens')]
class UserToken implements TokenInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     * @var int
     */
    #[Id]
    #[GeneratedValue]
    #[Column(type: Types::INTEGER)]
    public int $id;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    #[Column(type: TYPES::STRING)]
    public string $series;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    #[Column(type: TYPES::STRING)]
    public string $credential;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    #[Column(type: TYPES::STRING)]
    public string $random_password;

    /**
     * @ORM\Column(type="datetime")
     * @var Datetime
     */
    #[Column(type: TYPES::DATETIME_MUTABLE)]
    public DateTime $expiration_date;

    /**
     * @var bool
     */
    public bool $is_expired;

    /**
     * get token ID
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Set the value of ID
     *
     * @param  int  $id
     *
     * @return  self
     */
    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
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
     * Set the value of series
     *
     * @param  string  $series
     * @return  self
     */

    public function setSeries(string $series): static
    {
        $this->series = $series;

        return $this;
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
     * Set the value of credential
     *
     * @param  string  $credential
     *
     * @return  self
     */
    public function setCredential(string $credential): static
    {
        $this->credential = $credential;

        return $this;
    }

    /**
     * Get the random pasword hash
     *
     * @return string
     */
    public function getRandomPassword(): string
    {
        return $this->random_password;
    }

    /**
     * Set the value of random_password
     *
     * @param  string  $random_password
     *
     * @return  self
     */
    public function setRandomPassword(string $random_password): static
    {
        $this->random_password = $random_password;

        return $this;
    }

    /**
     * Get the expiration date
     *
     * @return DateTime
     */
    public function getExpirationDate(): DateTime
    {
        return $this->expiration_date;
    }

    /**
     * Set the value of expiration_date
     *
     * @param  Datetime  $expiration_date
     *
     * @return  self
     */
    public function setExpirationDate(Datetime $expiration_date): static
    {
        $this->expiration_date = $expiration_date;

        return $this;
    }

    /**
     * Get is_expired field as bool
     *
     * @return bool
     */
    public function getIsExpired(): bool
    {
        return $this->is_expired;
    }


    /**
     * Set the value of is_expired
     *
     * @param  bool  $is_expired
     * @return  self
     */
    public function setIsExpired(bool $is_expired): static
    {
        $this->is_expired = $is_expired;

        return $this;
    }
}
