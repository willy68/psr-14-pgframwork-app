<?php

use PgFramework\Environnement\Environnement;

return [
    'mailer.dsn' => Environnement::getEnv('MAILER_DSN', 'smtp://localhost:1025')
];
