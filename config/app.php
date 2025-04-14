<?php

use Core\Database\Env;

return [
    'name' => Env::get('APP_NAME', 'MyApp'),
    'env' => Env::get('APP_ENV', 'production'),
    'dev_mode' => Env::get('DEV_MODE', false) === 'true',
];
