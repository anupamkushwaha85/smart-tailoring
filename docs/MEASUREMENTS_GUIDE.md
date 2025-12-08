# 📏 Measurements System Guide

## Overview
The Smart Tailoring system allows customers to save their body measurements and tailors to access them when working on orders.

---

## 🔄 How It Works

### For Customers:

1. **Save Measurements**
   - Go to `customer/measurements.php`
   - Click "Add New Measurement"
   - Choose garment type (Shirt, Pants, or Full Body)
   - Enter all measurements in inches
   - Save with a label (e.g., "My Regular Fit", "Slim Fit")
   - Mark one as **default** for quick access

2. **When Placing Orders**
   - Create a new order
   - Select which saved measurements to use (optional)
   - The measurements are **captured as a snapshot** at order time
   - Future changes to your saved measurements won't affect existing orders

### For Tailors:

1. **View Order Measurements**
   - Open any order details
   - See the **Measurements Snapshot** section
   - This shows the measurements as they were when the order was placed

2. **View Customer's Saved Measurements**
   - Click the **"View All Saved Measurements"** button
   - See all measurements the customer has saved
   - Compare with the order snapshot
   - Check if customer has updated measurements

---

## 🗂️ Database Structure

### Tables Created:

#### `measurements`
- Stores customer's saved body measurements
- Fields: `label`, `garment_context` (shirt/pants/full), `measurements_data` (JSON), `is_default`
- Customers can save multiple measurements with different labels

#### `orders` (enhanced)
- Added `measurements_snapshot` - JSON copy of measurements at order time
- Added `measurement_id` - Reference to which saved measurement was used

---

## 🔒 Security & Access Control

### Authorization Rules:

1. **Customers**
   - Can only view/edit their own measurements
   - Cannot see other customer's measurements

2. **Tailors**
   - Can view measurements of customers who placed orders with them
   - Use API: `GET /api/measurements/get_measurements.php?customer_id=X`
   - Authorization check: Must have at least one order from that customer

---

## 📊 API Endpoints

### Measurement APIs:

```
POST   /api/measurements/save_measurement.php
GET    /api/measurements/get_measurements.php
GET    /api/measurements/get_measurement.php?id=X
POST   /api/measurements/set_default.php
DELETE /api/measurements/delete_measurement.php?id=X
```

### Key Features:

- **get_measurements.php** with `customer_id` parameter
  - Customers: Returns their own measurements
  - Tailors: Returns specified customer's measurements (with authorization check)

---

## 💡 Use Cases

### Scenario 1: Repeat Customer
1. Customer saves measurements once
2. Places multiple orders over time
3. Each order captures current measurements as snapshot
4. Customer updates measurements after weight change
5. New orders use new measurements, old orders keep original

### Scenario 2: Tailor Needs Reference
1. Customer places order with measurements
2. Tailor starts working on order
3. Tailor views measurements snapshot from order
4. Tailor wants to check if customer has other measurements
5. Clicks "View All Saved Measurements" button
6. Sees customer's complete measurement history

### Scenario 3: Alterations
1. Order goes to "First Fitting"
2. Tailor finds measurements need adjustment
3. Views customer's other saved measurements for reference
4. Records alteration notes
5. Updates order to "Alterations" status

---

## 🎯 Measurement Types

### Shirt Measurements (9 fields):
- Chest, Waist, Shoulder, Sleeve Length, Collar, Shirt Length, Bicep, Wrist, Back Width

### Pants Measurements (8 fields):
- Waist, Hips, Inseam, Outseam, Thigh, Knee, Calf, Bottom Opening

### Full Body (11 fields):
- All shirt + all pants measurements combined

---

## 🚀 Quick Start

### For Customers:
1. Visit `http://localhost/smart/smart-tailoring/customer/measurements.php`
2. Add your first measurement set
3. Use it when creating orders

### For Tailors:
1. Open any order in your dashboard
2. View customer measurements in order details
3. Click "View All Saved Measurements" to see customer's history

---

## ⚠️ Important Notes

1. **Snapshots are immutable** - Changing saved measurements doesn't affect existing orders
2. **One default per context** - Each garment type can have only one default measurement
3. **Authorization required** - Tailors can only view measurements for their customers
4. **JSON storage** - Measurements stored as JSON for flexibility

---

## 🐛 Troubleshooting

### "Failed to load measurements"
- Check if customer has actually saved measurements
- Verify tailor has at least one order from that customer

### "Authorization error"
- Ensure user is logged in
- Customers can only access their own measurements
- Tailors need an order relationship with the customer

### "400 Bad Request" on order history
- ✅ **FIXED**: Updated authorization to allow tailors to view unassigned orders
- Previously failed if `tailor_id` was NULL

---

## 📝 Change Log

### v1.0 (Current)
- ✅ Customer measurements CRUD
- ✅ Measurements snapshot in orders
- ✅ Tailor viewing of customer measurements
- ✅ Authorization fixes for order history
- ✅ Modal interface for measurement browsing

---

**Last Updated:** 2024
**System:** Smart Tailoring Management System
