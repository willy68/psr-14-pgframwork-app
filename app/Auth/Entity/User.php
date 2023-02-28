<?php

namespace App\Auth\Entity;

use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use PgFramework\Auth\UserInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="users")
 */
#[Entity]
#[Table(name: 'users')]
class User implements UserInterface
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
    public string $username;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string|null
     */
    #[Column(type: Types::STRING, nullable: true)]
    private ?string $firstname = null;


    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string|null
     */
    #[Column(type: Types::STRING, nullable: true)]
    private ?string $lastname = null;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    #[Column(type: TYPES::STRING)]
    public string $email;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    #[Column(type: TYPES::STRING)]
    public string $password;

    /**
     * @ORM\Column(type="json")
     * @var string[]
     */
    #[Column(type: TYPES::JSON)]
    public array $roles = [];

    /**
     * @ORM\Column(name="password_reset" ,type="string", nullable=true)
     * @var string|null
     */
    #[Column(name: 'password_reset', type: TYPES::STRING, nullable: true)]
    protected ?string $passwordReset = null;

    /**
     * @ORM\Column(name="password_reset_at" ,type="datetime", nullable=true)
     * @var DateTime|null
     */
    #[Column(name: 'password_reset_at', type: TYPES::DATETIME_MUTABLE, nullable: true)]
    protected ?DateTime $passwordResetAt = null;

    /**
     * Get the value of ID
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
     * @param $id
     * @return  self
     */
    public function setId($id): static
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of username
     *
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * Set the value of username
     *
     * @param $username
     * @return  self
     */
    public function setUsername($username): static
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get the value of email
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * Set the value of email
     *
     * @param $email
     * @return  self
     */
    public function setEmail($email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get the value of password
     *
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * Set the value of password
     *
     * @param $password
     * @return  self
     */
    public function setPassword($password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Set the value of roles
     *
     * @param $roles
     * @return  self
     */
    public function setRoles($roles): static
    {
        $this->roles = is_string($roles) ? json_decode($roles) : $roles;

        return $this;
    }

    /**
     * Get the value of roles
     *
     * @return string[]
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * Get the value of firstname
     * @return string|null
     */
    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    /**
     * Set the value of firstname
     *
     * @param $firstname
     * @return  self
     */
    public function setFirstname($firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * Get the value of lastname
     * @return string|null
     */
    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    /**
     * Set the value of lastname
     *
     * @param $lastname
     * @return  self
     */
    public function setLastname($lastname): static
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPasswordReset(): ?string
    {
        return $this->passwordReset;
    }

    /**
     * @param string $passwordReset
     * @return static
     */
    public function setPasswordReset(string $passwordReset): static
    {
        $this->passwordReset = $passwordReset;

        return $this;
    }

    /**
     * @param $date
     * @return static
     */
    public function setPasswordResetAt($date): static
    {
        if (is_string($date)) {
            $this->passwordResetAt = DateTime::createFromFormat('Y-m-d H:i:s', $date);
        } else {
            $this->passwordResetAt = $date;
        }
        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getPasswordResetAt(): ?DateTime
    {
        return $this->passwordResetAt;
    }
}
