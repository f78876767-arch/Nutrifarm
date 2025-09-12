import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:provider/provider.dart';
import '../theme/app_theme.dart';
import '../services/auth_service.dart';
import '../services/settings_service.dart';
import '../services/push_service.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:flutter_feather_icons/flutter_feather_icons.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../widgets/app_dialog.dart';
import '../widgets/app_alert.dart';
import 'edit_profile_page.dart';
import 'address_list_page.dart';

class SettingsPage extends StatefulWidget {
  const SettingsPage({super.key});

  @override
  State<SettingsPage> createState() => _SettingsPageState();
}

class _SettingsPageState extends State<SettingsPage> {
  bool _notificationsEnabled = true;
  bool _locationEnabled = false;
  bool _biometricEnabled = false;
  bool _darkModeEnabled = false;
  String _selectedLanguage = 'Bahasa Indonesia';
  
  @override
  void initState() {
    super.initState();
    _loadSettings();
  }

  Future<void> _loadSettings() async {
    final prefs = await SharedPreferences.getInstance();
    setState(() {
      _notificationsEnabled = prefs.getBool('notifications_enabled') ?? true;
      _locationEnabled = prefs.getBool('location_enabled') ?? false;
      _biometricEnabled = prefs.getBool('biometric_enabled') ?? false;
      _darkModeEnabled = prefs.getBool('dark_mode_enabled') ?? false;
      _selectedLanguage = prefs.getString('selected_language') ?? 'Bahasa Indonesia';
    });
  }

  Future<void> _saveSettings() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setBool('notifications_enabled', _notificationsEnabled);
    await prefs.setBool('location_enabled', _locationEnabled);
    await prefs.setBool('biometric_enabled', _biometricEnabled);
    await prefs.setBool('dark_mode_enabled', _darkModeEnabled);
    await prefs.setString('selected_language', _selectedLanguage);
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final scheme = Theme.of(context).colorScheme;
    return Scaffold
    (
      backgroundColor: Theme.of(context).scaffoldBackgroundColor,
      appBar: AppBar(
        backgroundColor: isDark ? Theme.of(context).appBarTheme.backgroundColor : Colors.white,
        elevation: 0,
        scrolledUnderElevation: 0,
        systemOverlayStyle: isDark ? SystemUiOverlayStyle.light : SystemUiOverlayStyle.dark,
        centerTitle: true,
        leading: IconButton(
          icon: Icon(FeatherIcons.arrowLeft, color: isDark ? Colors.white : Colors.black),
          onPressed: () => Navigator.pop(context),
        ),
        title: Text(
          'Pengaturan',
          style: GoogleFonts.nunitoSans(
            fontSize: 18,
            fontWeight: FontWeight.w700,
            color: isDark ? Colors.white : Colors.black,
          ),
        ),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Account Section
            _buildSectionHeader('Akun'),
            _buildSettingsCard([
              _buildSettingsTile(
                icon: FeatherIcons.user,
                title: 'Edit Profil',
                subtitle: 'Ubah nama, email, dan foto profil',
                onTap: () {
                  HapticFeedback.lightImpact();
                  Navigator.push(
                    context,
                    MaterialPageRoute(builder: (_) => const EditProfilePage()),
                  );
                },
              ),
              _buildSettingsTile(
                icon: FeatherIcons.mapPin,
                title: 'Alamat Pengiriman',
                subtitle: 'Kelola alamat pengiriman Anda',
                onTap: () {
                  HapticFeedback.lightImpact();
                  Navigator.push(
                    context,
                    MaterialPageRoute(builder: (_) => const AddressListPage()),
                  );
                },
              ),
            ]),

            // App Settings
            _buildSectionHeader('Aplikasi'),
            _buildSettingsCard([
              _buildSwitchTile(
                icon: FeatherIcons.bell,
                title: 'Notifikasi Push',
                subtitle: 'Terima notifikasi pesanan dan promosi',
                value: _notificationsEnabled,
                onChanged: (value) async {
                  if (value) {
                    // Ensure local notifications system is initialized
                    await SettingsService.initializeNotifications();
                    final granted = await SettingsService.requestNotificationPermission();
                    if (granted) {
                      // Initialize FCM push (requests permission on iOS and registers token)
                      await PushService.initialize();
                      setState(() => _notificationsEnabled = true);
                      await _saveSettings();
                      await SettingsService.showTestNotification();
                      _showSuccessSnackBar('Notifikasi push berhasil diaktifkan!');
                    } else {
                      _showErrorSnackBar('Izin notifikasi ditolak');
                    }
                  } else {
                    // Disable local flag and unregister token from backend
                    setState(() => _notificationsEnabled = false);
                    await _saveSettings();
                    await PushService.logoutCleanup();
                  }
                  HapticFeedback.lightImpact();
                },
              ),
              _buildSwitchTile(
                icon: FeatherIcons.mapPin,
                title: 'Akses Lokasi',
                subtitle: 'Untuk rekomendasi toko terdekat',
                value: _locationEnabled,
                onChanged: (value) async {
                  if (value) {
                    final granted = await SettingsService.requestLocationPermission();
                    if (granted) {
                      setState(() => _locationEnabled = true);
                      await _saveSettings();
                      final position = await SettingsService.getCurrentLocation();
                      if (position != null) {
                        _showSuccessSnackBar('Lokasi berhasil dideteksi!');
                      }
                    } else {
                      _showErrorSnackBar('Izin lokasi ditolak. Silakan aktifkan di pengaturan.');
                    }
                  } else {
                    setState(() => _locationEnabled = false);
                    await _saveSettings();
                  }
                  HapticFeedback.lightImpact();
                },
              ),
              _buildSwitchTile(
                icon: FeatherIcons.lock,
                title: 'Biometrik/Face ID',
                subtitle: 'Login lebih cepat dan aman',
                value: _biometricEnabled,
                onChanged: (value) async {
                  if (value) {
                    final isSupported = await SettingsService.checkBiometricSupport();
                    if (isSupported) {
                      final authenticated = await SettingsService.authenticateWithBiometric();
                      if (authenticated) {
                        setState(() => _biometricEnabled = true);
                        await _saveSettings();
                        _showSuccessSnackBar('Autentikasi biometrik berhasil diaktifkan!');
                      } else {
                        _showErrorSnackBar('Autentikasi biometrik gagal');
                      }
                    } else {
                      _showErrorSnackBar('Perangkat tidak mendukung autentikasi biometrik');
                    }
                  } else {
                    setState(() => _biometricEnabled = false);
                    await _saveSettings();
                  }
                  HapticFeedback.lightImpact();
                },
              ),
              _buildSwitchTile(
                icon: FeatherIcons.moon,
                title: 'Mode Gelap',
                subtitle: 'Tema gelap untuk mata yang nyaman',
                value: _darkModeEnabled,
                onChanged: (value) async {
                  setState(() => _darkModeEnabled = value);
                  await SettingsService.setDarkMode(value);
                  await _saveSettings();
                  _showSuccessSnackBar(value ? 'Mode gelap diaktifkan' : 'Mode terang diaktifkan');
                  HapticFeedback.lightImpact();
                },
              ),
            ]),

            // Preferences
            _buildSectionHeader('Preferensi'),
            _buildSettingsCard([
              _buildSettingsTile(
                icon: FeatherIcons.globe,
                title: 'Bahasa',
                subtitle: _selectedLanguage,
                onTap: () => _showLanguageDialog(),
              ),
            ]),

            // Support Section
            _buildSectionHeader('Dukungan'),
            _buildSettingsCard([
              _buildSettingsTile(
                icon: FeatherIcons.helpCircle,
                title: 'Bantuan & FAQ',
                subtitle: 'Dapatkan bantuan dan jawaban',
                onTap: () {
                  HapticFeedback.lightImpact();
                  // Navigate to help
                },
              ),
              _buildSettingsTile(
                icon: FeatherIcons.messageSquare,
                title: 'Hubungi Kami',
                subtitle: 'Customer service dan feedback',
                onTap: () {
                  HapticFeedback.lightImpact();
                  // Navigate to contact
                },
              ),
              _buildSettingsTile(
                icon: FeatherIcons.star,
                title: 'Beri Rating',
                subtitle: 'Rating aplikasi di App Store',
                onTap: () {
                  HapticFeedback.lightImpact();
                  // Open app store rating
                },
              ),
              _buildSettingsTile(
                icon: FeatherIcons.info,
                title: 'Tentang',
                subtitle: 'Versi aplikasi dan informasi',
                onTap: () => _showAboutDialog(),
              ),
            ]),

            // Advanced Section
            _buildSectionHeader('Lanjutan'),
            _buildSettingsCard([
              _buildSettingsTile(
                icon: FeatherIcons.trash2,
                title: 'Hapus Cache',
                subtitle: 'Bersihkan data sementara',
                onTap: _clearCache,
              ),
              _buildSettingsTile(
                icon: FeatherIcons.userX,
                title: 'Hapus Akun',
                subtitle: 'Hapus akun secara permanen',
                onTap: () => _showDeleteAccountDialog(),
                textColor: AppColors.error,
              ),
              _buildSettingsTile(
                icon: FeatherIcons.logOut,
                title: 'Keluar',
                subtitle: 'Keluar dari akun Anda',
                onTap: _logout,
                textColor: AppColors.error,
              ),
            ]),

            const SizedBox(height: 20),
            
            // App Version
            Center(
              child: Text(
                'Nutrifarm v1.0.0',
                style: GoogleFonts.nunitoSans(
                  color: scheme.onSurfaceVariant,
                  fontSize: 12,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  void _showSuccessSnackBar(String message) {
    AppAlert.showSuccess(context, message);
  }

  void _showErrorSnackBar(String message) {
    AppAlert.showError(context, message);
  }

  Widget _buildSectionHeader(String title) {
    return Padding(
      padding: const EdgeInsets.only(left: 4, bottom: 12, top: 20),
      child: Text(
        title,
        style: GoogleFonts.nunitoSans(
          fontSize: 16,
          fontWeight: FontWeight.w700,
          color: AppColors.primaryGreen,
        ),
      ),
    );
  }

  Widget _buildSettingsCard(List<Widget> children) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    return Container(
      decoration: BoxDecoration(
        color: Theme.of(context).colorScheme.surface,
        borderRadius: BorderRadius.circular(16),
        boxShadow: isDark
            ? []
            : [
                BoxShadow(
                  color: Colors.black.withOpacity(0.05),
                  blurRadius: 10,
                  offset: const Offset(0, 2),
                ),
              ],
      ),
      child: Column(
        children: children,
      ),
    );
  }

  Widget _buildSettingsTile({
    required IconData icon,
    required String title,
    required String subtitle,
    required VoidCallback onTap,
    Color? textColor,
    Widget? trailing,
  }) {
    final scheme = Theme.of(context).colorScheme;
    return Material(
      color: Colors.transparent,
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(16),
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Row(
            children: [
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: (textColor ?? AppColors.primaryGreen).withOpacity(0.1),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Icon(
                  icon,
                  color: textColor ?? AppColors.primaryGreen,
                  size: 20,
                ),
              ),
              const SizedBox(width: 16),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      title,
                      style: GoogleFonts.nunitoSans(
                        fontSize: 16,
                        fontWeight: FontWeight.w600,
                        color: textColor ?? scheme.onSurface,
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      subtitle,
                      style: GoogleFonts.nunitoSans(
                        fontSize: 13,
                        color: scheme.onSurfaceVariant,
                      ),
                    ),
                  ],
                ),
              ),
              trailing ?? Icon(
                FeatherIcons.chevronRight,
                color: scheme.onSurfaceVariant,
                size: 16,
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildSwitchTile({
    required IconData icon,
    required String title,
    required String subtitle,
    required bool value,
    required Function(bool) onChanged,
  }) {
    return _buildSettingsTile(
      icon: icon,
      title: title,
      subtitle: subtitle,
      onTap: () => onChanged(!value),
      trailing: Switch(
        value: value,
        onChanged: onChanged,
        activeColor: AppColors.primaryGreen,
      ),
    );
  }

  Widget _buildLanguageOption(String lang) {
    final selected = _selectedLanguage == lang;
    return InkWell(
      onTap: () async {
        HapticFeedback.selectionClick();
        setState(() => _selectedLanguage = lang);
        await _saveSettings();
        if (mounted) Navigator.of(context).pop();
        _showSuccessSnackBar('Bahasa diubah ke $lang');
      },
      borderRadius: BorderRadius.circular(12),
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 8),
        child: Row(
          children: [
            Expanded(
              child: Text(
                lang,
                style: GoogleFonts.nunitoSans(
                  fontSize: 16,
                  fontWeight: selected ? FontWeight.w700 : FontWeight.w600,
                  color: selected ? AppColors.primaryGreen : AppColors.onSurface,
                ),
              ),
            ),
            if (selected)
              Container(
                decoration: BoxDecoration(
                  color: AppColors.primaryGreen.withOpacity(0.1),
                  borderRadius: BorderRadius.circular(999),
                ),
                padding: const EdgeInsets.symmetric(vertical: 4, horizontal: 10),
                child: Text(
                  'Terpilih',
                  style: GoogleFonts.nunitoSans(
                    fontSize: 12,
                    color: AppColors.primaryGreen,
                    fontWeight: FontWeight.w700,
                  ),
                ),
              ),
          ],
        ),
      ),
    );
  }

  void _showLanguageDialog() {
    AppDialog.showInfo(
      context,
      title: 'Pilih Bahasa',
      content: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          _buildLanguageOption('Bahasa Indonesia'),
          _buildLanguageOption('English'),
          _buildLanguageOption('العربية'),
        ],
      ),
    );
  }

  void _showAboutDialog() {
    AppDialog.showInfo(
      context,
      title: 'Tentang Nutrifarm',
      content: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Nutrifarm Mobile App',
            style: GoogleFonts.nunitoSans(fontSize: 16, fontWeight: FontWeight.w600, color: AppColors.onSurface),
          ),
          const SizedBox(height: 8),
          Text(
            'Versi: 1.0.0\nDikembangkan oleh Tim Nutrifarm\n\n© 2024 Nutrifarm. All rights reserved.',
            style: GoogleFonts.nunitoSans(fontSize: 14, color: AppColors.onSurfaceVariant),
          ),
        ],
      ),
    );
  }

  Future<void> _clearCache() async {
    HapticFeedback.lightImpact();
    
    // Show loading dialog
    AppDialog.showLoading(context, message: 'Menghapus cache...');

    // Simulate cache clearing
    await Future.delayed(const Duration(seconds: 2));
    
    Navigator.pop(context); // Close loading dialog
    
    // Show success alert
    AppAlert.showSuccess(context, 'Cache berhasil dihapus');
  }

  Future<void> _logout() async {
    HapticFeedback.lightImpact();
    
    final confirm = await AppDialog.showConfirm(
      context,
      title: 'Keluar Akun',
      message: 'Apakah Anda yakin ingin keluar dari akun?',
    );
    if (confirm == true) {
      final authService = Provider.of<AuthService>(context, listen: false);
      await authService.logout();
      if (mounted) {
        Navigator.pushNamedAndRemoveUntil(context, '/login', (route) => false);
      }
    }
  }

  void _showDeleteAccountDialog() {
    AppDialog.showConfirm(
      context,
      title: 'Hapus Akun',
      message: 'Tindakan ini tidak dapat dibatalkan. Semua data Anda akan dihapus permanen.',
      confirmText: 'Hapus',
      destructive: true,
    ).then((confirm) {
      if (confirm == true) {
        // Implement account deletion logic
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Fitur hapus akun akan segera tersedia', style: GoogleFonts.nunitoSans()),
            backgroundColor: AppColors.primaryGreen,
          ),
        );
      }
    });
  }
}
