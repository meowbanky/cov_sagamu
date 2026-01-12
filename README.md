# Chapel of Victory (COV) Enterprise Management Suite

> **A robust, full-stack ecosystem for institutional financial management and location-verified attendance tracking.**

[![PHP Version](https://img.shields.io/badge/php-%5E7.4%20%7C%208.1%2B-blue.svg)](https://www.php.net/)
[![License](https://img.shields.io/badge/License-Proprietary-red.svg)]()
[![Architecture](https://img.shields.io/badge/Architecture-Monorepo%20%7C%20Modular-orange.svg)]()

## 📌 Executive Summary

The **COV Management Suite** is a mission-critical platform engineered for the Chapel of Victory Cooperative Society. It integrates a sophisticated **Double-Entry Accounting Engine** with a **Geofenced Attendance System**. The suite is designed to ensure financial transparency, automate complex loan lifecycles, and eliminate proxy attendance through hardware-level device binding.

---

## 🏗 System Architecture

The repository is structured as a modular monorepo, separating administrative core logic from specialized edge services:

### 1. 🏦 COV Admin Core (`/cov_admin`)

The central nervous system of the cooperative. Unlike basic CRUD apps, this module implements a strict **General Ledger (GL)** framework.

- **Financial Engineering:** Implements a full Double-Entry system. Every transaction is journalized, ensuring a real-time, balanced Trial Balance.
- **Loan Lifecycle Automation:** Handles multi-tier loan products (Standard, Special) with automated interest amortization and repayment scheduling.
- **Bank Reconciliation Engine:** A high-performance processor that parses bank statements and reconciles them against internal ledgers to identify discrepancies.
- **Institutional Reporting:** Generates fiscal-grade PDF/Excel reports including Member Equity Statements and Dividend Distributions.

### 2. 📍 Geofenced Attendance Module (`/event-attendance`)

A high-integrity service designed to validate physical presence for corporate/society events.

- **Geofencing Logic:** Leverages the Google Maps API to enforce a strict radius-based check-in (validation occurs server-side to prevent spoofing).
- **Anti-Proxy Security:** Implements **Device Binding** (Hardware Fingerprinting) to ensure one account per physical device, preventing users from checking in for colleagues.
- **Mobile-First API:** Optimized to serve as a high-availability backend for Flutter-based mobile applications.

---

## 🛠 Technical Stack & Implementation

| Layer         | Technology        | Purpose                                                    |
| :------------ | :---------------- | :--------------------------------------------------------- |
| **Backend**   | PHP 8.1+ (OOP)    | Core business logic and financial calculations.            |
| **Frontend**  | Tailwind CSS      | Utility-first responsive UI for administrative dashboards. |
| **Database**  | MySQL 5.7+        | Relational schema optimized for ACID compliance.           |
| **Services**  | Google Maps API   | Location validation and Geofencing.                        |
| **Utilities** | TCPDF / PHPMailer | Secure document generation and transactional alerts.       |

---

## 🚀 Deployment & Configuration

### Infrastructure Requirements

- **Web Server:** Apache/Nginx (with `mod_rewrite` for clean routing).
- **Environment:** PHP 8.1 recommended for JIT performance benefits in financial processing.
- **Dependencies:** Managed via Composer (Backend) and NPM (Frontend assets).

### Installation Workflow

1.  **Initialize Environment:**

    ```bash
    git clone https://github.com/meowbanky/cov_sagamu.git
    cd cov_sagamu
    ```

2.  **Core Backend Setup:**

    ```bash
    cd cov_admin
    composer install
    cp config.env.example config.env # Define DB_CREDENTIALS and API_KEYS
    ```

3.  **Asset Pipeline:**

    ```bash
    npm install
    npm run build # Compiles production-ready Tailwind CSS
    ```

4.  **Database Migration:**
    Execute `SETUP_FULL_ACCOUNTING_SYSTEM.sql` to initialize the chart of accounts and relational constraints.

---

## 🔐 Security & Compliance

- **Financial Integrity:** All ledger entries are immutable; corrections require reversing entries (Journal Vouchers) to maintain an audit trail.
- **RBAC:** Role-Based Access Control ensures that sensitive tasks (e.g., Loan Approval, Bank Reconciliation) are restricted to authorized personnel.
- **Input Validation:** Strict sanitization and Prepared Statements are used throughout the data access layer to mitigate SQL Injection and XSS.

---

## 📄 Documentation Indices

Detailed technical specifications are available in the `/docs` directory:

- [Full Accounting Logic & Schema](./cov_admin/docs/ACCOUNTING_SYSTEM_COMPLETE.md)
- [Bank Statement Processor Logic](./cov_admin/docs/BANK_STATEMENT_SYSTEM_README.md)
- [Geofencing & API Implementation](./event-attendance/README.md)

---

## 👥 Lead Developer

**Bankole Abiodun (Emmaggi)**  
_Senior Software Engineer_

---

_© 2024 Chapel of Victory Cooperative Society. All Rights Reserved. (Proprietary Software)_
