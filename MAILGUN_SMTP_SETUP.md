# Mailgun SMTP Setup Guide (Recommended for Production)

## Why Mailgun?
- ✅ 10,000 free emails per month
- ✅ Better deliverability than Gmail
- ✅ Professional email infrastructure
- ✅ Detailed analytics and monitoring
- ✅ Less likely to be marked as spam

## Step-by-Step Mailgun Setup

### 1. Create Mailgun Account
1. Go to [mailgun.com](https://www.mailgun.com/)
2. Sign up for a free account
3. Verify your email address

### 2. Add and Verify Your Domain
1. In Mailgun dashboard, go to "Sending" → "Domains"
2. Click "Add New Domain"
3. Enter your domain (e.g., `mg.nutrifarm.com` or use Mailgun's sandbox)
4. Choose your region (US or EU)

### 3. Configure DNS Records
Mailgun will provide DNS records to add to your domain:
- **TXT record** for domain verification
- **MX records** for receiving emails
- **CNAME records** for tracking

**If you don't have a domain yet**, use Mailgun's sandbox domain for testing.

### 4. Get SMTP Credentials
1. In Mailgun dashboard, go to "Sending" → "Domain settings"
2. Click on your domain
3. Go to "SMTP credentials" tab
4. Note down:
   - SMTP hostname: `smtp.mailgun.org`
   - Port: `587`
   - Username: `postmaster@your-domain.com`
   - Password: (click "Reset password" to get a new one)

### 5. Update Your .env File
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=postmaster@mg.nutrifarm.com
MAIL_PASSWORD=your-mailgun-smtp-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@nutrifarm.com"
MAIL_FROM_NAME="Nutrifarm"
```

### 6. Test Configuration
```bash
# Clear config cache
php artisan config:clear

# Test SMTP
php artisan test:smtp your-email@gmail.com
```

## Mailgun Benefits
- 📊 Email analytics and tracking
- 🚀 High deliverability rates
- 💳 Pay-as-you-scale pricing
- 🛡️ Built-in spam filtering
- 📧 Professional email infrastructure
