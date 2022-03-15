<?php

namespace App\Entity;

use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\Mapping\Id;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use App\Repository\PostRepository;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\GeneratedValue;

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
    #[GeneratedValue()]
    #[Column(type: Types::INTEGER)]
    protected $id;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    #[Column(type: TYPES::STRING)]
    protected $name;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    #[Column(type: TYPES::STRING)]
    protected $slug;

    /**
     * @ORM\Column(type="text")
     * @var string
     */
    #[Column(type: TYPES::TEXT)]
    protected $content;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @var DateTimeImmutable
     */
    #[Column(type: TYPES::DATETIME_IMMUTABLE)]
    protected $created_at;

    /**
     * @ORM\Column(type="datetime")
     * @var DateTime
     */
    #[Column(type: TYPES::DATETIME_MUTABLE)]
    protected $updated_at;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    #[Column(type: TYPES::STRING)]
    protected $image;

    /**
     * @ORM\Column(type="boolean")
     * @var bool
     */
    #[Column(type: TYPES::BOOLEAN)]
    protected $published;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Category", inversedBy="addPost")
     */
    #[ManyToOne(targetEntity: Category::class, inversedBy: "addPost")]
    protected $category;

    /**
     * Get the value of id
     *
     * @return  int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the value of id
     *
     * @param  int  $id
     *
     * @return  self
     */
    public function setId(int $id)
    {
        $this->id = $id;

        return $this;
    }


    /**
     * Get the value of name
     *
     * @return  string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the value of name
     *
     * @param  string  $name
     *
     * @return  self
     */
    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the value of slug
     *
     * @return  string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set the value of slug
     *
     * @param  string  $slug
     *
     * @return  self
     */
    public function setSlug(string $slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get the value of content
     *
     * @return  string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set the value of content
     *
     * @param  string  $content
     *
     * @return  self
     */
    public function setContent(string $content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get the value of createdAt
     *
     * @return  DateTime
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * Set the value of createdAt
     *
     * @param  DateTime  $createdAt
     *
     * @return  self
     */
    public function setCreatedAt(DateTime $createdAt)
    {
        $this->created_at = $createdAt;

        return $this;
    }

    /**
     * Get the value of updatedAt
     *
     * @return  DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    /**
     * Set the value of updatedAt
     *
     * @param  DateTime  $updatedAt
     *
     * @return  self
     */
    public function setUpdatedAt(DateTime $updatedAt)
    {
        $this->updated_at = $updatedAt;

        return $this;
    }

    /**
     * Get the value of image
     *
     * @return  string
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Set the value of image
     *
     * @param  string  $image
     *
     * @return  self
     */
    public function setImage(string $image)
    {
        $this->image = $image;

        return $this;
    }

    /**
     *
     * @return void
     */
    public function getThumb()
    {
        ['filename' => $filename, 'extension' => $extension] = pathinfo($this->image);
        return '/uploads/posts/' . $filename . '_thumb.' . $extension;
    }

    /**
     *
     * @return void
     */
    public function getImageUrl()
    {
        return '/uploads/posts/' . $this->image;
    }

    /**
     * Get the value of category
     */
    public function getCategory(): Category
    {
        return $this->category;
    }

    /**
     * Set the value of category
     *
     * @return  self
     */
    public function setCategory(Category $category)
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
    public function getPublished()
    {
        return $this->published;
    }

    /**
     * Set the value of published
     *
     * @param  bool  $published
     *
     * @return  self
     */
    public function setPublished(bool $published)
    {
        $this->published = $published;

        return $this;
    }
}
