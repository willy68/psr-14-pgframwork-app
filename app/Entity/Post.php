<?php

namespace App\Entity;

use App\Repository\PostRepository;
use DateTime;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PostRepository")
 * @ORM\Table(name="posts")
 */
#[Entity(repositoryClass: PostRepository::class)]
#[Table(name: 'posts')]
class Post
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
    protected int $id;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    #[Column(type: TYPES::STRING)]
    protected string $name;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    #[Column(type: TYPES::STRING)]
    protected string $slug;

    /**
     * @ORM\Column(type="text")
     * @var string
     */
    #[Column(type: TYPES::TEXT)]
    protected string $content;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @var DateTimeImmutable
     */
    #[Column(name: 'created_at', type: TYPES::DATETIME_IMMUTABLE)]
    protected DateTimeImmutable $createdAt;

    /**
     * @ORM\Column(type="datetime")
     * @var DateTime
     */
    #[Column(name: 'updated_at', type: TYPES::DATETIME_MUTABLE)]
    protected DateTime $updatedAt;

    /**
     * @ORM\Column(type="string")
     * @var string|null
     */
    #[Column(type: TYPES::STRING, nullable: true)]
    protected ?string $image = null;

    /**
     * @ORM\Column(type="boolean")
     * @var bool
     */
    #[Column(type: TYPES::BOOLEAN)]
    protected bool $published;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Category", inversedBy="addPost")
     */
    #[ManyToOne(targetEntity: Category::class, inversedBy: "addPost")]
    protected ?Category $category = null;

    /**
     * Get the value of ID
     *
     * @return  int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Set the value of ID
     *
     * @param int $id
     * @return  self
     */
    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }


    /**
     * Get the value of name
     *
     * @return  string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set the value of name
     *
     * @param string $name
     * @return  self
     */
    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the value of slug
     *
     * @return  string
     */
    public function getSlug(): string
    {
        return $this->slug;
    }

    /**
     * Set the value of slug
     *
     * @param string $slug
     * @return  self
     */
    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get the value of content
     *
     * @return  string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * Set the value of content
     *
     * @param string $content
     * @return  self
     */
    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get the value of createdAt
     *
     * @return  DateTimeImmutable
     */
    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Set the value of createdAt
     *
     * @param DateTimeImmutable|string $createdAt
     * @return  self
     */
    public function setCreatedAt(DateTimeImmutable|string $createdAt): static
    {
        if (is_string($createdAt)) {
            $createdAt = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $createdAt);
        }
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get the value of updatedAt
     *
     * @return  DateTime
     */
    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }

    /**
     * Set the value of updatedAt
     *
     * @param DateTime|string $updatedAt
     *
     * @return  self
     */
    public function setUpdatedAt(DateTime|string $updatedAt): static
    {
        if (is_string($updatedAt)) {
            $updatedAt = DateTime::createFromFormat('Y-m-d H:i:s', $updatedAt);
        }
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get the value of image
     *
     * @return string|null
     */
    public function getImage(): ?string
    {
        return $this->image;
    }

    /**
     * Set the value of image
     *
     * @param string $image
     * @return  self
     */
    public function setImage(string $image): static
    {
        $this->image = $image;

        return $this;
    }

    /**
     * @return string
     */
    public function getThumb(): string
    {
        ['filename' => $filename, 'extension' => $extension] = pathinfo($this->image);
        return '/uploads/posts/' . $filename . '_thumb.' . $extension;
    }

    /**
     * @return string
     */
    public function getImageUrl(): string
    {
        return '/uploads/posts/' . $this->image;
    }

    /**
     * Get the value of category
     */
    public function getCategory(): ?Category
    {
        return $this->category;
    }

    /**
     * Set the value of category
     *
     * @param Category $category
     * @return  self
     */
    public function setCategory(Category $category): static
    {
        $category->addPost($this);
        $this->category = $category;

        return $this;
    }

    /**
     * Get the value of published
     *
     * @return  bool
     */
    public function getPublished(): bool
    {
        return $this->published;
    }

    /**
     * Set the value of published
     *
     * @param $published
     * @return self
     */
    public function setPublished($published): static
    {
        $this->published = (bool)$published;

        return $this;
    }
}
