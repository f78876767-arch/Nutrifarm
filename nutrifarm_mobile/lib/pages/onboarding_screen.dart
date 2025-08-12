import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'dart:async';

class OnboardingScreen extends StatefulWidget {
  const OnboardingScreen({super.key});

  @override
  State<OnboardingScreen> createState() => _OnboardingScreenState();
}

class _OnboardingScreenState extends State<OnboardingScreen> {
  final ScrollController _scrollController = ScrollController();
  late Timer _scrollTimer;

  @override
  void initState() {
    super.initState();
    int step = 0;
    _scrollTimer = Timer.periodic(const Duration(milliseconds: 100), (timer) {
      if (_scrollController.hasClients) {
        final maxScroll = _scrollController.position.maxScrollExtent;
        final nextScroll = _scrollController.offset + 0.5 + (step * 0.01);

        _scrollController.animateTo(
          nextScroll >= maxScroll ? 0 : nextScroll,
          duration: const Duration(milliseconds: 100),
          curve: Curves.linear,
        );

        step++;
      }
    });
  }

  @override
  void dispose() {
    _scrollTimer.cancel();
    _scrollController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Scaffold(
      backgroundColor: Colors.white,
      body: SafeArea(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.start,
          crossAxisAlignment: CrossAxisAlignment.center,
          children: [
            Padding(
              padding: const EdgeInsets.symmetric(
                horizontal: 24.0,
                vertical: 32.0,
              ),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.center,
                children: [
                  const SizedBox(height: 24),
                  Text(
                    'Welcome to Nutrifarm',
                    textAlign: TextAlign.center,
                    style: GoogleFonts.nunitoSans(
                      color: Colors.black,
                      fontWeight: FontWeight.w900,
                      fontSize: 44,
                      letterSpacing: 1.5,
                      height: 1.1,
                      shadows: [
                        Shadow(
                          color: Colors.black.withOpacity(0.08),
                          blurRadius: 4,
                          offset: const Offset(0, 2),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 10),
                  Text(
                    'Your healthy lifestyle starts here',
                    textAlign: TextAlign.center,
                    style: GoogleFonts.nunitoSans(
                      color: const Color(0xFF444444),
                      fontWeight: FontWeight.w400,
                      fontSize: 18,
                    ),
                  ),
                ],
              ),
            ),
            Expanded(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  LayoutBuilder(
                    builder: (context, constraints) {
                      final screenWidth = MediaQuery.of(context).size.width;
                      final itemWidth = screenWidth / 2.3;
                      return SizedBox(
                        height: 190,
                        width: screenWidth,
                        child: ListView(
                          controller: _scrollController,
                          scrollDirection: Axis.horizontal,
                          physics: const BouncingScrollPhysics(),
                          padding: EdgeInsets.zero,
                          children: [
                            Transform.rotate(
                              angle: -0.3,
                              child: _PreviewCard(
                                image: 'assets/images/cuka-apel-250.png',
                                size: itemWidth * 0.95,
                                borderRadius: 16,
                              ),
                            ),
                            Transform.rotate(
                              angle: 0.12,
                              child: _BadgeCard(size: itemWidth * 1),
                            ),
                            Transform.rotate(
                              angle: -0.10,
                              child: _PreviewCard(
                                image:
                                    'assets/images/MADU-BUNGA-KELENGKENG-650ml-UPLOAD.png',
                                size: itemWidth,
                                borderRadius: 20,
                              ),
                            ),
                            Transform.rotate(
                              angle: 0.08,
                              child: _PreviewCard(
                                image:
                                    'assets/images/virgin-coconut-oil-500.png',
                                size: itemWidth * 1,
                                borderRadius: 18,
                              ),
                            ),
                          ],
                        ),
                      );
                    },
                  ),
                ],
              ),
            ),
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 24.0),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
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
                      onPressed: () {
                        Navigator.pushNamed(context, '/register');
                      },
                      child: Text(
                        'Create an account',
                        style: GoogleFonts.nunitoSans(
                          fontSize: 18,
                          fontWeight: FontWeight.w700,
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(height: 12),
                  SizedBox(
                    width: double.infinity,
                    child: OutlinedButton(
                      style: OutlinedButton.styleFrom(
                        foregroundColor: Colors.black,
                        backgroundColor: Colors.white,
                        side: const BorderSide(color: Colors.black, width: 2),
                        padding: const EdgeInsets.symmetric(vertical: 16),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
                      ),
                      onPressed: () {
                        Navigator.pushNamed(context, '/login');
                      },
                      child: Text(
                        'Sign in',
                        style: GoogleFonts.nunitoSans(
                          fontSize: 18,
                          fontWeight: FontWeight.w700,
                          color: Colors.black,
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(height: 32),
                  Text.rich(
                    TextSpan(
                      style: GoogleFonts.nunitoSans(
                        color: const Color(0xFF888888),
                        fontSize: 13,
                      ),
                      children: [
                        const TextSpan(
                          text: 'By continuing, you agree to our ',
                        ),
                        TextSpan(
                          text: 'Shopx Shopping Service',
                          style: const TextStyle(
                            decoration: TextDecoration.underline,
                            color: Colors.black,
                          ),
                        ),
                        const TextSpan(text: ' and '),
                        TextSpan(
                          text: 'Nutrifarm’s Privacy Policy',
                          style: const TextStyle(
                            decoration: TextDecoration.underline,
                            color: Colors.black,
                          ),
                        ),
                      ],
                    ),
                    textAlign: TextAlign.center,
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _PreviewCard extends StatelessWidget {
  final String image;
  final double size;
  final double borderRadius;
  const _PreviewCard({
    required this.image,
    required this.size,
    required this.borderRadius,
  });

  @override
  Widget build(BuildContext context) {
    return ClipRRect(
      borderRadius: BorderRadius.circular(borderRadius),
      child: Image.asset(image, fit: BoxFit.cover, height: size, width: size),
    );
  }
}

class _BadgeCard extends StatelessWidget {
  final double size;
  const _BadgeCard({this.size = 90});

  @override
  Widget build(BuildContext context) {
    return Container(
      height: size,
      width: size,
      decoration: BoxDecoration(
        color: Colors.white,
        shape: BoxShape.circle,
        border: Border.all(color: const Color(0xFFE0E0E0), width: 2),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.08),
            blurRadius: 8,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Text(
              '#1',
              style: GoogleFonts.nunitoSans(
                fontSize: size * 0.32,
                fontWeight: FontWeight.w900,
                color: const Color(0xFF1C1C1E),
              ),
            ),
            Text(
              'Best',
              style: GoogleFonts.nunitoSans(
                fontSize: size * 0.16,
                fontWeight: FontWeight.w700,
                color: const Color(0xFF3A3A3C),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
