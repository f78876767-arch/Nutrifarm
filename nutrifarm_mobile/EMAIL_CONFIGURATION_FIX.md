# Email Configuration Fix for Laravel Backend

## Issue
The Flutter app is successfully calling the Laravel backend API for email verification, but no actual emails are being sent. The API returns `{"success":true}` but users don't receive verification codes.

## Root Cause
Laravel backend is missing SMTP email configuration in the `.env` file.

## Quick Fix - Configure Email Sending

### Step 1: Update Laravel .env File
Add these email settings to your Laravel backend's `.env` file:

```env
# Email Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-nutrifarm-email@gmail.com
MAIL_PASSWORD=your-app-specific-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-nutrifarm-email@gmail.com
MAIL_FROM_NAME="Nutrifarm Store"
```

### Step 2: Gmail App Password Setup (if using Gmail)
1. Go to Google Account Settings
2. Enable 2-Factor Authentication
3. Generate an "App Password" for Laravel
4. Use the app password (not your regular Gmail password)

### Step 3: Test Email Sending
Run this test from your Laravel backend:

```bash
php artisan tinker
```

Then test email sending:
```php
Mail::raw('Test email from Nutrifarm backend', function ($message) {
    $message->to('test@example.com')->subject('Test Email');
});
```

### Step 4: Alternative - Mailtrap for Development
For development/testing, use Mailtrap instead of Gmail:

1. Sign up at https://mailtrap.io
2. Get SMTP credentials
3. Update `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your-mailtrap-username
MAIL_PASSWORD=your-mailtrap-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@nutrifarm.com
MAIL_FROM_NAME="Nutrifarm Store"
```

### Step 5: Verify Email Template Exists
Make sure this file exists in your Laravel backend:
- `resources/views/emails/verification-code.blade.php`

### Step 6: Restart Laravel Server
After updating `.env`:
```bash
php artisan config:cache
php artisan serve
```

## Expected Result
After configuration, users should receive actual emails with 6-digit verification codes.

## Testing
1. Flutter app calls: `POST /api/auth/send-verification-email`
2. Backend sends real email with verification code
3. User receives email and enters code
4. Flutter app calls: `POST /api/auth/verify-email-code`
5. Backend validates code and allows registration

## Current Workaround
The Flutter app has been updated to show fallback verification codes in the console if the backend email isn't configured. This allows continued testing while email is being set up.

Users will see:
```
📧 EMAIL SENT SUCCESSFULLY
⚠️  If no email received, backend SMTP not configured  
💡 Ask backend developer to set up SMTP in Laravel .env
📧 FALLBACK - EMAIL VERIFICATION CODE
Code: 123456
```

This is temporary - remove the fallback once real emails are working.
