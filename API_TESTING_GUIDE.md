# API Testing Guide for Flutter Development

## Base URL
```
http://127.0.0.1:8000/api
```

## Authentication Token (Development)
```
Bearer 4|k6WiTK9a3RSMbUj51yxVJIqxKhVehj2xa8GpsHkh5769faec
```

## Test Account
- **Email:** test@example.com
- **Password:** password123
- **User ID:** 12

---

## 🧪 API Testing Commands

### 1. Authentication Tests

#### Login
```bash
curl -X POST "http://127.0.0.1:8000/api/login" \
  -H "Content-Type: application/json" \
  -d '{"email": "test@example.com", "password": "password123"}'
```

#### Register New User
```bash
curl -X POST "http://127.0.0.1:8000/api/register" \
  -H "Content-Type: application/json" \
  -d '{"name": "New User", "email": "newuser@example.com", "password": "password123"}'
```

### 2. Email Verification Tests

#### Generate Verification Code
```bash
curl -X POST "http://127.0.0.1:8000/api/email/generate-code" \
  -H "Content-Type: application/json" \
  -d '{"email": "test@example.com"}'
```

#### Send Custom Verification Code
```bash
curl -X POST "http://127.0.0.1:8000/api/send-verification-email" \
  -H "Content-Type: application/json" \
  -d '{"email": "test@example.com", "verification_code": "1234"}'
```

#### Verify Email Code
```bash
curl -X POST "http://127.0.0.1:8000/api/verify-email-code" \
  -H "Content-Type: application/json" \
  -d '{"email": "test@example.com", "verification_code": "1234"}'
```

#### Check Verification Status
```bash
curl -X POST "http://127.0.0.1:8000/api/email/check-status" \
  -H "Content-Type: application/json" \
  -d '{"email": "test@example.com"}'
```

### 3. Product Tests (Public - No Auth Required)

#### Get All Products
```bash
curl -X GET "http://127.0.0.1:8000/api/products" \
  -H "Accept: application/json"
```

#### Get Single Product
```bash
curl -X GET "http://127.0.0.1:8000/api/products/1" \
  -H "Accept: application/json"
```

### 4. Cart Tests (Requires Authentication)

#### Get Cart
```bash
curl -X GET "http://127.0.0.1:8000/api/cart" \
  -H "Authorization: Bearer 4|k6WiTK9a3RSMbUj51yxVJIqxKhVehj2xa8GpsHkh5769faec" \
  -H "Accept: application/json"
```

#### Add Item to Cart
```bash
curl -X POST "http://127.0.0.1:8000/api/cart" \
  -H "Authorization: Bearer 4|k6WiTK9a3RSMbUj51yxVJIqxKhVehj2xa8GpsHkh5769faec" \
  -H "Content-Type: application/json" \
  -d '{"product_id": 1, "quantity": 2}'
```

#### Update Cart Item (replace {cart_item_id} with actual ID)
```bash
curl -X PUT "http://127.0.0.1:8000/api/cart/{cart_item_id}" \
  -H "Authorization: Bearer 4|k6WiTK9a3RSMbUj51yxVJIqxKhVehj2xa8GpsHkh5769faec" \
  -H "Content-Type: application/json" \
  -d '{"quantity": 3}'
```

#### Get Cart Count
```bash
curl -X GET "http://127.0.0.1:8000/api/cart/count" \
  -H "Authorization: Bearer 4|k6WiTK9a3RSMbUj51yxVJIqxKhVehj2xa8GpsHkh5769faec" \
  -H "Accept: application/json"
```

#### Clear Cart
```bash
curl -X DELETE "http://127.0.0.1:8000/api/cart" \
  -H "Authorization: Bearer 4|k6WiTK9a3RSMbUj51yxVJIqxKhVehj2xa8GpsHkh5769faec" \
  -H "Accept: application/json"
```

### 5. Favorites Tests (Requires Authentication)

#### Get Favorites
```bash
curl -X GET "http://127.0.0.1:8000/api/favorites" \
  -H "Authorization: Bearer 4|k6WiTK9a3RSMbUj51yxVJIqxKhVehj2xa8GpsHkh5769faec" \
  -H "Accept: application/json"
```

#### Add to Favorites
```bash
curl -X POST "http://127.0.0.1:8000/api/favorites" \
  -H "Authorization: Bearer 4|k6WiTK9a3RSMbUj51yxVJIqxKhVehj2xa8GpsHkh5769faec" \
  -H "Content-Type: application/json" \
  -d '{"product_id": 1}'
```

#### Toggle Favorite
```bash
curl -X POST "http://127.0.0.1:8000/api/favorites/toggle" \
  -H "Authorization: Bearer 4|k6WiTK9a3RSMbUj51yxVJIqxKhVehj2xa8GpsHkh5769faec" \
  -H "Content-Type: application/json" \
  -d '{"product_id": 1}'
```

#### Check if Product is Favorite
```bash
curl -X GET "http://127.0.0.1:8000/api/favorites/check/1" \
  -H "Authorization: Bearer 4|k6WiTK9a3RSMbUj51yxVJIqxKhVehj2xa8GpsHkh5769faec" \
  -H "Accept: application/json"
```

### 6. User Profile Test

#### Get Current User
```bash
curl -X GET "http://127.0.0.1:8000/api/user" \
  -H "Authorization: Bearer 4|k6WiTK9a3RSMbUj51yxVJIqxKhVehj2xa8GpsHkh5769faec" \
  -H "Accept: application/json"
```

---

## 🚀 Quick Start for Flutter Integration

### 1. Update your Flutter HTTP service base URL:
```dart
static const String baseUrl = 'http://127.0.0.1:8000/api';
```

### 2. Use the development token for testing:
```dart
static const String devToken = '4|k6WiTK9a3RSMbUj51yxVJIqxKhVehj2xa8GpsHkh5769faec';
```

### 3. Test the API endpoints above to ensure connectivity before implementing in Flutter.

### 4. Email Verification Flow:
1. Generate code: `POST /api/email/generate-code`
2. Show code input UI in Flutter
3. Verify code: `POST /api/verify-email-code`
4. Handle success/error responses

---

## 📧 Email Configuration

The system is currently configured with `MAIL_MAILER=log` for development, which means emails are logged to `storage/logs/laravel.log` instead of being sent. This is perfect for development and testing.

To check generated verification codes during development:
```bash
tail -f storage/logs/laravel.log
```

## 🔧 Mail Configuration for Production

When ready for production, update your `.env` file with real SMTP settings:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="Nutrifarm"
```

---

**✅ All APIs tested and working!** You now have a complete backend system with authentication, email verification, cart management, and favorites functionality ready for your Flutter frontend integration.
