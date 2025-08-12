import 'package:flutter/foundation.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'dart:convert';

class UserData {
  final String id;
  final String name;
  final String email;
  final String? phone;
  final String? profileImageUrl;
  final DateTime createdAt;
  final DateTime lastLogin;

  UserData({
    required this.id,
    required this.name,
    required this.email,
    this.phone,
    this.profileImageUrl,
    required this.createdAt,
    required this.lastLogin,
  });

  Map<String, dynamic> toJson() => {
    'id': id,
    'name': name,
    'email': email,
    'phone': phone,
    'profileImageUrl': profileImageUrl,
    'createdAt': createdAt.toIso8601String(),
    'lastLogin': lastLogin.toIso8601String(),
  };

  static UserData fromJson(Map<String, dynamic> json) => UserData(
    id: json['id'],
    name: json['name'],
    email: json['email'],
    phone: json['phone'],
    profileImageUrl: json['profileImageUrl'],
    createdAt: DateTime.parse(json['createdAt']),
    lastLogin: DateTime.parse(json['lastLogin']),
  );
}

class UserService extends ChangeNotifier {
  static final UserService _instance = UserService._internal();
  factory UserService() => _instance;
  UserService._internal();

  UserData? _currentUser;
  SharedPreferences? _prefs;
  bool _isAuthenticated = false;

  UserData? get currentUser => _currentUser;
  bool get isAuthenticated => _isAuthenticated;
  String get userName => _currentUser?.name ?? 'Guest';
  String get userEmail => _currentUser?.email ?? '';

  Future<void> initialize() async {
    _prefs = await SharedPreferences.getInstance();
    final userData = _prefs?.getString('user_data');
    if (userData != null) {
      try {
        _currentUser = UserData.fromJson(json.decode(userData));
        _isAuthenticated = true;
      } catch (e) {
        await logout(); // Clear invalid data
      }
    }
    notifyListeners();
  }

  Future<bool> login(String email, String password) async {
    try {
      // Simulate API call delay
      await Future.delayed(const Duration(seconds: 2));
      
      // Mock authentication - replace with real API
      if (email.isNotEmpty && password.length >= 6) {
        final now = DateTime.now();
        _currentUser = UserData(
          id: DateTime.now().millisecondsSinceEpoch.toString(),
          name: email.split('@')[0].toUpperCase(),
          email: email,
          phone: '+62 812-3456-7890',
          createdAt: now,
          lastLogin: now,
        );
        
        _isAuthenticated = true;
        await _saveUserData();
        notifyListeners();
        return true;
      }
      return false;
    } catch (e) {
      return false;
    }
  }

  Future<bool> register(String name, String email, String password, {String? phone}) async {
    try {
      // Simulate API call delay
      await Future.delayed(const Duration(seconds: 2));
      
      // Mock registration - replace with real API
      if (name.isNotEmpty && email.isNotEmpty && password.length >= 6) {
        final now = DateTime.now();
        _currentUser = UserData(
          id: DateTime.now().millisecondsSinceEpoch.toString(),
          name: name,
          email: email,
          phone: phone,
          createdAt: now,
          lastLogin: now,
        );
        
        _isAuthenticated = true;
        await _saveUserData();
        notifyListeners();
        return true;
      }
      return false;
    } catch (e) {
      return false;
    }
  }

  Future<void> updateProfile({
    String? name,
    String? phone,
    String? profileImageUrl,
  }) async {
    if (_currentUser != null) {
      _currentUser = UserData(
        id: _currentUser!.id,
        name: name ?? _currentUser!.name,
        email: _currentUser!.email,
        phone: phone ?? _currentUser!.phone,
        profileImageUrl: profileImageUrl ?? _currentUser!.profileImageUrl,
        createdAt: _currentUser!.createdAt,
        lastLogin: _currentUser!.lastLogin,
      );
      
      await _saveUserData();
      notifyListeners();
    }
  }

  Future<void> logout() async {
    _currentUser = null;
    _isAuthenticated = false;
    await _prefs?.remove('user_data');
    notifyListeners();
  }

  Future<void> _saveUserData() async {
    if (_currentUser != null) {
      await _prefs?.setString('user_data', json.encode(_currentUser!.toJson()));
    }
  }
}
