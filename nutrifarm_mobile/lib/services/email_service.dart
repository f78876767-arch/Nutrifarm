import 'dart:convert';
import 'dart:math';
import 'package:http/http.dart' as http;

class EmailResult {
  final bool success;
  final String message;

  EmailResult({required this.success, required this.message});
}

class EmailService {
  // Base URL for your Laravel backend
  static const String _baseUrl = 'http://127.0.0.1:8000/api';
  
  // Store verification codes temporarily for fallback
  static final Map<String, String> _verificationCodes = {};
  
  /// Send verification email to the user
  Future<EmailResult> sendVerificationEmail(String email) async {
    try {
      final response = await http.post(
        Uri.parse('$_baseUrl/auth/send-verification-email'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: json.encode({
          'email': email,
        }),
      );

      final data = json.decode(response.body);

      if (response.statusCode == 200 && data['success'] == true) {
        print('=================================');
        print('📧 EMAIL SENT SUCCESSFULLY (Backend Response)');
        print('Email: $email');
        print('Check your email inbox for verification code');
        print('Code expires in ${data['expires_in_minutes']} minutes');
        print('');
        print('⚠️  If no email received:');
        print('1. Check Laravel backend logs');
        print('2. Verify SMTP configuration in Laravel .env');
        print('3. Test: php artisan tinker -> Mail::raw()');
        print('=================================');
        return EmailResult(success: true, message: 'Verification email sent successfully');
      } else {
        print('Failed to send verification email: ${data['message']}');
        // Handle specific error cases like duplicate email
        if (response.statusCode == 422 || response.statusCode == 409) {
          return EmailResult(
            success: false, 
            message: data['message'] ?? 'Email validation failed'
          );
        }
        return EmailResult(
          success: false,
          message: data['message'] ?? 'Failed to send verification email'
        );
      }
    } catch (e) {
      print('Error sending verification email: $e');
      return EmailResult(
        success: false,
        message: 'Network error. Please check your connection.'
      );
    }
  }
  
  /// Verify the entered code against the server
  Future<bool> verifyCode(String email, String enteredCode) async {
    try {
      print('🔍 Verifying code...');
      print('Email: $email');
      print('Code: $enteredCode');
      
      final response = await http.post(
        Uri.parse('$_baseUrl/auth/verify-email-code'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: json.encode({
          'email': email,
          'verification_code': enteredCode,
        }),
      );

      print('Response status: ${response.statusCode}');
      print('Response body: ${response.body}');

      final data = json.decode(response.body);

      if (response.statusCode == 200 && data['success'] == true) {
        print('✅ Email verification successful for $email');
        return true;
      } else {
        print('❌ Verification failed: ${data['message']}');
        return false;
      }
    } catch (e) {
      print('Error verifying code: $e');
      return false;
    }
  }
  
  /// Resend verification email
  Future<EmailResult> resendVerificationEmail(String email) async {
    // Just call the main send method
    return await sendVerificationEmail(email);
  }
  
  /// Generate a 6-digit verification code
  String _generateVerificationCode() {
    final random = Random();
    return (100000 + random.nextInt(900000)).toString();
  }
  
  /// Get stored verification code (for testing purposes only)
  String? getStoredCode(String email) {
    return _verificationCodes[email];
  }
}
