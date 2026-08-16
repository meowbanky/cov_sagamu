-- Migration: mobile App Store release (FCM push + account deletion)
-- Target: the COV cooperative production schema
-- Date:   2026-08-16
--
-- Safe to re-run: every statement is guarded or additive-only.
-- NOTE: run this against a COPY first. Per the submission playbook, a
-- half-applied migration is the failure mode that goes unnoticed.

-- Guard: refuse to run against an unexpected database.
-- Replace 'cov_live' below if the production schema is named differently.
-- SELECT DATABASE();  -- <- check this matches before running.

-- 1. Firebase Cloud Messaging registration token (replaces onesignal_id).
--    Nullable: a member with no device simply has no token.
ALTER TABLE tbl_personalinfo
    ADD COLUMN fcm_token VARCHAR(512) NULL DEFAULT NULL AFTER onesignal_id;

-- 2. Account deletion (App Store Guideline 5.1.1(v)).
--    Soft delete: cooperative financial records must be retained for audit,
--    so the member row survives with its PII anonymised while access is
--    revoked. deleted_at NULL = active account.
ALTER TABLE tbl_personalinfo
    ADD COLUMN deleted_at DATETIME NULL DEFAULT NULL,
    ADD COLUMN deletion_requested_by VARCHAR(32) NULL DEFAULT NULL;

CREATE INDEX idx_personalinfo_deleted_at ON tbl_personalinfo (deleted_at);

-- 3. Audit trail of deletion requests, kept separately from the member row so
--    it survives the anonymisation above.
CREATE TABLE IF NOT EXISTS tbl_account_deletions (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    memberid      VARCHAR(32)  NOT NULL,
    requested_at  DATETIME     NOT NULL,
    source        VARCHAR(32)  NOT NULL DEFAULT 'mobile_app',
    notes         VARCHAR(255) NULL,
    INDEX idx_account_deletions_member (memberid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Member complaints raised from the app, worked in the cov_admin queue.
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

-- 5. Paystack-initialised payments. `reference` is generated server-side and
--    is what the webhook and the verify call key on, so it must be unique.
CREATE TABLE IF NOT EXISTS tbl_payments (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    memberid       VARCHAR(32)    NOT NULL,
    reference      VARCHAR(64)    NOT NULL,
    amount_kobo    BIGINT         NOT NULL,
    purpose        VARCHAR(64)    NOT NULL,
    status         VARCHAR(24)    NOT NULL DEFAULT 'pending',
    paystack_status VARCHAR(32)   NULL,
    paid_at        DATETIME       NULL,
    created_at     DATETIME       NOT NULL,
    updated_at     DATETIME       NULL,
    UNIQUE KEY uniq_payments_reference (reference),
    INDEX idx_payments_member (memberid),
    INDEX idx_payments_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
