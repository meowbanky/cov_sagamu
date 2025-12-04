# Event Attendance Feature - Files to Upload

This document lists all files that need to be uploaded for the Event Attendance feature implementation.

## 📋 Upload Checklist

### ✅ Already Accepted (Don't need to upload again)
- `config.env` (modified - added GOOGLE_MAPS_API_KEY)
- `config/EnvConfig.php` (modified - added getGoogleMapsApiKey method)
- `header.php` (modified - added Event Management menu item)

---

## 🗄️ Database Files

### New Files:
- [ ] `database/migrations/create_events_table.sql`
  - **Action Required**: Execute this SQL file in phpMyAdmin or via command line
  - Creates `events` and `event_attendance` tables

---

## 📁 Backend API Files

### Admin APIs (New Files):
- [ ] `api/admin/events.php`
  - Event CRUD operations (Create, Read, Update, Delete)

- [ ] `api/admin/export-attendance.php`
  - Export attendance to Excel

- [ ] `api/admin/search-members.php`
  - Member search for manual check-in

- [ ] `api/admin/manual-checkin.php`
  - Admin manual check-in with override

- [ ] `api/admin/reset-device-lock.php`
  - Reset device lock for events

### Mobile APIs (New Files):
- [ ] `api/events/list.php`
  - List events with filters (upcoming/active/past/all)

- [ ] `api/events/details.php`
  - Get single event details

- [ ] `api/events/checkin.php`
  - Location-based check-in with device binding

---

## 🎨 Admin Panel Pages

### New Files:
- [ ] `event-management.php`
  - Main event management page
  - Event list, create/edit event modal
  - Google Maps integration
  - Grace period slider
  - Attendance modal

- [ ] `event-details.php`
  - Event details page
  - Full attendance list
  - Manual check-in modal
  - Device lock reset
  - Google Maps display
  - Excel export button

---

## 🛠️ Helper/Utility Files

### New Files:
- [ ] `libs/utils/DistanceCalculator.php`
  - Distance calculation using Haversine formula

---

## 📝 Documentation Files

### New Files (Optional - for reference):
- [ ] `EVENT_ATTENDANCE_IMPLEMENTATION_PLAN.md`
  - Implementation guide and documentation

- [ ] `EVENT_ATTENDANCE_UPLOAD_LIST.md` (this file)
  - Upload checklist

---

## 📊 Summary

### Total New Files: 12 files
- 1 Database migration SQL file
- 5 Admin API files
- 3 Mobile API files
- 2 Admin panel pages
- 1 Helper class
- (Optional: 2 Documentation files)

### Modified Files: 3 files (already accepted)
- `config.env`
- `config/EnvConfig.php`
- `header.php`

---

## 🚀 Upload Instructions

1. **Upload all files** maintaining the folder structure:
   ```
   cov_admin/
   ├── database/migrations/create_events_table.sql
   ├── api/admin/
   │   ├── events.php
   │   ├── export-attendance.php
   │   ├── search-members.php
   │   ├── manual-checkin.php
   │   └── reset-device-lock.php
   ├── api/events/
   │   ├── list.php
   │   ├── details.php
   │   └── checkin.php
   ├── libs/utils/DistanceCalculator.php
   ├── event-management.php
   └── event-details.php
   ```

2. **Execute database migration**:
   - Open phpMyAdmin or use command line
   - Select your database (`emmaggic_cofv`)
   - Import/Execute: `database/migrations/create_events_table.sql`

3. **Set Google Maps API Key**:
   - Edit `config.env` on server
   - Add: `GOOGLE_MAPS_API_KEY=your_actual_api_key_here`

4. **Verify PhpSpreadsheet is installed**:
   - Run: `composer require phpoffice/phpspreadsheet` (if not already installed)
   - Or ensure `vendor/phpoffice/phpspreadsheet` exists

---

## ✅ Post-Upload Checklist

- [ ] All files uploaded with correct folder structure
- [ ] Database tables created (`events` and `event_attendance`)
- [ ] Google Maps API key configured in `config.env`
- [ ] PhpSpreadsheet library available
- [ ] Test event creation from admin panel
- [ ] Test manual check-in functionality
- [ ] Test Excel export feature
- [ ] Verify navigation menu shows "Event Management"

---

## 🔧 Quick Test Steps

1. Login to admin panel
2. Click "Event Management" in sidebar
3. Click "Create Event" button
4. Fill in event details, select location on map
5. Set grace period and geofence radius
6. Save event
7. Click attendance count to view attendance
8. Test manual check-in feature
9. Export attendance to Excel

---

**Note**: Make sure to backup your database before running the migration SQL file!

