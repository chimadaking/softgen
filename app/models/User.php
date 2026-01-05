<?php
namespace App\Models;

class User extends BaseModel {
    public function register($data) {
        $sql = "INSERT INTO users (username, email, password, first_name, last_name, status) VALUES (:username, :email, :password, :first_name, :last_name, 'active')";
        $params = [
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => password_hash($data['password'], PASSWORD_BCRYPT),
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name']
        ];
        
        if ($this->query($sql, $params)) {
            $userId = $this->lastInsertId();
            // Assign default role (user)
            $this->query("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)", [$userId, ROLE_USER]);
            // Create wallet
            $this->query("INSERT INTO wallet_balances (user_id, balance) VALUES (?, 0)", [$userId]);
            // Create loyalty account
            $this->query("INSERT INTO loyalty_accounts (user_id) VALUES (?)", [$userId]);
            return true;
        }
        return false;
    }

    public function login($email, $password) {
        $user = $this->fetch("SELECT u.* FROM users u WHERE u.email = ?", [$email]);
        
        if ($user && password_verify($password, $user->password)) {
            // Fetch all roles for this user
            $user->roles = $this->getUserRoles($user->id);
            return $user;
        }
        return false;
    }

    /**
     * Get all roles for a user
     * Returns array of role objects with id and name
     */
    public function getUserRoles($userId) {
        return $this->fetchAll("SELECT r.id, r.name FROM roles r
                               INNER JOIN user_roles ur ON r.id = ur.role_id
                               WHERE ur.user_id = ?", [$userId]);
    }

    /**
     * Check if user has a specific role
     */
    public function hasRole($userId, $roleId) {
        $result = $this->fetch("SELECT 1 FROM user_roles WHERE user_id = ? AND role_id = ?", [$userId, $roleId]);
        return $result !== null;
    }

    /**
     * Check if user has any of the given roles
     */
    public function hasAnyRole($userId, $roleIds) {
        if (!is_array($roleIds)) {
            $roleIds = [$roleIds];
        }
        $placeholders = implode(',', array_fill(0, count($roleIds), '?'));
        $params = array_merge([$userId], $roleIds);
        $result = $this->fetch("SELECT 1 FROM user_roles WHERE user_id = ? AND role_id IN ($placeholders)", $params);
        return $result !== null;
    }

    /**
     * Assign a role to a user (add without removing existing roles)
     */
    public function assignRole($userId, $roleId) {
        // Check if user already has this role
        if ($this->hasRole($userId, $roleId)) {
            return true; // Already assigned
        }
        return $this->query("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)", [$userId, $roleId]);
    }

    /**
     * Remove a role from a user
     */
    public function removeRole($userId, $roleId) {
        return $this->query("DELETE FROM user_roles WHERE user_id = ? AND role_id = ?", [$userId, $roleId]);
    }

    public function createAdmin($data) {
        $sql = "INSERT INTO users (username, email, password, status) VALUES (:username, :email, :password, 'active')";
        
        $this->query($sql, [
            ':username' => $data['username'],
            ':email' => $data['email'],
            ':password' => $data['password']
        ]);
        
        $userId = $this->lastInsertId();
        
        // Assign admin role (ROLE_ADMIN = 1)
        $this->query("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)", [$userId, ROLE_ADMIN]);
        
        return true;
    }

    public function findByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        return $stmt->fetch(\PDO::FETCH_OBJ);
    }

    public function findById($id) {
        return $this->fetch("SELECT * FROM users WHERE id = ?", [$id]);
    }

    public function getAllUsers() {
        return $this->fetchAll("SELECT DISTINCT u.* FROM users u");
    }

    public function updateProfile($userId, $data) {
        $sql = "UPDATE users SET first_name = :first_name, last_name = :last_name WHERE id = :id";
        $params = [
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'id' => $userId
        ];
        return $this->query($sql, $params);
    }

    public function changePassword($userId, $currentPassword, $newPassword) {
        $user = $this->findById($userId);
        if ($user && password_verify($currentPassword, $user->password)) {
            $sql = "UPDATE users SET password = :password WHERE id = :id";
            return $this->query($sql, ['password' => password_hash($newPassword, PASSWORD_BCRYPT), 'id' => $userId]);
        }
        return false;
    }

    public function updateStatus($userId, $status) {
        $sql = "UPDATE users SET status = ? WHERE id = ?";
        return $this->query($sql, [$status, $userId]);
    }

    public function deleteUser($userId) {
        // Delete user roles first (foreign key constraint)
        $this->query("DELETE FROM user_roles WHERE user_id = ?", [$userId]);
        // Then delete the user
        $sql = "DELETE FROM users WHERE id = ?";
        return $this->query($sql, [$userId]);
    }
}