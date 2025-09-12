import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:flutter/foundation.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'api_service.dart';
import 'push_service.dart';

class AuthService extends ChangeNotifier {
  static final AuthService _instance = AuthService._internal();
  factory AuthService() => _instance;
  AuthService._internal();

  // Base URL for your Laravel backend
  static const String _baseUrl = 'http://127.0.0.1:8000/api';
  
  UserData? _currentUser;
  SharedPreferences? _prefs;
  bool _isAuthenticated = false;
  String? _authToken;

  UserData? get currentUser => _currentUser;
  bool get isAuthenticated => _isAuthenticated;
  String? get authToken => _authToken;
  String get userName => _currentUser?.name ?? 'Guest';
  String get userEmail => _currentUser?.email ?? '';

  Future<void> initialize() async {
    _prefs = await SharedPreferences.getInstance();
    
    // Load saved token
    _authToken = _prefs?.getString('auth_token');
    print('🔥 AUTH DEBUG: Loaded token from prefs: ${_authToken != null ? "present (${_authToken!.substring(0, 20)}...)" : "null"}');
    
    // Load user data
    final userData = _prefs?.getString('user_data');
    if (userData != null && _authToken != null) {
      try {
        _currentUser = UserData.fromJson(json.decode(userData));
        _isAuthenticated = true;
        
        // Set token in API service
        ApiService.setAuthToken(_authToken!);
        print('🔥 AUTH DEBUG: Token set in ApiService, user authenticated as: ${_currentUser?.name}');
        
        // Verify token is still valid
        await _verifyToken();
      } catch (e) {
        print('🔥 AUTH DEBUG: Error loading user data: $e');
        await logout(); // Clear invalid data
      }
    } else {
      print('🔥 AUTH DEBUG: No valid auth data found');
    }
    notifyListeners();
  }

  /// Register a new user with email verification
  Future<AuthResult> registerWithEmailVerification({
    required String email,
    required String verificationCode,
    required String name,
    required String password,
    String? phone,
  }) async {
    try {
      final response = await http.post(
        Uri.parse('$_baseUrl/auth/register-with-email-verification'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: json.encode({
          'email': email,
          'verification_code': verificationCode,
          'name': name,
          'password': password,
          'password_confirmation': password,
          'phone': phone,
        }),
      );

      print('Registration response status: ${response.statusCode}');
      print('Registration response body: ${response.body}');

      if (response.body.isEmpty) {
        return AuthResult(
          success: false,
          message: 'Server returned empty response',
        );
      }

      final data = json.decode(response.body);

      if (response.statusCode == 201 || response.statusCode == 200) {
        // Registration successful
        _authToken = data['token'];
        _currentUser = UserData.fromJson(data['user']);
        _isAuthenticated = true;

        await _saveAuthData();
        ApiService.setAuthToken(_authToken!);
        notifyListeners();

        return AuthResult(success: true, message: 'Registration successful');
      } else {
        return AuthResult(
          success: false,
          message: data['message'] ?? 'Registration failed',
        );
      }
    } catch (e) {
      print('Registration error: $e');
      return AuthResult(
        success: false,
        message: 'Network error. Please check your connection.',
      );
    }
  }

  /// Login with email and password
  Future<AuthResult> login(String email, String password) async {
    try {
      final response = await http.post(
        Uri.parse('$_baseUrl/auth/login'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: json.encode({
          'email': email,
          'password': password,
        }),
      );

      final data = json.decode(response.body);

      if (response.statusCode == 200) {
        _authToken = data['token'];
        _currentUser = UserData.fromJson(data['user']);
        _isAuthenticated = true;

        await _saveAuthData();
        ApiService.setAuthToken(_authToken!);
        // Initialize push and register token
        await PushService.initialize();
        notifyListeners();

        return AuthResult(success: true, message: 'Login successful');
      } else {
        return AuthResult(
          success: false,
          message: data['message'] ?? 'Invalid credentials',
        );
      }
    } catch (e) {
      print('Login error: $e');
      return AuthResult(
        success: false,
        message: 'Network error. Please check your connection.',
      );
    }
  }

  /// Check if user is already registered with email
  Future<bool> isEmailRegistered(String email) async {
    try {
      final response = await http.post(
        Uri.parse('$_baseUrl/check-email'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: json.encode({'email': email}),
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        return data['exists'] ?? false;
      }
      return false;
    } catch (e) {
      return false;
    }
  }

  /// Update user profile
  Future<AuthResult> updateProfile({
    String? name,
    String? phone,
    String? profileImageUrl,
  }) async {
    if (!_isAuthenticated || _authToken == null) {
      return AuthResult(success: false, message: 'Not authenticated');
    }

    try {
      final response = await http.put(
        Uri.parse('$_baseUrl/profile'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'Authorization': 'Bearer $_authToken',
        },
        body: json.encode({
          if (name != null) 'name': name,
          if (phone != null) 'phone': phone,
          if (profileImageUrl != null) 'profile_image_url': profileImageUrl,
        }),
      );

      final data = json.decode(response.body);

      if (response.statusCode == 200) {
        _currentUser = UserData.fromJson(data['user']);
        await _saveAuthData();
        notifyListeners();

        return AuthResult(success: true, message: 'Profile updated');
      } else {
        return AuthResult(
          success: false,
          message: data['message'] ?? 'Update failed',
        );
      }
    } catch (e) {
      return AuthResult(
        success: false,
        message: 'Network error. Please try again.',
      );
    }
  }

  /// Request a password reset link to be sent to the user's email
  Future<AuthResult> requestPasswordReset(String email) async {
    try {
      final response = await http.post(
        Uri.parse('$_baseUrl/auth/forgot-password'),
        headers: const {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: json.encode({'email': email}),
      );

      if (response.body.isEmpty) {
        return AuthResult(success: false, message: 'Empty server response');
      }

      final data = json.decode(response.body);
      if (response.statusCode >= 200 && response.statusCode < 300) {
        return AuthResult(
          success: true,
          message: data['message'] ?? 'Password reset link sent. Please check your email.',
        );
      } else {
        return AuthResult(
          success: false,
          message: data['message'] ?? 'Failed to send reset link',
        );
      }
    } catch (e) {
      return AuthResult(
        success: false,
        message: 'Network error. Please check your connection.',
      );
    }
  }

  /// Reset password using token from email (if your backend supports this flow)
  Future<AuthResult> resetPassword({
    required String email,
    required String token,
    required String password,
  }) async {
    try {
      final response = await http.post(
        Uri.parse('$_baseUrl/auth/reset-password'),
        headers: const {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: json.encode({
          'email': email,
          'token': token,
          'password': password,
          'password_confirmation': password,
        }),
      );

      if (response.body.isEmpty) {
        return AuthResult(success: false, message: 'Empty server response');
      }

      final data = json.decode(response.body);
      if (response.statusCode >= 200 && response.statusCode < 300) {
        return AuthResult(
          success: true,
          message: data['message'] ?? 'Password has been reset successfully',
        );
      } else {
        return AuthResult(
          success: false,
          message: data['message'] ?? 'Password reset failed',
        );
      }
    } catch (e) {
      return AuthResult(
        success: false,
        message: 'Network error. Please try again.',
      );
    }
  }

  /// Verify if current token is still valid
  Future<bool> _verifyToken() async {
    if (_authToken == null) return false;

    try {
      final response = await http.get(
        Uri.parse('$_baseUrl/user'),
        headers: {
          'Accept': 'application/json',
          'Authorization': 'Bearer $_authToken',
        },
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        _currentUser = UserData.fromJson(data);
        await _saveAuthData();
        return true;
      } else {
        await logout();
        return false;
      }
    } catch (e) {
      await logout();
      return false;
    }
  }

  /// Logout user
  Future<void> logout() async {
    try {
      if (_authToken != null) {
        // Call logout API to invalidate token on server
        await http.post(
          Uri.parse('$_baseUrl/logout'),
          headers: {
            'Accept': 'application/json',
            'Authorization': 'Bearer $_authToken',
          },
        );
      }
    } catch (e) {
      // Continue with local logout even if API call fails
    }

    // Unregister device token
    await PushService.logoutCleanup();

    _currentUser = null;
    _isAuthenticated = false;
    _authToken = null;
    
    ApiService.clearAuthToken();
    await _prefs?.remove('user_data');
    await _prefs?.remove('auth_token');
    notifyListeners();
  }

  /// Save authentication data locally
  Future<void> _saveAuthData() async {
    if (_currentUser != null && _authToken != null) {
      await _prefs?.setString('user_data', json.encode(_currentUser!.toJson()));
      await _prefs?.setString('auth_token', _authToken!);
    }
  }
}

class UserData {
  final int id;
  final String name;
  final String email;
  final String? phone;
  final String? profileImageUrl;
  final DateTime createdAt;
  final DateTime updatedAt;
  final bool emailVerified;

  UserData({
    required this.id,
    required this.name,
    required this.email,
    this.phone,
    this.profileImageUrl,
    required this.createdAt,
    required this.updatedAt,
    this.emailVerified = false,
  });

  Map<String, dynamic> toJson() => {
    'id': id,
    'name': name,
    'email': email,
    'phone': phone,
    'profile_image_url': profileImageUrl,
    'created_at': createdAt.toIso8601String(),
    'updated_at': updatedAt.toIso8601String(),
    'email_verified_at': emailVerified ? DateTime.now().toIso8601String() : null,
  };

  static UserData fromJson(Map<String, dynamic> json) => UserData(
    id: json['id'],
    name: json['name'],
    email: json['email'],
    phone: json['phone'],
    profileImageUrl: json['profile_image_url'],
    createdAt: DateTime.parse(json['created_at']),
    updatedAt: DateTime.parse(json['updated_at']),
    emailVerified: json['email_verified_at'] != null,
  );
}

class AuthResult {
  final bool success;
  final String message;
  final Map<String, dynamic>? data;

  AuthResult({
    required this.success,
    required this.message,
    this.data,
  });
}
