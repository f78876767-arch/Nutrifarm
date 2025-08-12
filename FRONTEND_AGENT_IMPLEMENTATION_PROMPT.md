# Frontend Agent Implementation Prompt

## Task Overview
You are tasked with implementing a complete 3-step authentication system for a Flutter mobile app that connects to a Laravel backend API. The authentication flow consists of email verification, user registration, and traditional login.

## Backend API Details
The backend is already fully implemented and running at `http://localhost:8000` with the following endpoints ready for integration.

### Authentication Endpoints

#### Step 1: Send Verification Email
- **Endpoint**: `POST /api/auth/send-verification-email`
- **Request Body**: 
  ```json
  {
    "email": "user@example.com"
  }
  ```
- **Success Response (200)**:
  ```json
  {
    "success": true,
    "message": "Verification code sent to your email",
    "expires_in_minutes": 10
  }
  ```

#### Step 2: Verify Email Code
- **Endpoint**: `POST /api/auth/verify-email-code`
- **Request Body**:
  ```json
  {
    "email": "user@example.com",
    "verification_code": "123456"
  }
  ```
- **Success Response (200)**:
  ```json
  {
    "success": true,
    "message": "Email verified successfully"
  }
  ```

#### Step 3: Complete Registration
- **Endpoint**: `POST /api/auth/register-with-email-verification`
- **Request Body**:
  ```json
  {
    "email": "user@example.com",
    "verification_code": "123456",
    "name": "John Doe",
    "password": "securepassword123",
    "phone": "+1234567890"
  }
  ```
- **Success Response (200)**:
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

#### Step 4: Traditional Login (for returning users)
- **Endpoint**: `POST /api/auth/login`
- **Request Body**:
  ```json
  {
    "email": "user@example.com",
    "password": "securepassword123"
  }
  ```
- **Success Response (200)**:
  ```json
  {
    "success": true,
    "message": "Login successful",
    "user": { /* user object */ },
    "token": "7|MmzZIQvWg0sYylqTlzff5CF51p4l7VVqmfgTLbU9beb0fff5"
  }
  ```

### Authenticated Endpoints

#### Get User Profile
- **Endpoint**: `GET /api/me`
- **Headers**: `Authorization: Bearer {token}`
- **Success Response (200)**:
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

#### Logout
- **Endpoint**: `POST /api/logout`
- **Headers**: `Authorization: Bearer {token}`
- **Success Response (200)**:
  ```json
  {
    "success": true,
    "message": "Logged out successfully"
  }
  ```

## Implementation Requirements

### Project Structure
Create a Flutter project with the following structure:
```
lib/
├── main.dart
├── models/
│   ├── user_model.dart
│   └── api_response_model.dart
├── services/
│   ├── api_service.dart
│   ├── auth_service.dart
│   └── storage_service.dart
├── screens/
│   ├── auth/
│   │   ├── email_input_screen.dart
│   │   ├── verification_code_screen.dart
│   │   ├── registration_screen.dart
│   │   └── login_screen.dart
│   ├── home_screen.dart
│   └── profile_screen.dart
├── widgets/
│   ├── custom_button.dart
│   ├── custom_text_field.dart
│   └── loading_widget.dart
└── utils/
    ├── constants.dart
    ├── validators.dart
    └── routes.dart
```

### Core Features to Implement

#### 1. Authentication Flow Screens

**Email Input Screen (`email_input_screen.dart`)**
- Input field for email address
- Email validation (format check)
- "Send Verification Code" button
- Loading state during API call
- Error handling for invalid emails
- Navigation to verification code screen on success

**Verification Code Screen (`verification_code_screen.dart`)**
- 6-digit code input (preferably with individual boxes)
- Auto-focus progression between input boxes
- "Verify Code" button
- Resend code functionality with timer (60 seconds cooldown)
- Error handling for invalid/expired codes
- Navigation to registration screen on success

**Registration Screen (`registration_screen.dart`)**
- Pre-filled email field (disabled/read-only)
- Name input field
- Password input field with visibility toggle
- Phone number input field (optional)
- Form validation for all fields
- "Complete Registration" button
- Loading state during registration
- Error handling and validation messages
- Automatic login and navigation to home screen on success

**Login Screen (`login_screen.dart`)**
- Email input field
- Password input field with visibility toggle
- "Login" button
- "Forgot Password?" link (can be placeholder for now)
- "Don't have an account? Sign up" navigation to email input
- Error handling for invalid credentials
- Navigation to home screen on success

#### 2. Core Services

**API Service (`api_service.dart`)**
```dart
class ApiService {
  static const String baseUrl = 'http://localhost:8000/api';
  
  // HTTP methods with proper headers
  Future<Map<String, dynamic>> post(String endpoint, Map<String, dynamic> data);
  Future<Map<String, dynamic>> get(String endpoint, {String? token});
  
  // Authentication endpoints
  Future<Map<String, dynamic>> sendVerificationEmail(String email);
  Future<Map<String, dynamic>> verifyEmailCode(String email, String code);
  Future<Map<String, dynamic>> registerWithVerification(Map<String, dynamic> data);
  Future<Map<String, dynamic>> login(String email, String password);
  Future<Map<String, dynamic>> logout(String token);
  Future<Map<String, dynamic>> getProfile(String token);
}
```

**Auth Service (`auth_service.dart`)**
```dart
class AuthService {
  // Token management
  Future<void> saveToken(String token);
  Future<String?> getToken();
  Future<void> clearToken();
  
  // User management
  Future<void> saveUser(UserModel user);
  Future<UserModel?> getUser();
  Future<void> clearUser();
  
  // Authentication state
  bool get isLoggedIn;
  Stream<bool> get authStateStream;
  
  // Authentication methods
  Future<bool> sendVerificationEmail(String email);
  Future<bool> verifyEmailCode(String email, String code);
  Future<bool> completeRegistration(Map<String, dynamic> data);
  Future<bool> loginUser(String email, String password);
  Future<bool> logoutUser();
}
```

**Storage Service (`storage_service.dart`)**
- Use `shared_preferences` for local storage
- Methods for storing/retrieving token, user data
- Secure storage considerations

#### 3. Data Models

**User Model (`user_model.dart`)**
```dart
class UserModel {
  final int id;
  final String name;
  final String email;
  final String? phone;
  final DateTime? emailVerifiedAt;
  final DateTime createdAt;
  final DateTime updatedAt;

  // fromJson, toJson, copyWith methods
}
```

**API Response Model (`api_response_model.dart`)**
```dart
class ApiResponse<T> {
  final bool success;
  final String message;
  final T? data;
  final Map<String, List<String>>? errors;

  // Constructor and factory methods
}
```

#### 4. UI/UX Requirements

**Design Guidelines**
- Modern, clean interface with green theme (#28a745) matching Nutrifarm branding
- Consistent spacing and typography
- Loading states for all API calls
- Proper error message display
- Form validation with clear error messages
- Responsive design for different screen sizes

**User Experience**
- Smooth transitions between screens
- Auto-focus on input fields
- Keyboard handling (dismiss on tap outside)
- Back button handling (prevent going back from verification screen)
- Progress indicators for multi-step flow
- Success feedback for completed actions

#### 5. State Management
Use your preferred state management solution (Provider, Riverpod, Bloc, etc.) to:
- Manage authentication state globally
- Handle loading states
- Manage form states and validation
- Persist user session

#### 6. Error Handling
Implement comprehensive error handling for:
- Network connectivity issues
- API error responses (400, 401, 422, 500)
- Validation errors
- Token expiration
- Timeout scenarios

#### 7. Testing
Create unit tests for:
- API service methods
- Authentication service logic
- Model serialization/deserialization
- Form validation

### Dependencies Required
Add these to your `pubspec.yaml`:
```yaml
dependencies:
  http: ^1.1.0
  shared_preferences: ^2.2.2
  # Your choice of state management (provider, riverpod, bloc, etc.)
  # Form validation library (if needed)
  # Any UI libraries you prefer

dev_dependencies:
  mockito: ^5.4.2  # for testing
```

### Authentication Flow Logic

#### First-Time User Flow:
1. User enters email → API call to send verification code
2. User enters 6-digit code → API call to verify code
3. User completes registration form → API call to register + auto-login
4. Navigate to main app with stored token

#### Returning User Flow:
1. User enters email + password → API call to login
2. Store token and user data
3. Navigate to main app

#### App Launch Logic:
1. Check if token exists in storage
2. If token exists, validate with `/api/me` endpoint
3. If valid, navigate to home screen
4. If invalid/expired, clear storage and show login screen
5. If no token, show login screen

### Security Considerations
- Never store passwords locally
- Use HTTPS in production
- Implement token refresh logic if needed
- Clear sensitive data on logout
- Validate all inputs on client side
- Handle token expiration gracefully

### Additional Features (Optional but Recommended)
- Remember me functionality
- Biometric authentication
- Dark mode support
- Internationalization
- Offline handling
- Auto-logout on extended inactivity

### Deliverables Expected
1. Complete Flutter project with all authentication screens
2. Working API integration with the Laravel backend
3. Proper state management implementation
4. Error handling and validation
5. Clean, maintainable code structure
6. Basic unit tests for core functionality
7. README.md with setup and running instructions

### Success Criteria
- User can complete the full registration flow (email → code → registration)
- Returning users can login with email/password
- Authentication state persists across app restarts
- All error scenarios are handled gracefully
- Code is well-structured and follows Flutter best practices
- UI is responsive and user-friendly

### Testing Instructions
You can test against the live backend API. The backend is already running and all endpoints are functional. Use the curl examples in the backend documentation to verify API responses before implementing the Flutter integration.

Start with the email `test@nutrifarm.com` for testing, and you'll receive actual verification codes that you can use to test the complete flow.

Good luck with the implementation! The backend is fully ready and waiting for your beautiful Flutter frontend.
