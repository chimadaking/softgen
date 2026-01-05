<?php
namespace App\Models;

class APIInstance extends BaseModel
{
    /* =========================
       FETCH INSTANCES
    ========================= */

    public function getAllInstances()
    {
        return $this->fetchAll(
            "SELECT ai.*, p.name AS provider_name
             FROM api_instances ai
             JOIN providers p ON p.id = ai.provider_id
             ORDER BY ai.created_at DESC"
        );
    }

    public function getActiveInstances()
    {
        return $this->fetchAll(
            "SELECT * FROM api_instances
             WHERE status = 'active'
             ORDER BY name ASC"
        );
    }

    public function getInstanceById(int $id)
    {
        return $this->fetch(
            "SELECT ai.*, p.name AS provider_name
             FROM api_instances ai
             JOIN providers p ON p.id = ai.provider_id
             WHERE ai.id = ?",
            [$id]
        );
    }

    /* =========================
       CREATE / UPDATE / DELETE
    ========================= */

    public function createInstance(array $data)
    {
        $sql = "INSERT INTO api_instances
                (provider_id, name, base_url, api_key, default_country, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())";

        return $this->query($sql, [
            $data['provider_id'],
            $data['name'],
            rtrim($data['base_url'], '/'),
            $data['api_key'],
            $data['default_country'] ?? null,
            $data['status'] ?? 'active'
        ]);
    }

    public function updateInstance(int $id, array $data)
    {
        $sql = "UPDATE api_instances
                SET provider_id      = ?,
                    name             = ?,
                    base_url         = ?,
                    api_key          = ?,
                    default_country  = ?,
                    status           = ?,
                    updated_at       = NOW()
                WHERE id = ?";

        return $this->query($sql, [
            $data['provider_id'],
            $data['name'],
            rtrim($data['base_url'], '/'),
            $data['api_key'],
            $data['default_country'] ?? null,
            $data['status'] ?? 'active',
            $id
        ]);
    }

    public function deleteInstance(int $id)
    {
        // Safety: do not delete if services exist
        // NOTE: if your BaseModel does not have fetchColumn(), re-upload it and I’ll fix it.
        if (method_exists($this, 'fetchColumn')) {
            $count = $this->fetchColumn(
                "SELECT COUNT(*) FROM api_services WHERE api_instance_id = ?",
                [$id]
            );

            if ($count > 0) {
                return false;
            }
        }

        return $this->query(
            "DELETE FROM api_instances WHERE id = ?",
            [$id]
        );
    }

    /* =========================
       LOGGING
    ========================= */

    public function logRequest(
        int $userId,
        int $instanceId,
        string $endpoint,
        string $method,
        array $requestData,
        int $responseCode,
        array $responseData
    ) {
        $sql = "INSERT INTO api_logs
                (user_id, instance_id, endpoint, request_method,
                 request_data, response_code, response_data, ip_address, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";

        return $this->query($sql, [
            $userId,
            $instanceId,
            $endpoint,
            $method,
            json_encode($requestData),
            $responseCode,
            json_encode($responseData),
            $_SERVER['REMOTE_ADDR'] ?? ''
        ]);
    }

    public function getLogs(int $limit = 100)
    {
        return $this->fetchAll(
            "SELECT l.*, i.name AS instance_name
             FROM api_logs l
             LEFT JOIN api_instances i ON i.id = l.instance_id
             ORDER BY l.created_at DESC
             LIMIT {$limit}"
        );
    }
}
