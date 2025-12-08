# 🗺️ Map Integration Feature - Setup Guide

## Overview
This feature adds interactive maps to your Smart Tailoring Service website using **Leaflet.js** and **OpenStreetMap** (completely free, no API keys needed).

---

## 🎯 Features Implemented

### For Customers:
- **View Location Button** on each tailor card
- Click to see tailor's shop location on an interactive map
- Option to open location in Google Maps for directions
- Shows coordinates and shop details

### For Tailors:
- **Set Shop Location** button in profile page
- Get current location automatically using browser geolocation
- Or manually click on map to set location
- Saves latitude/longitude to database

---

## 📋 Installation Steps

### Step 1: Run Database Migration

Execute this SQL to add location columns to the `tailors` table:

```sql
-- Add location columns
ALTER TABLE tailors 
ADD COLUMN latitude DECIMAL(10, 8) NULL COMMENT 'Shop latitude coordinate',
ADD COLUMN longitude DECIMAL(11, 8) NULL COMMENT 'Shop longitude coordinate',
ADD COLUMN location_updated_at DATETIME NULL COMMENT 'When location was last updated',
ADD INDEX idx_location (latitude, longitude);
```

Or run the migration file:
```bash
# In MySQL/PhpMyAdmin
source database/migrations/002_add_tailor_location.sql
```

### Step 2: Verify Files Created

The following files should be in place:

```
smart-tailoring/
├── api/
│   └── tailors/
│       └── save_location.php          # API to save tailor location
├── assets/
│   └── js/
│       └── map-integration.js         # Leaflet map functionality
├── database/
│   └── migrations/
│       └── 002_add_tailor_location.sql # Database migration
```

### Step 3: Test the Feature

1. **As a Tailor:**
   - Login to tailor account
   - Go to Profile page (`/tailor/profile.php`)
   - Click "Set Shop Location" button
   - Allow browser location access OR click on map
   - Click "Save Location"

2. **As a Customer/Visitor:**
   - Go to homepage
   - Find any tailor card
   - Click the location button (📍 icon)
   - View shop location on interactive map

---

## 🔧 Technical Details

### Database Schema

```sql
tailors table additions:
├── latitude DECIMAL(10, 8)           # -90 to 90
├── longitude DECIMAL(11, 8)          # -180 to 180
└── location_updated_at DATETIME      # Timestamp
```

### API Endpoints

#### `POST /api/tailors/save_location.php`
Saves tailor's shop location.

**Request:**
```json
{
  "latitude": 28.6139,
  "longitude": 77.2090
}
```

**Response:**
```json
{
  "success": true,
  "message": "Location saved successfully",
  "data": {
    "latitude": 28.6139,
    "longitude": 77.2090,
    "updated_at": "2025-11-14 10:30:00"
  }
}
```

**Authorization:** Requires tailor login (session-based)

#### `GET /api/get_tailors.php`
Returns all tailors with location data.

**Response includes:**
```json
{
  "success": true,
  "tailors": [
    {
      "id": 1,
      "shop_name": "Sharma Tailors",
      "latitude": 28.6139,
      "longitude": 77.2090,
      "location_updated_at": "2025-11-14 10:30:00"
      // ... other fields
    }
  ]
}
```

### JavaScript Functions

#### For Viewing Location (Customers)
```javascript
showLocationModal(tailorId, tailorName, latitude, longitude)
// Opens modal with map showing tailor's location
```

#### For Setting Location (Tailors)
```javascript
showLocationSetterModal()
// Opens modal allowing tailor to set location
// Provides two options:
// 1. Get current device location
// 2. Click on map to manually select
```

---

## 🎨 UI Components

### View Location Modal
- **Size:** 600px wide, 400px map height
- **Features:**
  - Leaflet map with OpenStreetMap tiles
  - Marker showing shop location
  - Coordinates display
  - "Open in Google Maps" link
  - Responsive design

### Set Location Modal
- **Size:** 700px wide, 400px map height
- **Features:**
  - Interactive map (click to set location)
  - "Get My Location" button (uses geolocation)
  - Live coordinates display
  - "Save Location" button
  - Instructions panel

---

## 🔒 Security Features

1. **Session-based authorization** - Only logged-in tailors can save location
2. **Coordinate validation** - Checks lat/lng are within valid ranges
3. **SQL injection protection** - Uses prepared statements
4. **Error handling** - Proper error messages and logging

---

## 🌐 Browser Compatibility

### Geolocation Support:
- ✅ Chrome 50+
- ✅ Firefox 45+
- ✅ Safari 10+
- ✅ Edge 14+
- ✅ Mobile browsers (iOS Safari, Chrome Mobile)

### Leaflet.js Support:
- ✅ All modern browsers
- ✅ Mobile-friendly
- ✅ Touch-enabled

---

## 🐛 Troubleshooting

### Issue: "Location not available for this tailor"
**Solution:** Tailor hasn't set their location yet. Ask tailor to:
1. Login
2. Go to Profile
3. Click "Set Shop Location"
4. Set and save location

### Issue: Geolocation not working
**Possible causes:**
1. User denied permission
2. HTTP (not HTTPS) - geolocation requires HTTPS in production
3. Browser doesn't support geolocation

**Solutions:**
- Use HTTPS in production
- Fallback: Click on map manually to set location

### Issue: Map not loading
**Check:**
1. Internet connection (Leaflet loads tiles from CDN)
2. Browser console for errors
3. Leaflet CSS and JS are loaded

---

## 🎓 Usage Examples

### Example 1: Set Location Using Current Position
```javascript
// Tailor clicks "Get My Location"
getCurrentLocation();
// Browser asks for permission
// Map centers on current location with marker
// Click "Save Location" to persist
```

### Example 2: Set Location Manually
```javascript
// Tailor clicks anywhere on the map
// Marker appears at clicked position
// Coordinates update
// Click "Save Location" to persist
```

### Example 3: View Tailor Location
```javascript
// Customer clicks location button on tailor card
showLocation(tailorId);
// Fetches tailor data
// Opens modal with map
// Displays marker at shop location
```

---

## 📊 Sample Data

To test with sample coordinates (India major cities):

```sql
-- Delhi
UPDATE tailors SET latitude = 28.6139, longitude = 77.2090 WHERE id = 1;

-- Mumbai
UPDATE tailors SET latitude = 19.0760, longitude = 72.8777 WHERE id = 2;

-- Bangalore
UPDATE tailors SET latitude = 12.9716, longitude = 77.5946 WHERE id = 3;

-- Satna (your city)
UPDATE tailors SET latitude = 24.5765, longitude = 80.8296 WHERE id = 4;
```

---

## 🚀 Future Enhancements (Optional)

1. **Multi-shop support** - Single tailor with multiple shops
2. **Distance calculator** - Show distance from customer
3. **Nearby tailors** - Filter by radius
4. **Route directions** - Integrate full routing
5. **Store hours overlay** - Show open/closed status
6. **Custom map markers** - Shop-specific icons

---

## 📝 Notes

- **No API key required** - OpenStreetMap is completely free
- **No rate limits** - Unlike Google Maps API
- **Lightweight** - Leaflet.js is only ~39KB gzipped
- **Offline fallback** - Can cache map tiles
- **GDPR compliant** - No tracking, no cookies

---

## ✅ Checklist

- [ ] Run database migration
- [ ] Verify all files are uploaded
- [ ] Test as tailor (set location)
- [ ] Test as customer (view location)
- [ ] Check mobile responsiveness
- [ ] Verify HTTPS for geolocation in production

---

**Last Updated:** November 14, 2025  
**Version:** 1.0  
**License:** Free to use (OpenStreetMap ODbL license)
