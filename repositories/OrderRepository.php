<?php

/**
 * OrderRepository
 * Data Access Layer for Order operations
 * (Similar to JpaRepository in Spring Boot)
 */

require_once __DIR__ . '/../models/Order.php';

class OrderRepository
{
    private $conn;

    public function __construct($connection)
    {
        $this->conn = $connection;
    }

    /**
     * Create new order
     * @param Order $order
     * @return int|false Order ID or false on failure
     */
    public function create(Order $order)
    {
        $query = "INSERT INTO orders 
                  (order_number, customer_id, tailor_id, service_type, garment_type, 
                   quantity, measurements, special_instructions, estimated_price, 
                   order_status, payment_status, delivery_date) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'unpaid', ?)";

        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            return false;
        }

        $order_number = $order->getOrderNumber();
        $customer_id = $order->getCustomerId();
        $tailor_id = $order->getTailorId();
        $service_type = $order->getServiceType();
        $garment_type = $order->getGarmentType();
        $quantity = $order->getQuantity();
        $measurements = $order->getMeasurements();
        $special_instructions = $order->getSpecialInstructions();
        $estimated_price = $order->getEstimatedPrice();
        $delivery_date = $order->getDeliveryDate();

        $stmt->bind_param(
            "siisssisss",
            $order_number,
            $customer_id,
            $tailor_id,
            $service_type,
            $garment_type,
            $quantity,
            $measurements,
            $special_instructions,
            $estimated_price,
            $delivery_date
        );

        if ($stmt->execute()) {
            return $this->conn->insert_id;
        }

        return false;
    }

    /**
     * Find order by ID
     * @param int $id
     * @return Order|null
     */
    public function findById($id)
    {
        $query = "SELECT * FROM orders WHERE id = ?";
        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            return null;
        }

        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            return new Order($row);
        }

        return null;
    }

    /**
     * Find all orders by customer ID
     * @param int $customer_id
     * @return array Array of Order objects
     */
    public function findByCustomerId($customer_id)
    {
        $query = "SELECT o.*, 
                         t.shop_name, t.owner_name, t.phone as tailor_phone, t.area
                  FROM orders o
                  LEFT JOIN tailors t ON o.tailor_id = t.id
                  WHERE o.customer_id = ?
                  ORDER BY o.created_at DESC";

        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            return [];
        }

        $stmt->bind_param("i", $customer_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $orders = [];
        while ($row = $result->fetch_assoc()) {
            $order = new Order($row);
            // Add tailor info
            $row['tailor_info'] = [
                'shop_name' => $row['shop_name'],
                'owner_name' => $row['owner_name'],
                'phone' => $row['tailor_phone'],
                'area' => $row['area']
            ];
            $orders[] = $row; // Return as array for now (can be Order object)
        }

        return $orders;
    }

    /**
     * Find all orders by tailor ID
     * @param int $tailor_id
     * @return array Array of Order objects
     */
    public function findByTailorId($tailor_id)
    {
        $query = "SELECT o.*, 
                         c.full_name as customer_name, c.phone as customer_phone, c.address as customer_address
                  FROM orders o
                  LEFT JOIN customers c ON o.customer_id = c.id
                  WHERE o.tailor_id = ?
                  ORDER BY o.created_at DESC";

        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            return [];
        }

        $stmt->bind_param("i", $tailor_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $orders = [];
        while ($row = $result->fetch_assoc()) {
            // Add customer info
            $row['customer_info'] = [
                'name' => $row['customer_name'],
                'phone' => $row['customer_phone'],
                'address' => $row['customer_address']
            ];

            // Fetch customer's default measurement notes for this order
            $measurementNotes = $this->getCustomerMeasurementNotes($row['customer_id'], $row['garment_type']);
            if ($measurementNotes) {
                $row['measurement_notes'] = $measurementNotes;
            }

            $orders[] = $row;
        }

        return $orders;
    }

    /**
     * Get customer's measurement notes for a specific garment type
     * @param int $customer_id
     * @param string $garment_type
     * @return string|null
     */
    private function getCustomerMeasurementNotes($customer_id, $garment_type)
    {
        $query = "SELECT notes, measurements_data 
                  FROM measurements 
                  WHERE customer_id = ? 
                    AND is_default = 1 
                    AND (garment_context = ? OR garment_context = 'full')
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            return null;
        }

        $stmt->bind_param("is", $customer_id, $garment_type);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $notes = $row['notes'] ?? '';

            // Extract instructions from measurements_data if present
            if (!empty($row['measurements_data'])) {
                $measurementData = json_decode($row['measurements_data'], true);
                if ($measurementData && isset($measurementData['instructions'])) {
                    $instructions = $measurementData['instructions'];
                    $notes = trim($notes . ' ' . $instructions);
                }
            }

            return !empty($notes) && $notes !== '0' ? $notes : null;
        }

        return null;
    }
    /**
     * Update order status
     * @param int $order_id
     * @param string $status
     * @return bool
     */
    public function updateStatus($order_id, $status)
    {
        $query = "UPDATE orders SET order_status = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("si", $status, $order_id);
        $result = $stmt->execute();

        if ($result) {
            // Auto-generate OTPs based on status
            if ($status === 'booked' || $status === 'accepted') {
                $this->generateStartOtp($order_id);
            } elseif ($status === 'ready_for_pickup') {
                $this->generateDeliveryOtp($order_id);
            }
        }

        return $result;
    }

    /**
     * Generate Start OTP for order
     * @param int $order_id
     * @return string|false
     */
    public function generateStartOtp($order_id)
    {
        $otp = $this->generateOtp();
        $query = "UPDATE orders SET start_otp = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("si", $otp, $order_id);

        return $stmt->execute() ? $otp : false;
    }

    /**
     * Verify Start OTP
     * @param int $order_id
     * @param string $otp
     * @return bool
     */
    public function verifyStartOtp($order_id, $otp)
    {
        $query = "SELECT id FROM orders WHERE id = ? AND start_otp = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("is", $order_id, $otp);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // OTP is correct, update verified_at and status
            $updateQuery = "UPDATE orders 
                            SET start_otp_verified_at = NOW(), 
                                order_status = 'in_progress',
                                updated_at = NOW() 
                            WHERE id = ?";
            $updateStmt = $this->conn->prepare($updateQuery);
            $updateStmt->bind_param("i", $order_id);
            return $updateStmt->execute();
        }

        return false;
    }

    /**
     * Generate Delivery OTP for order
     * @param int $order_id
     * @return string|false
     */
    public function generateDeliveryOtp($order_id)
    {
        $otp = $this->generateOtp();
        $query = "UPDATE orders SET delivery_otp = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("si", $otp, $order_id);

        return $stmt->execute() ? $otp : false;
    }

    /**
     * Verify Delivery OTP
     * @param int $order_id
     * @param string $otp
     * @return bool
     */
    public function verifyDeliveryOtp($order_id, $otp)
    {
        $query = "SELECT id FROM orders WHERE id = ? AND delivery_otp = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("is", $order_id, $otp);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // OTP is correct, update verified_at and status
            $updateQuery = "UPDATE orders 
                            SET delivery_otp_verified_at = NOW(), 
                                order_status = 'delivered',
                                completed_at = NOW(),
                                updated_at = NOW() 
                            WHERE id = ?";
            $updateStmt = $this->conn->prepare($updateQuery);
            $updateStmt->bind_param("i", $order_id);
            return $updateStmt->execute();
        }

        return false;
    }

    /**
     * Generate a 4-digit OTP
     * @return string
     */
    private function generateOtp()
    {
        return str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
    }


    /**
     * Update payment status
     * @param int $order_id
     * @param string $payment_status
     * @return bool
     */
    public function updatePaymentStatus($order_id, $payment_status)
    {
        $query = "UPDATE orders SET payment_status = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("si", $payment_status, $order_id);
        return $stmt->execute();
    }

    /**
     * Update final price
     * @param int $order_id
     * @param float $price
     * @return bool
     */
    public function updateFinalPrice($order_id, $price)
    {
        $query = "UPDATE orders SET final_price = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("di", $price, $order_id);
        return $stmt->execute();
    }

    /**
     * Cancel order
     * @param int $order_id
     * @return bool
     */
    public function cancel($order_id)
    {
        return $this->updateStatus($order_id, 'cancelled');
    }

    /**
     * Complete order
     * @param int $order_id
     * @return bool
     */
    public function complete($order_id)
    {
        $query = "UPDATE orders 
                  SET order_status = 'completed', 
                      completed_at = NOW(), 
                      updated_at = NOW() 
                  WHERE id = ?";

        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("i", $order_id);
        return $stmt->execute();
    }

    /**
     * Get order counts by status for a tailor
     * @param int $tailor_id
     * @return array
     */
    public function getOrderCountsByStatus($tailor_id)
    {
        $query = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN order_status = 'pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN order_status = 'accepted' THEN 1 ELSE 0 END) as accepted,
                    SUM(CASE WHEN order_status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
                    SUM(CASE WHEN order_status = 'completed' THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN order_status = 'cancelled' THEN 1 ELSE 0 END) as cancelled
                  FROM orders 
                  WHERE tailor_id = ?";

        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            return ['total' => 0, 'pending' => 0, 'accepted' => 0, 'in_progress' => 0, 'completed' => 0, 'cancelled' => 0];
        }

        $stmt->bind_param("i", $tailor_id);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_assoc();
    }

    /**
     * Generate unique order number
     * @return string
     */
    public function generateOrderNumber()
    {
        return 'ORD' . date('Ymd') . rand(1000, 9999);
    }

    // ============ NEW METHODS FOR ENHANCED TAILORING WORKFLOW ============

    /**
     * Create order with enhanced workflow fields
     * @param Order $order
     * @return int|false Order ID or false on failure
     */
    public function createWithEnhancements(Order $order)
    {
        $query = "INSERT INTO orders 
                  (order_number, customer_id, tailor_id, service_type, garment_type, 
                   quantity, measurements, special_instructions, estimated_price, 
                   order_status, payment_status, delivery_date,
                   fabric_type, fabric_color, design_specifications, measurements_snapshot,
                   measurement_id, deadline, deposit_amount, balance_due, source_order_id) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            return false;
        }

        $order_number = $order->getOrderNumber();
        $customer_id = $order->getCustomerId();
        $tailor_id = $order->getTailorId();
        $service_type = $order->getServiceType();
        $garment_type = $order->getGarmentType();
        $quantity = $order->getQuantity();
        $measurements = $order->getMeasurements();
        $special_instructions = $order->getSpecialInstructions();
        $estimated_price = $order->getEstimatedPrice();
        $order_status = $order->getOrderStatus() ?: 'pending';
        $payment_status = $order->getPaymentStatus() ?: 'unpaid';
        $delivery_date = $order->getDeliveryDate();
        $fabric_type = $order->getFabricType();
        $fabric_color = $order->getFabricColor();
        $design_specifications = $order->getDesignSpecifications();
        $measurements_snapshot = $order->getMeasurementsSnapshot();
        $measurement_id = $order->getMeasurementId();
        $deadline = $order->getDeadline();
        $deposit_amount = $order->getDepositAmount();
        $balance_due = $order->getBalanceDue();
        $source_order_id = $order->getSourceOrderId();

        $stmt->bind_param(
            "siisssisssssssssisddi",
            $order_number,
            $customer_id,
            $tailor_id,
            $service_type,
            $garment_type,
            $quantity,
            $measurements,
            $special_instructions,
            $estimated_price,
            $order_status,
            $payment_status,
            $delivery_date,
            $fabric_type,
            $fabric_color,
            $design_specifications,
            $measurements_snapshot,
            $measurement_id,
            $deadline,
            $deposit_amount,
            $balance_due,
            $source_order_id
        );

        if ($stmt->execute()) {
            return $this->conn->insert_id;
        }

        return false;
    }

    /**
     * Get order history (audit trail)
     * @param int $order_id
     * @return array Array of history records
     */
    public function getOrderHistory($order_id)
    {
        $query = "SELECT oh.*, 
                         CASE 
                             WHEN oh.changed_by_type = 'customer' THEN c.full_name
                             WHEN oh.changed_by_type = 'tailor' THEN t.owner_name
                             ELSE 'System'
                         END as changed_by_name
                  FROM order_history oh
                  LEFT JOIN customers c ON oh.changed_by_type = 'customer' AND oh.changed_by_id = c.id
                  LEFT JOIN tailors t ON oh.changed_by_type = 'tailor' AND oh.changed_by_id = t.id
                  WHERE oh.order_id = ?
                  ORDER BY oh.created_at DESC";

        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            return [];
        }

        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $history = [];
        while ($row = $result->fetch_assoc()) {
            $history[] = $row;
        }

        return $history;
    }

    /**
     * Record order history
     * @param int $order_id
     * @param string $old_status
     * @param string $new_status
     * @param int $changed_by_id
     * @param string $changed_by_type ('customer' or 'tailor')
     * @param string $notes Optional notes
     * @return bool
     */
    public function recordHistory($order_id, $old_status, $new_status, $changed_by_id, $changed_by_type, $notes = null)
    {
        $query = "INSERT INTO order_history 
                  (order_id, old_status, new_status, changed_by_id, changed_by_type, notes)
                  VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("ississ", $order_id, $old_status, $new_status, $changed_by_id, $changed_by_type, $notes);
        return $stmt->execute();
    }

    /**
     * Update fitting dates
     * @param int $order_id
     * @param string $first_fitting_date
     * @param string $final_fitting_date
     * @return bool
     */
    public function updateFittingDates($order_id, $first_fitting_date = null, $final_fitting_date = null)
    {
        $updates = [];
        $params = [];
        $types = "";

        if ($first_fitting_date !== null) {
            $updates[] = "first_fitting_date = ?";
            $params[] = $first_fitting_date;
            $types .= "s";
        }

        if ($final_fitting_date !== null) {
            $updates[] = "final_fitting_date = ?";
            $params[] = $final_fitting_date;
            $types .= "s";
        }

        if (empty($updates)) {
            return false;
        }

        $updates[] = "updated_at = NOW()";
        $params[] = $order_id;
        $types .= "i";

        $query = "UPDATE orders SET " . implode(", ", $updates) . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param($types, ...$params);
        return $stmt->execute();
    }

    /**
     * Update deposit information
     * @param int $order_id
     * @param float $deposit_amount
     * @param bool $mark_as_paid
     * @return bool
     */
    public function updateDeposit($order_id, $deposit_amount, $mark_as_paid = false)
    {
        if ($mark_as_paid) {
            $query = "UPDATE orders 
                      SET deposit_amount = ?, 
                          deposit_paid_at = NOW(),
                          updated_at = NOW()
                      WHERE id = ?";
        } else {
            $query = "UPDATE orders 
                      SET deposit_amount = ?,
                          updated_at = NOW()
                      WHERE id = ?";
        }

        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("di", $deposit_amount, $order_id);
        return $stmt->execute();
    }

    /**
     * Record alteration
     * @param int $order_id
     * @param string $alteration_notes
     * @return bool
     */
    public function recordAlteration($order_id, $alteration_notes)
    {
        $query = "UPDATE orders 
                  SET alteration_notes = CONCAT(IFNULL(alteration_notes, ''), '\n', ?),
                      alteration_count = alteration_count + 1,
                      updated_at = NOW()
                  WHERE id = ?";

        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("si", $alteration_notes, $order_id);
        return $stmt->execute();
    }

    /**
     * Update fabric details
     * @param int $order_id
     * @param string $fabric_type
     * @param string $fabric_color
     * @return bool
     */
    public function updateFabricDetails($order_id, $fabric_type, $fabric_color)
    {
        $query = "UPDATE orders 
                  SET fabric_type = ?,
                      fabric_color = ?,
                      updated_at = NOW()
                  WHERE id = ?";

        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("ssi", $fabric_type, $fabric_color, $order_id);
        return $stmt->execute();
    }

    /**
     * Update design specifications
     * @param int $order_id
     * @param string $design_specifications JSON string
     * @return bool
     */
    public function updateDesignSpecifications($order_id, $design_specifications)
    {
        $query = "UPDATE orders 
                  SET design_specifications = ?,
                      updated_at = NOW()
                  WHERE id = ?";

        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("si", $design_specifications, $order_id);
        return $stmt->execute();
    }

    /**
     * Get orders by status for enhanced workflow
     * @param int $tailor_id
     * @param string $status
     * @return array
     */
    public function findByTailorAndStatus($tailor_id, $status)
    {
        $query = "SELECT o.*, 
                         c.full_name as customer_name, 
                         c.phone as customer_phone, 
                         c.address as customer_address
                  FROM orders o
                  LEFT JOIN customers c ON o.customer_id = c.id
                  WHERE o.tailor_id = ? AND o.order_status = ?
                  ORDER BY o.deadline ASC, o.created_at DESC";

        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            return [];
        }

        $stmt->bind_param("is", $tailor_id, $status);
        $stmt->execute();
        $result = $stmt->get_result();

        $orders = [];
        while ($row = $result->fetch_assoc()) {
            $row['customer_info'] = [
                'name' => $row['customer_name'],
                'phone' => $row['customer_phone'],
                'address' => $row['customer_address']
            ];
            $orders[] = $row;
        }

        return $orders;
    }

    /**
     * Get enhanced order counts by status (11 statuses)
     * @param int $tailor_id
     * @return array
     */
    public function getEnhancedOrderCountsByStatus($tailor_id)
    {
        $query = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN order_status = 'pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN order_status = 'booked' THEN 1 ELSE 0 END) as booked,
                    SUM(CASE WHEN order_status = 'cutting' THEN 1 ELSE 0 END) as cutting,
                    SUM(CASE WHEN order_status = 'stitching' THEN 1 ELSE 0 END) as stitching,
                    SUM(CASE WHEN order_status = 'first_fitting' THEN 1 ELSE 0 END) as first_fitting,
                    SUM(CASE WHEN order_status = 'alterations' THEN 1 ELSE 0 END) as alterations,
                    SUM(CASE WHEN order_status = 'final_fitting' THEN 1 ELSE 0 END) as final_fitting,
                    SUM(CASE WHEN order_status = 'ready_for_pickup' THEN 1 ELSE 0 END) as ready_for_pickup,
                    SUM(CASE WHEN order_status = 'delivered' THEN 1 ELSE 0 END) as delivered,
                    SUM(CASE WHEN order_status = 'completed' THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN order_status = 'cancelled' THEN 1 ELSE 0 END) as cancelled
                  FROM orders 
                  WHERE tailor_id = ?";

        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            return [
                'total' => 0,
                'pending' => 0,
                'booked' => 0,
                'cutting' => 0,
                'stitching' => 0,
                'first_fitting' => 0,
                'alterations' => 0,
                'final_fitting' => 0,
                'ready_for_pickup' => 0,
                'delivered' => 0,
                'completed' => 0,
                'cancelled' => 0
            ];
        }

        $stmt->bind_param("i", $tailor_id);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_assoc();
    }

    /**
     * Find orders requiring fitting appointments
     * @param int $tailor_id
     * @return array
     */
    public function findOrdersRequiringFitting($tailor_id)
    {
        $query = "SELECT o.*, 
                         c.full_name as customer_name, 
                         c.phone as customer_phone
                  FROM orders o
                  LEFT JOIN customers c ON o.customer_id = c.id
                  WHERE o.tailor_id = ? 
                  AND o.order_status IN ('first_fitting', 'final_fitting')
                  ORDER BY 
                      CASE 
                          WHEN o.first_fitting_date IS NOT NULL THEN o.first_fitting_date
                          WHEN o.final_fitting_date IS NOT NULL THEN o.final_fitting_date
                          ELSE o.deadline
                      END ASC";

        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            return [];
        }

        $stmt->bind_param("i", $tailor_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $orders = [];
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }

        return $orders;
    }

    /**
     * Find overdue orders
     * @param int $tailor_id
     * @return array
     */
    public function findOverdueOrders($tailor_id)
    {
        $query = "SELECT o.*, 
                         c.full_name as customer_name, 
                         c.phone as customer_phone
                  FROM orders o
                  LEFT JOIN customers c ON o.customer_id = c.id
                  WHERE o.tailor_id = ? 
                  AND o.deadline < CURDATE()
                  AND o.order_status NOT IN ('completed', 'cancelled', 'delivered')
                  ORDER BY o.deadline ASC";

        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            return [];
        }

        $stmt->bind_param("i", $tailor_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $orders = [];
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }

        return $orders;
    }
}
