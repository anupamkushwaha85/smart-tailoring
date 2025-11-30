/**
 * Notification System JavaScript
 * Handles notification display, badge updates, and user interactions
 */

let notificationInterval = null;
let isNotificationOpen = false;

// Initialize notifications on page load
document.addEventListener('DOMContentLoaded', function () {
    loadNotifications();

    // Refresh notifications every 30 seconds
    notificationInterval = setInterval(loadNotifications, 30000);

    // Close notification dropdown when clicking outside
    document.addEventListener('click', function (event) {
        const notificationContainer = document.querySelector('.notification-container');
        const notificationDropdown = document.getElementById('notificationDropdown');

        if (notificationContainer && !notificationContainer.contains(event.target)) {
            if (notificationDropdown && notificationDropdown.classList.contains('active')) {
                notificationDropdown.classList.remove('active');
                isNotificationOpen = false;
            }
        }
    });
});

/**
 * Toggle notification dropdown
 */
function toggleNotifications() {
    const dropdown = document.getElementById('notificationDropdown');
    isNotificationOpen = !isNotificationOpen;

    if (isNotificationOpen) {
        dropdown.classList.add('active');
        loadNotifications(); // Refresh notifications when opened
    } else {
        dropdown.classList.remove('active');
    }
}

/**
 * Load notifications from server
 */
function loadNotifications() {
    fetch('api/notifications/get_notifications.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateNotificationBadge(data.unread_count);
                displayNotifications(data.notifications);
            } else if (data.require_login) {
                updateNotificationBadge(0);
                displayLoginPrompt();
            }
        })
        .catch(error => {
            console.error('Error loading notifications:', error);
        });
}

/**
 * Update notification badge count
 */
function updateNotificationBadge(count) {
    const badge = document.getElementById('notificationBadge');

    if (count > 0) {
        badge.textContent = count > 99 ? '99+' : count;
        badge.style.display = 'flex';
    } else {
        badge.style.display = 'none';
    }
}

/**
 * Display notifications in dropdown
 */
function displayNotifications(notifications) {
    const notificationList = document.getElementById('notificationList');

    if (notifications.length === 0) {
        notificationList.innerHTML = `
            <div class="notification-empty">
                <i class="fas fa-bell-slash"></i>
                <p><strong>No notifications</strong></p>
                <p>You're all caught up!</p>
            </div>
        `;
        return;
    }

    let html = '';
    notifications.forEach(notification => {
        const icon = getNotificationIcon(notification.type);
        const unreadClass = notification.is_read ? '' : 'unread';

        html += `
            <div class="notification-item ${unreadClass}" onclick="handleNotificationClick(${notification.id}, '${notification.type}', ${notification.related_id})">
                <div class="notification-title">
                    <i class="${icon}"></i>
                    ${escapeHtml(notification.title)}
                </div>
                <div class="notification-message">
                    ${escapeHtml(notification.message)}
                </div>
                <div class="notification-time">
                    <i class="far fa-clock"></i>
                    ${notification.time_ago}
                </div>
            </div>
        `;
    });

    notificationList.innerHTML = html;
}

/**
 * Display login prompt when user is not logged in
 */
function displayLoginPrompt() {
    const notificationList = document.getElementById('notificationList');

    notificationList.innerHTML = `
        <div class="notification-login-prompt">
            <i class="fas fa-hand-sparkles"></i>
            <h4>Welcome! 👋</h4>
            <p>Please login or signup to view your notifications and stay updated with your orders!</p>
            <button class="notification-login-btn" onclick="openLoginModal(); toggleNotifications();">
                Login & Join Us
            </button>
        </div>
    `;
}

/**
 * Get appropriate icon for notification type
 */
function getNotificationIcon(type) {
    const icons = {
        'order_status': 'fas fa-shopping-bag',
        'order_cancelled': 'fas fa-times-circle',
        'order_accepted': 'fas fa-check-circle',
        'order_in_progress': 'fas fa-cut',
        'order_completed': 'fas fa-star',
        'new_order': 'fas fa-bell',
        'verification': 'fas fa-shield-check',
        'alteration': 'fas fa-ruler',
        'stitching': 'fas fa-thread',
        'default': 'fas fa-info-circle'
    };

    return icons[type] || icons['default'];
}

/**
 * Handle notification click
 */
function handleNotificationClick(notificationId, type, relatedId) {
    // Mark as read
    markNotificationAsRead(notificationId);

    // Navigate based on notification type
    if (type.includes('order') && relatedId) {
        // Get user type from body data attribute
        const userType = document.body.getAttribute('data-user-type');
        if (userType === 'customer') {
            window.location.href = `customer/orders.php?order_id=${relatedId}`;
        } else if (userType === 'tailor') {
            window.location.href = `tailor/orders.php?order_id=${relatedId}`;
        }
    } else if (type === 'verification') {
        window.location.href = 'tailor/profile.php';
    }

    // Close dropdown
    toggleNotifications();
}

/**
 * Mark single notification as read
 */
function markNotificationAsRead(notificationId) {
    fetch('api/notifications/mark_as_read.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ notification_id: notificationId })
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadNotifications(); // Refresh notifications
            }
        })
        .catch(error => {
            console.error('Error marking notification as read:', error);
        });
}

/**
 * Mark all notifications as read
 */
function markAllAsRead() {
    fetch('api/notifications/mark_all_read.php', {
        method: 'POST'
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadNotifications(); // Refresh notifications
            }
        })
        .catch(error => {
            console.error('Error marking all as read:', error);
        });
}

/**
 * Escape HTML to prevent XSS
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Cleanup on page unload
 */
window.addEventListener('beforeunload', function () {
    if (notificationInterval) {
        clearInterval(notificationInterval);
    }
});
