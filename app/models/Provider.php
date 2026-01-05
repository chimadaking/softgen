<?php
namespace App\Models;

class Provider extends BaseModel {

    public function getAllProviders(): array {
        return $this->fetchAll("SELECT * FROM providers ORDER BY name ASC");
    }

    public function getActiveProviders(): array {
        return $this->fetchAll("SELECT * FROM providers WHERE status = 'active' ORDER BY name ASC");
    }

    public function getProviderById($id): mixed {
        return $this->fetch("SELECT * FROM providers WHERE id = ?", [$id]);
    }

    public function getProviderBySlug($slug): mixed {
        return $this->fetch("SELECT * FROM providers WHERE slug = ?", [$slug]);
    }

    public function createProvider($data): bool {
        $slug = $this->generateSlug($data['name']);

        $sql = "INSERT INTO providers
                (name, slug, description, base_url_template, documentation_url, service_schema, status)
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        return (bool)$this->exec($sql, [
            $data['name'],
            $slug,
            $data['description'] ?? null,
            $data['base_url_template'] ?? null,
            $data['documentation_url'] ?? null,
            $data['service_schema'] ?? null,
            $data['status'] ?? 'active'
        ]);
    }

    public function updateProvider($id, $data): bool {
        $sql = "UPDATE providers
                SET name = ?,
                    description = ?,
                    base_url_template = ?,
                    documentation_url = ?,
                    service_schema = ?,
                    status = ?,
                    updated_at = NOW()
                WHERE id = ?";

        return (bool)$this->exec($sql, [
            $data['name'],
            $data['description'] ?? null,
            $data['base_url_template'] ?? null,
            $data['documentation_url'] ?? null,
            $data['service_schema'] ?? null,
            $data['status'] ?? 'active',
            $id
        ]);
    }

    public function deleteProvider($id): bool {
        $sql = "DELETE FROM providers WHERE id = ?";
        return (bool)$this->exec($sql, [$id]);
    }

    private function generateSlug($name): string {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
        return $slug;
    }
}
