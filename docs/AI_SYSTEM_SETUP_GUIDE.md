# AI Bank Statement Upload System - Setup Guide

## 🎯 What This System Does

This AI-powered system automatically processes bank statements from Nigerian banks and:

1. **Extracts transaction details** using OpenAI's GPT model
2. **Matches names** with your cooperative members using fuzzy matching
3. **Processes transactions** automatically:
   - **Credits** → `tbl_contributions` table
   - **Debits** → `tbl_loan` table
4. **Handles unmatched transactions** with manual matching interface

## 🚀 Quick Setup (5 Minutes)

### Step 1: Configure OpenAI API Key

1. Get your OpenAI API key from [OpenAI Platform](https://platform.openai.com/api-keys)
2. Edit `config.env` file and set:
   ```
   OPENAI_API_KEY=your_actual_api_key_here
   ```

### Step 2: Test the System

1. Run the test file: `test_ai_system.php`
2. Verify all tests pass ✅
3. If any tests fail, follow the troubleshooting guide below

### Step 3: Access the System

1. Navigate to `ai_bank_statement_upload.php`
2. Or use the sidebar menu: "AI Bank Statement Upload"

## 📋 How to Use

### 1. Select Period

Choose the appropriate payroll period from the dropdown.

### 2. Upload Bank Statements

- **Drag and drop** PDF, Excel, or image files
- **Or click "Browse Files"** to select manually
- Supported formats: PDF, Excel (.xlsx, .xls), Images (.jpg, .jpeg, .png)

### 3. AI Analysis

Click "Upload & Analyze with AI" - the system will:

- Extract text from your files
- Use AI to identify transactions
- Match names with your members
- Show you the results

### 4. Review Results

- **Green transactions**: Automatically matched ✅
- **Red transactions**: Need manual matching ⚠️
- **Statistics**: See totals and amounts

### 5. Manual Matching (if needed)

For unmatched transactions:

1. Click "Manual Match"
2. Search for the correct member
3. Click "Select" to match

### 6. Process Transactions

Click "Process All Transactions" to save to database.

## 🔧 Troubleshooting

### Common Issues

#### ❌ "OpenAI API key not configured"

**Solution**: Set your API key in `config.env`:

```
OPENAI_API_KEY=sk-your-actual-key-here
```

#### ❌ "Database connection failed"

**Solution**: Check `Connections/cov.php` has correct database credentials.

#### ❌ "Table does not exist"

**Solution**: The system creates required tables automatically. Check database permissions.

#### ❌ "File upload failed"

**Solution**:

```bash
chmod 755 uploads/bank_statements
chmod 755 uploads/exports
```

#### ❌ "Composer dependencies missing"

**Solution**:

```bash
composer install
```

### Test Your Setup

Run `test_ai_system.php` to check:

- ✅ Database connection
- ✅ Configuration
- ✅ Directory permissions
- ✅ Required files
- ✅ Dependencies
- ✅ Sample data

## 📊 Database Tables Used

### Your Existing Tables

- `tbl_personalinfo` - Member information for matching
- `tbl_contributions` - Credit transactions
- `tbl_loan` - Debit transactions
- `tbpayrollperiods` - Period selection

### New Tables Created

- `bank_statement_files` - File upload tracking

## 🔒 Security Features

- **File validation**: Only allows safe file types
- **Duplicate prevention**: Uses file hashing
- **SQL injection protection**: Prepared statements
- **API key security**: Stored in config file, not database
- **User authentication**: Requires login

## 💡 Tips for Best Results

### File Quality

- **PDFs**: Use text-based PDFs (not scanned images)
- **Excel**: Ensure data is in readable format
- **Images**: Use clear, high-resolution images

### Name Matching

- The AI is optimized for Nigerian names
- Fuzzy matching handles minor spelling variations
- Manual matching available for unmatched transactions

### Processing

- Start with small files to test
- Review results before processing
- Export results for record keeping

## 📈 Performance

### File Size Limits

- **Recommended**: Under 10MB per file
- **Maximum**: 50MB per file
- **Multiple files**: Can process multiple files at once

### Processing Time

- **Small files**: 10-30 seconds
- **Large files**: 1-3 minutes
- **AI analysis**: Depends on file complexity

## 🆘 Support

### If Something Goes Wrong

1. **Check the test file**: `test_ai_system.php`
2. **Check error logs**: PHP error logs
3. **Verify configuration**: `config.env` file
4. **Test API key**: Try a simple OpenAI API call

### Common Error Messages

| Error                           | Solution                       |
| ------------------------------- | ------------------------------ |
| "No files uploaded"             | Select files before uploading  |
| "Period is required"            | Choose a period from dropdown  |
| "OpenAI API key not configured" | Set API key in config.env      |
| "File type not supported"       | Use PDF, Excel, or image files |
| "Database connection failed"    | Check database credentials     |

## 🎉 Success Indicators

You'll know the system is working when:

1. ✅ Test file shows all green checkmarks
2. ✅ Can upload and process bank statements
3. ✅ AI extracts transaction details correctly
4. ✅ Names match with your members
5. ✅ Transactions save to database successfully

## 📞 Need Help?

If you encounter issues:

1. Run `test_ai_system.php` first
2. Check this troubleshooting guide
3. Review the main README: `AI_BANK_STATEMENT_README.md`
4. Contact support with error details

---

**🎯 Ready to start?** Navigate to `ai_bank_statement_upload.php` and upload your first bank statement!
