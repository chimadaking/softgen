<?php
/**
 * Softgenix Application - Public Entry Point
 * 
 * This file handles all URL routing and dispatches requests to the appropriate
 * controller and method. Routes are defined in routes.php.
 * 
 * The BASE_DIR constant determines the URL path prefix for the application.
 * Change BASE_DIR in config/constants.php to move the application to a different location.
 */

// Enable full error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Load bootstrap file
require_once __DIR__ . '/../bootstrap.php';

// Add this temporary test right after: require_once __DIR__ . '/../bootstrap.php';
error_log("=== CONSTANTS CHECK ===");
error_log("BASE_DIR: " . BASE_DIR);
error_log("APP_URL: " . APP_URL);
error_log("USE_REWRITE: " . (USE_REWRITE ? 'true' : 'false'));
error_log("site_url('auth/login'): " . site_url('auth/login'));
error_log("site_url('dashboard'): " . site_url('dashboard'));
error_log("=== END CHECK ===");

// Load routes configuration
$routes = require __DIR__ . '/../routes.php';
if (!is_array($routes)) {
    $routes = [];
}

/**
 * Get the clean URL path from the request using BASE_DIR
 * This function intelligently strips the BASE_DIR prefix from the request URI
 */
function getRequestPath(): string {
    // First check if 'url' parameter is set (from .htaccess rewrite)
    if (!empty($_GET['url'])) {
        // Some links/redirects may (incorrectly) include query string inside the url param,
        // e.g. index.php?url=product?page=2. Strip it and also recover the query into $_GET.
        $raw = (string)$_GET['url'];
        $path = parse_url($raw, PHP_URL_PATH) ?? $raw;

        // Recover query string parameters if they were embedded inside url=...
        $embeddedQuery = parse_url($raw, PHP_URL_QUERY);
        if (!empty($embeddedQuery) && is_string($embeddedQuery)) {
            $extra = [];
            parse_str($embeddedQuery, $extra);
            foreach ($extra as $k => $v) {
                if (!isset($_GET[$k])) {
                    $_GET[$k] = $v;
                }
            }
        }

        return trim((string)$path, '/');
    }
    
    // Get REQUEST_URI from server
    $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
    
    // Get BASE_DIR - handle both with and without leading slash
    $basePath = BASE_DIR;
    
    // Ensure BASE_DIR has a leading slash for consistent stripping
    if (!empty($basePath) && $basePath[0] !== '/') {
        $basePath = '/' . $basePath;
    }
    
    // Debug: Log what we're working with (temporary)
    // error_log("REQUEST_URI: " . $requestUri);
    // error_log("BASE_DIR: " . BASE_DIR);
    // error_log("basePath: " . $basePath);
    
    $path = $requestUri;
    
    // Remove query string first
    $path = explode('?', $path)[0];
    
    // Remove BASE_DIR prefix if present
    if (!empty($basePath) && strpos($path, $basePath) === 0) {
        $path = substr($path, strlen($basePath));
    }
    
    // Remove /index.php from path if present
    if (strpos($path, '/index.php') === 0) {
        $path = substr($path, strlen('/index.php'));
    }
    
    // Remove leading and trailing slashes
    $path = trim($path, '/');
    
    // Debug output (temporary)
    // error_log("Final path: " . ($path ?: 'empty'));
    
    return $path;
    }
/**
 * Resolve URL path to controller, method and parameters
 */
function resolveRoute(string $path, array $routes): array {
    // Empty path defaults to HomeController::index
    if (empty($path)) {
        return ['HomeController', 'index', []];
    }
    
    $pathSegments = explode('/', $path);
    
    // Try to match against defined routes
    foreach ($routes as $pattern => $handler) {
        // Skip empty pattern (already handled)
        if (empty($pattern)) {
            continue;
        }
        
        $patternSegments = explode('/', $pattern);
        
        // Segment count must match
        if (count($patternSegments) !== count($pathSegments)) {
            continue;
        }
        
        $params = [];
        $matched = true;
        
        foreach ($patternSegments as $i => $segment) {
            // Check if this is a parameter placeholder like {id}
            if (preg_match('/^\{([a-zA-Z_][a-zA-Z0-9_]*)\}$/', $segment)) {
                $params[] = $pathSegments[$i];
                continue;
            }
            
            // Exact match required for non-parameter segments
            if ($segment !== $pathSegments[$i]) {
                $matched = false;
                break;
            }
        }
        
        if ($matched) {
            $controller = $handler[0] ?? 'HomeController';
            $method = $handler[1] ?? 'index';
            return [$controller, $method, $params];
        }
    }
    
    // Default routing: /controller/method/param1/param2...
    $controller = ucfirst($pathSegments[0]) . 'Controller';
    $method = $pathSegments[1] ?? 'index';
    $params = array_slice($pathSegments, 2);
    
    return [$controller, $method, $params];
}

/**
 * Dispatch the request to the appropriate controller
 */
function dispatch(string $path, array $routes): void {
    [$controllerClass, $methodName, $params] = resolveRoute($path, $routes);
    
    $controllerNamespace = 'App\\Controllers\\' . $controllerClass;
    
    // Check if controller class exists
    if (!class_exists($controllerNamespace)) {
        show404($path, $controllerNamespace, $methodName, 'Controller class not found');
        return;
    }
    
    // Instantiate controller
    $controller = new $controllerNamespace();
    
    // Check if method exists
    if (!method_exists($controller, $methodName) || strpos($methodName, '__') === 0) {
        show404($path, $controllerNamespace, $methodName, 'Method not found');
        return;
    }
    
    // Call the controller method with parameters
    call_user_func_array([$controller, $methodName], $params);
}

/**
 * Display 404 error page
 */
function show404(string $path, string $controller, string $method, string $reason): void {
    http_response_code(404);
    
    // For development, show detailed error
    echo '<!DOCTYPE html>
<html>
<head>
    <title>404 - Page Not Found</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card shadow-sm">
                    <div class="card-header bg-danger text-white">
                        <h4 class="mb-0">404 - Page Not Found</h4>
                    </div>
                    <div class="card-body">
                        <p class="lead">The requested page was not found.</p>
                        <hr>
                        <p><strong>Requested Path:</strong> <code>' . htmlspecialchars($path) . '</code></p>
                        <p><strong>Controller:</strong> <code>' . htmlspecialchars($controller) . '</code></p>
                        <p><strong>Method:</strong> <code>' . htmlspecialchars($method) . '</code></p>
                        <p><strong>Reason:</strong> ' . htmlspecialchars($reason) . '</p>
                        <hr>
                        <a href="' . BASE_DIR . '/" class="btn btn-primary">Go to Homepage</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>';
    
    // Also include the actual 404 view
    $errorView = BASE_PATH . '/app/views/errors/404.php';
    if (file_exists($errorView)) {
        include $errorView;
    }
}

// Get the request path
$path = getRequestPath();

// Dispatch to appropriate controller
dispatch($path, $routes);
