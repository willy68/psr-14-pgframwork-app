<?php

namespace App\Blog\Entity;

class Post
{

    public $id;

    public $name;

    public $slug;

    public $content;

    public $createdAt;

    public $updatedAt;

    public $image;

    /**
     * Undocumented function
     *
     * @param [type] $datetime
     * @return void
     */
    public function setCreatedAt($datetime)
    {
        if (is_string($datetime)) {
            $this->createdAt = new \DateTime($datetime);
        }
    }

    /**
     * Undocumented function
     *
     * @param [type] $datetime
     * @return void
     */
    public function setUpdatedAt($datetime)
    {
        if (is_string($datetime)) {
            $this->updatedAt = new \DateTime($datetime);
        }
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public function getThumb()
    {
        ['filename' => $filename, 'extension' => $extension] = pathinfo($this->image);
        return '/uploads/posts/' . $filename . '_thumb.' . $extension;
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public function getImageUrl()
    {
        return '/uploads/posts/' . $this->image;
    }
}
