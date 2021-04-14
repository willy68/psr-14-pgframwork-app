<?php

namespace PgFramework\EventListener;

interface CsrfListenerInterface
{
    public function getFormKey(): string;

}
