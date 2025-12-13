<?php

/**
 * Tailor Orders Page
 * Displays all orders received by the logged-in tailor
 */

session_start();

// Check if user is logged in and is a tailor
if (!isset($_SESSION['logged_in']) || $_SESSION['user_type'] !== 'tailor') {
    header('Location: ../index.php');
    exit;
}

$tailor_id = $_SESSION['user_id'];
$tailor_name = $_SESSION['user_name'];
$shop_name = $_SESSION['shop_name'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders - Smart Tailoring Service</title>

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

        .filter-tabs {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        .filter-btn {
            padding: 0.75rem 1.5rem;
            border: 2px solid var(--border-color);
            background: var(--white);
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .filter-btn:hover {
            border-color: var(--primary-color);
        }

        .filter-btn.active {
            background: var(--primary-color);
            color: var(--white);
            border-color: var(--primary-color);
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
            background: #e9d5ff;
            color: #6b21a8;
        }

        .status-stitching {
            background: #ddd6fe;
            color: #5b21b6;
        }

        .status-first_fitting {
            background: #fbcfe8;
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
            background: #a7f3d0;
            color: #065f46;
        }

        .status-completed {
            background: #86efac;
            color: #14532d;
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
            min-width: 150px;
        }

        .detail-value {
            color: var(--text-dark);
        }

        .customer-info {
            background: var(--light-bg);
            padding: 1.5rem;
            border-radius: var(--radius-md);
        }

        .customer-info h4 {
            margin: 0 0 1rem 0;
            color: var(--text-dark);
        }

        .order-actions {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 2px solid var(--light-bg);
            flex-wrap: wrap;
        }

        .btn-action {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: var(--radius-md);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-accept {
            background: #10b981;
            color: var(--white);
        }

        .btn-accept:hover {
            background: #059669;
        }

        .btn-progress {
            background: #3b82f6;
            color: var(--white);
        }

        .btn-progress:hover {
            background: #2563eb;
        }

        .btn-ready {
            background: #8b5cf6;
            color: var(--white);
        }

        .btn-ready:hover {
            background: #7c3aed;
        }

        .btn-complete {
            background: #10b981;
            color: var(--white);
        }

        .btn-complete:hover {
            background: #059669;
        }

        .btn-reject {
            background: #ef4444;
            color: var(--white);
        }

        .btn-reject:hover {
            background: #dc2626;
        }

        .btn-contact {
            background: var(--light-bg);
            color: var(--text-dark);
            border: 2px solid var(--border-color);
        }

        .btn-contact:hover {
            border-color: var(--primary-color);
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

            .page-header {
                padding: 1.5rem 1rem;
            }

            .page-header h1 {
                font-size: 1.5rem;
            }

            .filter-tabs {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                padding-bottom: 0.5rem;
                margin: 0 -1rem;
                padding-left: 1rem;
                padding-right: 1rem;
            }

            .filter-btn {
                padding: 0.5rem 1rem;
                font-size: 0.85rem;
                white-space: nowrap;
            }

            .orders-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

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

            .customer-info {
                padding: 1rem;
            }

            .customer-info h4 {
                font-size: 1rem;
            }

            .order-actions {
                flex-direction: column;
                gap: 0.75rem;
            }

            .btn-action {
                width: 100%;
                justify-content: center;
                padding: 0.75rem;
                font-size: 0.9rem;
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

            .filter-btn {
                padding: 0.45rem 0.85rem;
                font-size: 0.8rem;
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

            .customer-info {
                padding: 0.85rem;
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
                <li><a href="orders.php" class="nav-link active">Orders</a></li>

                <li><a href="profile.php" class="nav-link">Shop Profile</a></li>
            </ul>

            <div class="nav-auth">
                <span class="welcome-text" style="margin-right: 1rem;">Welcome, <?php echo htmlspecialchars($tailor_name); ?>!</span>
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
            <h1><i class="fas fa-inbox"></i> Order Management</h1>
            <p>Manage and track all incoming orders for <?php echo htmlspecialchars($shop_name); ?></p>
        </div>

        <!-- Filter Tabs -->
        <div class="filter-tabs">
            <button class="filter-btn active" data-status="all" onclick="filterOrders('all')">
                <i class="fas fa-list"></i> All Orders
            </button>
            <button class="filter-btn" data-status="pending" onclick="filterOrders('pending')">
                <i class="fas fa-clock"></i> Pending
            </button>
            <button class="filter-btn" data-status="booked" onclick="filterOrders('booked')">
                <i class="fas fa-check-circle"></i> Booked
            </button>
            <button class="filter-btn" data-status="cutting" onclick="filterOrders('cutting')">
                <i class="fas fa-cut"></i> Cutting
            </button>
            <button class="filter-btn" data-status="stitching" onclick="filterOrders('stitching')">
                <i class="fas fa-sewing-machine"></i> Stitching
            </button>
            <button class="filter-btn" data-status="first_fitting" onclick="filterOrders('first_fitting')">
                <i class="fas fa-user-check"></i> First Fitting
            </button>
            <button class="filter-btn" data-status="alterations" onclick="filterOrders('alterations')">
                <i class="fas fa-tools"></i> Alterations
            </button>
            <button class="filter-btn" data-status="final_fitting" onclick="filterOrders('final_fitting')">
                <i class="fas fa-user-tie"></i> Final Fitting
            </button>
            <button class="filter-btn" data-status="ready_for_pickup" onclick="filterOrders('ready_for_pickup')">
                <i class="fas fa-box-open"></i> Ready
            </button>
            <button class="filter-btn" data-status="completed" onclick="filterOrders('completed')">
                <i class="fas fa-check-double"></i> Completed
            </button>
        </div>

        <!-- Orders Grid -->
        <div class="orders-grid" id="ordersGrid">
            <div class="loading" style="text-align: center; padding: 3rem;">
                <i class="fas fa-spinner fa-spin" style="font-size: 2rem; color: var(--primary-color);"></i>
                <p>Loading orders...</p>
            </div>
        </div>

    </div>

    <script>
        let allOrders = [];
        let currentFilter = 'all';

        // Load orders on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadOrders();
        });

        // Load tailor orders
        function loadOrders() {
            fetch('../api/orders/get_orders.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        allOrders = data.orders;
                        displayOrders(allOrders);
                    } else {
                        document.getElementById('ordersGrid').innerHTML = `
                            <div class="empty-state">
                                <i class="fas fa-inbox"></i>
                                <h2>No orders yet</h2>
                                <p>Orders from customers will appear here</p>
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

        // Filter orders by status
        function filterOrders(status) {
            currentFilter = status;

            // Update active button
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            document.querySelector(`[data-status="${status}"]`).classList.add('active');

            // Filter and display
            if (status === 'all') {
                displayOrders(allOrders);
            } else {
                const filtered = allOrders.filter(order => order.order_status === status);
                displayOrders(filtered);
            }
        }

        // Display orders
        function displayOrders(orders) {
            const grid = document.getElementById('ordersGrid');

            if (orders.length === 0) {
                grid.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <h2>No ${currentFilter === 'all' ? '' : currentFilter} orders</h2>
                        <p>${currentFilter === 'all' ? 'Orders from customers will appear here' : 'No orders with this status'}</p>
                    </div>
                `;
                return;
            }

            grid.innerHTML = orders.map(order => createOrderCard(order)).join('');
        }

        // Format measurements JSON
        function formatMeasurements(snapshot) {
            try {
                const data = typeof snapshot === 'string' ? JSON.parse(snapshot) : snapshot;
                if (!data) return 'N/A';

                // If it's a simple key-value object
                return Object.entries(data).map(([key, value]) => {
                    // Format key (e.g. "chest" -> "Chest")
                    const label = key.charAt(0).toUpperCase() + key.slice(1).replace('_', ' ');
                    return `<strong>${label}:</strong> ${value}`;
                }).join('<br>');
            } catch (e) {
                return snapshot;
            }
        }

        // Create order card HTML
        function createOrderCard(order) {
            const statusClass = `status-${order.order_status}`;
            const statusText = order.order_status.replace('_', ' ').toUpperCase();

            // Determine which action buttons to show
            let actionButtons = '';

            // New 11-status workflow
            if (order.order_status === 'pending') {
                actionButtons = `
                    <button class="btn-action btn-accept" onclick="updateOrderStatus(${order.id}, 'booked')">
                        <i class="fas fa-check"></i> Accept & Book Order
                    </button>
                    <button class="btn-action btn-reject" onclick="updateOrderStatus(${order.id}, 'cancelled')">
                        <i class="fas fa-times"></i> Reject
                    </button>
                `;
            } else if (order.order_status === 'booked') {
                actionButtons = `
                    <button class="btn-action btn-progress" onclick="updateOrderStatus(${order.id}, 'cutting')">
                        <i class="fas fa-cut"></i> Start Cutting
                    </button>
                `;
            } else if (order.order_status === 'cutting') {
                actionButtons = `
                    <button class="btn-action btn-progress" onclick="updateOrderStatus(${order.id}, 'stitching')">
                        <i class="fas fa-sewing-machine"></i> Start Stitching
                    </button>
                `;
            } else if (order.order_status === 'stitching') {
                actionButtons = `
                    <button class="btn-action btn-progress" onclick="updateOrderStatus(${order.id}, 'first_fitting')">
                        <i class="fas fa-user-check"></i> Schedule First Fitting
                    </button>
                `;
            } else if (order.order_status === 'first_fitting') {
                actionButtons = `
                    <button class="btn-action btn-progress" onclick="updateOrderStatus(${order.id}, 'alterations')">
                        <i class="fas fa-tools"></i> Needs Alterations
                    </button>
                    <button class="btn-action btn-success" onclick="updateOrderStatus(${order.id}, 'final_fitting')">
                        <i class="fas fa-user-tie"></i> Proceed to Final Fitting
                    </button>
                `;
            } else if (order.order_status === 'alterations') {
                actionButtons = `
                    <button class="btn-action btn-progress" onclick="updateOrderStatus(${order.id}, 'final_fitting')">
                        <i class="fas fa-user-tie"></i> Schedule Final Fitting
                    </button>
                `;
            } else if (order.order_status === 'final_fitting') {
                actionButtons = `
                    <button class="btn-action btn-progress" onclick="updateOrderStatus(${order.id}, 'alterations')">
                        <i class="fas fa-tools"></i> More Alterations Needed
                    </button>
                    <button class="btn-action btn-ready" onclick="updateOrderStatus(${order.id}, 'ready_for_pickup')">
                        <i class="fas fa-box-open"></i> Ready for Pickup
                    </button>
                `;
            } else if (order.order_status === 'ready_for_pickup') {
                actionButtons = `
                    <button class="btn-action btn-complete" onclick="updateOrderStatus(${order.id}, 'delivered')">
                        <i class="fas fa-truck"></i> Mark as Delivered
                    </button>
                `;
            } else if (order.order_status === 'delivered') {
                actionButtons = `
                    <button class="btn-action btn-complete" onclick="updateOrderStatus(${order.id}, 'completed')">
                        <i class="fas fa-check-circle"></i> Complete Order
                    </button>
                `;
            }

            return `
                <div class="order-card" data-status="${order.order_status}">
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
                            ${order.measurements ? `
                            <div class="detail-row">
                                <span class="detail-label">Measurements:</span>
                                <span class="detail-value">${order.measurements}</span>
                            </div>
                            ` : ''}
                            ${!order.measurements && order.measurements_snapshot ? `
                            <div class="detail-row">
                                <span class="detail-label">Measurements:</span>
                                <span class="detail-value">${formatMeasurements(order.measurements_snapshot)}</span>
                            </div>
                            ` : ''}
                            ${order.measurement_notes ? `
                            <div class="detail-row">
                                <span class="detail-label">Measurement Notes:</span>
                                <span class="detail-value" style="font-style: italic; color: #666;">${order.measurement_notes}</span>
                            </div>
                            ` : ''}
                            ${order.special_instructions ? `
                            <div class="detail-row">
                                <span class="detail-label">Instructions:</span>
                                <span class="detail-value">${order.special_instructions}</span>
                            </div>
                            ` : ''}
                            <div class="detail-row">
                                <span class="detail-label">Customer Budget:</span>
                                <span class="detail-value">₹${order.estimated_price || 'Not specified'}</span>
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
                        
                        <div class="customer-info">
                            <h4><i class="fas fa-user"></i> Customer Details</h4>
                            <div class="detail-row">
                                <i class="fas fa-user"></i>
                                <span>${order.customer_info.name}</span>
                            </div>
                            <div class="detail-row">
                                <i class="fas fa-phone"></i>
                                <span>${order.customer_info.phone}</span>
                            </div>
                            ${order.customer_info.address ? `
                            <div class="detail-row">
                                <i class="fas fa-map-marker-alt"></i>
                                <span>${order.customer_info.address}</span>
                            </div>
                            ` : ''}
                        </div>
                    </div>
                    
                    <div class="order-actions">
                        ${actionButtons}
                        <button class="btn-action btn-info" onclick="showTailorOrderDetailsModal(${order.id})" style="background: #8b5cf6;">
                            <i class="fas fa-info-circle"></i> View Full Details
                        </button>
                        <button class="btn-action btn-contact" onclick="contactCustomer('${order.customer_info.phone}')">
                            <i class="fas fa-phone"></i> Contact Customer
                        </button>
                    </div>
                </div>
            `;
        }

        // Update order status
        function updateOrderStatus(orderId, newStatus) {
            const statusNames = {
                'accepted': 'Accept',
                'in_progress': 'Start Work on',
                'ready': 'Mark as Ready',
                'completed': 'Complete',
                'cancelled': 'Reject'
            };

            if (!confirm(`Are you sure you want to ${statusNames[newStatus]} this order?`)) {
                return;
            }

            const formData = new FormData();
            formData.append('order_id', orderId);
            formData.append('status', newStatus);

            fetch('../api/orders/update_status.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Order status updated successfully!');
                        loadOrders(); // Reload orders
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to update order status');
                });
        }

        // Contact customer
        function contactCustomer(phone) {
            window.location.href = `tel:+91${phone}`;
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
    </script>

    <!-- Include Enhanced Order Modal JavaScript -->
    <script src="../assets/js/order-utils.js"></script>
    <script src="../assets/js/tailor-order-enhancements.js"></script>

</body>

</html>