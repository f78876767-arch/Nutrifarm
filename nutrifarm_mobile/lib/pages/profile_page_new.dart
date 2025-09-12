import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../theme/app_theme.dart';
import '../services/auth_service.dart';
import 'notifications_page.dart';
import 'settings_page.dart';

class ProfilePage extends StatelessWidget {
  const ProfilePage({super.key});

  @override
  Widget build(BuildContext context) {
    final scheme = Theme.of(context).colorScheme;
    final isDark = Theme.of(context).brightness == Brightness.dark;
    return Consumer<AuthService>(
      builder: (context, auth, _) {
        final user = auth.currentUser;
        return Scaffold
        (
          backgroundColor: Theme.of(context).scaffoldBackgroundColor,
          appBar: AppBar(
            automaticallyImplyLeading: false,
            backgroundColor: Theme.of(context).appBarTheme.backgroundColor,
            systemOverlayStyle: isDark ? SystemUiOverlayStyle.light : SystemUiOverlayStyle.dark,
            centerTitle: true,
            elevation: 0,
            title: Text(
              'Profil',
              style: GoogleFonts.nunitoSans(
                color: Theme.of(context).appBarTheme.titleTextStyle?.color ?? (isDark ? Colors.white : Colors.black),
                fontSize: 18,
                fontWeight: FontWeight.w700,
              ),
            ),
          ),
          body: ListView(
            padding: const EdgeInsets.all(20),
            children: [
              // Header
              Container(
                padding: const EdgeInsets.all(20),
                decoration: BoxDecoration(
                  color: scheme.surface,
                  borderRadius: BorderRadius.circular(16),
                  boxShadow: isDark
                      ? []
                      : [
                          BoxShadow(
                            color: scheme.onSurface.withOpacity(0.05),
                            blurRadius: 10,
                            offset: const Offset(0, 2),
                          ),
                        ],
                ),
                child: Row(
                  children: [
                    CircleAvatar(
                      radius: 36,
                      backgroundColor: AppColors.primaryGreen,
                      child: Text(
                        (user?.name.isNotEmpty == true ? user!.name[0] : 'G'),
                        style: GoogleFonts.nunitoSans(
                          fontSize: 24,
                          fontWeight: FontWeight.w700,
                          color: Colors.white,
                        ),
                      ),
                    ),
                    const SizedBox(width: 16),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            user?.name ?? 'Guest User',
                            style: GoogleFonts.nunitoSans(
                              fontSize: 18,
                              fontWeight: FontWeight.w700,
                              color: scheme.onSurface,
                            ),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            user?.email ?? 'Not logged in',
                            style: GoogleFonts.nunitoSans(
                              fontSize: 14,
                              color: scheme.onSurfaceVariant,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),

              const SizedBox(height: 16),

              // Quick actions & options
              Container(
                decoration: BoxDecoration(
                  color: scheme.surface,
                  borderRadius: BorderRadius.circular(16),
                  boxShadow: isDark
                      ? []
                      : [
                          BoxShadow(
                            color: scheme.onSurface.withOpacity(0.05),
                            blurRadius: 10,
                            offset: const Offset(0, 2),
                          ),
                        ],
                ),
                child: Column(
                  children: [
                    ListTile(
                      leading: const Icon(Icons.receipt_long, color: AppColors.primaryGreen),
                      title: Text('Riwayat Pesanan', style: GoogleFonts.nunitoSans(fontWeight: FontWeight.w600, color: scheme.onSurface)),
                      trailing: Icon(Icons.chevron_right, color: scheme.onSurfaceVariant),
                      onTap: () {
                        HapticFeedback.lightImpact();
                        Navigator.pushNamed(context, '/orders');
                      },
                    ),
                    const Divider(height: 1),
                    ListTile(
                      leading: const Icon(Icons.location_on_outlined, color: AppColors.primaryGreen),
                      title: Text('Alamat', style: GoogleFonts.nunitoSans(fontWeight: FontWeight.w600, color: scheme.onSurface)),
                      trailing: Icon(Icons.chevron_right, color: scheme.onSurfaceVariant),
                      onTap: () {
                        HapticFeedback.lightImpact();
                        Navigator.pushNamed(context, '/addresses');
                      },
                    ),
                    const Divider(height: 1),
                    ListTile(
                      leading: const Icon(Icons.notifications_outlined, color: AppColors.primaryGreen),
                      title: Text('Notifikasi', style: GoogleFonts.nunitoSans(fontWeight: FontWeight.w600, color: scheme.onSurface)),
                      trailing: Icon(Icons.chevron_right, color: scheme.onSurfaceVariant),
                      onTap: () {
                        HapticFeedback.lightImpact();
                        // Fallback to page if route not registered
                        Navigator.of(context).push(MaterialPageRoute(builder: (_) => const NotificationsPage()));
                      },
                    ),
                    const Divider(height: 1),
                    ListTile(
                      leading: const Icon(Icons.card_giftcard, color: AppColors.primaryGreen),
                      title: Text('Voucher & Promo', style: GoogleFonts.nunitoSans(fontWeight: FontWeight.w600, color: scheme.onSurface)),
                      trailing: Icon(Icons.chevron_right, color: scheme.onSurfaceVariant),
                      onTap: () {
                        HapticFeedback.lightImpact();
                        ScaffoldMessenger.of(context).showSnackBar(
                          SnackBar(content: Text('Fitur segera hadir', style: GoogleFonts.nunitoSans())),
                        );
                      },
                    ),
                    const Divider(height: 1),
                    ListTile(
                      leading: const Icon(Icons.settings_outlined, color: AppColors.primaryGreen),
                      title: Text('Pengaturan', style: GoogleFonts.nunitoSans(fontWeight: FontWeight.w600, color: scheme.onSurface)),
                      trailing: Icon(Icons.chevron_right, color: scheme.onSurfaceVariant),
                      onTap: () {
                        HapticFeedback.lightImpact();
                        Navigator.push(context, MaterialPageRoute(builder: (_) => const SettingsPage()));
                      },
                    ),
                    const Divider(height: 1),
                    ListTile(
                      leading: const Icon(Icons.info_outline, color: AppColors.primaryGreen),
                      title: Text('Tentang Aplikasi', style: GoogleFonts.nunitoSans(fontWeight: FontWeight.w600, color: scheme.onSurface)),
                      trailing: Icon(Icons.chevron_right, color: scheme.onSurfaceVariant),
                      onTap: () {
                        HapticFeedback.lightImpact();
                        showAboutDialog(
                          context: context,
                          applicationName: 'Nutrifarm',
                          applicationVersion: '1.0.0',
                        );
                      },
                    ),
                    const Divider(height: 1),
                    ListTile(
                      leading: const Icon(Icons.logout, color: Colors.redAccent),
                      title: Text('Keluar', style: GoogleFonts.nunitoSans(fontWeight: FontWeight.w600, color: Colors.redAccent)),
                      trailing: Icon(Icons.chevron_right, color: scheme.onSurfaceVariant),
                      onTap: () async {
                        HapticFeedback.lightImpact();
                        await AuthService().logout();
                        if (context.mounted) {
                          Navigator.pushNamedAndRemoveUntil(context, '/login', (route) => false);
                        }
                      },
                    ),
                  ],
                ),
              ),

              const SizedBox(height: 100),
            ],
          ),
          bottomNavigationBar: null,
        );
      },
    );
  }
}
