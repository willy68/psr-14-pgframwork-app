<?php

namespace App\Blog\Entity;

use DateTime;
use Exception;

class Post
{
    public int $id;

    public string $name;

    public string $slug;

    public string $content;

    public DateTime $createdAt;

    public DateTime $updatedAt;

    public string $image;

    /**
     * @param DateTime|string $datetime
     * @return void
     * @throws Exception
     */
    public function setCreatedAt(DateTime|string $datetime): void
    {
        if (is_string($datetime)) {
            $this->createdAt = new DateTime($datetime);
        }
    }

    /**
     * @param DateTime|string $datetime
     * @return void
     * @throws Exception
     */
    public function setUpdatedAt(DateTime|string $datetime): void
    {
        if (is_string($datetime)) {
            $this->updatedAt = new DateTime($datetime);
        } else {
            $this->updatedAt = $datetime;
        }
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
}
