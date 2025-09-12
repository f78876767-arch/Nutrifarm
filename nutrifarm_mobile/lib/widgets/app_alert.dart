import 'dart:async';
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import '../theme/app_theme.dart';

class AppAlert {
  static OverlayEntry? _current;
  static Timer? _timer;

  static void _show(
    BuildContext context, {
    required String message,
    required IconData icon,
    required Color color,
    Duration duration = const Duration(seconds: 2),
  }) {
    _hide();
    final overlay = Overlay.of(context, rootOverlay: true);

    final entry = OverlayEntry(
      builder: (_) => _AppAlertWidget(
        message: message,
        icon: icon,
        color: color,
      ),
    );

    overlay.insert(entry);
    _current = entry;

    _timer = Timer(duration, _hide);
  }

  static void _hide() {
    _timer?.cancel();
    _timer = null;
    _current?.remove();
    _current = null;
  }

  static void showSuccess(BuildContext context, String message) => _show(
        context,
        message: message,
        icon: Icons.check_circle_rounded,
        color: AppColors.primaryGreen,
      );

  static void showError(BuildContext context, String message) => _show(
        context,
        message: message,
        icon: Icons.error_rounded,
        color: Colors.redAccent,
      );

  static void showInfo(BuildContext context, String message) => _show(
        context,
        message: message,
        icon: Icons.info_rounded,
        color: AppColors.blue,
      );

  static void showWarning(BuildContext context, String message) => _show(
        context,
        message: message,
        icon: Icons.warning_rounded,
        color: const Color(0xFFF59E0B), // amber-500
      );
}

class _AppAlertWidget extends StatefulWidget {
  final String message;
  final IconData icon;
  final Color color;

  const _AppAlertWidget({
    required this.message,
    required this.icon,
    required this.color,
  });

  @override
  State<_AppAlertWidget> createState() => _AppAlertWidgetState();
}

class _AppAlertWidgetState extends State<_AppAlertWidget>
    with SingleTickerProviderStateMixin {
  late final AnimationController _ctrl;
  late final Animation<double> _fade;
  late final Animation<Offset> _slide;

  @override
  void initState() {
    super.initState();
    _ctrl = AnimationController(vsync: this, duration: const Duration(milliseconds: 220));
    _fade = CurvedAnimation(parent: _ctrl, curve: Curves.easeOut);
    _slide = Tween<Offset>(begin: const Offset(0, 0.2), end: Offset.zero).animate(_fade);
    _ctrl.forward();
  }

  @override
  void dispose() {
    _ctrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final bottom = MediaQuery.of(context).padding.bottom;

    return Positioned.fill(
      child: IgnorePointer(
        child: SafeArea(
          child: Align(
            alignment: Alignment.bottomCenter,
            child: Padding(
              padding: EdgeInsets.only(left: 16, right: 16, bottom: bottom > 0 ? 12 : 20),
              child: FadeTransition(
                opacity: _fade,
                child: SlideTransition(
                  position: _slide,
                  child: DecoratedBox(
                    decoration: BoxDecoration(
                      color: AppColors.surface,
                      borderRadius: BorderRadius.circular(14),
                      boxShadow: [
                        BoxShadow(
                          color: Colors.black.withOpacity(0.08),
                          blurRadius: 16,
                          offset: const Offset(0, 6),
                        ),
                      ],
                      border: Border.all(color: widget.color.withOpacity(0.2)),
                    ),
                    child: Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Container(
                            width: 28,
                            height: 28,
                            decoration: BoxDecoration(
                              color: widget.color.withOpacity(0.12),
                              shape: BoxShape.circle,
                            ),
                            child: Icon(widget.icon, color: widget.color, size: 18),
                          ),
                          const SizedBox(width: 10),
                          Flexible(
                            child: Text(
                              widget.message,
                              maxLines: 3,
                              overflow: TextOverflow.ellipsis,
                              style: GoogleFonts.nunitoSans(
                                fontSize: 14,
                                fontWeight: FontWeight.w700,
                                color: AppColors.onSurface,
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
          ),
        ),
      ),
    );
  }
}
