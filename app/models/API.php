<?php
namespace App\Models;

class API extends BaseModel {
    
    public function getProviders() {
        return $this->fetchAll("SELECT * FROM api_providers ORDER BY created_at DESC");
    }

    public function getProviderById($id) {
        return $this->fetch("SELECT * FROM api_providers WHERE id = ?", [$id]);
    }

    public function addProvider($data) {
        $sql = "INSERT INTO api_providers (provider, name, base_url, api_key, status, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())";
        return $this->query($sql, [
            $data['provider'],
            $data['name'],
            $data['base_url'],
            $data['api_key'],
            $data['status']
        ]);
    }

    public function updateProvider($id, $data) {
        $sql = "UPDATE api_providers 
                SET provider = ?, name = ?, base_url = ?, api_key = ?, status = ? 
                WHERE id = ?";
        return $this->query($sql, [
            $data['provider'],
            $data['name'],
            $data['base_url'],
            $data['api_key'],
            $data['status'],
            $id
        ]);
    }

    public function deleteProvider($id) {
        $sql = "DELETE FROM api_providers WHERE id = ?";
        return $this->query($sql, [$id]);
    }

    public function logRequest($userId, $providerId, $endpoint, $method, $requestData, $responseCode, $responseData) {
        $sql = "INSERT INTO api_logs (user_id, provider_id, endpoint, request_method, request_data, response_code, response_data, ip_address, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        return $this->query($sql, [
            $userId,
            $providerId,
            $endpoint,
            $method,
            json_encode($requestData),
            $responseCode,
            json_encode($responseData),
            $_SERVER['REMOTE_ADDR']
        ]);
    }

    public function getLogs() {
        return $this->fetchAll("SELECT * FROM api_logs ORDER BY created_at DESC LIMIT 100");
    }
}