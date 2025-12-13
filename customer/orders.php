<?php

/**
 * Customer Orders Page
 * Displays all orders for the logged-in customer
 */

session_start();

// Check if user is logged in and is a customer
if (!isset($_SESSION['logged_in']) || $_SESSION['user_type'] !== 'customer') {
    header('Location: ../index.php');
    exit;
}

$customer_id = $_SESSION['user_id'];
$customer_name = $_SESSION['user_name'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Smart Tailoring Service</title>

    <link rel="icon" type="image/jpg" href="../assets/images/STP-favicon.jpg">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">

    <style>
        .orders-container {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 2rem;
        }

        .page-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: var(--white);
            padding: 2rem;
            border-radius: var(--radius-lg);
            margin-bottom: 2rem;
        }

        .page-header h1 {
            margin: 0;
            font-size: 2rem;
        }

        .orders-grid {
            display: grid;
            gap: 1.5rem;
        }

        .order-card {
            background: var(--white);
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            box-shadow: var(--shadow-md);
            border: 2px solid var(--border-color);
            transition: all 0.3s ease;
        }

        .order-card:hover {
            border-color: var(--primary-color);
            box-shadow: var(--shadow-lg);
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--light-bg);
        }

        .order-number {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-dark);
        }

        .order-status {
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-booked {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-cutting {
            background: #e0d4ff;
            color: #6b21a8;
        }

        .status-stitching {
            background: #ddd6fe;
            color: #5b21b6;
        }

        .status-first_fitting {
            background: #fce7f3;
            color: #9f1239;
        }

        .status-alterations {
            background: #fed7aa;
            color: #9a3412;
        }

        .status-final_fitting {
            background: #cffafe;
            color: #155e75;
        }

        .status-ready_for_pickup {
            background: #d1fae5;
            color: #065f46;
        }

        .status-delivered {
            background: #bbf7d0;
            color: #14532d;
        }

        .status-completed {
            background: #d1fae5;
            color: #065f46;
        }

        .status-cancelled {
            background: #fee2e2;
            color: #991b1b;
        }

        /* Legacy status support */
        .status-accepted {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-in_progress {
            background: #e0e7ff;
            color: #4338ca;
        }

        .status-ready {
            background: #d1fae5;
            color: #065f46;
        }

        .order-body {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
        }

        .order-details {
            display: grid;
            gap: 0.75rem;
        }

        .detail-row {
            display: flex;
            gap: 0.5rem;
        }

        .detail-label {
            font-weight: 600;
            color: var(--text-light);
            min-width: 140px;
        }

        .detail-value {
            color: var(--text-dark);
        }

        .tailor-info {
            background: var(--light-bg);
            padding: 1.5rem;
            border-radius: var(--radius-md);
        }

        .tailor-info h4 {
            margin: 0 0 1rem 0;
            color: var(--text-dark);
        }

        .order-actions {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 2px solid var(--light-bg);
        }

        .btn-action {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: var(--radius-md);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-cancel {
            background: #fee2e2;
            color: #991b1b;
        }

        .btn-cancel:hover {
            background: #fecaca;
        }

        .btn-details {
            background: var(--primary-color);
            color: var(--white);
        }

        .btn-details:hover {
            background: var(--secondary-color);
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: var(--white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
        }

        .empty-state i {
            font-size: 4rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        @media (max-width: 768px) {

            /* Hide welcome text in navbar on mobile */
            .welcome-text {
                display: none !important;
            }

            /* Hide navigation menu on mobile */
            .nav-menu {
                display: none;
            }

            /* Make dashboard and logout buttons icon-only on mobile */
            .btn-dashboard .btn-text,
            .btn-logout .btn-text {
                display: none;
            }

            .btn-dashboard,
            .btn-logout {
                width: 40px;
                height: 40px;
                padding: 0;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 50%;
                margin-left: 0.5rem;
            }

            .btn-dashboard i,
            .btn-logout i {
                margin: 0;
                font-size: 1.1rem;
            }

            .orders-container {
                padding: 0 1rem;
                margin: 1rem auto;
            }

            .orders-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            /* Order card mobile responsive */
            .order-card {
                padding: 1rem;
            }

            .order-header {
                flex-direction: column;
                gap: 0.5rem;
                align-items: flex-start;
            }

            .order-number {
                font-size: 1rem;
            }

            .order-status {
                font-size: 0.85rem;
                padding: 0.4rem 0.85rem;
            }

            .order-body {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .detail-row {
                flex-direction: column;
                gap: 0.25rem;
            }

            .detail-label {
                min-width: auto;
                font-size: 0.85rem;
            }

            .detail-value {
                font-size: 0.95rem;
                font-weight: 500;
            }

            .tailor-info {
                padding: 1rem;
            }

            .tailor-info h4 {
                font-size: 1rem;
                margin-bottom: 0.75rem;
            }

            .order-actions {
                flex-direction: column;
                gap: 0.75rem;
            }

            .btn-action {
                width: 100%;
                padding: 0.75rem;
                justify-content: center;
                font-size: 0.9rem;
            }

            .page-header {
                padding: 1.5rem 1rem;
            }

            .page-header h1 {
                font-size: 1.5rem;
            }
        }

        @media (max-width: 480px) {
            .orders-container {
                padding: 0 0.75rem;
            }

            .page-header {
                padding: 1.25rem 0.85rem;
                margin-bottom: 1rem;
            }

            .page-header h1 {
                font-size: 1.25rem;
            }

            .order-card {
                padding: 0.85rem;
            }

            .order-number {
                font-size: 0.9rem;
            }

            .order-status {
                font-size: 0.75rem;
                padding: 0.35rem 0.75rem;
            }

            .detail-label {
                font-size: 0.8rem;
            }

            .detail-value {
                font-size: 0.9rem;
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                gap: 0.5rem;
            }

            .btn-action {
                padding: 0.65rem;
                font-size: 0.85rem;
            }
        }
    </style>
</head>

<body>

    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <img src="../assets/images/logo.png" alt="Logo">
                <span class="logo-text">Smart Tailoring Service</span>
            </div>

            <ul class="nav-menu">
                <li><a href="../index.php" class="nav-link">Home</a></li>
                <li><a href="dashboard.php" class="nav-link">Dashboard</a></li>
                <li><a href="orders.php" class="nav-link active">My Orders</a></li>
                <li><a href="measurements.php" class="nav-link">Measurements</a></li>
                <li><a href="profile.php" class="nav-link">Profile</a></li>
            </ul>

            <div class="nav-auth">
                <span class="welcome-text" style="margin-right: 1rem;">Welcome, <?php echo htmlspecialchars($customer_name); ?>!</span>
                <a href="dashboard.php" class="btn-dashboard" title="Dashboard">
                    <i class="fas fa-tachometer-alt"></i> <span class="btn-text">Dashboard</span>
                </a>
                <button class="btn-logout" onclick="window.location.href='../auth/logout.php'">
                    <i class="fas fa-sign-out-alt"></i> <span class="btn-text">Logout</span>
                </button>
            </div>
        </div>
    </nav>

    <!-- Orders Container -->
    <div class="orders-container">

        <!-- Page Header -->
        <div class="page-header">
            <h1><i class="fas fa-shopping-bag"></i> My Orders</h1>
            <p>Track and manage all your tailoring orders</p>
        </div>

        <!-- Orders Grid -->
        <div class="orders-grid" id="ordersGrid">
            <div class="loading" style="text-align: center; padding: 3rem;">
                <i class="fas fa-spinner fa-spin" style="font-size: 2rem; color: var(--primary-color);"></i>
                <p>Loading your orders...</p>
            </div>
        </div>

    </div>

    <script>
        // Load orders on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadOrders();
        });

        // Load customer orders
        function loadOrders() {
            fetch('../api/orders/get_orders.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayOrders(data.orders);
                    } else {
                        document.getElementById('ordersGrid').innerHTML = `
                            <div class="empty-state">
                                <i class="fas fa-inbox"></i>
                                <h2>No orders yet</h2>
                                <p>Start browsing tailors and place your first order!</p>
                                <button class="btn btn-primary" onclick="window.location.href='../index.php#tailors'" style="margin-top: 1.5rem;">
                                    <i class="fas fa-search"></i> Find Tailors
                                </button>
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error loading orders:', error);
                    document.getElementById('ordersGrid').innerHTML = `
                        <div class="empty-state">
                            <i class="fas fa-exclamation-triangle"></i>
                            <h2>Error loading orders</h2>
                            <p>Please try again later</p>
                        </div>
                    `;
                });
        }

        // Display orders
        function displayOrders(orders) {
            const grid = document.getElementById('ordersGrid');

            if (orders.length === 0) {
                grid.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <h2>No orders yet</h2>
                        <p>Start browsing tailors and place your first order!</p>
                        <button class="btn btn-primary" onclick="window.location.href='../index.php#tailors'" style="margin-top: 1.5rem;">
                            <i class="fas fa-search"></i> Find Tailors
                        </button>
                    </div>
                `;
                return;
            }

            grid.innerHTML = orders.map(order => createOrderCard(order)).join('');
        }

        // Create order card HTML
        function createOrderCard(order) {
            const statusClass = `status-${order.order_status}`;
            const statusText = order.order_status.replace('_', ' ').toUpperCase();
            const canCancel = ['pending', 'accepted'].includes(order.order_status);

            // OTP Display Logic
            let otpHtml = '';
            if ((order.order_status === 'accepted' || order.order_status === 'booked') && order.start_otp) {
                otpHtml = `
                    <div class="otp-section" style="background: #e0f2fe; padding: 1rem; border-radius: 8px; margin: 1rem 0; border: 1px dashed #0284c7;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <h4 style="margin: 0; color: #0369a1; font-size: 0.9rem;">START JOB CODE</h4>
                                <p style="margin: 0.25rem 0 0; font-size: 0.8rem; color: #0c4a6e;">Share with tailor to start work</p>
                            </div>
                            <div style="font-size: 1.5rem; font-weight: 700; letter-spacing: 2px; color: #0284c7; background: #fff; padding: 0.25rem 0.75rem; border-radius: 4px;">
                                ${order.start_otp}
                            </div>
                        </div>
                    </div>
                `;
            } else if (order.order_status === 'ready_for_pickup' && order.delivery_otp) {
                otpHtml = `
                    <div class="otp-section" style="background: #dcfce7; padding: 1rem; border-radius: 8px; margin: 1rem 0; border: 1px dashed #16a34a;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <h4 style="margin: 0; color: #15803d; font-size: 0.9rem;">DELIVERY CODE</h4>
                                <p style="margin: 0.25rem 0 0; font-size: 0.8rem; color: #14532d;">Share to pick up order</p>
                            </div>
                            <div style="font-size: 1.5rem; font-weight: 700; letter-spacing: 2px; color: #16a34a; background: #fff; padding: 0.25rem 0.75rem; border-radius: 4px;">
                                ${order.delivery_otp}
                            </div>
                        </div>
                    </div>
                `;
            }

            return `
                <div class="order-card">
                    <div class="order-header">
                        <div class="order-number">
                            <i class="fas fa-receipt"></i> ${order.order_number}
                        </div>
                        <div class="order-status ${statusClass}">
                            ${statusText}
                        </div>
                    </div>
                    
                    <div class="order-body">
                        <div class="order-details">
                            <div class="detail-row">
                                <span class="detail-label">Service Type:</span>
                                <span class="detail-value">${order.service_type}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Garment Type:</span>
                                <span class="detail-value">${order.garment_type}</span>
                            </div>
                            ${order.fabric_type ? `
                            <div class="detail-row">
                                <span class="detail-label">Fabric Type:</span>
                                <span class="detail-value">${order.fabric_type}</span>
                            </div>
                            ` : ''}
                            ${order.fabric_color ? `
                            <div class="detail-row">
                                <span class="detail-label">Fabric Color:</span>
                                <span class="detail-value">
                                    ${order.fabric_color}
                                    <span style="display: inline-block; width: 20px; height: 20px; background-color: ${order.fabric_color}; border: 1px solid #ccc; border-radius: 3px; margin-left: 8px; vertical-align: middle;"></span>
                                </span>
                            </div>
                            ` : ''}
                            <div class="detail-row">
                                <span class="detail-label">Quantity:</span>
                                <span class="detail-value">${order.quantity}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Estimated Price:</span>
                                <span class="detail-value">₹${order.estimated_price || 'TBD'}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Order Date:</span>
                                <span class="detail-value">${formatDate(order.created_at)}</span>
                            </div>
                            ${order.delivery_date ? `
                            <div class="detail-row">
                                <span class="detail-label">Expected Delivery:</span>
                                <span class="detail-value">${formatDate(order.delivery_date)}</span>
                            </div>
                            ` : ''}
                        </div>
                        
                        <div class="tailor-info">
                            <h4><i class="fas fa-store"></i> Tailor Details</h4>
                            <div class="detail-row">
                                <i class="fas fa-user"></i>
                                <span>${order.tailor_info.shop_name}</span>
                            </div>
                            <div class="detail-row">
                                <i class="fas fa-phone"></i>
                                <span>${order.tailor_info.phone}</span>
                            </div>
                            <div class="detail-row">
                                <i class="fas fa-map-marker-alt"></i>
                                <span>${order.tailor_info.area}</span>
                            </div>
                        </div>
                    </div>

                    ${otpHtml}
                    
                    <div class="order-actions">
                        ${canCancel ? `
                            <button class="btn-action btn-cancel" onclick="cancelOrder(${order.id})">
                                <i class="fas fa-times"></i> Cancel Order
                            </button>
                        ` : ''}
                        <button class="btn-action btn-details" onclick="viewOrderDetails(${order.id})">
                            <i class="fas fa-eye"></i> View Details
                        </button>
                    </div>
                </div>
            `;
        }

        // Format date
        function formatDate(dateString) {
            if (!dateString) return 'N/A';
            const date = new Date(dateString);
            return date.toLocaleDateString('en-IN', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
        }

        // Cancel order
        function cancelOrder(orderId) {
            if (!confirm('Are you sure you want to cancel this order?')) {
                return;
            }

            const formData = new FormData();
            formData.append('order_id', orderId);

            fetch('../api/orders/cancel_order.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Order cancelled successfully');
                        loadOrders(); // Reload orders
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to cancel order');
                });
        }

        // View order details
        function viewOrderDetails(orderId) {
            alert('Order details modal coming soon! Order ID: ' + orderId);
            // TODO: Implement order details modal
        }
    </script>

    <!-- Enhanced Order Features -->
    <script src="../assets/js/order-utils.js"></script>
    <script src="../assets/js/order-enhancements.js"></script>

</body>

</html>