import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'register_completion_page.dart';
import 'dart:async';
import '../services/email_service.dart';

class VerifyCodePage extends StatefulWidget {
  final String email;
  const VerifyCodePage({super.key, required this.email});

  @override
  State<VerifyCodePage> createState() => _VerifyCodePageState();
}

class _VerifyCodePageState extends State<VerifyCodePage> {
  final _formKey = GlobalKey<FormState>();
  final List<TextEditingController> _controllers = List.generate(6, (_) => TextEditingController());
  final List<FocusNode> _focusNodes = List.generate(6, (_) => FocusNode());
  bool _isVerifying = false;
  String? _errorText;
  int _resendSeconds = 60;
  bool _canResend = false;
  Timer? _timer;
  final EmailService _emailService = EmailService();

  @override
  void initState() {
    super.initState();
    _startResendTimer();
  }

  void _startResendTimer() {
    setState(() {
      _resendSeconds = 60;
      _canResend = false;
    });
    _timer?.cancel();
    _timer = Timer.periodic(const Duration(seconds: 1), (timer) {
      if (_resendSeconds == 0) {
        setState(() => _canResend = true);
        timer.cancel();
      } else {
        setState(() => _resendSeconds--);
      }
    });
  }

  @override
  void dispose() {
    for (final c in _controllers) {
      c.dispose();
    }
    for (final f in _focusNodes) {
      f.dispose();
    }
    _timer?.cancel();
    super.dispose();
  }

  void _onCodeChanged(int idx, String value) {
    if (value.length == 1 && idx < 5) {
      _focusNodes[idx + 1].requestFocus();
    } else if (value.isEmpty && idx > 0) {
      _focusNodes[idx - 1].requestFocus();
    }
  }

  String get _enteredCode => _controllers.map((c) => c.text).join();

  void _verifyCode() async {
    if (_enteredCode.length != 6) {
      setState(() {
        _errorText = 'Please enter the complete 6-digit code';
      });
      return;
    }

    setState(() {
      _isVerifying = true;
      _errorText = null;
    });

    try {
      final isValid = await _emailService.verifyCode(widget.email, _enteredCode);
      
      if (isValid) {
        // Code is valid, navigate to registration completion page
        Navigator.of(context).pushReplacement(
          MaterialPageRoute(
            builder: (context) => RegisterCompletionPage(
              email: widget.email,
              verificationCode: _enteredCode,
            ),
          ),
        );
      } else {
        setState(() {
          _isVerifying = false;
          _errorText = 'Invalid verification code. Please try again.';
          // Clear all fields
          for (final controller in _controllers) {
            controller.clear();
          }
          _focusNodes[0].requestFocus();
        });
      }
    } catch (e) {
      setState(() {
        _isVerifying = false;
        _errorText = 'Verification failed. Please try again.';
      });
    }
  }

  void _resendCode() async {
    setState(() {
      _errorText = null;
    });
    
    try {
      final result = await _emailService.resendVerificationEmail(widget.email);
      
      if (result.success) {
        _startResendTimer();
        
        // Show success dialog
        showDialog(
          context: context,
          barrierDismissible: true,
          builder: (context) => Dialog(
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
            backgroundColor: Colors.white,
            child: Padding(
              padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 32),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.center,
                children: [
                  Container(
                    decoration: const BoxDecoration(
                      color: Color(0xFFE8F5E9),
                      shape: BoxShape.circle,
                    ),
                    padding: const EdgeInsets.all(16),
                    child: const Icon(Icons.email_outlined, color: Color(0xFF43A047), size: 48),
                  ),
                  const SizedBox(height: 24),
                  Text(
                    'Code Sent!',
                    style: GoogleFonts.nunitoSans(
                      fontWeight: FontWeight.w900,
                      fontSize: 22,
                      color: Colors.black,
                    ),
                    textAlign: TextAlign.center,
                  ),
                  const SizedBox(height: 12),
                  Text(
                    'A new verification code has been sent to your email.',
                    style: GoogleFonts.nunitoSans(
                      fontWeight: FontWeight.w400,
                      fontSize: 16,
                      color: const Color(0xFF444444),
                    ),
                    textAlign: TextAlign.center,
                  ),
                  const SizedBox(height: 24),
                  SizedBox(
                    width: double.infinity,
                    child: ElevatedButton(
                      style: ElevatedButton.styleFrom(
                        backgroundColor: Colors.black,
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
                        elevation: 0,
                      ),
                      onPressed: () => Navigator.of(context).pop(),
                      child: Text(
                        'OK',
                        style: GoogleFonts.nunitoSans(
                          fontWeight: FontWeight.w700,
                          fontSize: 16,
                        ),
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ),
        );
      } else {
        setState(() {
          _errorText = result.message;
        });
      }
    } catch (e) {
      setState(() {
        _errorText = 'Error resending code. Please try again.';
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white,
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0,
        iconTheme: const IconThemeData(color: Colors.black),
        title: Text(
          'Verify Code',
          style: GoogleFonts.nunitoSans(
            color: Colors.black,
            fontWeight: FontWeight.w900,
            fontSize: 28,
          ),
        ),
        centerTitle: true,
      ),
      body: SafeArea(
        child: Center(
          child: SingleChildScrollView(
            child: Padding(
              padding: const EdgeInsets.symmetric(horizontal: 24.0, vertical: 32.0),
              child: Form(
                key: _formKey,
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.center,
                  children: [
                    Text(
                      'Enter the 6-digit code sent to',
                      style: GoogleFonts.nunitoSans(
                        color: Colors.black,
                        fontWeight: FontWeight.w700,
                        fontSize: 18,
                      ),
                      textAlign: TextAlign.center,
                    ),
                    const SizedBox(height: 4),
                    Text(
                      widget.email,
                      style: GoogleFonts.nunitoSans(
                        color: const Color(0xFF3A3A3C),
                        fontWeight: FontWeight.w700,
                        fontSize: 18,
                      ),
                      textAlign: TextAlign.center,
                    ),
                    const SizedBox(height: 32),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: List.generate(6, (i) {
                        return Container(
                          width: 48,  // Reduced from 64 to fit 6 boxes
                          height: 48, // Made square
                          margin: EdgeInsets.symmetric(horizontal: 4), // Reduced margin
                          child: TextFormField(
                            controller: _controllers[i],
                            focusNode: _focusNodes[i],
                            keyboardType: TextInputType.number,
                            textAlign: TextAlign.center,
                            maxLength: 1,
                            style: GoogleFonts.nunitoSans(
                              color: Colors.black,
                              fontWeight: FontWeight.w700,
                              fontSize: 28,
                            ),
                            decoration: InputDecoration(
                              counterText: '',
                              filled: true,
                              fillColor: const Color(0xFFF5F5F5),
                              border: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(12),
                                borderSide: const BorderSide(color: Color(0xFFE0E0E0)),
                              ),
                              enabledBorder: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(12),
                                borderSide: const BorderSide(color: Color(0xFFE0E0E0)),
                              ),
                              focusedBorder: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(12),
                                borderSide: const BorderSide(color: Colors.black, width: 2),
                              ),
                              errorText: i == 0 ? _errorText : null,
                            ),
                            onChanged: (val) => _onCodeChanged(i, val),
                            validator: (val) {
                              if (val == null || val.isEmpty) {
                                return '';
                              }
                              return null;
                            },
                          ),
                        );
                      }),
                    ),
                    const SizedBox(height: 32),
                    SizedBox(
                      width: double.infinity,
                      child: ElevatedButton(
                        style: ElevatedButton.styleFrom(
                          backgroundColor: Colors.black,
                          foregroundColor: Colors.white,
                          padding: const EdgeInsets.symmetric(vertical: 16),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(12),
                          ),
                          elevation: 0,
                        ),
                        onPressed: _isVerifying
                            ? null
                            : () {
                                if (_enteredCode.length == 6) {
                                  _verifyCode();
                                }
                              },
                        child: _isVerifying
                            ? const SizedBox(
                                width: 24,
                                height: 24,
                                child: CircularProgressIndicator(
                                  color: Colors.white,
                                  strokeWidth: 2.5,
                                ),
                              )
                            : Text(
                                'Verify',
                                style: GoogleFonts.nunitoSans(
                                  fontSize: 18,
                                  fontWeight: FontWeight.w700,
                                ),
                              ),
                      ),
                    ),
                    const SizedBox(height: 16),
                    TextButton(
                      onPressed: _canResend ? _resendCode : null,
                      child: _canResend
                          ? Text(
                              'Resend code',
                              style: GoogleFonts.nunitoSans(
                                color: Colors.black,
                                fontWeight: FontWeight.w700,
                                fontSize: 15,
                                decoration: TextDecoration.underline,
                              ),
                            )
                          : Text(
                              'Resend in $_resendSeconds s',
                              style: GoogleFonts.nunitoSans(
                                color: Colors.black.withOpacity(0.5),
                                fontWeight: FontWeight.w700,
                                fontSize: 15,
                              ),
                            ),
                    ),
                  ],
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }
}
