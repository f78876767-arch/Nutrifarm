# 🚀 Quick Flutter Setup for Nutrifarm Login

## Step-by-Step Implementation

### 1. Add HTTP Package
```yaml
# In pubspec.yaml
dependencies:
  flutter:
    sdk: flutter
  http: ^1.1.0
  shared_preferences: ^2.2.2
```

Run: `flutter pub get`

### 2. Create API Service
Copy the code from `FLUTTER_LOGIN_IMPLEMENTATION.md` and create:
- `lib/services/auth_service.dart`

### 3. Create Screens
Copy the code from `FLUTTER_LOGIN_IMPLEMENTATION.md` and create:
- `lib/screens/login_screen.dart`
- `lib/screens/email_verification_screen.dart`

### 4. Update Main App
```dart
// lib/main.dart
import 'package:flutter/material.dart';
import 'screens/login_screen.dart';

void main() {
  runApp(MyApp());
}

class MyApp extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Nutrifarm',
      theme: ThemeData(
        primarySwatch: Colors.green,
        visualDensity: VisualDensity.adaptivePlatformDensity,
      ),
      home: const LoginScreen(),
      debugShowCheckedModeBanner: false,
    );
  }
}
```

### 5. Test the Flow

1. **Start Laravel Server:**
   ```bash
   cd /Users/kevin/Nutrifarm
   php artisan serve
   ```

2. **Run Flutter App:**
   ```bash
   flutter run
   ```

3. **Test Login:**
   - Tap "Login with Test Account" button
   - OR manually enter: test@example.com / password123

4. **Test Email Verification:**
   - Tap "Test Email Verification" button
   - Check Laravel logs for the verification code
   - Enter the code in the Flutter app

---

## 🧪 Development Testing

### Quick Test Buttons (in LoginScreen)
- **"Login with Test Account"** - Auto-fills and logs in
- **"Test Email Verification"** - Tests email verification flow

### Verification Code Lookup
```bash
# In Laravel project terminal
tail -f storage/logs/laravel.log
```

### API Endpoints Test
```bash
# Run this to verify APIs
./test_apis.sh
```

---

## 📱 Expected User Flow

```
LoginScreen
    ↓
[Tap "Login with Test Account"]
    ↓
HomeScreen (with logout button)

OR

LoginScreen  
    ↓
[Tap "Test Email Verification"]
    ↓
EmailVerificationScreen
    ↓
[Enter code from Laravel logs]
    ↓
Success → Back to LoginScreen
```

---

## 🔧 Network Configuration

### For Android Emulator
The `127.0.0.1:8000` should work directly.

### For iOS Simulator
The `127.0.0.1:8000` should work directly.

### For Physical Device
Change the base URL in `auth_service.dart`:
```dart
static const String baseUrl = 'http://YOUR_COMPUTER_IP:8000/api';
```

Find your IP with:
```bash
# macOS
ifconfig | grep "inet " | grep -v 127.0.0.1
```

---

## ⚡ Quick Start Commands

```bash
# 1. Start Laravel (Terminal 1)
cd /Users/kevin/Nutrifarm
php artisan serve

# 2. Start Flutter (Terminal 2)
cd /path/to/your/flutter/project
flutter run

# 3. Monitor Laravel logs (Terminal 3)
cd /Users/kevin/Nutrifarm
tail -f storage/logs/laravel.log
```

---

## 🎯 What You Get

✅ **Complete Login System**
✅ **Email Verification Flow**
✅ **Token Management**
✅ **Error Handling**
✅ **Development Test Buttons**
✅ **Professional UI**

Ready to test! Your Laravel backend is fully functional and ready for Flutter integration.
