<?php
namespace App\Models;

class Product extends BaseModel {
    public function getAllProducts() {
        return $this->fetchAll("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.status = 'active'");
    }

    /**
     * Active categories for user-facing filters.
     */
    public function getActiveCategories(): array
    {
        return $this->fetchAll(
            "SELECT id, name, slug
             FROM categories
             WHERE status = 'active'
             ORDER BY name ASC"
        );
    }

    /**
     * Find a category by slug.
     */
    public function getCategoryBySlug(string $slug)
    {
        return $this->fetch(
            "SELECT * FROM categories WHERE slug = ? LIMIT 1",
            [$slug]
        );
    }

    /**
     * Paginated products for user-facing list, with optional search and category.
     */
    public function getActiveProductsPaginated(string $q = '', ?int $categoryId = null, int $limit = 12, int $offset = 0): array
    {
        $limit  = max(6, min(48, (int)$limit));
        $offset = max(0, (int)$offset);

        $sql = "
            SELECT p.*, c.name as category_name, c.slug as category_slug
            FROM products p
            JOIN categories c ON p.category_id = c.id
            WHERE p.status = 'active'
        ";

        $params = [];

        if ($categoryId !== null) {
            $sql .= " AND p.category_id = ?";
            $params[] = (int)$categoryId;
        }

        $q = trim($q);
        if ($q !== '') {
            $sql .= " AND (p.name LIKE ? OR p.slug LIKE ? OR c.name LIKE ?)";
            $like = '%' . $q . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        $sql .= " ORDER BY p.created_at DESC LIMIT {$limit} OFFSET {$offset}";

        return $this->fetchAll($sql, $params);
    }

    public function countActiveProducts(string $q = '', ?int $categoryId = null): int
    {
        $sql = "
            SELECT COUNT(*) AS total
            FROM products p
            JOIN categories c ON p.category_id = c.id
            WHERE p.status = 'active'
        ";
        $params = [];

        if ($categoryId !== null) {
            $sql .= " AND p.category_id = ?";
            $params[] = (int)$categoryId;
        }

        $q = trim($q);
        if ($q !== '') {
            $sql .= " AND (p.name LIKE ? OR p.slug LIKE ? OR c.name LIKE ?)";
            $like = '%' . $q . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        $row = $this->fetch($sql, $params);
        return (int)($row->total ?? 0);
    }

    public function getAllProductsAdmin() {
        return $this->fetchAll("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id ORDER BY p.created_at DESC");
    }

    public function getProductById($id) {
        return $this->fetch("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.id = ?", [$id]);
    }

    public function getCategories() {
        return $this->fetchAll("SELECT * FROM categories WHERE status = 'active'");
    }

    public function getProductsByCategory($categoryId) {
        return $this->fetchAll("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.category_id = ? AND p.status = 'active'", [$categoryId]);
    }

    public function create($data) {
        $sql = "INSERT INTO products (name, slug, category_id, description, short_description, price, original_price, stock, min_order, max_order, status) 
                VALUES (:name, :slug, :category_id, :description, :short_description, :price, :original_price, :stock, :min_order, :max_order, :status)";
        $params = [
            'name' => $data['name'],
            'slug' => $data['slug'],
            'category_id' => $data['category_id'],
            'description' => $data['description'],
            'short_description' => $data['short_description'],
            'price' => $data['price'],
            'original_price' => $data['original_price'],
            'stock' => $data['stock'],
            'min_order' => $data['min_order'],
            'max_order' => $data['max_order'],
            'status' => $data['status']
        ];
        return $this->query($sql, $params);
    }

    public function update($data) {
        $sql = "UPDATE products SET name = :name, slug = :slug, category_id = :category_id, description = :description, 
                short_description = :short_description, price = :price, original_price = :original_price, stock = :stock, 
                min_order = :min_order, max_order = :max_order, status = :status WHERE id = :id";
        $params = [
            'name' => $data['name'],
            'slug' => $data['slug'],
            'category_id' => $data['category_id'],
            'description' => $data['description'],
            'short_description' => $data['short_description'],
            'price' => $data['price'],
            'original_price' => $data['original_price'],
            'stock' => $data['stock'],
            'min_order' => $data['min_order'],
            'max_order' => $data['max_order'],
            'status' => $data['status'],
            'id' => $data['id']
        ];
        return $this->query($sql, $params);
    }

    public function delete($id) {
        return $this->query("DELETE FROM products WHERE id = ?", [$id]);
    }
}
