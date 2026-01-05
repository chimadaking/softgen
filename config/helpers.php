<?php
// config/helpers.php

function url(string $path = ''): string {
    return BASE_DIR . '/' . ltrim($path, '/');
}

function redirect($page) {
    header('Location: ' . site_url($page));
    exit;
}

function flash($name = '', $message = '', $class = 'alert alert-success') {
    if (!empty($name)) {
        if (!empty($message) && empty($_SESSION[$name])) {
            if (!empty($_SESSION[$name])) {
                unset($_SESSION[$name]);
            }
            if (!empty($_SESSION[$name . '_class'])) {
                unset($_SESSION[$name . '_class']);
            }
            $_SESSION[$name] = $message;
            $_SESSION[$name . '_class'] = $class;
        } elseif (empty($message) && !empty($_SESSION[$name])) {
            $class = !empty($_SESSION[$name . '_class']) ? $_SESSION[$name . '_class'] : '';
            echo '<div class="' . $class . '" id="msg-flash">' . $_SESSION[$name] . '</div>';
            unset($_SESSION[$name]);
            unset($_SESSION[$name . '_class']);
        }
    }
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getUser() {
    if (!isLoggedIn()) {
        return null;
    }
    return [
        'id' => $_SESSION['user_id'] ?? null,
        'email' => $_SESSION['user_email'] ?? null,
        'name' => $_SESSION['user_name'] ?? null,
        'roles' => $_SESSION['user_roles'] ?? []
    ];
}

function getUserName() {
    return $_SESSION['user_name'] ?? '';
}

function getUserRoles() {
    return $_SESSION['user_roles'] ?? [];
}

function redirectIfNotAuthenticated($redirectTo = 'auth/login') {
    if (!isLoggedIn()) {
        redirect($redirectTo);
    }
}

/**
 * Roles helpers
 */
function isAdmin() {
    return isLoggedIn() && in_array('admin', $_SESSION['user_roles'] ?? []);
}

function isUser() {
    return isLoggedIn() && in_array('user', $_SESSION['user_roles'] ?? []);
}

function isAffiliate() {
    return isLoggedIn() && in_array('affiliate', $_SESSION['user_roles'] ?? []);
}

function hasRole($role) {
    return isLoggedIn() && in_array($role, $_SESSION['user_roles'] ?? []);
}

function hasAnyRole($roles) {
    if (!isLoggedIn()) return false;
    foreach ((array)$roles as $role) {
        if (in_array($role, $_SESSION['user_roles'] ?? [])) {
            return true;
        }
    }
    return false;
}

function hasAllRoles($roles) {
    if (!isLoggedIn()) return false;
    foreach ((array)$roles as $role) {
        if (!in_array($role, $_SESSION['user_roles'] ?? [])) {
            return false;
        }
    }
    return true;
}

function requireAdmin($redirectTo = 'dashboard') {
    if (!isAdmin()) {
        redirect($redirectTo);
    }
}

function requireAnyRole($roles, $redirectTo = 'dashboard') {
    if (!hasAnyRole((array)$roles)) {
        redirect($redirectTo);
    }
}

/**
 * Safe HTML escape helper.
 * ✅ Fixes PHP 8.1+ deprecation: htmlspecialchars(null)
 */
function h($text): string {
    if ($text === null) {
        $text = '';
    } elseif (is_bool($text)) {
        $text = $text ? '1' : '0';
    } elseif (is_array($text) || is_object($text)) {
        // Avoid warnings: convert non-scalar to JSON for debugging-safe output
        $text = json_encode($text, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($text === false) {
            $text = '';
        }
    } else {
        $text = (string)$text;
    }

    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

/**
 * CSRF helpers
 */
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * ✅ LIVE USD → NGN RATE (cached)
 */
function getUsdToNgnRate(): float
{
    // Cache for 30 minutes
    $cacheSeconds = 1800;

    if (
        isset($_SESSION['usd_ngn_rate'], $_SESSION['usd_ngn_rate_time']) &&
        (time() - $_SESSION['usd_ngn_rate_time']) < $cacheSeconds
    ) {
        return (float) $_SESSION['usd_ngn_rate'];
    }

    $rate = 0;

    // Free & reliable API
    $url = 'https://api.exchangerate.host/latest?base=USD&symbols=NGN';
    $response = @file_get_contents($url);

    if ($response !== false) {
        $data = json_decode($response, true);
        if (isset($data['rates']['NGN'])) {
            $rate = (float) $data['rates']['NGN'];
        }
    }

    // Fallback if API fails
    if ($rate <= 0) {
        $rate = $_SESSION['usd_ngn_rate'] ?? 1500;
    }

    $_SESSION['usd_ngn_rate'] = $rate;
    $_SESSION['usd_ngn_rate_time'] = time();

    return $rate;
}
