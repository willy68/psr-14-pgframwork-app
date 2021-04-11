<?php

namespace PgFramework\EventListener;

interface CsrfListenerInterface
{
    public function getFormKey(): string;

    public function getToken(): string;
}
