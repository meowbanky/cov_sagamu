# Event Attendance Feature - Implementation Plan for COV Project

This document outlines how to implement the event attendance feature in the COV Cooperative Management System, adapted from the reference implementation guide.

## 📋 Project Structure Analysis

### Current Project Setup
- **Database**: MySQL with mysqli and PDO support
- **Connection File**: `Connections/cov.php`
- **Database Name**: `emmaggic_cofv`
- **Member Table**: `tbl_personalinfo` (NOT `tblemployees`)
  - Primary Key: `memberid`
  - Fields: `Lname`, `Fname`, `Mname`, `EmailAddress`, etc.
- **Config System**: `config/EnvConfig.php` (already exists)
- **Config File**: `config.env`
- **API Structure**: Files in `api/` folder
- **Admin Auth**: Session-based (`$_SESSION['UserID']`)
- **UI Framework**: Tailwind CSS, jQuery, SweetAlert2
- **Header/Footer**: `header.php` and `footer.php`

### Key Differences from Reference Implementation

| Reference | COV Project |
|-----------|-------------|
| `tblemployees` table | `tbl_personalinfo` table |
| `CoopID` field | `memberid` field |
| `FirstName`, `LastName` | `Fname`, `Lname`, `Mname` |
| `auth_api/api/` folder | `api/` folder |
| `user_coop_id` | `memberid` |

## 🎯 Implementation Steps

### Phase 1: Database Setup

#### Step 1.1: Create Initial Tables
**File**: `database/migrations/create_events_table.sql`

Create this file in `cov_admin/database/migrations/` (create folders if they don't exist).

**Key Adaptations**:
- Foreign key references `tbl_personalinfo(memberid)` instead of `tblemployees(CoopID)`
- Use `user_coop_id` field but it will store `memberid` values

```sql
-- Create events table for event management
CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL COMMENT 'Event title/name',
    description TEXT NULL COMMENT 'Event description',
    start_time DATETIME NOT NULL COMMENT 'Event start date and time',
    end_time DATETIME NOT NULL COMMENT 'Event end date and time',
    location_lat DECIMAL(10, 8) NOT NULL COMMENT 'Event location latitude',
    location_lng DECIMAL(11, 8) NOT NULL COMMENT 'Event location longitude',
    geofence_radius INT NOT NULL DEFAULT 50 COMMENT 'Geofence radius in meters (default 50m)',
    grace_period_minutes INT NOT NULL DEFAULT 20 COMMENT 'Grace period in minutes after event ends (default 20 minutes)',
    created_by VARCHAR(255) NOT NULL COMMENT 'Admin user who created the event',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_start_time (start_time),
    INDEX idx_end_time (end_time),
    INDEX idx_location (location_lat, location_lng)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Events created by admins for attendance tracking';

-- Create event_attendance table for tracking user check-ins
CREATE TABLE IF NOT EXISTS event_attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL COMMENT 'Foreign key to events table',
    user_coop_id VARCHAR(50) NOT NULL COMMENT 'User memberid who checked in (references tbl_personalinfo.memberid)',
    check_in_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'When user checked in',
    check_in_lat DECIMAL(10, 8) NOT NULL COMMENT 'User location latitude when checking in',
    check_in_lng DECIMAL(11, 8) NOT NULL COMMENT 'User location longitude when checking in',
    distance_from_event DECIMAL(10, 2) NOT NULL COMMENT 'Distance from event location in meters',
    device_id VARCHAR(255) NULL COMMENT 'Device identifier used for check-in',
    status ENUM('present', 'late', 'absent') DEFAULT 'present' COMMENT 'Attendance status',
    admin_override TINYINT(1) DEFAULT 0 COMMENT 'Flag indicating if check-in was done by admin override',
    checked_in_by_admin VARCHAR(255) NULL COMMENT 'Admin username who manually checked in the user',
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (user_coop_id) REFERENCES tbl_personalinfo(memberid) ON DELETE CASCADE,
    UNIQUE KEY unique_user_event (event_id, user_coop_id) COMMENT 'One check-in per user per event',
    INDEX idx_event_id (event_id),
    INDEX idx_user_coop_id (user_coop_id),
    INDEX idx_check_in_time (check_in_time),
    INDEX idx_event_device (event_id, device_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='User attendance records for events';
```

**To execute**: Run this SQL file in phpMyAdmin or via command line.

#### Step 1.2: Verify Tables
```sql
-- Check tables exist
DESCRIBE events;
DESCRIBE event_attendance;

-- Verify grace_period_minutes column exists
SHOW COLUMNS FROM events LIKE 'grace_period_minutes';

-- Verify device_id column exists
SHOW COLUMNS FROM event_attendance LIKE 'device_id';
```

### Phase 2: Configuration

#### Step 2.1: Add Google Maps API Key
**File**: `config.env`

Add this line:
```env
GOOGLE_MAPS_API_KEY=your_api_key_here
```

#### Step 2.2: Update EnvConfig.php
**File**: `config/EnvConfig.php`

Add this method after the `getOpenAIKey()` method:

```php
/**
 * Get Google Maps API key
 */
public static function getGoogleMapsApiKey() {
    return self::get('GOOGLE_MAPS_API_KEY', '');
}
```

### Phase 3: Backend API Implementation

#### Step 3.1: Admin Event Management API
**File**: `api/admin/events.php`

**Adaptations**:
- Use `Connections/cov.php` for database connection
- Use `$_SESSION['UserID']` for authentication
- Member queries use `tbl_personalinfo` with `memberid`
- Name concatenation: `CONCAT(Lname, ', ', Fname, ' ', IFNULL(Mname, ''))`

**Endpoints**:
- `POST` - Create event (includes `grace_period_minutes`)
- `GET` - List all events with attendance count
- `GET ?id={id}` - Get event details with full attendance list
- `PUT ?id={id}` - Update event (can update `grace_period_minutes`)
- `DELETE ?id={id}` - Delete event

**Member Name Query Pattern**:
```php
CONCAT(IFNULL(p.Lname, ''), ', ', IFNULL(p.Fname, ''), ' ', IFNULL(p.Mname, '')) as member_name
```

#### Step 3.2: Admin Attendance Management APIs

**Files to create**:

1. **`api/admin/export-attendance.php`**
   - Export attendance to Excel using PhpSpreadsheet
   - Include device_id and admin_override columns

2. **`api/admin/search-members.php`**
   - Search members by name or memberid
   - Query `tbl_personalinfo` table
   - Return up to 20 matches
   - Used for manual check-in member selection

3. **`api/admin/manual-checkin.php`**
   - Manually check in a member
   - Validate memberid exists in `tbl_personalinfo`
   - Set `admin_override = 1`
   - Store `$_SESSION['UserID']` in `checked_in_by_admin`

4. **`api/admin/reset-device-lock.php`**
   - Reset device lock for an event
   - Delete attendance records for a specific device_id

#### Step 3.3: Mobile Event APIs

**Files to create**:

1. **`api/events/list.php`**
   - List events with filters (upcoming/active/past/all)
   - Include `has_checked_in` flag for current user
   - Include `grace_period_minutes`

2. **`api/events/details.php`**
   - Get single event details
   - Include `has_checked_in` flag

3. **`api/events/checkin.php`**
   - Check in to event with device binding
   - Validate time window (start_time to end_time + grace_period_minutes)
   - Validate user hasn't checked in
   - Validate device hasn't been used by another user
   - Calculate distance using Haversine formula
   - Validate location is within geofence_radius
   - Return appropriate error messages

**Note**: Mobile APIs use JWT authentication (check existing `auth_api/api/` files for auth pattern).

### Phase 4: Admin Panel Implementation

#### Step 4.1: Event Management Page
**File**: `event-management.php`

**Features**:
- Event list table with: title, start/end time, location, radius, grace period, attendance count, status
- "Create Event" button opens modal
- Edit/Delete actions for each event
- Click attendance count to view attendance modal
- Google Maps integration for location selection
- Grace period slider (0-120 minutes)
- Geofence radius slider (10-500m)

**Structure** (similar to `mastertransaction.php`):
```php
<?php
session_start();
if (!isset($_SESSION['UserID'])) {
    header("Location:index.php");
    exit;
}
require_once('header.php');
require_once('Connections/cov.php');
?>
<!-- Event management UI here -->
<?php require_once('footer.php'); ?>
```

**Key Functions**:
- `loadEvents()` - Load all events via AJAX
- `createEvent()` - Create new event
- `editEvent(id)` - Edit existing event
- `deleteEvent(id)` - Delete event
- `viewAttendance(eventId)` - Show attendance modal

#### Step 4.2: Event Details Page
**File**: `event-details.php`

**Features**:
- Event information display (including grace period)
- Google Maps showing event location with geofence circle
- Full attendance list table with:
  - Member name (from `tbl_personalinfo`)
  - Member ID (`memberid`)
  - Check-in time
  - Distance from event
  - Device ID (with reset button)
  - Admin override badge
- "Manual Check-in" button
- Manual check-in modal with:
  - Member search field (autocomplete)
  - Clear button (X)
  - Device ID field (optional)
  - Skip location validation checkbox
- Export to Excel button
- Back button to `event-management.php`

#### Step 4.3: Navigation Updates
**File**: `header.php`

Add menu item in sidebar (around line 196):
```php
<li><a href="event-management.php"
        class="flex items-center px-4 py-2 rounded-lg hover:bg-blue-100 <?= $current=='event-management.php'?'sidebar-active':'' ?>"><i
            class="fa fa-calendar-check fa-fw mr-2"></i> Event Management</a></li>
```

### Phase 5: Helper Functions

#### Step 5.1: Distance Calculation Function
**File**: `libs/utils/DistanceCalculator.php` (create if doesn't exist)

```php
<?php
class DistanceCalculator {
    /**
     * Calculate distance between two coordinates using Haversine formula
     * @param float $lat1 Latitude of first point
     * @param float $lon1 Longitude of first point
     * @param float $lat2 Latitude of second point
     * @param float $lon2 Longitude of second point
     * @return float Distance in meters
     */
    public static function calculateDistance($lat1, $lon1, $lat2, $lon2) {
        $earthRadius = 6371000; // meters
        
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        
        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon/2) * sin($dLon/2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        
        return $earthRadius * $c;
    }
}
```

### Phase 6: Dependencies

#### Step 6.1: Install PhpSpreadsheet
```bash
cd cov_admin
composer require phpoffice/phpspreadsheet
```

Or add to `composer.json`:
```json
{
    "require": {
        "phpoffice/phpspreadsheet": "^1.29"
    }
}
```

### Phase 7: Testing Checklist

#### Admin Panel Testing
- [ ] Create event with custom grace period
- [ ] Verify event appears in list with grace period
- [ ] Edit event details including grace period
- [ ] Delete event
- [ ] Click attendance count to view attendance
- [ ] Test manual check-in with member search
- [ ] Test device lock reset
- [ ] Export attendance to Excel
- [ ] Verify Google Maps displays correctly
- [ ] Verify location picker works

#### Mobile API Testing
- [ ] Test events list endpoint
- [ ] Test event details endpoint
- [ ] Test check-in endpoint:
  - [ ] Check-in when within range and time window ✓
  - [ ] Check-in fails when outside range
  - [ ] Check-in fails when too early
  - [ ] Check-in fails when too late (after grace period)
  - [ ] Can't check in twice
  - [ ] Can't use same device for different user

## 🔑 Key Implementation Notes

### Database Adaptations
- **Member Table**: Use `tbl_personalinfo` instead of `tblemployees`
- **Member ID Field**: Use `memberid` (stored in `user_coop_id` column)
- **Member Name**: Use `CONCAT(Lname, ', ', Fname, ' ', IFNULL(Mname, ''))`

### API Authentication
- **Admin APIs**: Session-based (`$_SESSION['UserID']`)
- **Mobile APIs**: JWT authentication (check existing `auth_api/api/auth/login.php` for pattern)

### Error Messages
Keep these consistent with the reference implementation:
- Too Early: "Check-in is only available during the event. Event starts at [time]"
- Too Late: "Check-in period has ended. The grace period expired at [time]"
- Already Checked In: "You have already checked in to this event"
- Device Used: "This device has already been used to check in another user for this event"
- Outside Range: "You are too far from the event location" (with distance)

### File Structure Summary

```
cov_admin/
├── database/
│   └── migrations/
│       └── create_events_table.sql (NEW)
├── api/
│   ├── admin/
│   │   ├── events.php (NEW)
│   │   ├── export-attendance.php (NEW)
│   │   ├── search-members.php (NEW)
│   │   ├── manual-checkin.php (NEW)
│   │   └── reset-device-lock.php (NEW)
│   └── events/
│       ├── list.php (NEW)
│       ├── details.php (NEW)
│       └── checkin.php (NEW)
├── libs/
│   └── utils/
│       └── DistanceCalculator.php (NEW)
├── config/
│   └── EnvConfig.php (UPDATE - add getGoogleMapsApiKey)
├── config.env (UPDATE - add GOOGLE_MAPS_API_KEY)
├── event-management.php (NEW)
├── event-details.php (NEW)
└── header.php (UPDATE - add menu item)
```

## 📝 Next Steps

1. **Review this plan** and confirm adaptations are correct
2. **Create database tables** using the SQL migration file
3. **Set up Google Maps API key** in `config.env`
4. **Implement backend APIs** starting with admin event management
5. **Create admin panel pages** (event-management.php and event-details.php)
6. **Add navigation menu item** in header.php
7. **Test thoroughly** following the testing checklist
8. **Implement mobile APIs** if needed (can be done later)

## 🚀 Ready to Start?

Once you confirm this plan, I can help implement each phase step by step. Let me know if you'd like to start with any specific phase!

