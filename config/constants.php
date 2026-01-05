<?php
// config/constants.php

/*
|--------------------------------------------------------------------------
| BASE_DIR
|--------------------------------------------------------------------------
| URL path where the app lives (NO trailing slash)
| Example: /softgenix/public
*/
define('BASE_DIR', rtrim('/softgenix/public', '/'));

/*
|--------------------------------------------------------------------------
| BASE_PATH
|--------------------------------------------------------------------------
| Filesystem path (DO NOT CHANGE)
*/
define('BASE_PATH', dirname(__DIR__));

/*
|--------------------------------------------------------------------------
| APP_URL
|--------------------------------------------------------------------------
| Full base URL to the application
*/
define('APP_URL', 'http://localhost' . BASE_DIR);

/*
|--------------------------------------------------------------------------
| USE_REWRITE - Add this section
|--------------------------------------------------------------------------
| Set to true if Apache mod_rewrite/.htaccess is working
| Set to false to fallback to index.php?url=... format
*/
define('USE_REWRITE', false);

/*
|--------------------------------------------------------------------------
| Backward compatibility
|--------------------------------------------------------------------------
*/
define('URL_ROOT', APP_URL);
define('APP_ROOT', BASE_PATH);

/*
|--------------------------------------------------------------------------
| App metadata
|--------------------------------------------------------------------------
*/
define('SITE_NAME', 'Softgenix');

/*
|--------------------------------------------------------------------------
| Role IDs (based on seed)
|--------------------------------------------------------------------------
*/
define('ROLE_ADMIN', 1);
define('ROLE_USER', 2);
define('ROLE_AFFILIATE', 3);