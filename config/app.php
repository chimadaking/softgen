<?php
// config/app.php

return [
    'name' => SITE_NAME,
    'url' => APP_URL,
    'base_dir' => BASE_DIR,
    'base_path' => BASE_PATH,
    'debug' => getenv('APP_DEBUG') !== false ? filter_var(getenv('APP_DEBUG'), FILTER_VALIDATE_BOOLEAN) : true,
    'timezone' => 'UTC',
    'locale' => 'en',
];
