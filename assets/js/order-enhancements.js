/**
 * Order Enhancements JavaScript
 * Handles enhanced order workflow features
 */

// Order status configurations
const orderStatuses = {
    'pending': { label: 'Pending', icon: 'clock', color: '#fbbf24', order: 1 },
    'booked': { label: 'Booked', icon: 'check-circle', color: '#60a5fa', order: 2 },
    'cutting': { label: 'Cutting', icon: 'cut', color: '#a78bfa', order: 3 },
    'stitching': { label: 'Stitching', icon: 'sewing-machine', color: '#8b5cf6', order: 4 },
    'first_fitting': { label: 'First Fitting', icon: 'user-check', color: '#ec4899', order: 5 },
    'alterations': { label: 'Alterations', icon: 'tools', color: '#f97316', order: 6 },
    'final_fitting': { label: 'Final Fitting', icon: 'user-tie', color: '#06b6d4', order: 7 },
    'ready_for_pickup': { label: 'Ready for Pickup', icon: 'box-open', color: '#10b981', order: 8 },
    'delivered': { label: 'Delivered', icon: 'truck', color: '#059669', order: 9 },
    'completed': { label: 'Completed', icon: 'check-double', color: '#22c55e', order: 10 },
    'cancelled': { label: 'Cancelled', icon: 'times-circle', color: '#ef4444', order: 11 }
};

// Show order details modal with enhanced features
function showOrderDetailsModal(orderId) {
    // Create modal HTML with mobile responsive styles
    const modalHTML = `
        <style>
            @media (max-width: 768px) {
                #orderDetailsModal {
                    padding: 0.5rem !important;
                }
                
                #orderDetailsModal .modal-content {
                    width: 100% !important;
                    max-width: 100% !important;
                    border-radius: 8px !important;
                    max-height: 95vh !important;
                }
                
                #orderDetailsModal .modal-header {
                    padding: 1rem !important;
                }
                
                #orderDetailsModal .modal-header h2 {
                    font-size: 1.2rem !important;
                }
                
                #orderDetailsModal .modal-header .close-modal {
                    width: 32px !important;
                    height: 32px !important;
                    right: 1rem !important;
                    top: 1rem !important;
                }
                
                #orderDetailsContent {
                    padding: 1rem !important;
                }
                
                .order-details-container > div[style*="grid-template-columns: 1fr 1fr"] {
                    grid-template-columns: 1fr !important;
                    gap: 1rem !important;
                }
                
                .order-details-container > div[style*="display: flex"] > div:first-child {
                    flex-direction: column !important;
                    gap: 1rem !important;
                }
                
                .order-details-container > div[style*="display: flex"] > div > div:last-child {
                    width: 100% !important;
                    text-align: center !important;
                    padding: 0.5rem 1rem !important;
                }
                
                .order-details-container h3 {
                    font-size: 1rem !important;
                }
                
                .order-details-container > div[style*="display: flex; justify-content: space-between; align-items: center"] {
                    overflow-x: auto !important;
                    -webkit-overflow-scrolling: touch !important;
                }
                
                .order-details-container > div[style*="display: flex; justify-content: space-between; align-items: center"] > div {
                    min-width: 60px !important;
                }
                
                .order-details-container > div[style*="display: flex; justify-content: space-between; align-items: center"] > div > div:first-child {
                    width: 32px !important;
                    height: 32px !important;
                    font-size: 0.85rem !important;
                }
                
                .order-details-container > div[style*="display: flex; justify-content: space-between; align-items: center"] > div > span {
                    font-size: 0.65rem !important;
                }
                
                .order-details-container > div[style*="display: flex; justify-content: space-between; align-items: center"] > div[style*="flex: 1"] {
                    margin: 0 0.25rem 1.5rem 0.25rem !important;
                }
                
                .order-details-container > div:last-child[style*="display: flex"] {
                    flex-direction: column !important;
                    gap: 0.75rem !important;
                }
                
                .order-details-container > div:last-child[style*="display: flex"] button {
                    width: 100% !important;
                    padding: 0.75rem !important;
                }
                
                .order-details-container [style*="display: flex; gap: 1rem"][style*="border-bottom"] {
                    flex-direction: column !important;
                    gap: 0.5rem !important;
                }
                
                .order-details-container [style*="display: flex; gap: 1rem"][style*="border-bottom"] > div:first-child {
                    align-self: flex-start !important;
                }
            }
        </style>
        <div id="orderDetailsModal" class="modal" style="display: flex; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center; overflow-y: auto; padding: 2rem;">
            <div class="modal-content" style="max-width: 1000px; width: 95%; background: white; border-radius: 12px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); max-height: 90vh; overflow-y: auto; margin: auto;">
                <div class="modal-header" style="background: linear-gradient(135deg, #667eea, #764ba2); color: white; padding: 1.5rem; border-radius: 12px 12px 0 0; position: sticky; top: 0; z-index: 10;">
                    <h2 style="margin: 0;"><i class="fas fa-receipt"></i> Order Details</h2>
                    <button class="close-modal" onclick="closeOrderDetailsModal()" style="position: absolute; right: 1.5rem; top: 1.5rem; background: rgba(255,255,255,0.2); border: none; color: white; font-size: 1.5rem; cursor: pointer; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">&times;</button>
                </div>
                <div id="orderDetailsContent" style="padding: 2rem;">
                    <div style="text-align: center; padding: 3rem;">
                        <i class="fas fa-spinner fa-spin" style="font-size: 2rem; color: #667eea;"></i>
                        <p>Loading order details...</p>
                    </div>
                </div>
            </div>
        </div>
    `;

    // Add modal to page
    document.body.insertAdjacentHTML('beforeend', modalHTML);

    // Auto-scroll to modal
    setTimeout(() => {
        const modal = document.getElementById('orderDetailsModal');
        if (modal) {
            modal.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }, 100);

    // Load order details
    loadOrderDetails(orderId);
}

// Load order details
function loadOrderDetails(orderId) {
    Promise.all([
        fetch(`../api/orders/get_orders.php`).then(r => r.json()),
        fetch(`../api/orders/get_order_history.php?order_id=${orderId}`).then(r => r.json())
    ])
        .then(([ordersData, historyData]) => {

            if (!ordersData.success) {
                throw new Error(ordersData.message || 'Failed to load orders');
            }

            const order = ordersData.orders?.find(o => o.id == orderId);
            if (!order) {
                throw new Error('Order not found in response');
            }


            const history = historyData.success ? historyData.history : [];

            displayOrderDetails(order, history);
        })
        .catch(error => {
            console.error('Error loading order details:', error);
            document.getElementById('orderDetailsContent').innerHTML = `
            <div style="text-align: center; padding: 2rem;">
                <i class="fas fa-exclamation-triangle" style="font-size: 2rem; color: #ef4444;"></i>
                <p>Failed to load order details: ${error.message}</p>
            </div>
        `;
        });
}

// Display order details
function displayOrderDetails(order, history) {
    const statusConfig = orderStatuses[order.order_status] || orderStatuses.pending;

    const html = `
        <div class="order-details-container">
            <!-- Order Header -->
            <div style="background: linear-gradient(135deg, #667eea, #764ba2); color: white; padding: 1.5rem; border-radius: 8px; margin-bottom: 1.5rem;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h3 style="margin: 0; font-size: 1.5rem;">${order.order_number}</h3>
                        <p style="margin: 0.5rem 0 0 0; opacity: 0.9;">Placed on ${formatDateTime(order.created_at)}</p>
                    </div>
                    <div style="background: white; color: ${statusConfig.color}; padding: 0.75rem 1.5rem; border-radius: 50px; font-weight: 700;">
                        <i class="fas fa-${statusConfig.icon}"></i> ${statusConfig.label}
                    </div>
                </div>
            </div>
            
            <!-- Order Status Timeline -->
            <div style="margin-bottom: 2rem;">
                <h3><i class="fas fa-route"></i> Order Progress</h3>
                ${createStatusTimeline(order.order_status)}
            </div>
            
            <!-- Order Information Grid -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
                <!-- Order Details -->
                <div>
                    <h3><i class="fas fa-info-circle"></i> Order Information</h3>
                    <div style="background: #f9fafb; padding: 1rem; border-radius: 8px;">
                        ${createDetailRow('Service Type', order.service_type)}
                        ${createDetailRow('Garment Type', order.garment_type)}
                        ${createDetailRow('Quantity', order.quantity)}
                        ${createDetailRow('Estimated Price', '₹' + (order.estimated_price || 'TBD'))}
                        ${order.final_price ? createDetailRow('Final Price', '₹' + order.final_price) : ''}
                        ${order.fabric_type ? createDetailRow('Fabric Type', order.fabric_type) : ''}
                        ${order.fabric_color ? createDetailRow('Fabric Color', `${order.fabric_color} <span style="display: inline-block; width: 20px; height: 20px; background-color: ${order.fabric_color}; border: 1px solid #ccc; border-radius: 3px; margin-left: 8px; vertical-align: middle;"></span>`) : ''}
                        ${order.deadline ? createDetailRow('Deadline', formatDate(order.deadline)) : ''}
                        ${order.deposit_amount ? createDetailRow('Deposit', '₹' + order.deposit_amount) : ''}
                        ${order.balance_due ? createDetailRow('Balance Due', '₹' + order.balance_due) : ''}
                    </div>
                </div>
                
                <!-- Tailor Details -->
                <div>
                    <h3><i class="fas fa-store"></i> Tailor Information</h3>
                    <div style="background: #f9fafb; padding: 1rem; border-radius: 8px;">
                        ${order.tailor_info ? `
                            ${createDetailRow('Shop Name', order.tailor_info.shop_name || 'N/A')}
                            ${createDetailRow('Owner', order.tailor_info.owner_name || 'N/A')}
                            ${createDetailRow('Phone', order.tailor_info.phone || 'N/A')}
                            ${createDetailRow('Area', order.tailor_info.area || 'N/A')}
                        ` : '<p style="color: #6b7280;">Tailor information not available</p>'}
                    </div>
                </div>
            </div>
            
            <!-- Final Fitting Date -->
            ${order.final_fitting_date ? `
                <div style="margin-bottom: 2rem;">
                    <h3><i class="fas fa-calendar-check"></i> Final Fitting Scheduled</h3>
                    <div style="background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%); padding: 1.5rem; border-radius: 12px; border: 2px solid #6ee7b7;">
                        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                            <div style="background: white; padding: 1rem; border-radius: 50%; width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-calendar-alt" style="font-size: 1.5rem; color: #10b981;"></i>
                            </div>
                            <div>
                                <strong style="font-size: 1.1rem; color: #065f46;">Final Fitting Date</strong><br>
                                <span style="font-size: 1.2rem; color: #064e3b; font-weight: 600;">${formatDateTime(order.final_fitting_date)}</span>
                            </div>
                        </div>
                        <div style="background: white; padding: 1rem; border-radius: 8px; border-left: 4px solid #10b981;">
                            <p style="color: #065f46; font-size: 0.95rem; margin: 0;">
                                <i class="fas fa-info-circle"></i> <strong>Note:</strong> Please contact the tailor before this date if you need any changes to your garment.
                            </p>
                        </div>
                    </div>
                </div>
            ` : ''}
            
            <!-- Alterations -->
            ${order.alteration_count > 0 ? `
                <div style="margin-bottom: 2rem;">
                    <h3><i class="fas fa-tools"></i> Alterations (${order.alteration_count})</h3>
                    <div style="background: #fef3c7; padding: 1rem; border-radius: 8px;">
                        ${order.alteration_notes || 'No notes available'}
                    </div>
                </div>
            ` : ''}
            
            <!-- Special Instructions -->
            ${order.special_instructions ? `
                <div style="margin-bottom: 2rem;">
                    <h3><i class="fas fa-sticky-note"></i> Special Instructions</h3>
                    <div style="background: #e0e7ff; padding: 1rem; border-radius: 8px;">
                        ${order.special_instructions}
                    </div>
                </div>
            ` : ''}
            
            <!-- Order History -->
            ${history.length > 0 ? `
                <div>
                    <h3><i class="fas fa-history"></i> Order History</h3>
                    <div style="background: #f9fafb; border-radius: 8px; padding: 1rem;">
                        ${createHistoryTimeline(history)}
                    </div>
                </div>
            ` : ''}
            
            <!-- Actions -->
            <div style="margin-top: 2rem; display: flex; gap: 1rem; justify-content: flex-end;">
                ${canCancelOrder(order.order_status) ? `
                    <button onclick="cancelOrderFromModal(${order.id})" style="padding: 0.75rem 1.5rem; background: #ef4444; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                        <i class="fas fa-times"></i> Cancel Order
                    </button>
                ` : ''}
                ${canReorder(order.order_status) ? `
                    <button onclick="reorderFromModal(${order.id})" style="padding: 0.75rem 1.5rem; background: #10b981; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                        <i class="fas fa-redo"></i> Reorder
                    </button>
                ` : ''}
                ${canConfirmDelivery(order.order_status) ? `
                    <button onclick="confirmDelivery(${order.id})" style="padding: 0.75rem 1.5rem; background: #10b981; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                        <i class="fas fa-check"></i> Confirm Delivery
                    </button>
                ` : ''}
                ${canMarkComplete(order.order_status) ? `
                    <button onclick="markOrderComplete(${order.id})" style="padding: 0.75rem 1.5rem; background: #22c55e; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                        <i class="fas fa-check-double"></i> Mark as Completed
                    </button>
                ` : ''}
            </div>
        </div>
    `;

    document.getElementById('orderDetailsContent').innerHTML = html;
}

// Create status timeline
function createStatusTimeline(currentStatus) {
    const workflow = ['pending', 'booked', 'cutting', 'stitching', 'first_fitting', 'alterations', 'final_fitting', 'ready_for_pickup', 'delivered', 'completed'];
    const currentIndex = workflow.indexOf(currentStatus);

    return `
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; background: #f9fafb; border-radius: 8px; overflow-x: auto;">
            ${workflow.map((status, index) => {
        const config = orderStatuses[status];
        const isActive = index <= currentIndex;
        const isCurrent = status === currentStatus;

        return `
                    <div style="display: flex; flex-direction: column; align-items: center; min-width: 80px;">
                        <div style="width: 40px; height: 40px; border-radius: 50%; background: ${isActive ? config.color : '#e5e7eb'}; color: white; display: flex; align-items: center; justify-content: center; margin-bottom: 0.5rem; ${isCurrent ? 'transform: scale(1.2); box-shadow: 0 4px 12px rgba(0,0,0,0.2);' : ''}">
                            <i class="fas fa-${config.icon}"></i>
                        </div>
                        <span style="font-size: 0.75rem; text-align: center; font-weight: ${isCurrent ? 'bold' : 'normal'}; color: ${isActive ? '#1f2937' : '#9ca3af'};">
                            ${config.label}
                        </span>
                    </div>
                    ${index < workflow.length - 1 ? `
                        <div style="flex: 1; height: 2px; background: ${index < currentIndex ? config.color : '#e5e7eb'}; margin: 0 0.5rem 2rem 0.5rem;"></div>
                    ` : ''}
                `;
    }).join('')}
        </div>
    `;
}

// Create history timeline
function createHistoryTimeline(history) {
    return history.map((item, index) => `
        <div style="display: flex; gap: 1rem; padding: 1rem; ${index < history.length - 1 ? 'border-bottom: 1px solid #e5e7eb;' : ''}">
            <div style="flex-shrink: 0;">
                <div style="width: 40px; height: 40px; border-radius: 50%; background: #667eea; color: white; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-${orderStatuses[item.new_status]?.icon || 'circle'}"></i>
                </div>
            </div>
            <div style="flex: 1;">
                <div style="font-weight: 600; color: #1f2937;">
                    ${item.old_status ? `${formatStatus(item.old_status)} → ` : ''}${formatStatus(item.new_status)}
                </div>
                <div style="font-size: 0.9rem; color: #6b7280; margin-top: 0.25rem;">
                    by ${item.changed_by_name} (${item.changed_by_type})
                </div>
                ${item.notes ? `
                    <div style="margin-top: 0.5rem; padding: 0.5rem; background: white; border-radius: 4px; font-size: 0.9rem;">
                        <i class="fas fa-comment"></i> ${item.notes}
                    </div>
                ` : ''}
                <div style="font-size: 0.85rem; color: #9ca3af; margin-top: 0.25rem;">
                    <i class="far fa-clock"></i> ${formatDateTime(item.changed_at)}
                </div>
            </div>
        </div>
    `).join('');
}

// Helper functions
function createDetailRow(label, value) {
    return `
        <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #e5e7eb;">
            <span style="color: #6b7280;">${label}:</span>
            <span style="font-weight: 600; color: #1f2937;">${value}</span>
        </div>
    `;
}

function formatStatus(status) {
    return orderStatuses[status]?.label || status.replace('_', ' ').toUpperCase();
}

function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-IN', { year: 'numeric', month: 'short', day: 'numeric' });
}

function formatDateTime(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleString('en-IN', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function canCancelOrder(status) {
    return ['pending', 'booked'].includes(status);
}

function canReorder(status) {
    return ['completed', 'delivered'].includes(status);
}

function canConfirmDelivery(status) {
    return status === 'ready_for_pickup';
}

function canMarkComplete(status) {
    return status === 'delivered';
}

// Action functions
function cancelOrderFromModal(orderId) {
    if (!confirm('Are you sure you want to cancel this order?')) return;

    const formData = new FormData();
    formData.append('order_id', orderId);
    formData.append('status', 'cancelled');

    fetch('../api/orders/update_status.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Order cancelled successfully');
                closeOrderDetailsModal();
                if (typeof loadOrders === 'function') loadOrders();
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to cancel order');
        });
}

function confirmDelivery(orderId) {
    const formData = new FormData();
    formData.append('order_id', orderId);
    formData.append('status', 'delivered');

    fetch('../api/orders/update_status.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Delivery confirmed!');
                closeOrderDetailsModal();
                if (typeof loadOrders === 'function') loadOrders();
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to confirm delivery');
        });
}

function markOrderComplete(orderId) {
    const formData = new FormData();
    formData.append('order_id', orderId);
    formData.append('status', 'completed');

    fetch('../api/orders/update_status.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Order marked as completed!');
                closeOrderDetailsModal();
                if (typeof loadOrders === 'function') loadOrders();
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to mark order as complete');
        });
}

function reorderFromModal(orderId) {
    alert('Reorder feature coming soon! This will pre-fill the order form with details from order #' + orderId);
    // TODO: Implement reorder functionality
}

// Close order details modal
function closeOrderDetailsModal() {
    const modal = document.getElementById('orderDetailsModal');
    if (modal) {
        modal.remove();
    }
}

// Update the viewOrderDetails function in orders.php
function viewOrderDetails(orderId) {
    showOrderDetailsModal(orderId);
}
