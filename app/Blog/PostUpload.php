<?php

namespace App\Blog;

use Framework\Upload;

class PostUpload extends Upload
{
    protected $path = 'uploads/posts';

    protected $formats = [
        'thumb' => [120, 60]
    ];
}
