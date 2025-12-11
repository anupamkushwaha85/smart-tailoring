<?php

/**
 * CustomerRepository
 * Data Access Layer for Customer operations
 */

class CustomerRepository
{
    private $conn;

    public function __construct($connection)
    {
        $this->conn = $connection;
    }

    /**
     * Find customer by ID
     * @param int $id
     * @return array|null
     */
    public function findById($id)
    {
        $query = "SELECT * FROM customers WHERE id = ?";
        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            return null;
        }

        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            return $row;
        }

        return null;
    }

    /**
     * Update customer profile
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateProfile($id, $data)
    {
        $query = "UPDATE customers 
                  SET full_name = ?, 
                      phone = ?, 
                      address = ?,
                      updated_at = NOW()
                  WHERE id = ?";

        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param(
            "sssi",
            $data['full_name'],
            $data['phone'],
            $data['address'],
            $id
        );

        return $stmt->execute();
    }

    /**
     * Update password
     * @param int $id
     * @param string $new_password_hash
     * @return bool
     */
    public function updatePassword($id, $new_password_hash)
    {
        $query = "UPDATE customers SET password = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("si", $new_password_hash, $id);
        return $stmt->execute();
    }

    /**
     * Check if email exists (excluding current user)
     * @param string $email
     * @param int $exclude_id
     * @return bool
     */
    public function emailExists($email, $exclude_id = null)
    {
        if ($exclude_id) {
            $query = "SELECT id FROM customers WHERE email = ? AND id != ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("si", $email, $exclude_id);
        } else {
            $query = "SELECT id FROM customers WHERE email = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("s", $email);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        return $result->num_rows > 0;
    }

    /**
     * Verify current password
     * @param int $id
     * @param string $password
     * @return bool
     */
    public function verifyPassword($id, $password)
    {
        $query = "SELECT password FROM customers WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            return password_verify($password, $row['password']);
        }

        return false;
    }

    /**
     * Update customer profile image
     * @param int $id
     * @param string $image_filename
     * @return bool
     */
    public function updateProfileImage($id, $image_filename)
    {
        $query = "UPDATE customers SET profile_image = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("si", $image_filename, $id);
        return $stmt->execute();
    }

    /**
     * Delete customer profile image
     * @param int $id
     * @return bool
     */
    public function deleteProfileImage($id)
    {
        $query = "UPDATE customers SET profile_image = NULL, updated_at = NOW() WHERE id = ?";
        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
