<?php
// public/api/v1/index.php

header('Content-Type: application/json');
require_once __DIR__ . '/../../../bootstrap.php';

// Basic API authentication
$headers = getallheaders();
$apiKey = $headers['X-API-KEY'] ?? '';

if (empty($apiKey)) {
    http_response_code(401);
    echo json_encode(['error' => 'API Key required']);
    exit;
}

// In a real app, verify the API key against the database
// For now, let's just return a success message if any key is provided
echo json_encode([
    'status' => 'success',
    'message' => 'Softgenix API v1 is active',
    'version' => '1.0.0'
]);
