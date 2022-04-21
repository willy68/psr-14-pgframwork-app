<?php

use PgFramework\Response\ResponseSender;

$basePath = dirname(__DIR__);

require $basePath . '/vendor/autoload.php';

/** @var \PgFramework\App $app */
$app = require $basePath . '/app/Bootstrap/Bootstrap.php';

if (php_sapi_name() !== 'cli') {
    $response = $app->run();
    ResponseSender::send($response);
}
