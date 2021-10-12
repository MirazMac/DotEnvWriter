<?php

use MirazMac\DotEnv\Writer;

require_once '../vendor/autoload.php';

$writer = new Writer(__DIR__ . '/' . '.env');

$writer
->set('APP_NAME', 'My App')
->set('APP_URL', 'https://laravel.com')
// usage of an existing variable
->set('APP_DIR', '${BASE_DIR}/app')
// Force quote a single word value
->set('APP_BUCKET', 's3-bucket', true)
// Write the values to file
->write();
