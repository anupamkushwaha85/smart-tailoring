<?php

/**
 * TailorRepository
 * Data Access Layer for Tailor operations
 */

class TailorRepository
{
    private $conn;

    public function __construct($connection)
    {
        $this->conn = $connection;
    }

    /**
     * Find tailor by ID
     * @param int $id
     * @return array|null
     */
    public function findById($id)
    {
        $query = "SELECT * FROM tailors WHERE id = ?";
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
     * Update tailor profile
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateProfile($id, $data)
    {
        $query = "UPDATE tailors 
                  SET shop_name = ?, 
                      owner_name = ?, 
                      phone = ?, 
                      shop_address = ?,
                      area = ?,
                      speciality = ?,
                      services_offered = ?,
                      experience_years = ?,
                      price_range = ?,
                      updated_at = NOW()
                  WHERE id = ?";

        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param(
            "sssssssssi",
            $data['shop_name'],
            $data['owner_name'],
            $data['phone'],
            $data['shop_address'],
            $data['area'],
            $data['speciality'],
            $data['services_offered'],
            $data['experience_years'],
            $data['price_range'],
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
        $query = "UPDATE tailors SET password = ?, updated_at = NOW() WHERE id = ?";
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
            $query = "SELECT id FROM tailors WHERE email = ? AND id != ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("si", $email, $exclude_id);
        } else {
            $query = "SELECT id FROM tailors WHERE email = ?";
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
        $query = "SELECT password FROM tailors WHERE id = ?";
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
     * Update tailor shop image
     * @param int $id
     * @param string $image_filename
     * @return bool
     */
    public function updateShopImage($id, $image_filename)
    {
        $query = "UPDATE tailors SET shop_image = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("si", $image_filename, $id);
        return $stmt->execute();
    }

    /**
     * Delete tailor shop image
     * @param int $id
     * @return bool
     */
    public function deleteShopImage($id)
    {
        $query = "UPDATE tailors SET shop_image = NULL, updated_at = NOW() WHERE id = ?";
        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
