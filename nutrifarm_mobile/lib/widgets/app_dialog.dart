import 'dart:ui';
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import '../theme/app_theme.dart';

class AppDialog {
  static Future<bool?> showConfirm(
    BuildContext context, {
    required String title,
    required String message,
    String confirmText = 'Ya',
    String cancelText = 'Batal',
    bool destructive = false,
    IconData? icon,
    bool barrierDismissible = true,
  }) {
    return showDialog<bool>(
      context: context,
      barrierDismissible: barrierDismissible,
      barrierColor: Colors.black.withOpacity(0.35),
      builder: (context) {
        return _BaseDialog(
          icon: icon ?? (destructive ? Icons.warning_amber_rounded : Icons.help_outline),
          title: title,
          message: message,
          actions: [
            _TextBtn(
              label: cancelText,
              onTap: () => Navigator.pop(context, false),
            ),
            const SizedBox(width: 8),
            _FilledBtn(
              label: confirmText,
              color: destructive ? AppColors.error : AppColors.primaryGreen,
              onTap: () => Navigator.pop(context, true),
            ),
          ],
        );
      },
    );
  }

  static Future<void> showInfo(
    BuildContext context, {
    required String title,
    required Widget content,
    String buttonText = 'Tutup',
    IconData icon = Icons.info_outline,
  }) {
    return showDialog<void>(
      context: context,
      barrierDismissible: true,
      barrierColor: Colors.black.withOpacity(0.35),
      builder: (context) {
        return _BaseDialog(
          icon: icon,
          title: title,
          content: content,
          actions: [
            _FilledBtn(
              label: buttonText,
              onTap: () => Navigator.pop(context),
            ),
          ],
        );
      },
    );
  }

  static Future<T?> showOptions<T>(
    BuildContext context, {
    required String title,
    required List<T> options,
    required T selected,
    required String Function(T) labelBuilder,
  }) {
    T current = selected;
    return showDialog<T>(
      context: context,
      barrierDismissible: true,
      barrierColor: Colors.black.withOpacity(0.35),
      builder: (context) {
        return _BaseDialog(
          icon: Icons.tune,
          title: title,
          content: StatefulBuilder(
            builder: (context, setState) => Column(
              mainAxisSize: MainAxisSize.min,
              children: options.map((opt) {
                return RadioListTile<T>(
                  title: Text(labelBuilder(opt), style: GoogleFonts.nunitoSans(fontSize: 15)),
                  value: opt,
                  groupValue: current,
                  onChanged: (val) => setState(() => current = val as T),
                  activeColor: AppColors.primaryGreen,
                );
              }).toList(),
            ),
          ),
          actions: [
            _TextBtn(label: 'Batal', onTap: () => Navigator.pop(context)),
            const SizedBox(width: 8),
            _FilledBtn(label: 'Pilih', onTap: () => Navigator.pop(context, current)),
          ],
        );
      },
    );
  }

  static Future<String?> showPrompt(
    BuildContext context, {
    required String title,
    String? initialText,
    String hintText = 'Ketik di sini...',
    String confirmText = 'Simpan',
    String cancelText = 'Batal',
    int maxLines = 3,
  }) {
    final controller = TextEditingController(text: initialText);
    return showDialog<String>(
      context: context,
      barrierDismissible: true,
      barrierColor: Colors.black.withOpacity(0.35),
      builder: (context) {
        return _BaseDialog(
          icon: Icons.edit_note,
          title: title,
          content: TextField(
            controller: controller,
            maxLines: maxLines,
            decoration: InputDecoration(
              hintText: hintText,
              hintStyle: GoogleFonts.nunitoSans(color: Colors.black45),
              filled: true,
              fillColor: const Color(0xFFF5F6F7),
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(12),
                borderSide: const BorderSide(color: Color(0xFFE5E7EB)),
              ),
              enabledBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(12),
                borderSide: const BorderSide(color: Color(0xFFE5E7EB)),
              ),
              focusedBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(12),
                borderSide: const BorderSide(color: AppColors.primaryGreen),
              ),
            ),
            style: GoogleFonts.nunitoSans(fontSize: 14),
          ),
          actions: [
            _TextBtn(label: cancelText, onTap: () => Navigator.pop(context)),
            const SizedBox(width: 8),
            _FilledBtn(label: confirmText, onTap: () => Navigator.pop(context, controller.text.trim())),
          ],
        );
      },
    );
  }

  static Future<void> showLoading(
    BuildContext context, {
    required String message,
  }) {
    return showDialog<void>(
      context: context,
      barrierDismissible: false,
      barrierColor: Colors.black.withOpacity(0.35),
      builder: (context) {
        return Dialog(
          backgroundColor: Colors.transparent,
          insetPadding: const EdgeInsets.symmetric(horizontal: 80),
          child: ClipRRect(
            borderRadius: BorderRadius.circular(16),
            child: BackdropFilter(
              filter: ImageFilter.blur(sigmaX: 10, sigmaY: 10),
              child: Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: Colors.white.withOpacity(0.9),
                  borderRadius: BorderRadius.circular(16),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    const SizedBox(
                      height: 24,
                      width: 24,
                      child: CircularProgressIndicator(strokeWidth: 2),
                    ),
                    const SizedBox(width: 12),
                    Flexible(
                      child: Text(message, style: GoogleFonts.nunitoSans(fontWeight: FontWeight.w600)),
                    ),
                  ],
                ),
              ),
            ),
          ),
        );
      },
    );
  }
}

class _BaseDialog extends StatelessWidget {
  final IconData icon;
  final String title;
  final String? message;
  final Widget? content;
  final List<Widget> actions;

  const _BaseDialog({
    required this.icon,
    required this.title,
    this.message,
    this.content,
    required this.actions,
  });

  @override
  Widget build(BuildContext context) {
    return Dialog(
      backgroundColor: Colors.transparent,
      insetPadding: const EdgeInsets.symmetric(horizontal: 24, vertical: 24),
      child: ClipRRect(
        borderRadius: BorderRadius.circular(20),
        child: BackdropFilter(
          filter: ImageFilter.blur(sigmaX: 12, sigmaY: 12),
          child: Container(
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(20),
              boxShadow: [
                BoxShadow(color: Colors.black.withOpacity(0.08), blurRadius: 24, offset: const Offset(0, 10)),
              ],
            ),
            child: Padding(
              padding: const EdgeInsets.fromLTRB(20, 20, 20, 12),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Container(
                        width: 40,
                        height: 40,
                        decoration: BoxDecoration(
                          color: AppColors.primaryGreen.withOpacity(0.1),
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: Icon(icon, color: AppColors.primaryGreen),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Text(
                          title,
                          style: GoogleFonts.nunitoSans(fontSize: 18, fontWeight: FontWeight.w800, color: const Color(0xFF111827)),
                        ),
                      ),
                    ],
                  ),
                  if (message != null) ...[
                    const SizedBox(height: 12),
                    Text(
                      message!,
                      style: GoogleFonts.nunitoSans(fontSize: 14, color: const Color(0xFF4B5563), height: 1.5),
                    ),
                  ],
                  if (content != null) ...[
                    const SizedBox(height: 16),
                    content!,
                  ],
                  const SizedBox(height: 16),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.end,
                    children: actions,
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}

class _FilledBtn extends StatelessWidget {
  final String label;
  final VoidCallback onTap;
  final Color color;
  const _FilledBtn({
    required this.label,
    required this.onTap,
    this.color = AppColors.primaryGreen,
  });
  @override
  Widget build(BuildContext context) {
    return ElevatedButton(
      onPressed: onTap,
      style: ElevatedButton.styleFrom(
        backgroundColor: color,
        foregroundColor: Colors.white,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
        elevation: 0,
      ),
      child: Text(label, style: GoogleFonts.nunitoSans(fontWeight: FontWeight.w700)),
    );
  }
}

class _TextBtn extends StatelessWidget {
  final String label;
  final VoidCallback onTap;
  const _TextBtn({required this.label, required this.onTap});
  @override
  Widget build(BuildContext context) {
    return TextButton(
      onPressed: onTap,
      child: Text(label, style: GoogleFonts.nunitoSans(color: AppColors.onSurfaceVariant, fontWeight: FontWeight.w700)),
    );
  }
}
