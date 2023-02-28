<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CategoryRepository")
 * @ORM\Table(name="categories")
 */
#[Entity(repositoryClass: CategoryRepository::class)]
#[Table(name: 'categories')]
class Category
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
    public string $name;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    #[Column(type: TYPES::STRING)]
    public string $slug;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Post", mappedBy="category")
     * @var ArrayCollection<Post> An ArrayCollection of Post objects.
     */
    #[OneToMany(mappedBy: 'category', targetEntity: Post::class)]
    public ArrayCollection $posts;

    public function __construct()
    {
        $this->posts = new ArrayCollection();
    }


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

    public function addPost(Post $post): void
    {
        $this->posts[] = $post;
    }
}
