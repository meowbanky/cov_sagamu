-- Migration: mobile App Store release (FCM push + account deletion)
-- Schema: emmaggic_cofv
-- Date:   2026-08-16
--
-- HOW TO RUN (phpMyAdmin): run STEP 0 first and confirm the output, then run
-- each step below one at a time, checking each succeeds before moving on.
-- phpMyAdmin aborts the whole batch on the first error, which is exactly how a
-- migration half-applies without anyone noticing.
--
-- IDEMPOTENCY: steps 1-3 (CREATE TABLE IF NOT EXISTS) are safe to re-run.
-- Steps 4-6 (ALTER / CREATE INDEX) are NOT — re-running them errors with
-- "Duplicate column name" / "Duplicate key name". That error is harmless and
-- means the step was already applied.


-- ===== STEP 0 — confirm you are on the right database =====
-- Expected output: emmaggic_cofv
SELECT DATABASE() AS current_database;


-- ===== STEP 1 — account deletion requests =====
-- A cooperative member cannot delete their own record while indebted, so
-- deletion is a REQUEST that an officer approves, not a self-service action.
-- Apple permits this for regulated financial services provided the member can
-- always initiate the request in-app (Guideline 5.1.1(v)).
--
-- Kept separate from the member row so it survives the anonymisation that
-- happens on approval.
--
-- status:
--   pending             — no outstanding balance, awaiting officer approval
--   pending_settlement  — member owes money; blocked until settled
--   approved            — anonymisation carried out, access revoked
--   rejected            — officer declined; member keeps their account
CREATE TABLE IF NOT EXISTS tbl_account_deletions (
    id                     INT AUTO_INCREMENT PRIMARY KEY,
    memberid               VARCHAR(32)    NOT NULL,
    requested_at           DATETIME       NOT NULL,
    status                 VARCHAR(32)    NOT NULL DEFAULT 'pending',
    outstanding_at_request DECIMAL(15,2)  NOT NULL DEFAULT 0,
    source                 VARCHAR(32)    NOT NULL DEFAULT 'mobile_app',
    member_note            VARCHAR(500)   NULL,
    reviewed_at            DATETIME       NULL,
    reviewed_by            VARCHAR(64)    NULL,
    review_note            VARCHAR(500)   NULL,
    INDEX idx_account_deletions_member (memberid),
    INDEX idx_account_deletions_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ===== STEP 2 — member complaints raised from the app =====
-- Private between the member and the office; never shown to other members.
CREATE TABLE IF NOT EXISTS tbl_complaints (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    memberid     VARCHAR(32)  NOT NULL,
    category     VARCHAR(64)  NOT NULL,
    subject      VARCHAR(160) NOT NULL,
    body         TEXT         NOT NULL,
    status       VARCHAR(24)  NOT NULL DEFAULT 'open',
    admin_reply  TEXT         NULL,
    created_at   DATETIME     NOT NULL,
    updated_at   DATETIME     NULL,
    INDEX idx_complaints_member (memberid),
    INDEX idx_complaints_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ===== STEP 3 — Paystack payments =====
-- `reference` is generated server-side and is what verify and the webhook key
-- on, so it must be unique. Amounts are stored in kobo to avoid float error.
CREATE TABLE IF NOT EXISTS tbl_payments (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    memberid        VARCHAR(32)  NOT NULL,
    reference       VARCHAR(64)  NOT NULL,
    amount_kobo     BIGINT       NOT NULL,
    purpose         VARCHAR(64)  NOT NULL,
    status          VARCHAR(24)  NOT NULL DEFAULT 'pending',
    paystack_status VARCHAR(32)  NULL,
    paid_at         DATETIME     NULL,
    created_at      DATETIME     NOT NULL,
    updated_at      DATETIME     NULL,
    UNIQUE KEY uniq_payments_reference (reference),
    INDEX idx_payments_member (memberid),
    INDEX idx_payments_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ===== STEP 4 — FCM registration token =====
-- Replaces onesignal_id. No AFTER clause: column ordering is cosmetic and
-- pinning it to another column makes the statement fail if that column moves.
ALTER TABLE tbl_personalinfo
    ADD COLUMN fcm_token VARCHAR(512) NULL DEFAULT NULL;


-- ===== STEP 5 — soft-delete columns (Guideline 5.1.1(v)) =====
-- deleted_at NULL = active account. Financial records stay; PII is scrubbed.
ALTER TABLE tbl_personalinfo
    ADD COLUMN deleted_at DATETIME NULL DEFAULT NULL,
    ADD COLUMN deletion_requested_by VARCHAR(32) NULL DEFAULT NULL;


-- ===== STEP 6 — index the soft-delete flag =====
-- Every login now filters on deleted_at IS NULL.
CREATE INDEX idx_personalinfo_deleted_at ON tbl_personalinfo (deleted_at);


-- ===== STEP 7 — VERIFY (run last, paste me the output) =====
-- Expect: 3 new columns on tbl_personalinfo, and 3 new tables.
SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE
  FROM INFORMATION_SCHEMA.COLUMNS
 WHERE TABLE_SCHEMA = DATABASE()
   AND TABLE_NAME   = 'tbl_personalinfo'
   AND COLUMN_NAME IN ('fcm_token', 'deleted_at', 'deletion_requested_by')
 ORDER BY COLUMN_NAME;

SELECT TABLE_NAME
  FROM INFORMATION_SCHEMA.TABLES
 WHERE TABLE_SCHEMA = DATABASE()
   AND TABLE_NAME IN ('tbl_account_deletions', 'tbl_complaints', 'tbl_payments')
 ORDER BY TABLE_NAME;
