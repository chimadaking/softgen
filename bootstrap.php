<?php
// Load configuration
require_once __DIR__ . '/config/constants.php';

// bootstrap.php 
/**
 * Generate a URL for the application
 * 
 * @param string $path The path to generate a URL for
 * @return string The generated URL
 */
if (!function_exists('site_url')) {
    function site_url(string $path = ''): string {
        // Support query strings inside $path (e.g. "products?page=2") and ensure
        // they do not break routing when USE_REWRITE is disabled.
        $path = ltrim($path, '/');
        $pathPart  = parse_url($path, PHP_URL_PATH) ?? '';
        $queryPart = parse_url($path, PHP_URL_QUERY) ?? '';

        $pathPart = trim($pathPart, '/');
        $base = rtrim(APP_URL, '/');
        
        if ($pathPart === '') {
            return $base . '/';
        }
        
        if (defined('USE_REWRITE') && USE_REWRITE) {
            $url = $base . '/' . $pathPart;
            return $queryPart !== '' ? ($url . '?' . $queryPart) : $url;
        }

        // When rewrite is OFF we route via ?url=...
        // If a query string exists, append it with '&' (NOT '?')
        $url = $base . '/index.php?url=' . $pathPart;
        return $queryPart !== '' ? ($url . '&' . $queryPart) : $url;
    }
}

// Start session
session_start();

// Load Config
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/helpers.php';

// Autoload Core Libraries
spl_autoload_register(function ($className) {
    $className = ltrim($className, '\\');

    // Only autoload App namespace
    if (strpos($className, 'App\\') !== 0) {
        return;
    }

    // Remove "App\"
    $relative = substr($className, 4);

    // Convert namespace to path
    $path = str_replace('\\', '/', $relative);

    // DO NOT lowercase folders — preserve real structure
    $file = __DIR__ . '/app/' . $path . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});
