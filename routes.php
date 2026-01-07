<?php

return [

    // Home
    '' => ['HomeController', 'index'],

    // Auth
    'auth/login' => ['AuthController', 'login'],
    'auth/register' => ['AuthController', 'register'],
    'auth/logout' => ['AuthController', 'logout'],
    'auth/forgot-password' => ['AuthController', 'forgotPassword'],
    'auth/reset-password/{token}' => ['AuthController', 'resetPassword'],

    // User Dashboard
    'dashboard' => ['DashboardController', 'index'],

    // Loyalty Program
    'loyalty' => ['LoyaltyController', 'index'],
    'loyalty/redeem' => ['LoyaltyController', 'redeem'],
    '/api/loyalty/stats' => ['LoyaltyController', 'getStats'],

    // Admin Dashboard
    'admin' => ['AdminController', 'index'],

    // Admin Auth (optional explicit routes)
    'admin/login' => ['AdminController', 'login'],
    'admin/register' => ['AdminController', 'register'],

    // Loyalty Management (admin)
    'admin/loyalty' => ['AdminController', 'loyalty'],
    'admin/loyalty/{id}' => ['AdminController', 'loyaltyDetail'],
    'admin/loyalty/settings' => ['AdminController', 'loyaltySettings'],

    // Roles / Users (admin)
    'admin/users' => ['AdminController', 'users'],
    'admin/users/create' => ['AdminController', 'userCreate'],
    'admin/users/edit/{id}' => ['AdminController', 'userEdit'],
    'admin/users/delete/{id}' => ['AdminController', 'userDelete'],
    'admin/users/status/{id}' => ['AdminController', 'userStatus'],
    'admin/users/roles/{id}' => ['AdminController', 'userRoles'],

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
