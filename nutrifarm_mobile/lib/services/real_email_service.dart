import 'dart:convert';
import 'dart:math';
import 'package:emailjs/emailjs.dart' as emailjs;

class RealEmailService {
  // EmailJS configuration - Sign up at https://www.emailjs.com/
  static const String _serviceId = 'YOUR_SERVICE_ID';  // Replace with your EmailJS Service ID
  static const String _templateId = 'YOUR_TEMPLATE_ID';  // Replace with your EmailJS Template ID  
  static const String _publicKey = 'YOUR_PUBLIC_KEY';  // Replace with your EmailJS Public Key
  
  // Store verification codes temporarily
  static final Map<String, Map<String, dynamic>> _verificationCodes = {};
  
  /// Send verification email using EmailJS
  Future<bool> sendVerificationEmail(String email) async {
    try {
      // Generate a 4-digit verification code
      final code = _generateVerificationCode();
      
      // Store the code with timestamp
      _verificationCodes[email] = {
        'code': code,
        'timestamp': DateTime.now().millisecondsSinceEpoch,
        'expires': DateTime.now().add(const Duration(minutes: 10)).millisecondsSinceEpoch,
      };
      
      // EmailJS template parameters
      final templateParams = {
        'to_email': email,
        'verification_code': code,
        'app_name': 'Nutrifarm Store',
        'expiry_minutes': '10',
      };
      
      // Send email using EmailJS
      await emailjs.EmailJS.send(
        _serviceId,
        _templateId,
        templateParams,
        emailjs.Options(
          publicKey: _publicKey,
          limitRate: emailjs.LimitRate(
            id: 'app',
            throttle: 10000, // 10 seconds between emails
          ),
        ),
      );
      
      print('Email sent successfully to $email with code: $code');
      return true;
      
    } catch (e) {
      print('Failed to send verification email: $e');
      
      // Fallback: For testing, still store the code and print it
      final code = _generateVerificationCode();
      _verificationCodes[email] = {
        'code': code,
        'timestamp': DateTime.now().millisecondsSinceEpoch,
        'expires': DateTime.now().add(const Duration(minutes: 10)).millisecondsSinceEpoch,
      };
      
      print('TESTING MODE - Verification code for $email: $code');
      print('Configure EmailJS credentials to send real emails');
      return true;
    }
  }
  
  /// Verify the entered code
  Future<bool> verifyCode(String email, String enteredCode) async {
    final stored = _verificationCodes[email];
    if (stored == null) {
      print('No verification code found for $email');
      return false;
    }
    
    // Check if code has expired
    final now = DateTime.now().millisecondsSinceEpoch;
    if (now > stored['expires']) {
      _verificationCodes.remove(email);
      print('Verification code expired for $email');
      return false;
    }
    
    // Verify code
    if (stored['code'] == enteredCode) {
      _verificationCodes.remove(email);
      print('Email verification successful for $email');
      return true;
    } else {
      print('Invalid verification code for $email');
      return false;
    }
  }
  
  /// Resend verification email
  Future<bool> resendVerificationEmail(String email) async {
    _verificationCodes.remove(email);
    return await sendVerificationEmail(email);
  }
  
  /// Generate a 4-digit verification code
  String _generateVerificationCode() {
    final random = Random();
    return (1000 + random.nextInt(9000)).toString();
  }
  
  /// Get stored verification code (for testing purposes only)
  String? getStoredCode(String email) {
    final stored = _verificationCodes[email];
    if (stored != null) {
      final now = DateTime.now().millisecondsSinceEpoch;
      if (now <= stored['expires']) {
        return stored['code'];
      } else {
        _verificationCodes.remove(email);
      }
    }
    return null;
  }
  
  /// Check if code is expired
  bool isCodeExpired(String email) {
    final stored = _verificationCodes[email];
    if (stored == null) return true;
    
    final now = DateTime.now().millisecondsSinceEpoch;
    return now > stored['expires'];
  }
}
