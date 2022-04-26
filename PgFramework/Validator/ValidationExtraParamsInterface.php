<?php

namespace PgFramework\Validator;

interface ValidationExtraParamsInterface
{
    /**
     * Set Request Parsed Body Params
     *
     * @param array $params
     * @return void
     */
    public function setBodyParams(array $params): void;
}
