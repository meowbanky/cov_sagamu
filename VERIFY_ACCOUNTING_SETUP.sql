-- ============================================================================
-- VERIFICATION SCRIPT FOR FULL ACCOUNTING SYSTEM
-- ============================================================================
-- Run this script to verify all tables, accounts, and structures are correct
-- ============================================================================

-- 1. Check all accounting tables were created
SELECT 'TABLES CREATED' AS Check_Type, COUNT(*) AS Count 
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME LIKE 'coop_%';

-- Expected: 12 tables

-- ============================================================================

-- 2. List all accounting tables
SELECT TABLE_NAME, TABLE_ROWS, 
       ROUND(DATA_LENGTH/1024/1024, 2) AS Size_MB,
       ENGINE, TABLE_COLLATION
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME LIKE 'coop_%'
ORDER BY TABLE_NAME;

-- ============================================================================

-- 3. Verify chart of accounts was populated
SELECT 'CHART OF ACCOUNTS' AS Check_Type, 
       COUNT(*) AS Total_Accounts,
       SUM(CASE WHEN account_type = 'asset' THEN 1 ELSE 0 END) AS Assets,
       SUM(CASE WHEN account_type = 'liability' THEN 1 ELSE 0 END) AS Liabilities,
       SUM(CASE WHEN account_type = 'equity' THEN 1 ELSE 0 END) AS Equity,
       SUM(CASE WHEN account_type = 'revenue' THEN 1 ELSE 0 END) AS Revenue,
       SUM(CASE WHEN account_type = 'expense' THEN 1 ELSE 0 END) AS Expenses
FROM coop_accounts;

-- Expected: 90 total accounts

-- ============================================================================

-- 4. View main account categories (Level 1)
SELECT account_code, account_name, account_type, 
       (SELECT COUNT(*) FROM coop_accounts c2 WHERE c2.parent_id = c1.id) AS Sub_Accounts
FROM coop_accounts c1
WHERE parent_id IS NULL
ORDER BY account_code;

-- ============================================================================

-- 5. Verify system accounts are protected
SELECT 'SYSTEM ACCOUNTS' AS Check_Type, COUNT(*) AS Count
FROM coop_accounts
WHERE is_system_account = TRUE;

-- Expected: Critical accounts marked as system

-- ============================================================================

-- 6. Check foreign key constraints
SELECT 
    TABLE_NAME,
    CONSTRAINT_NAME,
    COLUMN_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM information_schema.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME LIKE 'coop_%'
AND REFERENCED_TABLE_NAME IS NOT NULL
ORDER BY TABLE_NAME, CONSTRAINT_NAME;

-- ============================================================================

-- 7. Check indexes
SELECT 
    TABLE_NAME,
    INDEX_NAME,
    GROUP_CONCAT(COLUMN_NAME ORDER BY SEQ_IN_INDEX) AS Columns,
    NON_UNIQUE,
    INDEX_TYPE
FROM information_schema.STATISTICS
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME LIKE 'coop_%'
GROUP BY TABLE_NAME, INDEX_NAME, NON_UNIQUE, INDEX_TYPE
ORDER BY TABLE_NAME, INDEX_NAME;

-- ============================================================================

-- 8. Verify views were created
SELECT 'VIEWS CREATED' AS Check_Type, COUNT(*) AS Count
FROM information_schema.VIEWS
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME LIKE 'vw_%';

-- Expected: 3 views

-- ============================================================================

-- 9. List all views
SELECT TABLE_NAME, VIEW_DEFINITION
FROM information_schema.VIEWS
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME LIKE 'vw_%';

-- ============================================================================

-- 10. Check stored procedures/functions
SELECT 'STORED ROUTINES' AS Check_Type, 
       ROUTINE_TYPE, 
       COUNT(*) AS Count
FROM information_schema.ROUTINES
WHERE ROUTINE_SCHEMA = DATABASE()
AND (ROUTINE_NAME LIKE '%Account%' OR ROUTINE_NAME LIKE '%NBV%')
GROUP BY ROUTINE_TYPE;

-- Expected: 1 procedure, 1 function

-- ============================================================================

-- 11. Sample account hierarchy (Assets)
SELECT 
    CONCAT(REPEAT('  ', 
        CASE 
            WHEN parent_id IS NULL THEN 0
            WHEN EXISTS(SELECT 1 FROM coop_accounts WHERE id = a.parent_id AND parent_id IS NULL) THEN 1
            ELSE 2
        END
    ), account_code, ' - ', account_name) AS Account_Hierarchy,
    account_type,
    normal_balance
FROM coop_accounts a
WHERE account_code LIKE '1%'
ORDER BY account_code;

-- ============================================================================

-- 12. Sample account hierarchy (Equity)
SELECT 
    CONCAT(REPEAT('  ', 
        CASE 
            WHEN parent_id IS NULL THEN 0
            WHEN EXISTS(SELECT 1 FROM coop_accounts WHERE id = a.parent_id AND parent_id IS NULL) THEN 1
            ELSE 2
        END
    ), account_code, ' - ', account_name) AS Account_Hierarchy,
    account_type,
    normal_balance
FROM coop_accounts a
WHERE account_code LIKE '3%'
ORDER BY account_code;

-- ============================================================================

-- 13. Verify control accounts
SELECT account_code, account_name, account_type,
       (SELECT COUNT(*) FROM coop_accounts c2 WHERE c2.parent_id = c1.id) AS Sub_Account_Count
FROM coop_accounts c1
WHERE is_control_account = TRUE
ORDER BY account_code;

-- ============================================================================

-- 14. Check empty tables (should all be empty initially)
SELECT 'coop_journal_entries' AS Table_Name, COUNT(*) AS Row_Count FROM coop_journal_entries
UNION ALL
SELECT 'coop_journal_entry_lines', COUNT(*) FROM coop_journal_entry_lines
UNION ALL
SELECT 'coop_period_balances', COUNT(*) FROM coop_period_balances
UNION ALL
SELECT 'coop_member_accounts', COUNT(*) FROM coop_member_accounts
UNION ALL
SELECT 'coop_fixed_assets', COUNT(*) FROM coop_fixed_assets
UNION ALL
SELECT 'coop_depreciation_schedule', COUNT(*) FROM coop_depreciation_schedule
UNION ALL
SELECT 'coop_budget', COUNT(*) FROM coop_budget
UNION ALL
SELECT 'coop_reserves', COUNT(*) FROM coop_reserves
UNION ALL
SELECT 'coop_appropriation', COUNT(*) FROM coop_appropriation
UNION ALL
SELECT 'coop_bank_reconciliation', COUNT(*) FROM coop_bank_reconciliation
UNION ALL
SELECT 'coop_audit_trail', COUNT(*) FROM coop_audit_trail;

-- All should be 0 (only coop_accounts should have data)

-- ============================================================================

-- 15. FINAL SUMMARY
SELECT 
    'SETUP COMPLETE!' AS Status,
    (SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME LIKE 'coop_%') AS Tables_Created,
    (SELECT COUNT(*) FROM coop_accounts) AS Accounts_Populated,
    (SELECT COUNT(*) FROM information_schema.VIEWS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME LIKE 'vw_%') AS Views_Created,
    NOW() AS Verification_Time;

-- ============================================================================
-- VERIFICATION COMPLETE
-- ============================================================================

