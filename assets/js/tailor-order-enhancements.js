/**
 * Tailor Order Enhancements JavaScript
 * Handles enhanced order workflow features for tailors
 */

// Order status configurations (shared with order-enhancements.js)
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

// Show tailor order details modal
function showTailorOrderDetailsModal(orderId) {
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
                    padding-right: 2rem;
                }
                
                #orderDetailsModal .modal-header .close-modal {
                    width: 32px !important;
                    height: 32px !important;
                    right: 1rem !important;
                    top: 1rem !important;
                    font-size: 1.2rem !important;
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
                    font-size: 0.9rem !important;
                }
                
                .order-details-container h3 {
                    font-size: 1rem !important;
                    margin-bottom: 0.75rem !important;
                }
                
                .order-details-container > div[style*="display: flex; justify-content: space-between; align-items: center"] {
                    overflow-x: auto !important;
                    -webkit-overflow-scrolling: touch !important;
                    padding-bottom: 0.5rem !important;
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
                
                .order-details-container > div[style*="display: flex; gap: 1rem; flex-wrap: wrap"] {
                    flex-direction: column !important;
                    gap: 0.75rem !important;
                }
                
                .order-details-container > div[style*="display: flex; gap: 1rem; flex-wrap: wrap"] button {
                    width: 100% !important;
                    justify-content: center !important;
                }
                
                .order-details-container [style*="display: flex; gap: 1rem"][style*="border-bottom"] {
                    flex-direction: column !important;
                    gap: 0.5rem !important;
                }
                
                .order-details-container [style*="display: flex; gap: 1rem"][style*="border-bottom"] > div:first-child {
                    align-self: flex-start !important;
                }
                
                .order-details-container select,
                .order-details-container textarea {
                    font-size: 0.9rem !important;
                }
                
                .order-details-container [style*="display: grid; gap: 1rem"] {
                    gap: 0.75rem !important;
                }
            }
        </style>
        <div id="orderDetailsModal" class="modal" style="display: flex; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center; overflow-y: auto; padding: 2rem;">
            <div class="modal-content" style="max-width: 1000px; width: 95%; background: white; border-radius: 12px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); max-height: 90vh; overflow-y: auto; margin: auto;">
                <div class="modal-header" style="background: linear-gradient(135deg, #667eea, #764ba2); color: white; padding: 1.5rem; border-radius: 12px 12px 0 0; position: sticky; top: 0; z-index: 10;">
                    <h2 style="margin: 0;"><i class="fas fa-receipt"></i> Order Management</h2>
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

    document.body.insertAdjacentHTML('beforeend', modalHTML);

    // Auto-scroll to modal
    setTimeout(() => {
        const modal = document.getElementById('orderDetailsModal');
        if (modal) {
            modal.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }, 100);

    loadTailorOrderDetails(orderId);
}

// Load tailor order details
function loadTailorOrderDetails(orderId) {
    Promise.all([
        fetch(`../api/orders/get_orders.php`).then(r => {
            if (!r.ok) throw new Error(`Failed to fetch orders: ${r.status}`);
            return r.json();
        }),
        fetch(`../api/orders/get_order_history.php?order_id=${orderId}`).then(r => {
            // Don't throw error if history fails, just return empty
            if (!r.ok) {
                console.warn('Order history failed:', r.status);
                return { success: false, history: [] };
            }
            return r.json();
        })
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
            displayTailorOrderDetails(order, history);
        })
        .catch(error => {
            console.error('Error loading order details:', error);
            document.getElementById('orderDetailsContent').innerHTML = `
            <div style="text-align: center; padding: 2rem;">
                <i class="fas fa-exclamation-triangle" style="font-size: 2rem; color: #ef4444;"></i>
                <p style="color: #ef4444; font-weight: 600;">Failed to load order details</p>
                <p style="color: #6b7280; font-size: 0.875rem;">${error.message}</p>
            </div>
        `;
        });
}

// Display tailor order details
function displayTailorOrderDetails(order, history) {
    const statusConfig = orderStatuses[order.order_status] || orderStatuses.pending;

    // Determine next possible statuses
    const nextStatuses = getNextTailorStatuses(order.order_status);

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
            
            <!-- Status Update Section -->
            <div style="background: #f0f9ff; padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem;">
                <h3><i class="fas fa-exchange-alt"></i> Update Order Status</h3>
                <div style="display: flex; gap: 1rem; margin-top: 1rem; flex-wrap: wrap;">
                    ${nextStatuses.map(status => `
                        <button onclick="updateOrderStatus(${order.id}, '${status}')" 
                                style="padding: 0.75rem 1.5rem; background: ${orderStatuses[status].color}; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.3s;">
                            <i class="fas fa-${orderStatuses[status].icon}"></i> Move to ${orderStatuses[status].label}
                        </button>
                    `).join('')}
                </div>
                <div style="margin-top: 1rem;">
                    <textarea id="statusNotes" placeholder="Add notes (optional)" style="width: 100%; padding: 0.75rem; border: 2px solid #e5e7eb; border-radius: 8px; min-height: 60px;"></textarea>
                </div>
            </div>
            
            <!-- Order Progress Timeline -->
            <div style="margin-bottom: 2rem;">
                <h3><i class="fas fa-route"></i> Order Progress</h3>
                ${createStatusTimeline(order.order_status)}
            </div>
            
            <!-- Information Grid -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
                <!-- Customer Information -->
                <div>
                    <h3><i class="fas fa-user"></i> Customer Information</h3>
                    <div style="background: #f9fafb; padding: 1rem; border-radius: 8px;">
                        ${createDetailRow('Name', order.customer_info.name)}
                        ${createDetailRow('Phone', order.customer_info.phone)}
                        ${createDetailRow('Address', order.customer_info.address || 'N/A')}
                    </div>
                </div>
                
                <!-- Order Details -->
                <div>
                    <h3><i class="fas fa-info-circle"></i> Order Details</h3>
                    <div style="background: #f9fafb; padding: 1rem; border-radius: 8px;">
                        ${createDetailRow('Service', order.service_type)}
                        ${createDetailRow('Garment', order.garment_type)}
                        ${createDetailRow('Quantity', order.quantity)}
                        ${createDetailRow('Estimated Price', '₹' + (order.estimated_price || 'TBD'))}
                        ${order.fabric_type ? createDetailRow('Fabric', order.fabric_type) : ''}
                        ${order.fabric_color ? createDetailRow('Color', `${order.fabric_color} <span style="display: inline-block; width: 20px; height: 20px; background-color: ${order.fabric_color}; border: 1px solid #ccc; border-radius: 3px; margin-left: 8px; vertical-align: middle;"></span>`) : ''}
                    </div>
                </div>
            </div>
            
            <!-- Measurements Snapshot -->
            ${order.measurements_snapshot ? `
                <div style="margin-bottom: 2rem;">
                    <h3><i class="fas fa-ruler"></i> Customer Measurements (Order Snapshot)</h3>
                    <div style="background: #fef3c7; padding: 1rem; border-radius: 8px; display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 0.5rem;">
                        ${createMeasurementsDisplay(order.measurements_snapshot)}
                    </div>
                    <button onclick="viewCustomerMeasurements(${order.customer_id}, '${order.customer_info.name}')" style="margin-top: 1rem; padding: 0.75rem 1.5rem; background: #8b5cf6; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                        <i class="fas fa-ruler-combined"></i> View All Saved Measurements
                    </button>
                </div>
            ` : `
                <div style="margin-bottom: 2rem;">
                    <h3><i class="fas fa-ruler"></i> Customer Measurements</h3>
                    <div style="background: #fef3c7; padding: 1rem; border-radius: 8px; text-align: center;">
                        <p>No measurements snapshot attached to this order.</p>
                        <button onclick="viewCustomerMeasurements(${order.customer_id}, '${order.customer_info.name}')" style="margin-top: 1rem; padding: 0.75rem 1.5rem; background: #8b5cf6; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                            <i class="fas fa-ruler-combined"></i> View Customer's Saved Measurements
                        </button>
                    </div>
                </div>
            `}
            
            <!-- Fitting Management -->
            <div style="margin-bottom: 2rem;">
                <h3><i class="fas fa-calendar-check"></i> Fitting Schedule</h3>
                <div style="background: #f0f9ff; padding: 1rem; border-radius: 8px; border: 2px solid #bae6fd; margin-bottom: 1rem;">
                    <p style="color: #0369a1; margin-bottom: 0.5rem;"><i class="fas fa-info-circle"></i> <strong>Important:</strong></p>
                    <p style="color: #0c4a6e; font-size: 0.9rem;">Set the final fitting date so customers know when to contact you for any last-minute changes before delivery.</p>
                </div>
                <div>
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Final Fitting Date</label>
                    <input type="datetime-local" id="finalFittingDate" value="${order.final_fitting_date ? formatDateTimeInput(order.final_fitting_date) : ''}" style="width: 100%; padding: 0.75rem; border: 2px solid #e5e7eb; border-radius: 8px;">
                </div>
                <button onclick="updateFittingDates(${order.id})" style="margin-top: 1rem; padding: 0.75rem 1.5rem; background: #3b82f6; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                    <i class="fas fa-save"></i> Save Fitting Date
                </button>
            </div>
            
            <!-- Alteration Management -->
            <div style="margin-bottom: 2rem;">
                <h3><i class="fas fa-tools"></i> Alteration Notes</h3>
                <div style="background: #f9fafb; padding: 1rem; border-radius: 8px;">
                    <p><strong>Alteration Count:</strong> ${order.alteration_count || 0}</p>
                    ${order.alteration_notes ? `<p><strong>Previous Notes:</strong><br>${order.alteration_notes.replace(/\n/g, '<br>')}</p>` : ''}
                    <textarea id="newAlterationNote" placeholder="Add new alteration note" style="width: 100%; padding: 0.75rem; border: 2px solid #e5e7eb; border-radius: 8px; min-height: 80px; margin-top: 0.5rem;"></textarea>
                    <button onclick="recordAlteration(${order.id})" style="margin-top: 1rem; padding: 0.75rem 1.5rem; background: #f97316; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                        <i class="fas fa-plus"></i> Record Alteration
                    </button>
                </div>
            </div>
            
            <!-- Special Instructions -->
            ${order.special_instructions ? `
                <div style="margin-bottom: 2rem;">
                    <h3><i class="fas fa-sticky-note"></i> Special Instructions from Customer</h3>
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
        </div>
    `;

    document.getElementById('orderDetailsContent').innerHTML = html;
}

// Get next possible statuses for tailor
function getNextTailorStatuses(currentStatus) {
    const transitions = {
        'pending': ['booked', 'cancelled'],
        'booked': ['cutting'],
        'cutting': ['stitching'],
        'stitching': ['first_fitting'],
        'first_fitting': ['alterations', 'final_fitting'],
        'alterations': ['final_fitting'],
        'final_fitting': ['alterations', 'ready_for_pickup'],
        'ready_for_pickup': ['delivered'],
        'delivered': ['completed']
    };

    return transitions[currentStatus] || [];
}

// Update order status (tailor)
function updateOrderStatus(orderId, newStatus) {
    const notes = document.getElementById('statusNotes')?.value || '';

    if (!confirm(`Move order to ${orderStatuses[newStatus].label}?`)) return;

    const formData = new FormData();
    formData.append('order_id', orderId);
    formData.append('status', newStatus);
    if (notes) formData.append('notes', notes);

    fetch('../api/orders/update_status.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Order status updated successfully!');
                closeOrderDetailsModal();
                if (typeof loadOrders === 'function') loadOrders();
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to update status');
        });
}

// Update fitting dates
function updateFittingDates(orderId) {
    const finalFittingDate = document.getElementById('finalFittingDate').value;

    if (!finalFittingDate) {
        alert('Please select a final fitting date');
        return;
    }

    const formData = new FormData();
    formData.append('order_id', orderId);
    formData.append('final_fitting_date', finalFittingDate);

    fetch('../api/orders/update_fitting_date.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Final fitting date updated successfully');
                closeOrderDetailsModal();
                loadOrders(); // Reload orders
            } else {
                alert(data.message || 'Failed to update fitting date');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating fitting date');
        });
}

// Record alteration
function recordAlteration(orderId) {
    const notes = document.getElementById('newAlterationNote').value.trim();

    if (!notes) {
        alert('Please enter alteration notes');
        return;
    }

    // TODO: Implement API endpoint for recording alterations
    alert('Alteration recording API endpoint needs to be created');
}

// Helper: Create measurements display
function createMeasurementsDisplay(measurementsJson) {
    try {
        const measurements = typeof measurementsJson === 'string' ? JSON.parse(measurementsJson) : measurementsJson;
        return Object.entries(measurements).map(([key, value]) => `
            <div style="background: white; padding: 0.5rem; border-radius: 4px;">
                <strong>${key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}:</strong> ${value}"
            </div>
        `).join('');
    } catch (e) {
        return '<p>Invalid measurement data</p>';
    }
}

// View customer's saved measurements
function viewCustomerMeasurements(customerId, customerName) {
    // Create modal for measurements
    const modalHTML = `
        <div id="customerMeasurementsModal" class="modal" style="display: flex; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 10001; align-items: center; justify-content: center; overflow-y: auto; padding: 2rem;">
            <div class="modal-content" style="max-width: 900px; width: 95%; background: white; border-radius: 12px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); max-height: 90vh; overflow-y: auto; margin: auto;">
                <div class="modal-header" style="background: linear-gradient(135deg, #8b5cf6, #ec4899); color: white; padding: 1.5rem; border-radius: 12px 12px 0 0; position: sticky; top: 0; z-index: 10;">
                    <h2 style="margin: 0;"><i class="fas fa-ruler-combined"></i> ${customerName}'s Saved Measurements</h2>
                    <button class="close-modal" onclick="closeCustomerMeasurementsModal()" style="position: absolute; right: 1.5rem; top: 1.5rem; background: rgba(255,255,255,0.2); border: none; color: white; font-size: 1.5rem; cursor: pointer; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">&times;</button>
                </div>
                <div id="customerMeasurementsContent" style="padding: 2rem;">
                    <div style="text-align: center; padding: 3rem;">
                        <i class="fas fa-spinner fa-spin" style="font-size: 2rem; color: #667eea;"></i>
                        <p>Loading measurements...</p>
                    </div>
                </div>
            </div>
        </div>
    `;

    document.body.insertAdjacentHTML('beforeend', modalHTML);

    // Auto-scroll to modal
    setTimeout(() => {
        const modal = document.getElementById('customerMeasurementsModal');
        if (modal) {
            modal.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }, 100);

    // Fetch customer measurements
    fetch(`../api/measurements/get_measurements.php?customer_id=${customerId}`)
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                throw new Error(data.message || 'Failed to load measurements');
            }

            const measurements = data.measurements || [];

            if (measurements.length === 0) {
                document.getElementById('customerMeasurementsContent').innerHTML = `
                    <div style="text-align: center; padding: 2rem;">
                        <i class="fas fa-ruler" style="font-size: 3rem; color: #d1d5db;"></i>
                        <p style="margin-top: 1rem; color: #6b7280;">This customer has no saved measurements yet.</p>
                    </div>
                `;
                return;
            }

            // Display measurements
            const html = measurements.map(m => {
                const measurementsData = typeof m.measurements_data === 'string'
                    ? JSON.parse(m.measurements_data)
                    : m.measurements_data;

                return `
                    <div style="background: ${m.is_default ? '#e0e7ff' : '#f9fafb'}; padding: 1.5rem; border-radius: 8px; margin-bottom: 1rem; border: ${m.is_default ? '2px solid #667eea' : '2px solid #e5e7eb'};">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                            <h3 style="margin: 0;">
                                ${m.label}
                                ${m.is_default ? '<span style="background: #667eea; color: white; padding: 0.25rem 0.75rem; border-radius: 50px; font-size: 0.75rem; margin-left: 0.5rem;">DEFAULT</span>' : ''}
                            </h3>
                            <span style="color: #6b7280; font-size: 0.875rem;">
                                ${m.garment_context.replace(/_/g, ' ').toUpperCase()}
                            </span>
                        </div>
                        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 1rem;">
                            ${Object.entries(measurementsData).map(([key, value]) => `
                                <div style="background: white; padding: 0.75rem; border-radius: 4px;">
                                    <div style="font-size: 0.75rem; color: #6b7280; text-transform: uppercase;">${key.replace(/_/g, ' ')}</div>
                                    <div style="font-size: 1.25rem; font-weight: 700; color: #1f2937;">${value}"</div>
                                </div>
                            `).join('')}
                        </div>
                        <div style="margin-top: 1rem; font-size: 0.875rem; color: #6b7280;">
                            <i class="fas fa-clock"></i> Created: ${formatDateTime(m.created_at)}
                        </div>
                    </div>
                `;
            }).join('');

            document.getElementById('customerMeasurementsContent').innerHTML = html;
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('customerMeasurementsContent').innerHTML = `
                <div style="text-align: center; padding: 2rem;">
                    <i class="fas fa-exclamation-triangle" style="font-size: 2rem; color: #ef4444;"></i>
                    <p>Failed to load measurements: ${error.message}</p>
                </div>
            `;
        });
}

// Close customer measurements modal
function closeCustomerMeasurementsModal() {
    const modal = document.getElementById('customerMeasurementsModal');
    if (modal) {
        modal.remove();
    }
}

// Helper: Format datetime for input
function formatDateTimeInput(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toISOString().slice(0, 16);
}

// Update viewOrderDetails for tailors
function viewOrderDetails(orderId) {
    showTailorOrderDetailsModal(orderId);
}

// Helper functions (shared)
function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-IN', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
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

function createDetailRow(label, value) {
    return `
        <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #e5e7eb;">
            <span style="color: #6b7280; font-weight: 600;">${label}:</span>
            <span style="color: #1f2937;">${value || 'N/A'}</span>
        </div>
    `;
}

function createStatusTimeline(currentStatus) {
    const statuses = ['pending', 'booked', 'cutting', 'stitching', 'first_fitting', 'alterations', 'final_fitting', 'ready_for_pickup', 'delivered', 'completed'];
    const currentIndex = statuses.indexOf(currentStatus);

    return `
        <div style="display: flex; align-items: center; gap: 0.5rem; overflow-x: auto; padding: 1rem 0;">
            ${statuses.map((status, index) => {
        const config = orderStatuses[status];
        const isPast = index < currentIndex;
        const isCurrent = index === currentIndex;
        const isFuture = index > currentIndex;

        return `
                    <div style="flex: 1; text-align: center; position: relative;">
                        <div style="width: 40px; height: 40px; margin: 0 auto; border-radius: 50%; background: ${isCurrent ? config.color : isPast ? '#10b981' : '#e5e7eb'}; color: white; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                            <i class="fas fa-${config.icon}"></i>
                        </div>
                        <div style="margin-top: 0.5rem; font-size: 0.75rem; color: ${isCurrent ? config.color : '#6b7280'}; font-weight: ${isCurrent ? '700' : '400'};">
                            ${config.label}
                        </div>
                        ${index < statuses.length - 1 ? `
                            <div style="position: absolute; top: 20px; left: 60%; width: 100%; height: 2px; background: ${isPast ? '#10b981' : '#e5e7eb'};"></div>
                        ` : ''}
                    </div>
                `;
    }).join('')}
        </div>
    `;
}

function createHistoryTimeline(history) {
    if (!history || history.length === 0) {
        return '<p style="color: #6b7280;">No history available</p>';
    }

    return history.map(item => {
        const statusConfig = orderStatuses[item.new_status] || orderStatuses.pending;
        return `
            <div style="border-left: 3px solid ${statusConfig.color}; padding-left: 1rem; margin-bottom: 1rem;">
                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                    <i class="fas fa-${statusConfig.icon}" style="color: ${statusConfig.color};"></i>
                    <strong>${statusConfig.label}</strong>
                </div>
                <div style="color: #6b7280; font-size: 0.875rem;">
                    ${formatDateTime(item.created_at)}
                </div>
                ${item.changed_by_name ? `
                    <div style="color: #6b7280; font-size: 0.875rem;">
                        By: ${item.changed_by_name}
                    </div>
                ` : ''}
                ${item.notes ? `
                    <div style="margin-top: 0.5rem; padding: 0.5rem; background: #f9fafb; border-radius: 4px; font-size: 0.875rem;">
                        ${item.notes}
                    </div>
                ` : ''}
            </div>
        `;
    }).join('');
}

function closeOrderDetailsModal() {
    const modal = document.getElementById('orderDetailsModal');
    if (modal) {
        modal.remove();
    }
}
