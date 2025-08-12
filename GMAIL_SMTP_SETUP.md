# Gmail SMTP Setup Guide

## Step-by-Step Gmail Configuration

### 1. Create a Gmail Account for Nutrifarm
- Create a new Gmail account like `nutrifarm.noreply@gmail.com`
- Or use your existing Gmail account

### 2. Enable 2-Factor Authentication
1. Go to [Google Account Settings](https://myaccount.google.com/)
2. Click "Security" in the left sidebar
3. Enable "2-Step Verification" if not already enabled

### 3. Generate App Password
1. In Google Account Settings → Security
2. Click "App passwords" (you'll need 2FA enabled first)
3. Select "Mail" as the app
4. Select "Other" as the device and enter "Nutrifarm Backend"
5. Click "Generate"
6. **Copy the 16-character password** (e.g., `abcd efgh ijkl mnop`)

### 4. Update Your .env File
Replace the mail configuration in your `.env` file with:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=nutrifarm.noreply@gmail.com
MAIL_PASSWORD=abcd efgh ijkl mnop
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@nutrifarm.com"
MAIL_FROM_NAME="Nutrifarm"
```

### 5. Test the Configuration
Run these commands to test:

```bash
# Clear config cache
php artisan config:clear

# Test SMTP connection
php artisan test:smtp your-personal-email@gmail.com

# Test via API
curl -X POST http://localhost:8000/api/auth/send-verification-email \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email": "your-personal-email@gmail.com"}'
```

You should receive a beautiful verification email in your inbox!

## Gmail Setup Limitations
- 500 emails per day limit (perfect for testing)
- May go to spam initially (normal for new sending domains)
- Great for development, consider upgrading for production
