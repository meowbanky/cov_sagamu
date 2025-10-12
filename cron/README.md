# Email Queue Cron Job Setup

## Overview

This directory contains the email queue processing system for automated email notifications.

## Files

- `process_email_queue.php` - Main cron job script for processing email queue
- `README.md` - This setup guide

## Cron Job Setup

### 1. Basic Setup (Every 30 minutes)

```bash
# Edit crontab
crontab -e

# Add this line to run every 30 minutes
*/30 * * * * /usr/bin/php /path/to/your/project/cron/process_email_queue.php

# Or run every hour
0 * * * * /usr/bin/php /path/to/your/project/cron/process_email_queue.php
```

### 2. Advanced Setup (Multiple Schedules)

```bash
# Process queue every 30 minutes during business hours (8 AM - 6 PM)
*/30 8-18 * * * /usr/bin/php /path/to/your/project/cron/process_email_queue.php

# Process queue every hour during off-hours (7 PM - 7 AM)
0 19-23,0-7 * * * /usr/bin/php /path/to/your/project/cron/process_email_queue.php

# Process queue every 15 minutes on weekends
*/15 * * * 0,6 /usr/bin/php /path/to/your/project/cron/process_email_queue.php
```

### 3. With Logging

```bash
# Process with detailed logging
*/30 * * * * /usr/bin/php /path/to/your/project/cron/process_email_queue.php >> /var/log/email_queue.log 2>&1
```

## Database Setup

### 1. Run the SQL Setup Script

```bash
mysql -u your_username -p your_database < email_queue_setup.sql
```

### 2. Verify Tables Created

```sql
SHOW TABLES LIKE 'tbl_email_queue%';
```

## Configuration

### Rate Limiting

Edit the `EmailQueueManager.php` file to adjust:

- `$maxEmailsPerHour` - Maximum emails per hour (default: 50)
- `$batchSize` - Emails processed per batch (default: 10)
- `$retryDelay` - Delay between retries in seconds (default: 300)

### Email Templates

Customize email templates in `EmailTemplateService.php`:

- Transaction summary emails
- Monthly statements
- Loan reminders
- Contribution alerts

## Monitoring

### 1. Check Log Files

```bash
# View recent log entries
tail -f logs/email_queue.log

# Check for errors
grep "ERROR" logs/email_queue.log
```

### 2. Database Monitoring

```sql
-- Check queue status
SELECT status, COUNT(*) as count FROM tbl_email_queue GROUP BY status;

-- Check rate limiting
SELECT date, hour, emails_sent FROM tbl_email_rate_limit
WHERE date = CURDATE() ORDER BY hour;

-- Check recent activity
SELECT * FROM tbl_email_queue_log
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
ORDER BY created_at DESC;
```

### 3. Manual Processing

Access the dashboard at: `email_queue_dashboard.php`

- View queue statistics
- Process queue manually
- Monitor email status

## Troubleshooting

### Common Issues

#### 1. Cron Job Not Running

```bash
# Check if cron service is running
systemctl status cron

# Check cron logs
tail -f /var/log/cron

# Test script manually
/usr/bin/php /path/to/your/project/cron/process_email_queue.php
```

#### 2. Permission Issues

```bash
# Make sure script is executable
chmod +x /path/to/your/project/cron/process_email_queue.php

# Check file permissions
ls -la /path/to/your/project/cron/process_email_queue.php
```

#### 3. Database Connection Issues

- Verify database credentials in `Connections/cov.php`
- Check if MySQL service is running
- Ensure database user has proper permissions

#### 4. Email Sending Issues

- Update the `sendEmail()` method in `EmailQueueManager.php`
- Configure SMTP settings
- Check email service limits

### Performance Optimization

#### 1. Batch Size Tuning

- Increase `$batchSize` for faster processing
- Decrease for lower server load
- Monitor server resources during processing

#### 2. Rate Limit Adjustment

- Increase `$maxEmailsPerHour` if hosting allows
- Decrease if getting spam warnings
- Monitor delivery rates

#### 3. Scheduling Optimization

- Run during off-peak hours
- Spread processing across multiple cron jobs
- Use different schedules for different priorities

## Security Considerations

1. **File Permissions**: Ensure log files are not web-accessible
2. **Database Security**: Use prepared statements (already implemented)
3. **Email Content**: Avoid spam trigger words
4. **Rate Limiting**: Respect hosting provider limits
5. **Error Handling**: Don't expose sensitive information in logs

## Integration

### With Transaction Processing

```php
// After processing monthly transactions
require_once('libs/services/EmailTemplateService.php');
$emailService = new EmailTemplateService($cov);
$result = $emailService->queueTransactionSummaryEmails($periodId);
```

### With Member Registration

```php
// After member registration
require_once('libs/services/EmailQueueManager.php');
$queueManager = new EmailQueueManager($cov);
$queueManager->addToQueue($memberId, $periodId, 'welcome', $email, $name, $subject, $message);
```

## Support

For issues or questions:

1. Check the log files first
2. Verify database setup
3. Test cron job manually
4. Check email service configuration
5. Review rate limiting settings
