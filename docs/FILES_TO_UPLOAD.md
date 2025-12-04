# 📦 COMPLETE FILE LIST - ACCOUNTING SYSTEM DEPLOYMENT

## 🎯 QUICK UPLOAD CHECKLIST

Copy this list when uploading files to your server.

---

## 1️⃣ SQL SCRIPTS (Run First via phpMyAdmin)

### **CRITICAL - Must Run:**

```
✅ SETUP_FULL_ACCOUNTING_SYSTEM.sql
```

**How to Run:**

1. phpMyAdmin → Select database `emmaggic_cofv`
2. Import tab → Choose file
3. Click "Go"
4. Wait for success message

### **Optional - For Verification:**

```
📋 VERIFY_ACCOUNTING_SETUP.sql
```

---

## 2️⃣ PHP CLASS FILES

### **libs/services/** (Core Accounting Engine)

```
✅ libs/services/AccountingEngine.php
✅ libs/services/AccountBalanceCalculator.php
✅ libs/services/MemberAccountManager.php
```

### **libs/reports/** (Financial Statement Generators)

```
✅ libs/reports/IncomeExpenditureStatement.php
✅ libs/reports/BalanceSheet.php
```

**Action:** Upload entire folders to preserve structure

---

## 3️⃣ USER INTERFACE PAGES (Root Directory)

### **New Pages:**

```
✅ coop_chart_of_accounts.php
✅ coop_journal_entries.php
✅ coop_trial_balance.php
✅ coop_financial_statements.php
✅ coop_member_statement.php
```

### **Modified Existing Pages:**

```
✅ process.php (OVERWRITE - accounting integration added)
✅ header.php (OVERWRITE - navigation menu updated)
✅ dashboard.php (OVERWRITE - quick access cards added)
```

⚠️ **IMPORTANT:** These 3 files have been modified. Make sure to upload and overwrite the existing files.

---

## 4️⃣ API ENDPOINTS

### **api/** (AJAX Endpoints)

```
✅ api/get_journal_entry_lines.php
```

---

## 5️⃣ DOCUMENTATION (Optional - For Reference)

### **User Guides:**

```
📖 ACCOUNTING_ENGINE_USAGE_GUIDE.md
📖 INTEGRATION_COMPLETE.md
📖 ACCOUNTING_DEPLOYMENT_GUIDE.md
📖 FILES_TO_UPLOAD.md (this file)
```

**Note:** These are for your reference and don't need to be uploaded to the live server.

---

## 📂 COMPLETE DIRECTORY STRUCTURE

```
cov/
│
├── SQL SCRIPTS (Run in phpMyAdmin)
│   ├── SETUP_FULL_ACCOUNTING_SYSTEM.sql ✅ RUN THIS
│   └── VERIFY_ACCOUNTING_SETUP.sql 📋 OPTIONAL
│
├── DOCUMENTATION (Keep locally)
│   ├── ACCOUNTING_ENGINE_USAGE_GUIDE.md
│   ├── INTEGRATION_COMPLETE.md
│   ├── ACCOUNTING_DEPLOYMENT_GUIDE.md
│   └── FILES_TO_UPLOAD.md
│
├── ROOT DIRECTORY PAGES
│   ├── coop_chart_of_accounts.php ✅ NEW
│   ├── coop_journal_entries.php ✅ NEW
│   ├── coop_trial_balance.php ✅ NEW
│   ├── coop_financial_statements.php ✅ NEW
│   ├── coop_member_statement.php ✅ NEW
│   ├── process.php ✅ MODIFIED
│   ├── header.php ✅ MODIFIED
│   └── dashboard.php ✅ MODIFIED
│
├── libs/services/ (Core Classes)
│   ├── AccountingEngine.php ✅ NEW
│   ├── AccountBalanceCalculator.php ✅ NEW
│   ├── MemberAccountManager.php ✅ NEW
│   ├── EmailQueueManager.php (existing)
│   ├── EmailTemplateService.php (existing)
│   └── NotificationService.php (existing)
│
├── libs/reports/ (Report Generators)
│   ├── IncomeExpenditureStatement.php ✅ NEW
│   └── BalanceSheet.php ✅ NEW
│
└── api/ (AJAX Endpoints)
    ├── get_journal_entry_lines.php ✅ NEW
    ├── get_all_members.php (existing)
    └── periods.php (existing)
```

---

## 🚀 QUICK DEPLOYMENT (Step-by-Step)

### **Method 1: FTP Upload (Recommended)**

1. **Connect to FTP:**

   - Host: Your server
   - Path: `/public_html/cov/`

2. **Upload in this order:**

   **a) Create directories (if not exist):**

   ```
   libs/reports/
   ```

   **b) Upload SQL script:**

   ```
   SETUP_FULL_ACCOUNTING_SYSTEM.sql (to root or temp folder)
   ```

   **c) Upload new PHP classes:**

   ```
   libs/services/AccountingEngine.php
   libs/services/AccountBalanceCalculator.php
   libs/services/MemberAccountManager.php
   libs/reports/IncomeExpenditureStatement.php
   libs/reports/BalanceSheet.php
   ```

   **d) Upload new UI pages:**

   ```
   coop_chart_of_accounts.php
   coop_journal_entries.php
   coop_trial_balance.php
   coop_financial_statements.php
   coop_member_statement.php
   ```

   **e) Upload API endpoint:**

   ```
   api/get_journal_entry_lines.php
   ```

   **f) OVERWRITE these existing files:**

   ```
   process.php
   header.php
   dashboard.php
   ```

3. **Run SQL Script:**

   - phpMyAdmin → Import → `SETUP_FULL_ACCOUNTING_SYSTEM.sql`

4. **Test:**
   - Visit: `https://cov.emmaggi.com/coop_trial_balance.php`

---

### **Method 2: cPanel File Manager**

1. **Login to cPanel**
2. **File Manager** → Navigate to `/public_html/cov/`
3. **Upload** → Select all files from your local `cov/` folder
4. **Extract** (if using ZIP)
5. **Permissions** → Set to 644 for all PHP files
6. **phpMyAdmin** → Import SQL script

---

### **Method 3: Git Deploy (If Using Git on Server)**

```bash
cd /home/emmaggic/public_html/cov/
git pull origin master
```

Then run SQL script via phpMyAdmin.

---

## ✅ POST-DEPLOYMENT VERIFICATION

### **Check 1: Database**

```sql
-- Should return 12
SELECT COUNT(*) FROM information_schema.TABLES
WHERE TABLE_SCHEMA = 'emmaggic_cofv'
AND TABLE_NAME LIKE 'coop_%';

-- Should return 90
SELECT COUNT(*) FROM coop_accounts;
```

### **Check 2: Pages Load**

Visit each page and verify no errors:

```
✅ https://cov.emmaggi.com/coop_chart_of_accounts.php
✅ https://cov.emmaggi.com/coop_journal_entries.php
✅ https://cov.emmaggi.com/coop_trial_balance.php
✅ https://cov.emmaggi.com/coop_financial_statements.php
✅ https://cov.emmaggi.com/coop_member_statement.php
```

### **Check 3: Navigation Menu**

- [ ] "Accounting" section appears in sidebar
- [ ] 5 links displayed correctly
- [ ] Dashboard cards show Trial Balance & Financial Statements

### **Check 4: September Data**

- [ ] Trial balance shows September transactions
- [ ] Journal entries list shows September entries
- [ ] Financial statements generate for September
- [ ] Member statements show September activity

---

## 🔥 COMMON DEPLOYMENT ISSUES & FIXES

### **Issue 1: "Table already exists" Error**

**Solution:** Tables were already created. You can:

- Skip the SQL import (tables exist)
- Or drop tables first (CAREFUL!):
  ```sql
  DROP TABLE IF EXISTS coop_audit_trail;
  DROP TABLE IF EXISTS coop_bank_reconciliation;
  -- etc. (drop in reverse dependency order)
  ```

### **Issue 2: "Class not found" Error**

**Solution:**

- Verify file paths are correct
- Check case sensitivity (AccountingEngine.php not accountingengine.php)
- Ensure files uploaded to correct directories

### **Issue 3: "No such file or directory" Error**

**Solution:**

- Check `require_once` paths in files
- Verify `libs/services/` and `libs/reports/` directories exist
- Upload files to exact locations

### **Issue 4: Pages Show Blank**

**Solution:**

- Check PHP error logs
- Enable error display temporarily:
  ```php
  error_reporting(E_ALL);
  ini_set('display_errors', 1);
  ```
- Verify all require_once files exist

---

## 📈 PERFORMANCE NOTES

### **Expected Performance:**

- Trial Balance: < 2 seconds
- Journal Entry List: < 1 second
- Financial Statements: < 3 seconds
- Member Statement: < 1 second

### **If Slow:**

- Check database indexes exist
- Verify tables optimized
- Consider adding more indexes for large datasets

---

## 🎊 DEPLOYMENT COMPLETE CONFIRMATION

After successful deployment, you should see:

✅ **Navigation Menu:**

```
Accounting
├── Chart of Accounts
├── Journal Entries
├── Trial Balance
├── Financial Statements
└── Member Statement
```

✅ **Dashboard Cards:**

```
[⚖️ Trial Balance]  [📊 Financial Statements]
```

✅ **Working Pages:**

- All 5 accounting pages load without errors
- September data displays correctly
- Trial balance shows balanced status

✅ **Integration:**

- Processing members creates journal entries
- Member accounts update automatically
- Audit trail maintained

---

## 🎯 SUCCESS CRITERIA

**Your deployment is successful if:**

1. ✅ All pages load without errors
2. ✅ Navigation menu shows Accounting section
3. ✅ Trial balance displays September data
4. ✅ Journal entries show September transactions
5. ✅ Financial statements generate correctly
6. ✅ Processing new members creates journal entries

---

## 📞 NEED HELP?

If you encounter issues:

1. Check server error logs
2. Verify file upload paths
3. Confirm database tables created
4. Test with simple member processing
5. Review verification queries

---

**Ready to deploy? Follow the steps above! 🚀**

**Total Files to Upload: 16 files**
**Estimated Deployment Time: 15-20 minutes**
**Database Setup Time: 2-3 minutes**
