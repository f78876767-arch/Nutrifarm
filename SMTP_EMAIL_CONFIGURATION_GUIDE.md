# SMTP Email Configuration Guide

## Overview
The Nutrifarm backend now supports sending verification codes via SMTP email. This guide covers configuration for popular email providers.

## Current Configuration
The system uses:
- **Mailable Class**: `VerificationCodeMail` (queued for better performance)
- **Email Templates**: HTML and text versions with Nutrifarm branding
- **Queue System**: Emails are queued to prevent blocking API responses
- **Error Handling**: Comprehensive error catching and logging

## SMTP Provider Configurations

### 1. Gmail SMTP (Recommended for Development)

Update your `.env` file:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-nutrifarm-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@nutrifarm.com"
MAIL_FROM_NAME="Nutrifarm"
```

**Setup Steps:**
1. Create a Gmail account for Nutrifarm
2. Enable 2-Factor Authentication
3. Generate an App Password:
   - Go to Google Account settings
   - Security → App passwords
   - Generate password for "Mail"
   - Use this password in `MAIL_PASSWORD`

### 2. Mailgun (Recommended for Production)

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=postmaster@mg.yourdomain.com
MAIL_PASSWORD=your-mailgun-smtp-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@nutrifarm.com"
MAIL_FROM_NAME="Nutrifarm"
```

**Setup Steps:**
1. Sign up for Mailgun account
2. Add and verify your domain
3. Get SMTP credentials from Mailgun dashboard
4. Update DNS records as required

### 3. SendGrid

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=your-sendgrid-api-key
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@nutrifarm.com"
MAIL_FROM_NAME="Nutrifarm"
```

### 4. Amazon SES

```env
MAIL_MAILER=smtp
MAIL_HOST=email-smtp.us-east-1.amazonaws.com
MAIL_PORT=587
MAIL_USERNAME=your-aws-access-key-id
MAIL_PASSWORD=your-aws-secret-access-key
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@nutrifarm.com"
MAIL_FROM_NAME="Nutrifarm"
```

### 5. Custom SMTP Server

```env
MAIL_MAILER=smtp
MAIL_HOST=mail.yourdomain.com
MAIL_PORT=587  # or 25, 465
MAIL_USERNAME=noreply@yourdomain.com
MAIL_PASSWORD=your-email-password
MAIL_ENCRYPTION=tls  # or ssl, or null
MAIL_FROM_ADDRESS="noreply@nutrifarm.com"
MAIL_FROM_NAME="Nutrifarm"
```

## Queue Configuration

Since emails are queued, you need to run the queue worker:

### Development
```bash
php artisan queue:work
```

### Production (with Supervisor)
Create `/etc/supervisor/conf.d/laravel-worker.conf`:
```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/your/project/artisan queue:work --sleep=3 --tries=3 --max-time=3600
directory=/path/to/your/project
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/path/to/your/project/storage/logs/worker.log
stopwaitsecs=3600
```

Then:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*
```

## Testing Email Configuration

### 1. Test SMTP Connection
```bash
php artisan tinker
```

```php
Mail::raw('Test email from Nutrifarm', function ($message) {
    $message->to('test@example.com')->subject('SMTP Test');
});
```

### 2. Test Verification Email
```bash
curl -X POST http://localhost:8000/api/auth/send-verification-email \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email": "your-test-email@gmail.com"}'
```

### 3. Check Queue Status
```bash
php artisan queue:work --once
```

## Troubleshooting

### Common Issues

**1. Connection Refused**
- Check MAIL_HOST and MAIL_PORT
- Verify firewall settings
- Try different encryption (tls/ssl/null)

**2. Authentication Failed**
- Verify MAIL_USERNAME and MAIL_PASSWORD
- For Gmail: Use App Password, not account password
- Check if 2FA is required

**3. Emails Not Sending**
- Check if queue worker is running: `php artisan queue:work`
- Check failed jobs: `php artisan queue:failed`
- Check logs: `tail -f storage/logs/laravel.log`

**4. Emails Going to Spam**
- Set up SPF records
- Configure DKIM signing
- Use proper FROM address
- Avoid spam trigger words

### Log Debugging
Check email logs in `storage/logs/laravel.log`:
```bash
tail -f storage/logs/laravel.log | grep -i mail
```

## Production Recommendations

1. **Use Professional Email Service**: Mailgun, SendGrid, or SES
2. **Set Up Domain Authentication**: SPF, DKIM, DMARC records
3. **Monitor Email Delivery**: Track bounce rates and complaints
4. **Use Queue Workers**: Always run queue workers in production
5. **Set Up Monitoring**: Monitor queue health and email delivery
6. **Rate Limiting**: Implement rate limiting for email endpoints
7. **Backup SMTP**: Configure backup SMTP providers

## Email Template Customization

The email templates are located at:
- HTML: `resources/views/emails/verification-code.blade.php`
- Text: `resources/views/emails/verification-code-text.blade.php`

You can customize:
- Colors and branding
- Logo and images
- Message content
- Button styles
- Footer information

## Security Considerations

1. **Environment Variables**: Never commit SMTP credentials to Git
2. **App Passwords**: Use app-specific passwords for Gmail
3. **Encryption**: Always use TLS or SSL encryption
4. **Rate Limiting**: Implement email sending limits
5. **Validation**: Validate email addresses before sending
6. **Logging**: Log email attempts without exposing credentials

## Current Email Features

✅ **Professional HTML Template** with Nutrifarm branding  
✅ **Text Fallback** for better compatibility  
✅ **Queue System** for improved performance  
✅ **Error Handling** with detailed logging  
✅ **6-Digit Verification Codes** with 10-minute expiry  
✅ **Responsive Design** works on all email clients  
✅ **Security Features** prevent code reuse and expiry  

Your email system is production-ready! Just configure your preferred SMTP provider and start the queue worker.
