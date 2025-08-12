# Real Email Verification Setup Guide

## Testing Your App's Email Verification System

Currently, your app is configured for testing with simulated emails. Here's how to test and set up real email sending:

## Current Testing Method (No Setup Required)

1. **Run the app**: `flutter run -d ios`
2. **Start registration**: Tap "Sign Up" and enter any email
3. **Watch console output**: Look for lines like:
   ```
   Simulated verification code for user@email.com: 1234
   ```
4. **Enter the code**: Use the printed code to complete verification

## Option 1: EmailJS Setup (Easiest for Testing)

### Step 1: Create EmailJS Account
1. Go to https://www.emailjs.com/
2. Sign up for a free account
3. Create a new service (Gmail, Outlook, etc.)
4. Create an email template

### Step 2: Configure Template
Create a template with these variables:
```html
Subject: Your Nutrifarm Store Verification Code

Dear User,

Your verification code for {{app_name}} is: {{verification_code}}

This code will expire in {{expiry_minutes}} minutes.

If you didn't request this code, please ignore this email.

Best regards,
Nutrifarm Store Team
```

### Step 3: Update Configuration
In `/lib/services/real_email_service.dart`, replace:
```dart
static const String _serviceId = 'YOUR_SERVICE_ID';      // Your EmailJS Service ID
static const String _templateId = 'YOUR_TEMPLATE_ID';    // Your EmailJS Template ID  
static const String _publicKey = 'YOUR_PUBLIC_KEY';      // Your EmailJS Public Key
```

### Step 4: Update Your App
Replace the EmailService import in your verification pages:
```dart
// Replace this import
import '../services/email_service.dart';

// With this
import '../services/real_email_service.dart';

// And change EmailService() to RealEmailService()
```

## Option 2: Laravel Backend (Production Ready)

### Step 1: Set Up Laravel Backend
1. Create a Laravel project
2. Install required packages:
   ```bash
   composer install
   php artisan make:migration create_email_verifications_table
   ```

### Step 2: Configure Mail Settings
In your Laravel `.env` file:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="Nutrifarm Store"
```

### Step 3: Create API Endpoints
```php
// routes/api.php
Route::post('/send-verification-email', [AuthController::class, 'sendVerificationEmail']);
Route::post('/verify-email-code', [AuthController::class, 'verifyEmailCode']);
```

### Step 4: Update Flutter App
Update the base URL in your email service:
```dart
static const String _baseUrl = 'https://your-domain.com/api';
```

## Option 3: Firebase (Google's Solution)

### Step 1: Add Firebase to Your Project
```bash
flutter pub add firebase_core firebase_auth
```

### Step 2: Configure Firebase
1. Go to https://console.firebase.google.com/
2. Create a new project
3. Add your iOS app
4. Download and add `GoogleService-Info.plist`

### Step 3: Enable Email Authentication
1. In Firebase Console, go to Authentication
2. Enable "Email/Password" sign-in method
3. Configure email templates

## Testing Steps

1. **Choose your method** (EmailJS recommended for quick testing)
2. **Configure credentials** as shown above
3. **Run the app**: `flutter run -d ios`
4. **Test the flow**:
   - Tap "Sign Up"
   - Enter your real email address
   - Check your email inbox
   - Enter the received code
   - Complete registration

## Troubleshooting

### Email Not Received?
- Check spam/junk folder
- Verify email service configuration
- Check console for error messages
- Ensure email template is correctly set up

### Code Expiry Issues?
- Codes expire after 10 minutes
- Request a new code if expired
- Check system time is correct

### Development Tips
- Use a real email address you can access
- Test with different email providers
- Monitor console output for debugging
- Keep EmailJS within free tier limits (200 emails/month)

## Current App Status
✅ **Authentication system**: Complete and ready
✅ **Email verification flow**: Implemented
✅ **User registration**: Working with simulated emails
⏳ **Real email sending**: Needs configuration (see options above)

Choose EmailJS for quickest testing, or Laravel backend for production use.
