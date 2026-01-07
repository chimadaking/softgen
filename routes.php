<?php

return [

    // Home
    '' => ['HomeController', 'index'],

    // Auth
    'auth/login' => ['AuthController', 'login'],
    'auth/register' => ['AuthController', 'register'],
    'auth/logout' => ['AuthController', 'logout'],

    // User Dashboard
    'dashboard' => ['DashboardController', 'index'],

    // Admin Dashboard
    'admin' => ['AdminController', 'index'],

    // Admin Auth (optional explicit routes)
    'admin/login' => ['AdminController', 'login'],
    'admin/register' => ['AdminController', 'register'],

    // Roles / Users (admin)
    'admin/users' => ['UserController', 'index'],
    'admin/users/create' => ['UserController', 'create'],
    'admin/users/edit/{id}' => ['UserController', 'edit'],
    'admin/users/delete/{id}' => ['UserController', 'delete'],

    // Providers (admin)
    'admin/providers' => ['ProviderController', 'index'],
    'admin/providers/create' => ['ProviderController', 'create'],
    'admin/providers/edit/{id}' => ['ProviderController', 'edit'],
    'admin/providers/delete/{id}' => ['ProviderController', 'delete'],

    // API Instances (admin)
    'api' => ['APIController', 'index'],
    'api/create' => ['APIController', 'create'],
    'api/edit/{id}' => ['APIController', 'edit'],
    'api/delete/{id}' => ['APIController', 'delete'],
    'api/test' => ['APIController', 'test'],
    'api/test/{id}' => ['APIController', 'test'],
    'api/logs' => ['APIController', 'logs'],

    // API Services (manage)
    'api/services/{id}' => ['APIController', 'services'],
    'api/updateService' => ['APIController', 'updateService'],
    'api/bulkUpdateServices' => ['APIController', 'bulkUpdateServices'],
    'api/syncServices/{id}' => ['APIController', 'syncServices'],
    'api/applyBulkMarkup' => ['APIController', 'applyBulkMarkup'],
    'api/instanceSettings/{id}' => ['APIController', 'instanceSettings'],

    // Markup Management
    'api/applyBulkMarkup' => ['APIController', 'applyBulkMarkup'],
    'api/instanceSettings' => ['APIController', 'instanceSettings'],

];
