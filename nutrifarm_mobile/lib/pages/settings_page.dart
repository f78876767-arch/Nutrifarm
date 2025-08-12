import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:provider/provider.dart';
import '../theme/app_theme.dart';
import '../services/auth_service.dart';
import '../services/settings_service.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:flutter_feather_icons/flutter_feather_icons.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:local_auth/local_auth.dart';

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
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        backgroundColor: AppColors.surface,
        elevation: 0,
        scrolledUnderElevation: 1,
        leading: IconButton(
          icon: const Icon(FeatherIcons.arrowLeft, color: AppColors.primaryGreen),
          onPressed: () => Navigator.pop(context),
        ),
        title: Text(
          'Pengaturan',
          style: GoogleFonts.nunitoSans(
            fontSize: 24,
            fontWeight: FontWeight.w700,
            color: AppColors.primaryGreen,
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
                  // Navigate to profile edit
                },
              ),
              _buildSettingsTile(
                icon: FeatherIcons.mapPin,
                title: 'Alamat Pengiriman',
                subtitle: 'Kelola alamat pengiriman Anda',
                onTap: () {
                  HapticFeedback.lightImpact();
                  // Navigate to address management
                },
              ),
              _buildSettingsTile(
                icon: FeatherIcons.creditCard,
                title: 'Metode Pembayaran',
                subtitle: 'Tambah atau edit metode pembayaran',
                onTap: () {
                  HapticFeedback.lightImpact();
                  // Navigate to payment methods
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
                    final granted = await SettingsService.requestNotificationPermission();
                    if (granted) {
                      setState(() => _notificationsEnabled = true);
                      await _saveSettings();
                      await SettingsService.showTestNotification();
                      _showSuccessSnackBar('Notifikasi push berhasil diaktifkan!');
                    } else {
                      _showErrorSnackBar('Izin notifikasi ditolak');
                    }
                  } else {
                    setState(() => _notificationsEnabled = false);
                    await _saveSettings();
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
              _buildSettingsTile(
                icon: FeatherIcons.dollarSign,
                title: 'Mata Uang',
                subtitle: 'IDR (Rupiah)',
                onTap: () {
                  HapticFeedback.lightImpact();
                  // Show currency selection
                },
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
                  color: AppColors.onSurfaceVariant,
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
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message, style: GoogleFonts.nunitoSans()),
        backgroundColor: AppColors.primaryGreen,
        duration: Duration(seconds: 3),
      ),
    );
  }

  void _showErrorSnackBar(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message, style: GoogleFonts.nunitoSans()),
        backgroundColor: Colors.red,
        duration: Duration(seconds: 3),
      ),
    );
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
    return Container(
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
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
                        color: textColor ?? AppColors.onSurface,
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      subtitle,
                      style: GoogleFonts.nunitoSans(
                        fontSize: 13,
                        color: AppColors.onSurfaceVariant,
                      ),
                    ),
                  ],
                ),
              ),
              trailing ?? Icon(
                FeatherIcons.chevronRight,
                color: AppColors.onSurfaceVariant,
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

  void _showLanguageDialog() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        backgroundColor: AppColors.surface,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: Text(
          'Pilih Bahasa',
          style: GoogleFonts.nunitoSans(
            fontSize: 20,
            fontWeight: FontWeight.w700,
            color: AppColors.onSurface,
          ),
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            _buildLanguageOption('Bahasa Indonesia'),
            _buildLanguageOption('English'),
            _buildLanguageOption('العربية'),
          ],
        ),
      ),
    );
  }

  Widget _buildLanguageOption(String language) {
    return RadioListTile<String>(
      title: Text(
        language,
        style: GoogleFonts.nunitoSans(
          fontSize: 16,
          color: AppColors.onSurface,
        ),
      ),
      value: language,
      groupValue: _selectedLanguage,
      onChanged: (value) {
        setState(() => _selectedLanguage = value!);
        _saveSettings();
        Navigator.pop(context);
        HapticFeedback.lightImpact();
      },
      activeColor: AppColors.primaryGreen,
    );
  }

  void _showAboutDialog() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        backgroundColor: AppColors.surface,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: Text(
          'Tentang Nutrifarm',
          style: GoogleFonts.nunitoSans(
            fontSize: 20,
            fontWeight: FontWeight.w700,
            color: AppColors.onSurface,
          ),
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Nutrifarm Mobile App',
              style: GoogleFonts.nunitoSans(
                fontSize: 16,
                fontWeight: FontWeight.w600,
                color: AppColors.onSurface,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              'Versi: 1.0.0\nDikembangkan oleh Tim Nutrifarm\n\n© 2024 Nutrifarm. All rights reserved.',
              style: GoogleFonts.nunitoSans(
                fontSize: 14,
                color: AppColors.onSurfaceVariant,
              ),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: Text(
              'Tutup',
              style: GoogleFonts.nunitoSans(
                color: AppColors.primaryGreen,
                fontWeight: FontWeight.w600,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Future<void> _clearCache() async {
    HapticFeedback.lightImpact();
    
    // Show loading dialog
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) => AlertDialog(
        backgroundColor: AppColors.surface,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const CircularProgressIndicator(),
            const SizedBox(height: 16),
            Text(
              'Menghapus cache...',
              style: GoogleFonts.nunitoSans(
                fontSize: 16,
                color: AppColors.onSurface,
              ),
            ),
          ],
        ),
      ),
    );

    // Simulate cache clearing
    await Future.delayed(const Duration(seconds: 2));
    
    Navigator.pop(context); // Close loading dialog
    
    // Show success message
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(
          'Cache berhasil dihapus',
          style: GoogleFonts.nunitoSans(),
        ),
        backgroundColor: AppColors.primaryGreen,
      ),
    );
  }

  Future<void> _logout() async {
    HapticFeedback.lightImpact();
    
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        backgroundColor: AppColors.surface,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: Text(
          'Keluar Akun',
          style: GoogleFonts.nunitoSans(
            fontSize: 20,
            fontWeight: FontWeight.w700,
            color: AppColors.onSurface,
          ),
        ),
        content: Text(
          'Apakah Anda yakin ingin keluar dari akun?',
          style: GoogleFonts.nunitoSans(
            fontSize: 16,
            color: AppColors.onSurfaceVariant,
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: Text(
              'Batal',
              style: GoogleFonts.nunitoSans(
                color: AppColors.onSurfaceVariant,
                fontWeight: FontWeight.w600,
              ),
            ),
          ),
          ElevatedButton(
            onPressed: () async {
              Navigator.pop(context);
              final authService = Provider.of<AuthService>(context, listen: false);
              await authService.logout();
              Navigator.pushNamedAndRemoveUntil(context, '/login', (route) => false);
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.error,
              foregroundColor: Colors.white,
            ),
            child: Text(
              'Keluar',
              style: GoogleFonts.nunitoSans(fontWeight: FontWeight.w600),
            ),
          ),
        ],
      ),
    );
  }

  void _showDeleteAccountDialog() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        backgroundColor: AppColors.surface,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: Text(
          'Hapus Akun',
          style: GoogleFonts.nunitoSans(
            fontSize: 20,
            fontWeight: FontWeight.w700,
            color: AppColors.error,
          ),
        ),
        content: Text(
          'Tindakan ini tidak dapat dibatalkan. Semua data Anda akan dihapus permanen.',
          style: GoogleFonts.nunitoSans(
            fontSize: 16,
            color: AppColors.onSurfaceVariant,
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: Text(
              'Batal',
              style: GoogleFonts.nunitoSans(
                color: AppColors.onSurfaceVariant,
                fontWeight: FontWeight.w600,
              ),
            ),
          ),
          ElevatedButton(
            onPressed: () {
              Navigator.pop(context);
              // Implement account deletion logic
              ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(
                  content: Text(
                    'Fitur hapus akun akan segera tersedia',
                    style: GoogleFonts.nunitoSans(),
                  ),
                  backgroundColor: AppColors.primaryGreen,
                ),
              );
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.error,
              foregroundColor: Colors.white,
            ),
            child: Text(
              'Hapus',
              style: GoogleFonts.nunitoSans(fontWeight: FontWeight.w600),
            ),
          ),
        ],
      ),
    );
  }
}
