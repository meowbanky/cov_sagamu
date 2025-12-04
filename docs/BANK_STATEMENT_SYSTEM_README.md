# Bank Statement Upload & Analysis System

## Overview

This system allows users to upload bank statements in various formats (PDF, Excel, Images) and automatically extract transaction information using OpenAI's GPT model. The system then matches extracted names with employee records and processes contributions and debits accordingly.

## Features

### Core Functionality

- **Multi-format File Upload**: Supports PDF, Excel (.xlsx, .xls), and Image files (.jpg, .jpeg, .png)
- **OpenAI Integration**: Uses GPT-3.5-turbo to extract transaction details from uploaded files
- **Fuzzy Name Matching**: Automatically matches extracted names with employee records using fuzzy search
- **Period-based Processing**: Links transactions to specific payroll periods
- **Duplicate Prevention**: Uses file hashing to prevent processing the same file multiple times
- **Manual Matching**: Allows manual matching for unmatched transactions

### Transaction Processing

- **Credit Transactions**: Automatically inserted into `tbl_monthlycontribution` table
- **Debit Transactions**: Automatically inserted into `tbl_debits` table
- **Unmatched Transactions**: Stored for manual resolution

### User Interface

- **Drag & Drop Upload**: Modern drag-and-drop interface for file uploads
- **Real-time Processing**: Live feedback during file processing
- **Transaction Preview**: Shows matched and unmatched transactions before insertion
- **History Tracking**: Complete audit trail of uploaded files and processing status

## Database Schema

### New Tables Created

#### `bank_statement_files`

```sql
CREATE TABLE bank_statement_files (
    id INT AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_hash VARCHAR(32) NOT NULL UNIQUE,
    period_id INT NOT NULL,
    uploaded_by VARCHAR(50) NOT NULL,
    upload_date DATETIME NOT NULL,
    processed BOOLEAN DEFAULT FALSE,
    INDEX (file_hash),
    INDEX (period_id)
);
```

#### `tbl_debits`

```sql
CREATE TABLE tbl_debits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    coopID VARCHAR(10) NOT NULL,
    amount DECIMAL(20,2) NOT NULL,
    period INT NOT NULL,
    transaction_date DATETIME NOT NULL,
    created_by VARCHAR(50),
    created_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX (coopID),
    INDEX (period)
);
```

#### `unmatched_transactions`

```sql
CREATE TABLE unmatched_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    amount DECIMAL(20,2) NOT NULL,
    type ENUM('credit', 'debit') NOT NULL,
    period INT NOT NULL,
    file_id INT NOT NULL,
    created_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    resolved BOOLEAN DEFAULT FALSE,
    resolved_coop_id VARCHAR(10),
    resolved_date DATETIME,
    INDEX (name),
    INDEX (period),
    INDEX (resolved)
);
```

## File Structure

```
coop_admin/
├── bank_statement_upload.php          # Main upload interface
├── bank_statement_processor.php       # Backend processing logic
├── bank_statement_history.php         # Upload history and statistics
├── config_manager.php                 # Configuration management interface
├── config/
│   └── EnvConfig.php                  # Environment configuration loader
├── config.env                         # Configuration file (create this)
├── uploads/
│   └── bank_statements/               # Uploaded files storage
└── BANK_STATEMENT_SYSTEM_README.md    # This documentation
```

## Installation & Setup

### Prerequisites

1. PHP 7.4 or higher
2. MySQL 5.7 or higher
3. Composer (for PHP dependencies)
4. OpenAI API key

### Dependencies

The system requires the following PHP packages:

- `phpoffice/phpspreadsheet` - For Excel file processing
- `guzzlehttp/guzzle` - For OpenAI API calls

Install dependencies:

```bash
composer require phpoffice/phpspreadsheet guzzlehttp/guzzle
```

### Configuration

1. Ensure database connection is properly configured in `Connections/coop.php`
2. Create the uploads directory: `mkdir -p uploads/bank_statements`
3. Set proper permissions: `chmod 755 uploads/bank_statements`
4. Configure your OpenAI API key using the Configuration Manager or by editing `config.env`

### Optional: PDF Processing

For better PDF text extraction, install `pdftotext`:

```bash
# Ubuntu/Debian
sudo apt-get install poppler-utils

# macOS
brew install poppler

# Windows
# Download and install Xpdf tools
```

## Usage

### Uploading Bank Statements

1. **Access the System**: Navigate to "Bank Statement Upload" in the sidebar menu
2. **Configure API Key**: If not already configured, click "Configure API Key" to set up your OpenAI API key
3. **Select Period**: Choose the appropriate payroll period from the dropdown
4. **Upload Files**:
   - Drag and drop files into the upload area, or
   - Click "Browse Files" to select files manually
5. **Process Files**: Click "Upload & Analyze" to start processing

### Reviewing Results

After processing, the system displays:

- **Matched Transactions**: Automatically matched with employee records
- **Unmatched Transactions**: Require manual matching
- **Summary Statistics**: Total transactions, matched count, unmatched count

### Manual Matching

For unmatched transactions:

1. Click "Manual Match" button
2. Select the correct employee from the dropdown
3. Click "Save Match" to process the transaction

### Viewing History

1. Click "View History" to see all uploaded files
2. View processing status and statistics
3. Download original files or reprocess if needed

### Configuration Management

1. Access the Configuration Manager from the upload page
2. Update OpenAI API key, database settings, and other configurations
3. Test configuration using the built-in test system
4. All changes are saved to the `config.env` file

## API Integration

### OpenAI Configuration

The system uses OpenAI's GPT-3.5-turbo model for text extraction. The prompt is optimized for financial data extraction:

```
Extract financial transactions from the following bank statement text.
For each transaction, identify the person's name, amount, and whether it's a credit or debit.
Return the data in JSON format with this structure:
[{"name": "Person Name", "amount": 1000.00, "type": "credit" or "debit"}]
```

### Response Format

OpenAI responses are parsed to extract JSON data. The system handles various response formats and validates the extracted data.

## Security Considerations

### File Upload Security

- File type validation (MIME type checking)
- File size limits
- Secure file storage outside web root
- File hash verification for duplicate prevention

### API Key Security

- OpenAI API keys are stored securely in the `config.env` file
- Keys are not stored in the database or transmitted in forms
- Configuration manager provides secure interface for key management
- Consider implementing key rotation policies

### Database Security

- Prepared statements for all database queries
- Input validation and sanitization
- User authentication required for all operations

## Error Handling

### Common Issues

1. **File Upload Failures**: Check file permissions and disk space
2. **OpenAI API Errors**: Verify API key and network connectivity
3. **Database Errors**: Check database connection and table structure
4. **Memory Issues**: Large files may require PHP memory limit adjustments

### Debugging

- Check PHP error logs for detailed error messages
- Enable error reporting in development environment
- Monitor OpenAI API usage and rate limits

## Performance Optimization

### File Processing

- Implement file size limits to prevent memory issues
- Use streaming for large file processing
- Consider background job processing for large files

### Database Optimization

- Index frequently queried columns
- Implement pagination for large result sets
- Regular database maintenance and cleanup

## Maintenance

### Regular Tasks

1. **Cleanup Old Files**: Remove processed files older than specified retention period
2. **Database Maintenance**: Regular backup and optimization
3. **Log Rotation**: Manage application and error logs
4. **Security Updates**: Keep dependencies updated

### Monitoring

- Monitor OpenAI API usage and costs
- Track file processing success rates
- Monitor system performance and resource usage

## Troubleshooting

### File Processing Issues

```php
// Check file permissions
ls -la uploads/bank_statements/

// Verify PHP extensions
php -m | grep -E "(gd|imagick|zip)"

// Test OpenAI connection
curl -H "Authorization: Bearer YOUR_API_KEY" \
     https://api.openai.com/v1/models
```

### Database Issues

```sql
-- Check table structure
DESCRIBE bank_statement_files;
DESCRIBE tbl_debits;
DESCRIBE unmatched_transactions;

-- Verify data integrity
SELECT COUNT(*) FROM bank_statement_files;
SELECT COUNT(*) FROM unmatched_transactions WHERE resolved = 0;
```

## Future Enhancements

### Planned Features

1. **Batch Processing**: Process multiple files in background jobs
2. **Advanced OCR**: Integration with specialized OCR services
3. **Machine Learning**: Improved name matching algorithms
4. **Reporting**: Advanced analytics and reporting features
5. **API Endpoints**: RESTful API for external integrations

### Integration Possibilities

- **Accounting Software**: Integration with QuickBooks, Xero, etc.
- **Bank APIs**: Direct bank statement retrieval
- **Mobile App**: Mobile interface for file uploads
- **Email Processing**: Automatic processing of emailed statements

## Support

For technical support or feature requests, please contact the development team with:

- Detailed description of the issue
- Error messages and logs
- Steps to reproduce the problem
- System environment details

## License

This system is proprietary software developed for internal use. All rights reserved.
