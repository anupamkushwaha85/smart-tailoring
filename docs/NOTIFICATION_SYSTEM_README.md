# 🔔 Notification System - Smart Tailoring Service

## Overview
A comprehensive real-time notification system that keeps customers and tailors informed about order status changes, new orders, and verification updates.

## ✨ Features

### For Customers
- **Order Status Notifications**: Get notified through all 11 stages of order processing:
  1. Pending (Order Placed)
  2. Accepted (Tailor Confirmed)
  3. Measurement Taken
  4. Fabric Received
  5. Cutting
  6. Stitching
  7. Fitting
  8. Alteration
  9. Finishing
  10. Quality Check
  11. Completed
  
- **Cancellation Alerts**: Instant notification if order is cancelled
- **Visual Badge Counter**: See unread notification count at a glance
- **Click-to-Navigate**: Click notification to go directly to the relevant order

### For Tailors
- **New Order Alerts**: Get notified immediately when a customer places an order
- **Verification Updates**: Know when your shop is verified or pending
- **Cancellation Notices**: Get informed when customers cancel orders
- **Badge Counter**: Track unread notifications

### General Features
- **🔐 Login Prompt**: Non-logged-in users see a welcoming message encouraging signup
- **⚡ Auto-refresh**: Notifications refresh every 30 seconds
- **📱 Responsive Design**: Works beautifully on desktop and mobile
- **🎨 Beautiful UI**: Modern, gradient-themed dropdown with smooth animations
- **✅ Mark as Read**: Individual or bulk "mark all as read" functionality
- **⏰ Time Stamps**: Shows relative time (e.g., "2 minutes ago", "Yesterday")

## 📂 File Structure

```
smart-tailoring/
├── api/
│   └── notifications/
│       ├── get_notifications.php      # Fetch user notifications
│       ├── mark_as_read.php          # Mark single notification as read
│       └── mark_all_read.php         # Mark all notifications as read
│
├── services/
│   └── NotificationService.php       # Core notification logic
│
├── assets/
│   ├── css/
│   │   └── style.css                 # Notification UI styles
│   └── js/
│       └── notifications.js          # Client-side notification handling
│
├── database/
│   └── migrations/
│       ├── create_notifications_table.sql
│       └── run_notification_migration.php
│
└── index.php                         # Notification icon in navbar
```

## 🗄️ Database Schema

```sql
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    user_type ENUM('customer', 'tailor') NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type VARCHAR(50) NOT NULL,
    related_id INT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_notifications (user_id, user_type, is_read),
    INDEX idx_created_at (created_at)
);
```

## 🚀 Installation

### Step 1: Run Database Migration
Navigate to: `http://localhost/smart-tailoring/database/migrations/run_notification_migration.php`

This will:
- Create the notifications table
- Display success confirmation
- Show current statistics

### Step 2: Verify Installation
1. Refresh your homepage
2. Look for the bell icon (🔔) in the navigation bar (left of Login & Register button)
3. If logged in, you should see your notifications
4. If logged out, clicking shows a welcome message

## 🎯 Usage

### Automatic Notifications
Notifications are created automatically when:

1. **Customer places an order** → Tailor receives "New Order" notification
2. **Tailor updates order status** → Customer receives status update notification
3. **Order is cancelled** → Both parties get notified
4. **Admin verifies tailor** → Tailor receives verification notification

### Manual Notification Creation
Use the `NotificationService` class:

```php
require_once 'services/NotificationService.php';

$notificationService = new NotificationService($conn);

// Create custom notification
$notificationService->createNotification(
    $user_id,           // User ID
    'customer',         // 'customer' or 'tailor'
    'Custom Title',     // Notification title
    'Custom message',   // Notification message
    'custom_type',      // Notification type
    $order_id           // Optional: Related ID
);

// Or use helper methods
$notificationService->notifyOrderStatus($customer_id, $order_id, 'stitching', 'ABC Tailors');
$notificationService->notifyNewOrder($tailor_id, $order_id, 'John Doe', 'Shirt');
$notificationService->notifyVerification($tailor_id, true);
```

## 🎨 UI Components

### Notification Icon
- **Location**: Navigation bar, left of Login & Register button
- **Badge**: Shows unread count (1-99+)
- **Animation**: Subtle pulse animation on badge
- **Colors**: Primary color theme with gradient on hover

### Notification Dropdown
- **Width**: 380px desktop, 90vw mobile
- **Max Height**: 500px with scrolling
- **Features**:
  - Header with "Mark all as read" button
  - Scrollable notification list
  - Empty state message
  - Login prompt for non-authenticated users

### Notification Item
- **Visual Indicators**:
  - Blue left border for unread notifications
  - Light blue background for unread
  - Icon based on notification type
  - Relative timestamp
- **Hover Effect**: Subtle background color change
- **Click Action**: Navigate to related page and mark as read

## 📱 Responsive Design

### Desktop View
- Full-width navigation with all elements visible
- 380px dropdown width
- Notification icon next to admin button

### Mobile View (≤768px)
- Compact notification icon (40px circular)
- Dropdown width: 90vw (max 350px)
- Smaller badge (18px)
- Touch-friendly tap targets

## 🔧 Customization

### Add New Notification Type
1. Update notification icon mapping in `notifications.js`:
```javascript
const icons = {
    'your_new_type': 'fas fa-your-icon',
    // ... existing types
};
```

2. Create notification using service:
```php
$notificationService->createNotification(
    $user_id, 
    $user_type, 
    'Title', 
    'Message', 
    'your_new_type',
    $related_id
);
```

### Customize Notification Messages
Edit the message arrays in `NotificationService.php`:
```php
$messages = [
    'your_status' => [
        'title' => 'Your Title',
        'message' => 'Your message',
        'type' => 'notification_type'
    ]
];
```

### Change Refresh Interval
In `notifications.js`, modify:
```javascript
// Default: 30 seconds (30000ms)
notificationInterval = setInterval(loadNotifications, 30000);
```

## 🔍 API Endpoints

### GET /api/notifications/get_notifications.php
**Response:**
```json
{
    "success": true,
    "unread_count": 5,
    "notifications": [
        {
            "id": 1,
            "title": "Order Accepted! 🎉",
            "message": "Great news! ABC Tailors has accepted your order",
            "type": "order_accepted",
            "related_id": 123,
            "is_read": false,
            "created_at": "2025-11-30 10:30:00",
            "time_ago": "2 hours ago"
        }
    ]
}
```

### POST /api/notifications/mark_as_read.php
**Request:**
```json
{
    "notification_id": 1
}
```

### POST /api/notifications/mark_all_read.php
No request body needed. Marks all notifications as read for the logged-in user.

## 🎭 Notification Types

| Type | Icon | Used For |
|------|------|----------|
| `order_status` | 🛍️ | General order updates |
| `order_accepted` | ✅ | Order acceptance |
| `order_cancelled` | ❌ | Order cancellation |
| `order_in_progress` | ✂️ | Processing stages |
| `order_completed` | ⭐ | Order completion |
| `new_order` | 🔔 | New order for tailor |
| `verification` | 🛡️ | Tailor verification |
| `alteration` | 📏 | Alteration stage |
| `stitching` | 🧵 | Stitching stage |

## 🐛 Troubleshooting

### Notifications not showing?
1. Check if database migration ran successfully
2. Verify `notifications` table exists in database
3. Check browser console for JavaScript errors
4. Ensure you're logged in
5. Try refreshing the page

### Badge count incorrect?
- The badge updates every 30 seconds
- Click "Mark all as read" to reset
- Check database for orphaned notifications

### Notification icon not visible?
1. Clear browser cache
2. Check if `notifications.js` is loaded
3. Verify CSS file includes notification styles
4. Check browser developer tools for 404 errors

## 📊 Performance

- **Auto-refresh**: Every 30 seconds (configurable)
- **Query Optimization**: Indexed database queries
- **Lazy Loading**: Only fetches last 20 notifications
- **Efficient Updates**: Individual or bulk read marking

## 🔐 Security

- ✅ Session-based authentication required
- ✅ User can only see their own notifications
- ✅ SQL injection protection (prepared statements)
- ✅ XSS prevention (HTML escaping)
- ✅ Authorization checks on all API endpoints

## 🎯 Future Enhancements (Optional)

- [ ] Push notifications (browser)
- [ ] Email notifications
- [ ] SMS notifications
- [ ] Notification preferences/settings
- [ ] Notification sound
- [ ] Group notifications by type
- [ ] Search/filter notifications
- [ ] Pagination for older notifications
- [ ] Export notification history

## 📝 Notes

- Notifications are stored permanently (consider adding cleanup job for old notifications)
- Mobile view ready but can be further customized per your requirements
- All notification triggers are integrated into existing order workflow
- No breaking changes to existing functionality

## 🙏 Support

For issues or questions about the notification system, please check:
1. This README file
2. Database migration results
3. Browser console for errors
4. PHP error logs

---

**Created by Team Anupam Kushwaha** | Smart Tailoring Service
