-- Create events table for event management
-- Event Attendance Feature - Database Migration
-- COV Cooperative Management System

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
    user_coop_id INT NOT NULL COMMENT 'User memberid who checked in (references tbl_personalinfo.memberid)',
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

