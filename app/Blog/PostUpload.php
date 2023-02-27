<?php

namespace App\Blog;

use PgFramework\File\Upload;

class PostUpload extends Upload
{
    protected string $path = 'uploads/posts';

    protected array $formats = [
        'thumb' => [120, 60]
    ];
}
