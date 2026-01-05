<?php
namespace App\Models;

class Review extends BaseModel {
    public function getProductReviews($productId) {
        return $this->fetchAll("SELECT r.*, u.username FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.product_id = ? AND r.status = 'approved' ORDER BY r.created_at DESC", [$productId]);
    }

    public function addReview($data) {
        $sql = "INSERT INTO reviews (user_id, product_id, rating, comment) VALUES (?, ?, ?, ?)";
        return $this->query($sql, [$data['user_id'], $data['product_id'], $data['rating'], $data['comment']]);
    }
}
