# Chapel of Victory Cooperative Society Management System

A comprehensive software suite for managing the Chapel of Victory (COV) Cooperative Society operations, including membership, finances, loans, and event attendance.

## 📂 Project Structure

This repository contains two main components:

### 1. [COV Admin Panel](./cov_admin) (`cov_admin`)

The core administrative interface for the details about the cooperative's day-to-day operations.

**Key Features:**

- **Member Management**: Complete database of members, registration, and profile updates.
- **Financial Accounting**: Robust double-entry accounting system with General Ledger, Journal Entries, and Trial Balances.
- **Loan Management**: Processing, approval, and tracking of loans (Standard, Special, etc.).
- **Contribution Tracking**: Management of monthly contributions and savings.
- **Bank Reconciliation**: Automated processing of bank statements and reconciliation with internal records.
- **Reporting**: Generation of financial statements, member statements, and dividend reports.
- **Communication**: Integrated email and SMS notification systems.

**Documentation:**

- [Accounting System Guide](./cov_admin/docs/ACCOUNTING_SYSTEM_COMPLETE.md)
- [Bank Statement Processor](./cov_admin/docs/BANK_STATEMENT_SYSTEM_README.md)
- [Deployment Checklist](./cov_admin/docs/FINAL_DEPLOYMENT_CHECKLIST.md)

### 2. [Event Attendance](./event-attendance) (`event-attendance`)

A specialized module for managing event attendance with location-based validation.

**Key Features:**

- **Geofencing**: Validates user presence within a specific radius of the event location using Google Maps API.
- **Device Binding**: Prevents proxy attendance by binding a user's check-in to a specific device.
- **Admin Controls**: Manual override capabilities, grace period configuration, and device lock resets.
- **Reporting**: Excel exports of attendance data.
- **Mobile Integration**: Designed to backend a Flutter mobile application.

**Documentation:**

- [Implementation Guide](./event-attendance/README.md)

---

## 🚀 Getting Started

### Prerequisites

- **PHP**: 7.4 or higher (8.1+ recommended)
- **MySQL**: 5.7 or higher
- **Composer**: For PHP dependency management
- **Node.js & npm**: For building frontend assets (Tailwind CSS)
- **Web Server**: Apache or Nginx

### Installation

1.  **Clone the Repository**

    ```bash
    git clone <repository-url>
    cd cov
    ```

2.  **Setup the Admin Panel**

    ```bash
    cd cov_admin

    # Install PHP dependencies
    composer install

    # Install Frontend dependencies
    npm install

    # Build Tailwind CSS
    npm run build
    ```

3.  **Database Configuration**

    - Create a new MySQL database.
    - Import the initial schema files found in `cov_admin/` (e.g., `SETUP_FULL_ACCOUNTING_SYSTEM.sql`).
    - Copy the configuration example:
      ```bash
      cp config.env.example config.env
      ```
    - Edit `config.env` with your database credentials, API keys (Google Maps, Mailer, etc.).

4.  **Event Attendance Setup**
    - Refer to the [Event Attendance README](./event-attendance/README.md#database-setup) for specific migration files (e.g., `create_events_table.sql`).

## 🛠️ Technologies Used

- **Backend**: Native PHP, Object-Oriented compliant.
- **Frontend**: HTML5, Tailwind CSS, JavaScript.
- **Database**: MySQL.
- **PDF Generation**: TCPDF / DomPDF.
- **Excel Processing**: PhpSpreadsheet.
- **Email**: PHPMailer.

## 👥 Authors

- **Bankole Abiodun (Emmaggi)** - _Lead Developer_

## 📄 License

This project is proprietary software developed for the Chapel of Victory Cooperative Society.
