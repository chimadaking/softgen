<?php
namespace App\Controllers;

class BaseController {
    public function model($model) {
        $modelClass = 'App\\Models\\' . $model;
        return new $modelClass();
    }

    public function view($view, $data = []) {
        if (file_exists(APP_ROOT . '/app/views/' . $view . '.php')) {
            require_once APP_ROOT . '/app/views/' . $view . '.php';
        } else {
            die('View does not exist: ' . $view);
        }
    }

    public function render($view, $data = []) {
        extract($data);
        $viewFile = APP_ROOT . '/app/views/' . $view . '.php';
        if (file_exists($viewFile)) {
            require_once APP_ROOT . '/app/views/layouts/header.php';
            require_once $viewFile;
            require_once APP_ROOT . '/app/views/layouts/footer.php';
        } else {
            die('View does not exist: ' . $view);
        }
    }
}