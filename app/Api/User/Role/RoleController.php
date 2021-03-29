<?php

namespace App\Api\User\Role;

use App\Models\Role;
use App\Api\AbstractApiController;

class RoleController extends AbstractApiController
{

    /**
     * Model class
     *
     * @var string
     */
    protected $model = Role::class;

    /**
     * Default to 'entreprise_id'
     * @var string
     */
    protected $foreignKey = '';
}
