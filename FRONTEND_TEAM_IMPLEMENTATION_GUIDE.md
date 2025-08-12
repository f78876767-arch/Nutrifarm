# Complete 3-Step Authentication Implementation Guide

This document provides the complete implementation guide for the 3-step authentication system that matches your frontend requirements exactly.

## Overview

The authentication system follows a 3-step process:
1. **Email Verification**: Send and verify a 6-digit code
2. **Registration**: Complete user registration with name and password
3. **Login**: Traditional email/password authentication

## API Endpoints

### Step 1: Send Verification Email

**Endpoint:** `POST /api/auth/send-verification-email`

**Request:**
```json
{
    "email": "user@example.com"
}
```

**Response (Success):**
```json
{
    "success": true,
    "message": "Verification code sent to your email",
    "expires_in_minutes": 10
}
```

**Response (Error):**
```json
{
    "success": false,
    "message": "Failed to send verification email",
    "error": "Error details"
}
```

### Step 1.5: Verify Email Code

**Endpoint:** `POST /api/auth/verify-email-code`

**Request:**
```json
{
    "email": "user@example.com",
    "verification_code": "123456"
}
```

**Response (Success):**
```json
{
    "success": true,
    "message": "Email verified successfully"
}
```

**Response (Error):**
```json
{
    "success": false,
    "message": "Invalid or expired verification code"
}
```

### Step 2: Complete Registration

**Endpoint:** `POST /api/auth/register-with-email-verification`

**Request:**
```json
{
    "email": "user@example.com",
    "verification_code": "123456",
    "name": "John Doe",
    "password": "securepassword123",
    "phone": "+1234567890"  // optional
}
```

**Response (Success):**
```json
{
    "success": true,
    "message": "Registration successful",
    "user": {
        "id": 13,
        "name": "John Doe",
        "email": "user@example.com",
        "phone": "+1234567890",
        "email_verified_at": null,
        "created_at": "2025-08-09T16:55:18.000000Z",
        "updated_at": "2025-08-09T16:55:18.000000Z"
    },
    "token": "6|6DF9hxDsPMtKRzHoU2TMAyLzyFqKyjYq2DVPPjGU8d89f54f"
}
```

### Step 3: Traditional Login

**Endpoint:** `POST /api/auth/login`

**Request:**
```json
{
    "email": "user@example.com",
    "password": "securepassword123"
}
```

**Response (Success):**
```json
{
    "success": true,
    "message": "Login successful",
    "user": {
        "id": 13,
        "name": "John Doe",
        "email": "user@example.com",
        "phone": "+1234567890",
        "email_verified_at": null,
        "created_at": "2025-08-09T16:55:18.000000Z",
        "updated_at": "2025-08-09T16:55:18.000000Z"
    },
    "token": "7|MmzZIQvWg0sYylqTlzff5CF51p4l7VVqmfgTLbU9beb0fff5"
}
```

**Response (Error):**
```json
{
    "success": false,
    "message": "Invalid credentials"
}
```

## Authenticated Endpoints

### Get User Profile

**Endpoint:** `GET /api/me`

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

**Response:**
```json
{
    "success": true,
    "user": {
        "id": 13,
        "name": "John Doe",
        "email": "user@example.com",
        "phone": "+1234567890",
        "email_verified_at": null,
        "created_at": "2025-08-09T16:55:18.000000Z",
        "updated_at": "2025-08-09T16:55:18.000000Z"
    }
}
```

### Logout

**Endpoint:** `POST /api/logout`

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

**Response:**
```json
{
    "success": true,
    "message": "Logged out successfully"
}
```

## Frontend Integration Flow

### Step 1: Email Verification Screen

1. User enters email address
2. App calls `POST /api/auth/send-verification-email`
3. On success, navigate to verification code screen

### Step 2: Verification Code Screen

1. User enters 6-digit code received via email
2. App calls `POST /api/auth/verify-email-code`
3. On success, navigate to registration form

### Step 3: Registration Form

1. User enters name and password (email pre-filled from step 1)
2. App calls `POST /api/auth/register-with-email-verification`
3. On success, store the token and navigate to main app

### Step 4: Future Logins

1. User enters email and password
2. App calls `POST /api/auth/login`
3. On success, store the token and navigate to main app

## Error Handling

All endpoints follow consistent error response format:

```json
{
    "success": false,
    "message": "Error description",
    "errors": {  // Optional validation errors
        "field_name": ["Validation error message"]
    }
}
```

## Validation Rules

### Email Verification
- `email`: Required, valid email format

### Verification Code
- `email`: Required, valid email format
- `verification_code`: Required, exactly 6 digits

### Registration
- `email`: Required, valid email, unique in database
- `verification_code`: Required, exactly 6 digits, must be verified
- `name`: Required, maximum 255 characters
- `password`: Required, minimum 8 characters
- `phone`: Optional, maximum 20 characters

### Login
- `email`: Required, valid email format
- `password`: Required

## Security Features

1. **Code Expiration**: Verification codes expire after 10 minutes
2. **Single Use**: Each verification code can only be used once
3. **Email Uniqueness**: Each email can only be registered once
4. **Secure Tokens**: Laravel Sanctum provides secure API tokens
5. **Password Hashing**: All passwords are securely hashed

## Testing Endpoints

You can test the complete flow using curl:

```bash
# Step 1: Send verification email
curl -X POST http://localhost:8000/api/auth/send-verification-email \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email": "test@example.com"}'

# Step 2: Verify email code (get code from database or email)
curl -X POST http://localhost:8000/api/auth/verify-email-code \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email": "test@example.com", "verification_code": "123456"}'

# Step 3: Complete registration
curl -X POST http://localhost:8000/api/auth/register-with-email-verification \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "test@example.com",
    "verification_code": "123456",
    "name": "Test User",
    "password": "securepassword123"
  }'

# Step 4: Login
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "securepassword123"
  }'

# Get profile (replace token)
curl -X GET http://localhost:8000/api/me \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"

# Logout (replace token)
curl -X POST http://localhost:8000/api/logout \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

## Status Codes

- `200`: Success
- `400`: Bad Request (invalid data)
- `401`: Unauthorized (invalid token)
- `422`: Validation Error
- `500`: Server Error

## Notes

1. All endpoints expect `Content-Type: application/json` and `Accept: application/json` headers
2. Authenticated endpoints require `Authorization: Bearer {token}` header
3. Tokens are persistent until logout or manual revocation
4. Email templates are styled with Nutrifarm branding
5. The system is compatible with your existing product, cart, and favorites APIs

## Complete Implementation Checklist

- ✅ Email verification system with 6-digit codes
- ✅ Verification code expiration (10 minutes)
- ✅ Email uniqueness validation
- ✅ Registration with name and password
- ✅ Traditional login authentication
- ✅ Secure token-based authentication
- ✅ Profile management endpoints
- ✅ Logout functionality
- ✅ Consistent error handling
- ✅ Beautiful email templates
- ✅ Complete API documentation
- ✅ Testing examples provided

Your backend is now fully ready for the 3-step authentication flow as specified in your requirements!
