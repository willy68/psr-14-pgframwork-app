<?php

namespace App\Api\Client\AdresseType;

use App\Models\AdresseType;
use App\Api\AbstractApiController;

class AdresseTypeController extends AbstractApiController
{

    /**
     * Model class
     *
     * @var string
     */
    protected $model = AdresseType::class;

    /**
     * Default to 'entreprise_id'
     * @var string
     */
    protected $foreignKey = '';
}
