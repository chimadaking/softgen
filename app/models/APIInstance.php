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
       DEFAULT MARKUP METHODS
    ========================= */

    /**
     * Get the default markup for an instance.
     *
     * @param int $id Instance ID
     * @return float Default markup value (0.00 if not set)
     */
    public function getDefaultMarkup(int $id): float
    {
        $row = $this->fetch(
            "SELECT default_markup FROM api_instances WHERE id = ?",
            [$id]
        );

        return (float)($row->default_markup ?? 0.00);
    }

    /**
     * Set the default markup for an instance.
     *
     * @param int $id Instance ID
     * @param float $markup Default markup value
     * @return bool
     */
    public function setDefaultMarkup(int $id, float $markup): bool
    {
        $id = (int)$id;
        if ($id <= 0) {
            return false;
        }

        // Normalize markup
        $markup = (float)$markup;
        if ($markup < 0) {
            $markup = 0.00;
        }
        if ($markup > 1000.00) {
            $markup = 1000.00;
        }
        $markup = round($markup, 2);

        $sql = "UPDATE api_instances SET default_markup = ?, updated_at = NOW() WHERE id = ?";
        $this->query($sql, [$markup, $id]);

        return true;
    }

    /**
     * Clear the default markup (set to 0.00).
     *
     * @param int $id Instance ID
     * @return bool
     */
    public function clearDefaultMarkup(int $id): bool
    {
        return $this->setDefaultMarkup($id, 0.00);
    }

    /* =========================
       CREATE / UPDATE / DELETE
    ========================= */

    public function createInstance(array $data)
    {
        $defaultMarkup = (float)($data['default_markup'] ?? 0.00);
        if ($defaultMarkup < 0) $defaultMarkup = 0.00;
        if ($defaultMarkup > 1000.00) $defaultMarkup = 1000.00;
        $defaultMarkup = round($defaultMarkup, 2);

        $sql = "INSERT INTO api_instances
                (provider_id, name, base_url, api_key, default_country, default_markup, status, created_at)
                (provider_id, name, base_url, api_key, default_markup, default_country, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";

        return $this->query($sql, [
            $data['provider_id'],
            $data['name'],
            rtrim($data['base_url'], '/'),
            $data['api_key'],
            (float)($data['default_markup'] ?? 0.00),
            $data['default_country'] ?? null,
            $defaultMarkup,
            $data['status'] ?? 'active'
        ]);
    }

    public function updateInstance(int $id, array $data)
    {
        $defaultMarkup = (float)($data['default_markup'] ?? 0.00);
        if ($defaultMarkup < 0) $defaultMarkup = 0.00;
        if ($defaultMarkup > 1000.00) $defaultMarkup = 1000.00;
        $defaultMarkup = round($defaultMarkup, 2);

        $sql = "UPDATE api_instances
                SET provider_id      = ?,
                    name             = ?,
                    base_url         = ?,
                    api_key          = ?,
                    default_markup   = ?,
                    default_country  = ?,
                    default_markup   = ?,
                    status           = ?,
                    updated_at       = NOW()
                WHERE id = ?";

        return $this->query($sql, [
            $data['provider_id'],
            $data['name'],
            rtrim($data['base_url'], '/'),
            $data['api_key'],
            (float)($data['default_markup'] ?? 0.00),
            $data['default_country'] ?? null,
            $defaultMarkup,
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
       MARKUP MANAGEMENT
    ========================= */

    public function getDefaultMarkup(int $id): float
    {
        $row = $this->fetch(
            "SELECT default_markup FROM api_instances WHERE id = ?",
            [$id]
        );
        return (float)($row->default_markup ?? 0.00);
    }

    public function setDefaultMarkup(int $id, float $markup): bool
    {
        $markup = max(0.0, min(1000.00, $markup));
        $markup = round($markup, 2);

        $this->query(
            "UPDATE api_instances SET default_markup = ?, updated_at = NOW() WHERE id = ?",
            [$markup, $id]
        );

        return true;
    }

    public function applyDefaultMarkupToServices(int $id): int
    {
        $instance = $this->getInstanceById($id);
        if (!$instance) {
            return 0;
        }

        $markup = (float)($instance->default_markup ?? 0.00);

        $stmt = $this->query(
            "UPDATE api_services
             SET markup = ?,
                 final_price = ROUND(api_rate * (1 + ?), 4),
                 updated_at = NOW()
             WHERE api_instance_id = ?",
            [$markup, $markup, $id]
        );

        return (int)$stmt->rowCount();
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
