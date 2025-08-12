#!/bin/bash

# Nutrifarm Email Setup Script
echo "🌱 Nutrifarm Email Configuration Setup"
echo "======================================"
echo ""

# Check current configuration
echo "Current configuration:"
echo "MAIL_MAILER: $(grep MAIL_MAILER .env | cut -d '=' -f2)"
echo "MAIL_USERNAME: $(grep MAIL_USERNAME .env | cut -d '=' -f2)"
echo ""

echo "To send real emails, you need to:"
echo ""
echo "1. 📧 Set up Gmail App Password:"
echo "   - Go to https://myaccount.google.com/"
echo "   - Security → 2-Step Verification (enable if not already)"
echo "   - App passwords → Generate password for 'Mail'"
echo ""

echo "2. 🔧 Update your .env file with:"
echo "   MAIL_MAILER=smtp"
echo "   MAIL_USERNAME=your-gmail@gmail.com"
echo "   MAIL_PASSWORD=your-16-char-app-password"
echo ""

echo "3. 🧪 Test the configuration:"
echo "   php artisan config:clear"
echo "   php artisan test:smtp your-email@gmail.com"
echo ""

echo "4. 🚀 Start queue worker (for production):"
echo "   php artisan queue:work"
echo ""

echo "Example .env configuration:"
echo "-------------------------"
echo "MAIL_MAILER=smtp"
echo "MAIL_HOST=smtp.gmail.com"
echo "MAIL_PORT=587"
echo "MAIL_USERNAME=nutrifarm.noreply@gmail.com"
echo "MAIL_PASSWORD=abcd efgh ijkl mnop"
echo "MAIL_ENCRYPTION=tls"
echo "MAIL_FROM_ADDRESS=\"noreply@nutrifarm.com\""
echo "MAIL_FROM_NAME=\"Nutrifarm\""
echo ""

echo "💡 Need help? Check:"
echo "   - GMAIL_SMTP_SETUP.md (step-by-step Gmail setup)"
echo "   - MAILGUN_SMTP_SETUP.md (production alternative)"
echo "   - SMTP_EMAIL_CONFIGURATION_GUIDE.md (all providers)"

read -p "Press Enter to continue..."
