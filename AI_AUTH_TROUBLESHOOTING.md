# AI Bank Statement Upload - Authentication Troubleshooting

## Issue: "Unauthorized" Error

If you're getting an "Unauthorized" error when trying to use the AI Bank Statement Upload system, follow these steps:

## 🔍 Step 1: Test Session Status

1. **Access the session test page**: Navigate to `session_test.php` in your browser
2. **Check the results**:
   - ✅ Green = Session is working
   - ❌ Red = Session has issues

## 🔧 Step 2: Common Solutions

### Solution A: Clear Browser Cache

1. Clear your browser cache and cookies
2. Log out and log back in
3. Try accessing the AI upload again

### Solution B: Check Session Configuration

1. Ensure you're accessing the site via the same domain
2. Check if you're using HTTPS vs HTTP consistently
3. Verify session cookies are enabled in your browser

### Solution C: Database Session Check

If the session test shows no variables, try:

1. Log out completely
2. Clear browser data
3. Log in again
4. Test the session again

## 🧪 Step 3: Debug Tests

### Test 1: Basic Session Test

```bash
# Access this URL in your browser
https://your-domain.com/session_test.php
```

### Test 2: AI Auth Test

```bash
# Access this URL in your browser
https://your-domain.com/test_ai_auth.php
```

### Test 3: Manual Session Check

1. Log into your dashboard
2. Open browser developer tools (F12)
3. Go to Application/Storage tab
4. Check if session cookies exist

## 🔄 Step 4: Alternative Authentication

If sessions continue to fail, the system now includes:

1. **Multiple Session Variable Support**: Checks for `UserID`, `userid`, `SESS_FIRST_NAME`, `FirstName`
2. **Database Fallback**: Can authenticate using session ID from database
3. **Enhanced Error Messages**: Provides detailed debug information

## 📋 Step 5: Manual Fix

If all else fails, you can temporarily bypass authentication by:

1. **Edit the processor file**: `ai_bank_statement_processor.php`
2. **Comment out the authentication check** (lines 15-45)
3. **Test the functionality**
4. **Re-enable authentication** once working

## 🚨 Emergency Bypass (Temporary Only)

```php
// In ai_bank_statement_processor.php, temporarily comment out:
/*
if (!$is_logged_in) {
    http_response_code(401);
    echo json_encode([...]);
    exit();
}
*/
```

## 📞 Support Information

### What to Check:

- [ ] Session test results
- [ ] Browser console errors
- [ ] Server error logs
- [ ] Database connection status

### Information to Provide:

- Session test output
- Browser type and version
- Error messages from console
- Steps to reproduce the issue

## ✅ Success Indicators

You'll know it's working when:

1. Session test shows green checkmarks
2. AI upload page loads without redirect
3. File upload and processing works
4. No "Unauthorized" errors in console

## 🔒 Security Note

The authentication system is designed to protect your data. Only bypass it temporarily for testing, and always re-enable it for production use.

---

**Need Help?** Run the session test first and provide the output for better assistance.
