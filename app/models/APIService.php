<?php
namespace App\Models;

class APIService extends BaseModel
{
    /**
     * Get all services for a specific API instance
     */
    public function getByInstance(int $instanceId)
    {
        return $this->fetchAll(
            "SELECT s.*, i.name AS instance_name
             FROM api_services s
             LEFT JOIN api_instances i ON i.id = s.api_instance_id
             WHERE s.api_instance_id = ?
             ORDER BY s.category ASC, s.name ASC",
            [$instanceId]
        );
    }

    /**
     * Paginated list of services for an instance, with optional search.
     * Search matches name, category, and external_service_id.
     */
    public function getByInstancePaginated(int $instanceId, string $q = '', int $limit = 50, int $offset = 0): array
    {
        $limit  = max(10, min(200, (int)$limit));
        $offset = max(0, (int)$offset);

        $sql = "
            SELECT s.*, i.name AS instance_name
            FROM api_services s
            LEFT JOIN api_instances i ON i.id = s.api_instance_id
            WHERE s.api_instance_id = ?
        ";

        $params = [$instanceId];

        $q = trim($q);
        if ($q !== '') {
            $sql .= " AND (s.name LIKE ? OR s.category LIKE ? OR s.external_service_id LIKE ?)";
            $like = '%' . $q . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        $sql .= " ORDER BY s.category ASC, s.name ASC LIMIT {$limit} OFFSET {$offset}";

        return $this->fetchAll($sql, $params);
    }

    /**
     * Count services for an instance with optional search filter.
     */
    public function countByInstanceFiltered(int $instanceId, string $q = ''): int
    {
        $sql = "SELECT COUNT(*) AS total FROM api_services WHERE api_instance_id = ?";
        $params = [$instanceId];

        $q = trim($q);
        if ($q !== '') {
            $sql .= " AND (name LIKE ? OR category LIKE ? OR external_service_id LIKE ?)";
            $like = '%' . $q . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        $row = $this->fetch($sql, $params);

        return (int)($row->total ?? 0);
    }

    /**
     * Get all visible & active services (for user-facing usage later)
     */
    public function getActiveVisible()
    {
        return $this->fetchAll(
            "SELECT *
             FROM api_services
             WHERE status = 'active'
               AND visible = 'yes'
             ORDER BY category ASC, name ASC"
        );
    }

    /**
     * Normalize decimal input from API and clamp to DB schema limits.
     * For DECIMAL(10,4): max = 999999.9999
     */
    private function normalizeDecimal($value, float $min = 0.0, float $max = 999999.9999): float
    {
        if ($value === null || $value === '' || $value === false) {
            return 0.0;
        }

        if (is_string($value)) {
            $value = trim($value);
            // Remove thousands separators/spaces
            $value = str_replace([',', ' '], ['', ''], $value);
        }

        $num = (float)$value;

        if (!is_finite($num)) {
            $num = 0.0;
        }

        if ($num < $min) {
            $num = $min;
        }

        if ($num > $max) {
            $num = $max;
        }

        // Match DECIMAL(10,4) precision
        return round($num, 4);
    }

    /**
     * Normalize integer quantities
     */
    private function normalizeInt($value, int $min = 0, int $max = 2147483647): int
    {
        if ($value === null || $value === '' || $value === false) {
            return $min;
        }

        if (is_string($value)) {
            $value = trim($value);
            $value = str_replace([',', ' '], ['', ''], $value);
        }

        $num = (int)$value;

        if ($num < $min) {
            return $min;
        }

        if ($num > $max) {
            return $max;
        }

        return $num;
    }

    /**
     * Insert or update a service safely (UPSERT)
     *
     * - Preserves admin-controlled fields
     * - Updates API-controlled fields
     * - Never auto-enables or auto-exposes services
     */
    public function upsertService(array $data): bool
    {
        $apiRate    = $this->normalizeDecimal($data['api_rate'] ?? 0, 0.0, 999999.9999);
        $finalPrice = $this->normalizeDecimal($data['final_price'] ?? $apiRate, 0.0, 999999.9999);

        $minQty = $this->normalizeInt($data['min_qty'] ?? 1, 0);
        $maxQty = $this->normalizeInt($data['max_qty'] ?? 100000, 0);

        if ($maxQty > 0 && $maxQty < $minQty) {
            $maxQty = $minQty;
        }

        $sql = "
            INSERT INTO api_services (
                api_instance_id,
                external_service_id,
                name,
                category,
                type,
                api_rate,
                final_price,
                min_qty,
                max_qty,
                extra,
                status,
                visible,
                created_at
            ) VALUES (
                :api_instance_id,
                :external_service_id,
                :name,
                :category,
                :type,
                :api_rate,
                :final_price,
                :min_qty,
                :max_qty,
                :extra,
                'inactive',
                'no',
                NOW()
            )
            ON DUPLICATE KEY UPDATE
                name        = VALUES(name),
                category    = VALUES(category),
                type        = VALUES(type),
                api_rate    = VALUES(api_rate),
                -- Keep admin-controlled markup but ensure final_price stays consistent when api_rate changes
                final_price = ROUND(VALUES(api_rate) * (1 + markup), 4),
                min_qty     = VALUES(min_qty),
                max_qty     = VALUES(max_qty),
                extra       = VALUES(extra),
                updated_at  = NOW()
        ";

        $this->query($sql, [
            ':api_instance_id'      => (int)($data['api_instance_id'] ?? 0),
            ':external_service_id'  => (string)($data['external_service_id'] ?? ''),
            ':name'                 => (string)($data['name'] ?? ''),
            ':category'             => (string)($data['category'] ?? ''),
            ':type'                 => (string)($data['type'] ?? ''),
            ':api_rate'             => $apiRate,
            ':final_price'          => $finalPrice,
            ':min_qty'              => $minQty,
            ':max_qty'              => $maxQty,
            ':extra'                => json_encode($data['extra'] ?? [], JSON_UNESCAPED_UNICODE),
        ]);

        return true;
    }

    /**
     * Update admin-controlled fields only (single row)
     *
     * Markup is stored as a fraction:
     * 0.50 = 50%
     * final_price = api_rate * (1 + markup)
     */
    public function updateAdminSettings(
        int $id,
        float $markup,
        string $status,
        string $visible
    ): bool {
        // markup is DECIMAL(10,2) and represents a fraction (e.g., 0.50 = 50%)
        $markup = $this->normalizeDecimal($markup, 0.0, 1000.00);
        $markup = round($markup, 2);

        $sql = "
            UPDATE api_services
            SET
                markup = ?,
                final_price = ROUND(api_rate * (1 + ?), 4),
                status = ?,
                visible = ?,
                updated_at = NOW()
            WHERE id = ?
        ";

        $this->query($sql, [
            $markup,
            $markup,
            $status,
            $visible,
            $id
        ]);

        return true;
    }

    /**
     * Update ONLY markup for a single service and recalculate final_price
     */
    public function updateMarkup(int $id, float $markup): bool
    {
        $markup = $this->normalizeDecimal($markup, 0.0, 1000.00);
        $markup = round($markup, 2);

        $sql = "
            UPDATE api_services
            SET
                markup = ?,
                final_price = ROUND(api_rate * (1 + ?), 4),
                updated_at = NOW()
            WHERE id = ?
        ";

        $this->query($sql, [$markup, $markup, $id]);
        return true;
    }

    /**
     * Update markup for ALL services in an instance
     */
    public function bulkUpdateMarkup(int $instanceId, float $markup): int
    {
        $markup = $this->normalizeDecimal($markup, 0.0, 1000.00);
        $markup = round($markup, 2);

        $stmt = $this->query(
            "UPDATE api_services
             SET markup = ?,
                 final_price = ROUND(api_rate * (1 + ?), 4),
                 updated_at = NOW()
             WHERE api_instance_id = ?",
            [$markup, $markup, $instanceId]
        );

        return (int)$stmt->rowCount();
    }

    /**
     * Bulk update selected services for a given instance.
     *
     * Supported bulk operations:
     * - visible: 'yes'|'no'
     * - status: 'active'|'inactive'
     * - markup: float (fraction; final_price = api_rate * (1 + markup))
     */
    public function bulkUpdateByInstance(
        int $instanceId,
        array $ids,
        ?string $status = null,
        ?string $visible = null,
        ?float $markup = null
    ): bool {
        $instanceId = (int)$instanceId;

        $cleanIds = [];
        foreach ($ids as $id) {
            $id = (int)$id;
            if ($id > 0) $cleanIds[] = $id;
        }
        $cleanIds = array_values(array_unique($cleanIds));

        if ($instanceId <= 0 || empty($cleanIds)) {
            return false;
        }

        $set = [];
        $params = [];

        if ($status !== null) {
            $status = ($status === 'active') ? 'active' : 'inactive';
            $set[] = "status = ?";
            $params[] = $status;
        }

        if ($visible !== null) {
            $visible = ($visible === 'yes') ? 'yes' : 'no';
            $set[] = "visible = ?";
            $params[] = $visible;
        }

        if ($markup !== null) {
            $m = $this->normalizeDecimal($markup, 0.0, 1000.00);
            $m = round($m, 2);
            $set[] = "markup = ?";
            $params[] = $m;

            // keep final_price consistent
            $set[] = "final_price = ROUND(api_rate * (1 + ?), 4)";
            $params[] = $m;
        }

        if (empty($set)) {
            return false;
        }

        $set[] = "updated_at = NOW()";

        $placeholders = implode(',', array_fill(0, count($cleanIds), '?'));
        $sql = "UPDATE api_services SET " . implode(', ', $set) . " WHERE api_instance_id = ? AND id IN ({$placeholders})";

        $params[] = $instanceId;
        foreach ($cleanIds as $id) {
            $params[] = $id;
        }

        $this->query($sql, $params);

        return true;
    }

    /**
     * Update api_rate from a pricing map (external_service_id => rate)
     * and keep final_price consistent with stored markup.
     */
    public function updateRatesFromPricing(int $instanceId, array $rates): int
    {
        $instanceId = (int)$instanceId;
        if ($instanceId <= 0) return 0;

        $clean = [];
        foreach ($rates as $sid => $rate) {
            $sid = trim((string)$sid);
            if ($sid === '') continue;
            if (!is_numeric($rate)) continue;
            $clean[$sid] = $this->normalizeDecimal($rate, 0.0, 999999.9999);
        }

        if (!$clean) return 0;

        $caseParts = [];
        $caseParams = [];
        foreach ($clean as $sid => $rate) {
            $caseParts[] = "WHEN ? THEN ?";
            $caseParams[] = $sid;
            $caseParams[] = $rate;
        }

        $in = implode(',', array_fill(0, count($clean), '?'));
        $inParams = array_keys($clean);

        $sql = "
            UPDATE api_services
            SET
                api_rate = CASE external_service_id
                    " . implode(' ', $caseParts) . "
                    ELSE api_rate
                END,
                final_price = ROUND(
                    (CASE external_service_id
                        " . implode(' ', $caseParts) . "
                        ELSE api_rate
                    END) * (1 + markup),
                    4
                ),
                updated_at = NOW()
            WHERE api_instance_id = ?
              AND external_service_id IN ($in)
        ";

        $params = array_merge($caseParams, $caseParams, [$instanceId], $inParams);

        $stmt = $this->query($sql, $params);
        return (int)$stmt->rowCount();
    }

    /**
     * Publish selected API services to products table so they show on the user products page.
     *
     * IMPORTANT:
     * - We set products.api_provider_id = NULL to avoid FK issues with api_providers.
     * - We store external_service_id in products.api_product_id.
     *
     * FIX:
     * - Prevent "Data too long" by trimming name/slug to DB column limits.
     */
    public function publishServicesToProducts(int $instanceId, array $ids): int
    {
        $instanceId = (int)$instanceId;
        if ($instanceId <= 0) return 0;

        $cleanIds = array_values(array_unique(array_filter(array_map('intval', $ids), fn($v) => $v > 0)));
        if (!$cleanIds) return 0;

        $placeholders = implode(',', array_fill(0, count($cleanIds), '?'));
        $services = $this->fetchAll(
            "SELECT * FROM api_services WHERE api_instance_id = ? AND id IN ($placeholders)",
            array_merge([$instanceId], $cleanIds)
        );

        if (!$services) return 0;

        $categoryId = $this->getOrCreateCategoryId('API Services', 'api-services');

        // Detect real DB limits (fallback safe defaults)
        $nameMax = $this->getVarcharLimit('products', 'name', 190);
        $slugMax = $this->getVarcharLimit('products', 'slug', 190);

        $published = 0;

        foreach ($services as $s) {
            $rawName = (string)($s->name ?? '');
            $name = $this->limitString($rawName !== '' ? $rawName : ('API Service #' . (int)$s->id), $nameMax);

            $baseSlug = $this->slugify($name);
            if ($baseSlug === '') {
                $baseSlug = 'api-service-' . (int)$s->id;
            }

            // Make slug unique but keep within $slugMax
            $slug = $this->makeUniqueSlug($baseSlug, (int)$s->id, $slugMax);

            $apiProductId = (string)$s->external_service_id;

            // If product already exists by api_product_id, update it.
            $existing = $this->fetch(
                "SELECT id FROM products WHERE api_product_id = ? LIMIT 1",
                [$apiProductId]
            );

            if ($existing) {
                $this->query(
                    "UPDATE products
                     SET category_id = ?,
                         name = ?,
                         slug = ?,
                         price = ?,
                         original_price = ?,
                         min_order = ?,
                         max_order = ?,
                         status = 'active',
                         updated_at = NOW()
                     WHERE id = ?",
                    [
                        $categoryId,
                        $name,
                        $slug,
                        round((float)$s->final_price, 2),
                        round((float)$s->api_rate, 2),
                        (int)$s->min_qty,
                        (int)$s->max_qty,
                        (int)$existing->id
                    ]
                );
            } else {
                $this->query(
                    "INSERT INTO products
                     (category_id, api_provider_id, api_product_id, name, slug, description, short_description, price, original_price, stock, min_order, max_order, status, created_at, updated_at)
                     VALUES
                     (?, NULL, ?, ?, ?, NULL, NULL, ?, ?, 0, ?, ?, 'active', NOW(), NOW())",
                    [
                        $categoryId,
                        $apiProductId,
                        $name,
                        $slug,
                        round((float)$s->final_price, 2),
                        round((float)$s->api_rate, 2),
                        (int)$s->min_qty,
                        (int)$s->max_qty
                    ]
                );
            }

            $published++;
        }

        return $published;
    }

    private function getOrCreateCategoryId(string $name, string $slug): int
    {
        $row = $this->fetch("SELECT id FROM categories WHERE slug = ? LIMIT 1", [$slug]);
        if ($row && isset($row->id)) {
            return (int)$row->id;
        }

        $this->query(
            "INSERT INTO categories (parent_id, name, slug, description, icon, status, created_at)
             VALUES (NULL, ?, ?, NULL, NULL, 'active', NOW())",
            [$name, $slug]
        );

        $idRow = $this->fetch("SELECT id FROM categories WHERE slug = ? LIMIT 1", [$slug]);
        return (int)($idRow->id ?? 1);
    }

    private function slugify(string $text): string
    {
        $text = strtolower(trim($text));
        $text = preg_replace('~[^a-z0-9]+~', '-', $text);
        $text = trim($text, '-');
        return $text;
    }

    /**
     * Get VARCHAR length limit from INFORMATION_SCHEMA, fallback to provided default.
     * Uses current database connection (DATABASE()).
     */
    private function getVarcharLimit(string $table, string $column, int $fallback): int
    {
        try {
            $row = $this->fetch(
                "SELECT CHARACTER_MAXIMUM_LENGTH AS len
                 FROM INFORMATION_SCHEMA.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME = ?
                   AND COLUMN_NAME = ?
                 LIMIT 1",
                [$table, $column]
            );

            $len = isset($row->len) ? (int)$row->len : 0;
            return $len > 0 ? $len : $fallback;
        } catch (\Throwable $e) {
            return $fallback;
        }
    }

    /**
     * Safely limit string length for UTF-8.
     */
    private function limitString(string $text, int $maxLen): string
    {
        $text = trim($text);
        if ($maxLen <= 0) return $text;

        if (function_exists('mb_strlen') && function_exists('mb_substr')) {
            if (mb_strlen($text, 'UTF-8') > $maxLen) {
                return mb_substr($text, 0, $maxLen, 'UTF-8');
            }
            return $text;
        }

        // Fallback
        if (strlen($text) > $maxLen) {
            return substr($text, 0, $maxLen);
        }
        return $text;
    }

    /**
     * Ensure slug is unique and within DB limit.
     */
    private function makeUniqueSlug(string $baseSlug, int $serviceId, int $maxLen): string
    {
        $baseSlug = trim($baseSlug, '-');
        if ($baseSlug === '') {
            $baseSlug = 'api-service';
        }

        // First attempt: base within limit
        $slug = $this->limitString($baseSlug, $maxLen);
        $exists = $this->fetch("SELECT id FROM products WHERE slug = ? LIMIT 1", [$slug]);
        if (!$exists) {
            return $slug;
        }

        // Second attempt: append -{serviceId} within limit
        $suffix = '-' . $serviceId;
        $allowedBase = max(1, $maxLen - (function_exists('mb_strlen') ? mb_strlen($suffix, 'UTF-8') : strlen($suffix)));
        $slug = $this->limitString($baseSlug, $allowedBase) . $suffix;

        $exists2 = $this->fetch("SELECT id FROM products WHERE slug = ? LIMIT 1", [$slug]);
        if (!$exists2) {
            return $slug;
        }

        // Final attempt: add random tail
        $rand = '-' . substr(md5((string)microtime(true)), 0, 6);
        $suffix2 = $suffix . $rand;
        $allowedBase2 = max(1, $maxLen - (function_exists('mb_strlen') ? mb_strlen($suffix2, 'UTF-8') : strlen($suffix2)));
        $slug = $this->limitString($baseSlug, $allowedBase2) . $suffix2;

        return $slug;
    }

    /**
     * Toggle service status (active / inactive)
     */
    public function toggleStatus(int $id): bool
    {
        $sql = "
            UPDATE api_services
            SET
                status = IF(status = 'active', 'inactive', 'active'),
                updated_at = NOW()
            WHERE id = ?
        ";

        $this->query($sql, [$id]);

        return true;
    }

    /**
     * Toggle service visibility (yes / no)
     */
    public function toggleVisibility(int $id): bool
    {
        $sql = "
            UPDATE api_services
            SET
                visible = IF(visible = 'yes', 'no', 'yes'),
                updated_at = NOW()
            WHERE id = ?
        ";

        $this->query($sql, [$id]);

        return true;
    }

    /**
     * Get a single service by ID
     */
    public function getById(int $id)
    {
        return $this->fetch(
            "SELECT *
             FROM api_services
             WHERE id = ?",
            [$id]
        );
    }

    /**
     * Count services for an API instance
     */
    public function countByInstance(int $instanceId): int
    {
        $row = $this->fetch(
            "SELECT COUNT(*) AS total
             FROM api_services
             WHERE api_instance_id = ?",
            [$instanceId]
        );

        return (int)($row->total ?? 0);
    }
}
