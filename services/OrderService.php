<?php

/**
 * OrderService
 * Business Logic Layer for Order operations
 * (Similar to @Service in Spring Boot)
 */

require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../repositories/OrderRepository.php';

class OrderService
{
    private $orderRepository;

    public function __construct($connection)
    {
        $this->orderRepository = new OrderRepository($connection);
    }

    /**
     * Verify Start OTP
     * @param int $order_id
     * @param string $otp
     * @param int $tailor_id
     * @return array
     */
    public function verifyStartOtp($order_id, $otp, $tailor_id)
    {
        try {
            // Verify order belongs to tailor
            $order = $this->orderRepository->findById($order_id);
            if (!$order || $order->getTailorId() != $tailor_id) {
                return ['success' => false, 'message' => 'Order not found or unauthorized'];
            }

            if ($this->orderRepository->verifyStartOtp($order_id, $otp)) {
                return ['success' => true, 'message' => 'Start OTP verified successfully'];
            }

            return ['success' => false, 'message' => 'Invalid OTP'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error verifying OTP: ' . $e->getMessage()];
        }
    }

    /**
     * Verify Delivery OTP
     * @param int $order_id
     * @param string $otp
     * @param int $tailor_id
     * @return array
     */
    public function verifyDeliveryOtp($order_id, $otp, $tailor_id)
    {
        try {
            // Verify order belongs to tailor
            $order = $this->orderRepository->findById($order_id);
            if (!$order || $order->getTailorId() != $tailor_id) {
                return ['success' => false, 'message' => 'Order not found or unauthorized'];
            }

            if ($this->orderRepository->verifyDeliveryOtp($order_id, $otp)) {
                return ['success' => true, 'message' => 'Delivery OTP verified successfully'];
            }

            return ['success' => false, 'message' => 'Invalid OTP'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error verifying OTP: ' . $e->getMessage()];
        }
    }

    /**
     * Create new order with validation
     * @param array $orderData
     * @return array Response with success status and message
     */
    public function createOrder($orderData)
    {
        try {
            // Validate required fields
            $validation = $this->validateOrderData($orderData);
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'message' => $validation['message']
                ];
            }

            // Create Order object
            $order = new Order();
            $order->setOrderNumber($this->orderRepository->generateOrderNumber());
            $order->setCustomerId($orderData['customer_id']);
            $order->setTailorId($orderData['tailor_id']);
            $order->setServiceType($orderData['service_type']);
            $order->setGarmentType($orderData['garment_type'] ?? 'General');
            $order->setQuantity($orderData['quantity'] ?? 1);
            $order->setMeasurements($orderData['measurements'] ?? '');
            $order->setSpecialInstructions($orderData['special_instructions'] ?? '');
            $order->setEstimatedPrice($orderData['estimated_price'] ?? 0);
            $order->setDeliveryDate($orderData['delivery_date'] ?? null);

            // Validate Order object
            if (!$order->isValid()) {
                return [
                    'success' => false,
                    'message' => 'Invalid order data'
                ];
            }

            // Save to database
            $orderId = $this->orderRepository->create($order);

            if ($orderId) {
                return [
                    'success' => true,
                    'message' => 'Order placed successfully!',
                    'order_id' => $orderId,
                    'order_number' => $order->getOrderNumber()
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to create order. Please try again.'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Validate order data
     * @param array $data
     * @return array
     */
    private function validateOrderData($data)
    {
        // Check required fields
        if (empty($data['customer_id'])) {
            return ['valid' => false, 'message' => 'Customer ID is required'];
        }

        if (empty($data['tailor_id'])) {
            return ['valid' => false, 'message' => 'Tailor ID is required'];
        }

        if (empty($data['service_type'])) {
            return ['valid' => false, 'message' => 'Service type is required'];
        }

        // Validate service type
        $validServices = ['Stitching', 'Alteration', 'Embroidery', 'Repair', 'Designer Work'];
        if (!in_array($data['service_type'], $validServices)) {
            return ['valid' => false, 'message' => 'Invalid service type'];
        }

        // Validate quantity
        if (isset($data['quantity']) && $data['quantity'] < 1) {
            return ['valid' => false, 'message' => 'Quantity must be at least 1'];
        }

        // Validate estimated price
        if (isset($data['estimated_price']) && $data['estimated_price'] < 0) {
            return ['valid' => false, 'message' => 'Price cannot be negative'];
        }

        return ['valid' => true];
    }

    /**
     * Get customer's orders
     * @param int $customer_id
     * @return array
     */
    public function getCustomerOrders($customer_id)
    {
        try {
            $orders = $this->orderRepository->findByCustomerId($customer_id);

            return [
                'success' => true,
                'orders' => $orders,
                'count' => count($orders)
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to fetch orders',
                'orders' => []
            ];
        }
    }

    /**
     * Get tailor's orders
     * @param int $tailor_id
     * @return array
     */
    public function getTailorOrders($tailor_id)
    {
        try {
            $orders = $this->orderRepository->findByTailorId($tailor_id);

            return [
                'success' => true,
                'orders' => $orders,
                'count' => count($orders)
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to fetch orders',
                'orders' => []
            ];
        }
    }

    /**
     * Get single order details
     * @param int $order_id
     * @return array
     */
    public function getOrderDetails($order_id)
    {
        try {
            $order = $this->orderRepository->findById($order_id);

            if ($order) {
                return [
                    'success' => true,
                    'order' => $order->toArray()
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Order not found'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to fetch order details'
            ];
        }
    }

    /**
     * Update order status (with authorization check)
     * @param int $order_id
     * @param string $new_status
     * @param int $user_id
     * @param string $user_type
     * @return array
     */
    public function updateOrderStatus($order_id, $new_status, $user_id, $user_type)
    {
        try {
            // Get order
            $order = $this->orderRepository->findById($order_id);

            if (!$order) {
                return [
                    'success' => false,
                    'message' => 'Order not found'
                ];
            }

            // Get order as array for easier access
            $orderData = $order->toArray();

            // Authorization check
            if ($user_type === 'customer') {
                if ($orderData['customer_id'] != $user_id) {
                    return [
                        'success' => false,
                        'message' => 'Unauthorized: This is not your order'
                    ];
                }
            } else if ($user_type === 'tailor') {
                if ($orderData['tailor_id'] != $user_id) {
                    return [
                        'success' => false,
                        'message' => 'Unauthorized: This order was not placed to your shop (Order tailor_id: ' . $orderData['tailor_id'] . ', Your ID: ' . $user_id . ')'
                    ];
                }
            }

            // Validate status transition
            $validTransition = $this->isValidStatusTransition(
                $orderData['order_status'],
                $new_status,
                $user_type
            );

            if (!$validTransition) {
                return [
                    'success' => false,
                    'message' => 'Invalid status transition from ' . $orderData['order_status'] . ' to ' . $new_status
                ];
            }

            // Update status
            if ($new_status === 'completed') {
                $result = $this->orderRepository->complete($order_id);
            } else if ($new_status === 'cancelled') {
                $result = $this->orderRepository->cancel($order_id);
            } else {
                $result = $this->orderRepository->updateStatus($order_id, $new_status);
            }

            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Order status updated successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to update order status'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ];
        }
    }


    /**
     * Validate status transition based on user role
     * @param string $current_status
     * @param string $new_status
     * @param string $user_type
     * @return bool
     */
    private function isValidStatusTransition($current_status, $new_status, $user_type)
    {
        // Enhanced workflow: pending -> booked -> cutting -> stitching -> first_fitting -> alterations -> final_fitting -> ready_for_pickup -> delivered -> completed

        $allowedTransitions = [
            'customer' => [
                'pending' => ['cancelled'],
                'booked' => ['cancelled'],
                'accepted' => ['cancelled'], // Legacy support
                'ready_for_pickup' => ['delivered'],
                'delivered' => ['completed'] // Customer confirms final completion
            ],
            'tailor' => [
                'pending' => ['booked', 'cancelled'],
                'booked' => ['cutting', 'cancelled'],
                'cutting' => ['stitching'],
                'stitching' => ['first_fitting'],
                'first_fitting' => ['alterations', 'final_fitting'],
                'alterations' => ['final_fitting'],
                'final_fitting' => ['alterations', 'ready_for_pickup'],
                'ready_for_pickup' => ['delivered'],
                'delivered' => ['completed'],
                // Legacy support
                'accepted' => ['in_progress', 'cancelled'],
                'in_progress' => ['cutting', 'ready', 'cancelled'],
                'ready' => ['completed']
            ]
        ];

        if (!isset($allowedTransitions[$user_type][$current_status])) {
            return false;
        }

        return in_array($new_status, $allowedTransitions[$user_type][$current_status]);
    }

    /**
     * Cancel order
     * @param int $order_id
     * @param int $user_id
     * @param string $user_type
     * @return array
     */
    public function cancelOrder($order_id, $user_id, $user_type)
    {
        return $this->updateOrderStatus($order_id, 'cancelled', $user_id, $user_type);
    }

    /**
     * Get order statistics for dashboard
     * @param int $tailor_id
     * @return array
     */
    public function getTailorOrderStats($tailor_id)
    {
        try {
            $stats = $this->orderRepository->getOrderCountsByStatus($tailor_id);

            return [
                'success' => true,
                'stats' => $stats
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'stats' => []
            ];
        }
    }

    // ============ NEW METHODS FOR ENHANCED TAILORING WORKFLOW ============

    /**
     * Create order with enhanced workflow fields (measurements, fabric, etc.)
     * @param array $orderData
     * @return array
     */
    public function createOrderWithMeasurements($orderData)
    {
        try {
            // Validate required fields
            $validation = $this->validateOrderData($orderData);
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'message' => $validation['message']
                ];
            }

            // Create Order object
            $order = new Order();
            $order->setOrderNumber($this->orderRepository->generateOrderNumber());
            $order->setCustomerId($orderData['customer_id']);
            $order->setTailorId($orderData['tailor_id']);
            $order->setServiceType($orderData['service_type']);
            $order->setGarmentType($orderData['garment_type'] ?? 'General');
            $order->setQuantity($orderData['quantity'] ?? 1);
            $order->setMeasurements($orderData['measurements'] ?? '');
            $order->setSpecialInstructions($orderData['special_instructions'] ?? '');
            $order->setEstimatedPrice($orderData['estimated_price'] ?? 0);
            $order->setDeliveryDate($orderData['delivery_date'] ?? null);

            // Set enhanced fields
            $order->setFabricType($orderData['fabric_type'] ?? null);
            $order->setFabricColor($orderData['fabric_color'] ?? null);
            $order->setMeasurementId($orderData['measurement_id'] ?? null);
            $order->setDeadline($orderData['deadline'] ?? null);
            $order->setSourceOrderId($orderData['source_order_id'] ?? null);
            $order->setOrderStatus($orderData['order_status'] ?? 'pending');
            $order->setPaymentStatus($orderData['payment_status'] ?? 'unpaid');

            // Handle JSON fields
            if (isset($orderData['design_specifications'])) {
                if (is_array($orderData['design_specifications'])) {
                    $order->setDesignSpecifications(json_encode($orderData['design_specifications']));
                } else {
                    $order->setDesignSpecifications($orderData['design_specifications']);
                }
            }

            if (isset($orderData['measurements_snapshot'])) {
                if (is_array($orderData['measurements_snapshot'])) {
                    $order->setMeasurementsSnapshot(json_encode($orderData['measurements_snapshot']));
                } else {
                    $order->setMeasurementsSnapshot($orderData['measurements_snapshot']);
                }
            }

            // Handle deposit
            if (isset($orderData['deposit_amount'])) {
                $order->setDepositAmount($orderData['deposit_amount']);
                // Calculate balance
                $balance = ($orderData['estimated_price'] ?? 0) - $orderData['deposit_amount'];
                $order->setBalanceDue($balance > 0 ? $balance : 0);
            }

            // Validate Order object
            if (!$order->isValid()) {
                return [
                    'success' => false,
                    'message' => 'Invalid order data'
                ];
            }

            // Save to database
            $orderId = $this->orderRepository->createWithEnhancements($order);

            if ($orderId) {
                // Record initial history
                $this->recordHistory(
                    $orderId,
                    null,
                    $order->getOrderStatus(),
                    $orderData['customer_id'],
                    'customer',
                    'Order created'
                );

                return [
                    'success' => true,
                    'message' => 'Order placed successfully!',
                    'order_id' => $orderId,
                    'order_number' => $order->getOrderNumber()
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to create order. Please try again.'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Record order status change history
     * @param int $order_id
     * @param string $old_status
     * @param string $new_status
     * @param int $changed_by_id
     * @param string $changed_by_type
     * @param string $notes
     * @return bool
     */
    public function recordHistory($order_id, $old_status, $new_status, $changed_by_id, $changed_by_type, $notes = null)
    {
        try {
            return $this->orderRepository->recordHistory(
                $order_id,
                $old_status,
                $new_status,
                $changed_by_id,
                $changed_by_type,
                $notes
            );
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get order history
     * @param int $order_id
     * @return array
     */
    public function getOrderHistory($order_id)
    {
        try {
            $history = $this->orderRepository->getOrderHistory($order_id);

            return [
                'success' => true,
                'history' => $history,
                'count' => count($history)
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to fetch order history',
                'history' => []
            ];
        }
    }

    /**
     * Update order status with history recording
     * @param int $order_id
     * @param string $new_status
     * @param int $user_id
     * @param string $user_type
     * @param string $notes
     * @return array
     */
    public function updateOrderStatusWithHistory($order_id, $new_status, $user_id, $user_type, $notes = null)
    {
        try {
            // Get current order
            $order = $this->orderRepository->findById($order_id);

            if (!$order) {
                return [
                    'success' => false,
                    'message' => 'Order not found'
                ];
            }

            $orderData = $order->toArray();
            $old_status = $orderData['order_status'];

            // Use existing updateOrderStatus for authorization and validation
            $result = $this->updateOrderStatus($order_id, $new_status, $user_id, $user_type);

            // If successful, record history
            if ($result['success']) {
                $this->recordHistory($order_id, $old_status, $new_status, $user_id, $user_type, $notes);
            }

            return $result;
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Schedule fitting appointment
     * @param int $order_id
     * @param string $fitting_type 'first' or 'final'
     * @param string $fitting_date
     * @return array
     */
    public function scheduleFitting($order_id, $fitting_type, $fitting_date)
    {
        try {
            $order = $this->orderRepository->findById($order_id);

            if (!$order) {
                return [
                    'success' => false,
                    'message' => 'Order not found'
                ];
            }

            if ($fitting_type === 'first') {
                $result = $this->orderRepository->updateFittingDates($order_id, $fitting_date, null);
            } else if ($fitting_type === 'final') {
                $result = $this->orderRepository->updateFittingDates($order_id, null, $fitting_date);
            } else {
                return [
                    'success' => false,
                    'message' => 'Invalid fitting type. Must be "first" or "final"'
                ];
            }

            if ($result) {
                return [
                    'success' => true,
                    'message' => ucfirst($fitting_type) . ' fitting scheduled successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to schedule fitting'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Record alteration
     * @param int $order_id
     * @param string $alteration_notes
     * @param int $user_id
     * @param string $user_type
     * @return array
     */
    public function recordAlteration($order_id, $alteration_notes, $user_id, $user_type)
    {
        try {
            $order = $this->orderRepository->findById($order_id);

            if (!$order) {
                return [
                    'success' => false,
                    'message' => 'Order not found'
                ];
            }

            $result = $this->orderRepository->recordAlteration($order_id, $alteration_notes);

            if ($result) {
                // Record in history
                $this->recordHistory(
                    $order_id,
                    $order->getOrderStatus(),
                    'alterations',
                    $user_id,
                    $user_type,
                    'Alteration recorded: ' . $alteration_notes
                );

                // Update status to alterations if not already
                if ($order->getOrderStatus() !== 'alterations') {
                    $this->orderRepository->updateStatus($order_id, 'alterations');
                }

                return [
                    'success' => true,
                    'message' => 'Alteration recorded successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to record alteration'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Process reorder (create new order from existing one)
     * @param int $source_order_id
     * @param int $customer_id
     * @param array $modifications Optional modifications to the reorder
     * @return array
     */
    public function processReorder($source_order_id, $customer_id, $modifications = [])
    {
        try {
            // Get source order
            $sourceOrder = $this->orderRepository->findById($source_order_id);

            if (!$sourceOrder) {
                return [
                    'success' => false,
                    'message' => 'Source order not found'
                ];
            }

            $sourceData = $sourceOrder->toArray();

            // Verify ownership
            if ($sourceData['customer_id'] != $customer_id) {
                return [
                    'success' => false,
                    'message' => 'Unauthorized: This is not your order'
                ];
            }

            // Prepare new order data from source
            $newOrderData = [
                'customer_id' => $customer_id,
                'tailor_id' => $sourceData['tailor_id'],
                'service_type' => $sourceData['service_type'],
                'garment_type' => $sourceData['garment_type'],
                'quantity' => $sourceData['quantity'],
                'measurements' => $sourceData['measurements'],
                'special_instructions' => $sourceData['special_instructions'],
                'estimated_price' => $sourceData['estimated_price'],
                'fabric_type' => $sourceData['fabric_type'],
                'fabric_color' => $sourceData['fabric_color'],
                'design_specifications' => $sourceData['design_specifications'],
                'measurements_snapshot' => $sourceData['measurements_snapshot'],
                'measurement_id' => $sourceData['measurement_id'],
                'source_order_id' => $source_order_id
            ];

            // Apply modifications
            foreach ($modifications as $key => $value) {
                if (array_key_exists($key, $newOrderData)) {
                    $newOrderData[$key] = $value;
                }
            }

            // Create the reorder
            return $this->createOrderWithMeasurements($newOrderData);
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Update deposit information
     * @param int $order_id
     * @param float $deposit_amount
     * @param bool $mark_as_paid
     * @return array
     */
    public function updateDeposit($order_id, $deposit_amount, $mark_as_paid = false)
    {
        try {
            $result = $this->orderRepository->updateDeposit($order_id, $deposit_amount, $mark_as_paid);

            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Deposit updated successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to update deposit'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get enhanced order statistics for dashboard
     * @param int $tailor_id
     * @return array
     */
    public function getEnhancedTailorOrderStats($tailor_id)
    {
        try {
            $stats = $this->orderRepository->getEnhancedOrderCountsByStatus($tailor_id);

            return [
                'success' => true,
                'stats' => $stats
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'stats' => []
            ];
        }
    }

    /**
     * Get orders requiring fitting appointments
     * @param int $tailor_id
     * @return array
     */
    public function getOrdersRequiringFitting($tailor_id)
    {
        try {
            $orders = $this->orderRepository->findOrdersRequiringFitting($tailor_id);

            return [
                'success' => true,
                'orders' => $orders,
                'count' => count($orders)
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to fetch fitting orders',
                'orders' => []
            ];
        }
    }

    /**
     * Get overdue orders
     * @param int $tailor_id
     * @return array
     */
    public function getOverdueOrders($tailor_id)
    {
        try {
            $orders = $this->orderRepository->findOverdueOrders($tailor_id);

            return [
                'success' => true,
                'orders' => $orders,
                'count' => count($orders)
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to fetch overdue orders',
                'orders' => []
            ];
        }
    }
}
