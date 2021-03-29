<?php

use function Http\Response\send;

$basePath = dirname(__DIR__);

require $basePath . '/vendor/autoload.php';

/** @var \Framework\App $app */
$app = require $basePath . '/app/Bootstrap/Bootstrap.php';

if (php_sapi_name() !== 'cli') {
    $response = $app->run();
    send($response);
}
