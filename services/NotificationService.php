<?php

/**
 * Notification Service
 * Helper class to create and manage notifications
 */

class NotificationService
{
    private $conn;

    public function __construct($db_connection)
    {
        $this->conn = $db_connection;
    }

    /**
     * Create a new notification
     * 
     * @param int $user_id User ID to send notification to
     * @param string $user_type 'customer' or 'tailor'
     * @param string $title Notification title
     * @param string $message Notification message
     * @param string $type Notification type (order_status, verification, etc.)
     * @param int|null $related_id Related order ID or other reference
     * @return bool Success status
     */
    public function createNotification($user_id, $user_type, $title, $message, $type, $related_id = null)
    {
        try {
            $stmt = $this->conn->prepare(
                "INSERT INTO notifications (user_id, user_type, title, message, type, related_id) 
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            $stmt->bind_param("issssi", $user_id, $user_type, $title, $message, $type, $related_id);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Notification creation failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Create order status notification for customer
     */
    public function notifyOrderStatus($customer_id, $order_id, $status, $tailor_name = '')
    {
        $messages = [
            'pending' => [
                'title' => 'Order Placed Successfully',
                'message' => 'Your order has been placed and is waiting for tailor confirmation.',
                'type' => 'order_status'
            ],
            'accepted' => [
                'title' => 'Order Accepted! 🎉',
                'message' => "Great news! {$tailor_name} has accepted your order and will start working on it soon.",
                'type' => 'order_accepted'
            ],
            'measurement_taken' => [
                'title' => 'Measurements Taken',
                'message' => 'Your measurements have been recorded by the tailor.',
                'type' => 'order_status'
            ],
            'fabric_received' => [
                'title' => 'Fabric Received',
                'message' => 'The tailor has received your fabric.',
                'type' => 'order_status'
            ],
            'cutting' => [
                'title' => 'Cutting in Progress',
                'message' => 'Your garment cutting has started.',
                'type' => 'order_in_progress'
            ],
            'stitching' => [
                'title' => 'Stitching in Progress ✂️',
                'message' => 'Your garment is being stitched. Almost there!',
                'type' => 'stitching'
            ],
            'fitting' => [
                'title' => 'Ready for Fitting',
                'message' => 'Your garment is ready for trial fitting.',
                'type' => 'order_status'
            ],
            'alteration' => [
                'title' => 'Alterations in Progress',
                'message' => 'Minor alterations are being made to ensure perfect fit.',
                'type' => 'alteration'
            ],
            'finishing' => [
                'title' => 'Final Touches',
                'message' => 'Your garment is in the finishing stage.',
                'type' => 'order_in_progress'
            ],
            'quality_check' => [
                'title' => 'Quality Check',
                'message' => 'Your garment is undergoing quality inspection.',
                'type' => 'order_status'
            ],
            'completed' => [
                'title' => 'Order Completed! ⭐',
                'message' => 'Your order is ready for pickup. Don\'t forget to leave a review!',
                'type' => 'order_completed'
            ],
            'cancelled' => [
                'title' => 'Order Cancelled',
                'message' => 'Your order has been cancelled.',
                'type' => 'order_cancelled'
            ]
        ];

        if (isset($messages[$status])) {
            $data = $messages[$status];
            return $this->createNotification(
                $customer_id,
                'customer',
                $data['title'],
                $data['message'],
                $data['type'],
                $order_id
            );
        }

        return false;
    }

    /**
     * Create new order notification for tailor
     */
    public function notifyNewOrder($tailor_id, $order_id, $customer_name, $garment_type)
    {
        return $this->createNotification(
            $tailor_id,
            'tailor',
            'New Order Received! 🎉',
            "You have a new order from {$customer_name} for {$garment_type}.",
            'new_order',
            $order_id
        );
    }

    /**
     * Create order cancellation notification for tailor
     */
    public function notifyOrderCancelled($tailor_id, $order_id, $customer_name)
    {
        return $this->createNotification(
            $tailor_id,
            'tailor',
            'Order Cancelled',
            "Order from {$customer_name} has been cancelled.",
            'order_cancelled',
            $order_id
        );
    }

    /**
     * Create verification notification for tailor
     */
    public function notifyVerification($tailor_id, $verified)
    {
        if ($verified) {
            return $this->createNotification(
                $tailor_id,
                'tailor',
                'Verification Approved! ✅',
                'Congratulations! Your shop has been verified. You can now receive orders.',
                'verification',
                null
            );
        } else {
            return $this->createNotification(
                $tailor_id,
                'tailor',
                'Verification Pending',
                'Your shop is under verification. You will be notified once approved.',
                'verification',
                null
            );
        }
    }

    /**
     * Get unread notification count
     */
    public function getUnreadCount($user_id, $user_type)
    {
        try {
            $stmt = $this->conn->prepare(
                "SELECT COUNT(*) as count FROM notifications 
                 WHERE user_id = ? AND user_type = ? AND is_read = FALSE"
            );
            $stmt->bind_param("is", $user_id, $user_type);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            return $row['count'];
        } catch (Exception $e) {
            return 0;
        }
    }
}
