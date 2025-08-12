import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:flutter_feather_icons/flutter_feather_icons.dart';
import 'package:provider/provider.dart';
import 'package:permission_handler/permission_handler.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../theme/app_theme.dart';
import '../services/auth_service.dart';

class SettingsPage extends StatefulWidget {
  const SettingsPage({super.key});

  @override
  _SettingsPageState createState() => _SettingsPageState();
}

class _SettingsPageState extends State<SettingsPage> {
  bool _notificationsEnabled = true;
  bool _locationEnabled = false;
  bool _biometricEnabled = false;
  bool _emailNotifications = true;
  bool _pushNotifications = true;
  bool _orderUpdates = true;
  bool _promotionalEmails = false;
  String _selectedLanguage = 'Bahasa Indonesia';
  String _selectedTheme = 'System';
  bool _isLoading = false;

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
      _emailNotifications = prefs.getBool('email_notifications') ?? true;
      _pushNotifications = prefs.getBool('push_notifications') ?? true;
      _orderUpdates = prefs.getBool('order_updates') ?? true;
      _promotionalEmails = prefs.getBool('promotional_emails') ?? false;
      _selectedLanguage = prefs.getString('selected_language') ?? 'Bahasa Indonesia';
      _selectedTheme = prefs.getString('selected_theme') ?? 'System';
    });
  }

  Future<void> _saveSettings() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setBool('notifications_enabled', _notificationsEnabled);
    await prefs.setBool('location_enabled', _locationEnabled);
    await prefs.setBool('biometric_enabled', _biometricEnabled);
    await prefs.setBool('email_notifications', _emailNotifications);
    await prefs.setBool('push_notifications', _pushNotifications);
    await prefs.setBool('order_updates', _orderUpdates);
    await prefs.setBool('promotional_emails', _promotionalEmails);
    await prefs.setString('selected_language', _selectedLanguage);
    await prefs.setString('selected_theme', _selectedTheme);
  }

  Future<void> _requestLocationPermission() async {
    final status = await Permission.location.request();
    if (status.isGranted) {
      setState(() => _locationEnabled = true);
      await _saveSettings();
      _showSuccessSnackBar('Location permission granted');
    } else if (status.isDenied) {
      setState(() => _locationEnabled = false);
      _showErrorSnackBar('Location permission denied');
    } else if (status.isPermanentlyDenied) {
      _showPermissionDialog('Location');
    }
  }

  Future<void> _requestNotificationPermission() async {
    final status = await Permission.notification.request();
    if (status.isGranted) {
      setState(() {
        _notificationsEnabled = true;
        _pushNotifications = true;
      });
      await _saveSettings();
      _showSuccessSnackBar('Notification permission granted');
    } else {
      setState(() {
        _notificationsEnabled = false;
        _pushNotifications = false;
      });
      _showErrorSnackBar('Notification permission denied');
    }
  }

  void _showPermissionDialog(String permission) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: Text('Permission Required'),
        content: Text('$permission permission is required. Please enable it in Settings.'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: Text('Cancel'),
          ),
          ElevatedButton(
            onPressed: () {
              Navigator.pop(context);
              openAppSettings();
            },
            child: Text('Open Settings'),
          ),
        ],
      ),
    );
  }

  void _showSuccessSnackBar(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: AppColors.success,
        duration: Duration(seconds: 2),
      ),
    );
  }

  void _showErrorSnackBar(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: AppColors.error,
        duration: Duration(seconds: 2),
      ),
    );
  }

  Future<void> _logout() async {
    final shouldLogout = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: Text('Logout'),
        content: Text('Are you sure you want to logout?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: Text('Cancel'),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(backgroundColor: AppColors.error),
            onPressed: () => Navigator.pop(context, true),
            child: Text('Logout'),
          ),
        ],
      ),
    );

    if (shouldLogout == true) {
      setState(() => _isLoading = true);
      
      try {
        final authService = Provider.of<AuthService>(context, listen: false);
        await authService.logout();
        
        // Navigate to login and remove all previous routes
        Navigator.of(context).pushNamedAndRemoveUntil('/login', (route) => false);
      } catch (e) {
        _showErrorSnackBar('Failed to logout. Please try again.');
      } finally {
        setState(() => _isLoading = false);
      }
    }
  }

  Future<void> _clearCache() async {
    final shouldClear = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: Text('Clear Cache'),
        content: Text('This will clear all cached data. Continue?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: Text('Cancel'),
          ),
          ElevatedButton(
            onPressed: () => Navigator.pop(context, true),
            child: Text('Clear'),
          ),
        ],
      ),
    );

    if (shouldClear == true) {
      setState(() => _isLoading = true);
      
      try {
        // Simulate cache clearing
        await Future.delayed(Duration(seconds: 1));
        _showSuccessSnackBar('Cache cleared successfully');
      } catch (e) {
        _showErrorSnackBar('Failed to clear cache');
      } finally {
        setState(() => _isLoading = false);
      }
    }
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
          'Settings',
          style: GoogleFonts.nunitoSans(
            fontSize: 24,
            fontWeight: FontWeight.w700,
            color: AppColors.primaryGreen,
          ),
        ),
      ),
      body: _isLoading 
        ? Center(child: CircularProgressIndicator(color: AppColors.primaryGreen))
        : SingleChildScrollView(
            child: Column(
              children: [
                // Notifications Section
                _buildSectionHeader('Notifications'),
                _buildSettingsCard([
                  _buildSwitchTile(
                    icon: FeatherIcons.bell,
                    title: 'Push Notifications',
                    subtitle: 'Receive notifications on your device',
                    value: _pushNotifications,
                    onChanged: (value) async {
                      if (value) {
                        await _requestNotificationPermission();
                      } else {
                        setState(() => _pushNotifications = false);
                        await _saveSettings();
                      }
                    },
                  ),
                  _buildSwitchTile(
                    icon: FeatherIcons.mail,
                    title: 'Email Notifications',
                    subtitle: 'Receive notifications via email',
                    value: _emailNotifications,
                    onChanged: (value) async {
                      setState(() => _emailNotifications = value);
                      await _saveSettings();
                    },
                  ),
                  _buildSwitchTile(
                    icon: FeatherIcons.package,
                    title: 'Order Updates',
                    subtitle: 'Get updates about your orders',
                    value: _orderUpdates,
                    onChanged: (value) async {
                      setState(() => _orderUpdates = value);
                      await _saveSettings();
                    },
                  ),
                  _buildSwitchTile(
                    icon: FeatherIcons.tag,
                    title: 'Promotional Emails',
                    subtitle: 'Receive offers and promotions',
                    value: _promotionalEmails,
                    onChanged: (value) async {
                      setState(() => _promotionalEmails = value);
                      await _saveSettings();
                    },
                  ),
                ]),

                // Privacy & Security Section
                _buildSectionHeader('Privacy & Security'),
                _buildSettingsCard([
                  _buildSwitchTile(
                    icon: FeatherIcons.mapPin,
                    title: 'Location Services',
                    subtitle: 'Allow location access for deliveries',
                    value: _locationEnabled,
                    onChanged: (value) async {
                      if (value) {
                        await _requestLocationPermission();
                      } else {
                        setState(() => _locationEnabled = false);
                        await _saveSettings();
                      }
                    },
                  ),
                  _buildSwitchTile(
                    icon: FeatherIcons.fingerprint,
                    title: 'Biometric Authentication',
                    subtitle: 'Use fingerprint or face ID',
                    value: _biometricEnabled,
                    onChanged: (value) async {
                      setState(() => _biometricEnabled = value);
                      await _saveSettings();
                      if (value) {
                        _showSuccessSnackBar('Biometric authentication enabled');
                      }
                    },
                  ),
                ]),

                // App Preferences Section
                _buildSectionHeader('App Preferences'),
                _buildSettingsCard([
                  _buildSelectTile(
                    icon: FeatherIcons.globe,
                    title: 'Language',
                    subtitle: _selectedLanguage,
                    onTap: () => _showLanguageDialog(),
                  ),
                  _buildSelectTile(
                    icon: FeatherIcons.moon,
                    title: 'Theme',
                    subtitle: _selectedTheme,
                    onTap: () => _showThemeDialog(),
                  ),
                ]),

                // Account Section
                _buildSectionHeader('Account'),
                _buildSettingsCard([
                  _buildSettingsTile(
                    icon: FeatherIcons.user,
                    title: 'Profile Information',
                    subtitle: 'Edit your personal details',
                    onTap: () {
                      // Navigate to profile edit page
                      Navigator.pushNamed(context, '/user-info');
                    },
                  ),
                  _buildSettingsTile(
                    icon: FeatherIcons.creditCard,
                    title: 'Payment Methods',
                    subtitle: 'Manage your payment options',
                    onTap: () {
                      _showComingSoonDialog('Payment Methods');
                    },
                  ),
                  _buildSettingsTile(
                    icon: FeatherIcons.mapPin,
                    title: 'Delivery Addresses',
                    subtitle: 'Manage your delivery locations',
                    onTap: () {
                      _showComingSoonDialog('Address Management');
                    },
                  ),
                ]),

                // Support Section
                _buildSectionHeader('Support'),
                _buildSettingsCard([
                  _buildSettingsTile(
                    icon: FeatherIcons.helpCircle,
                    title: 'Help & Support',
                    subtitle: 'Get help and contact support',
                    onTap: () => _showComingSoonDialog('Help Center'),
                  ),
                  _buildSettingsTile(
                    icon: FeatherIcons.messageSquare,
                    title: 'Feedback',
                    subtitle: 'Send us your feedback',
                    onTap: () => _showFeedbackDialog(),
                  ),
                  _buildSettingsTile(
                    icon: FeatherIcons.info,
                    title: 'About',
                    subtitle: 'App version and information',
                    onTap: () => _showAboutDialog(),
                  ),
                ]),

                // Advanced Section
                _buildSectionHeader('Advanced'),
                _buildSettingsCard([
                  _buildSettingsTile(
                    icon: FeatherIcons.trash2,
                    title: 'Clear Cache',
                    subtitle: 'Free up storage space',
                    onTap: _clearCache,
                  ),
                  _buildSettingsTile(
                    icon: FeatherIcons.logOut,
                    title: 'Logout',
                    subtitle: 'Sign out of your account',
                    onTap: _logout,
                    textColor: AppColors.error,
                  ),
                ]),

                SizedBox(height: 20),
                
                // App Version
                Container(
                  padding: EdgeInsets.all(20),
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
                icon: FeatherIcons.mapPin,
                title: 'Alamat Pengiriman',
                subtitle: 'Kelola alamat pengiriman Anda',
                onTap: () {
                  HapticFeedback.lightImpact();
                },
              ),
              _buildSettingsTile(
                icon: FeatherIcons.creditCard,
                title: 'Metode Pembayaran',
                subtitle: 'Tambah atau edit metode pembayaran',
                onTap: () {
                  HapticFeedback.lightImpact();
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
                onChanged: (value) {
                  setState(() => _notificationsEnabled = value);
                  HapticFeedback.lightImpact();
                },
              ),
              _buildSwitchTile(
                icon: FeatherIcons.mapPin,
                title: 'Akses Lokasi',
                subtitle: 'Untuk rekomendasi toko terdekat',
                value: _locationEnabled,
                onChanged: (value) {
                  setState(() => _locationEnabled = value);
                  HapticFeedback.lightImpact();
                },
              ),
              _buildSwitchTile(
                icon: FeatherIcons.lock,
                title: 'Biometrik/Face ID',
                subtitle: 'Login lebih cepat dan aman',
                value: _biometricEnabled,
                onChanged: (value) {
                  setState(() => _biometricEnabled = value);
                  HapticFeedback.lightImpact();
                },
              ),
              _buildDropdownTile(
                icon: FeatherIcons.globe,
                title: 'Bahasa',
                value: _selectedLanguage,
                options: ['Bahasa Indonesia', 'English', 'Melayu'],
                onChanged: (value) {
                  setState(() => _selectedLanguage = value!);
                  HapticFeedback.lightImpact();
                },
              ),
              _buildDropdownTile(
                icon: FeatherIcons.moon,
                title: 'Tema',
                value: _selectedTheme,
                options: ['System', 'Light', 'Dark'],
                onChanged: (value) {
                  setState(() => _selectedTheme = value!);
                  HapticFeedback.lightImpact();
                },
              ),
            ]),

            // Support & Help
            _buildSectionHeader('Bantuan & Dukungan'),
            _buildSettingsCard([
              _buildSettingsTile(
                icon: FeatherIcons.helpCircle,
                title: 'Pusat Bantuan',
                subtitle: 'FAQ dan panduan penggunaan',
                onTap: () {
                  HapticFeedback.lightImpact();
                },
              ),
              _buildSettingsTile(
                icon: FeatherIcons.messageCircle,
                title: 'Hubungi Kami',
                subtitle: 'Chat dengan customer service',
                onTap: () {
                  HapticFeedback.lightImpact();
                },
              ),
              _buildSettingsTile(
                icon: FeatherIcons.star,
                title: 'Beri Rating',
                subtitle: 'Berikan rating di App Store/Play Store',
                onTap: () {
                  HapticFeedback.lightImpact();
                },
              ),
              _buildSettingsTile(
                icon: FeatherIcons.share,
                title: 'Bagikan Aplikasi',
                subtitle: 'Ajak teman menggunakan Nutrifarm',
                onTap: () {
                  HapticFeedback.lightImpact();
                },
              ),
            ]),

            // Legal
            _buildSectionHeader('Legal'),
            _buildSettingsCard([
              _buildSettingsTile(
                icon: FeatherIcons.fileText,
                title: 'Syarat & Ketentuan',
                subtitle: 'Baca syarat dan ketentuan layanan',
                onTap: () {
                  HapticFeedback.lightImpact();
                },
              ),
              _buildSettingsTile(
                icon: FeatherIcons.shield,
                title: 'Kebijakan Privasi',
                subtitle: 'Perlindungan data dan privasi Anda',
                onTap: () {
                  HapticFeedback.lightImpact();
                },
              ),
            ]),

            // Account Actions
            _buildSectionHeader('Akun'),
            _buildSettingsCard([
              _buildSettingsTile(
                icon: FeatherIcons.logOut,
                title: 'Keluar',
                subtitle: 'Logout dari akun Anda',
                textColor: AppColors.error,
                onTap: () {
                  _showLogoutDialog();
                },
              ),
              _buildSettingsTile(
                icon: FeatherIcons.userX,
                title: 'Hapus Akun',
                subtitle: 'Hapus permanen akun dan data Anda',
                textColor: AppColors.error,
                onTap: () {
                  _showDeleteAccountDialog();
                },
              ),
            ]),

            const SizedBox(height: 100), // Space for bottom navigation
          ],
        ),
      ),
    );
  }

  Widget _buildSectionHeader(String title) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.fromLTRB(20, 32, 20, 8),
      child: Text(
        title,
        style: GoogleFonts.nunitoSans(
          fontSize: 18,
          fontWeight: FontWeight.w700,
          color: AppColors.primaryGreen,
        ),
      ),
    );
  }

  Widget _buildSettingsCard(List<Widget> children) {
    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 20, vertical: 8),
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: AppColors.onSurface.withOpacity(0.05),
            blurRadius: 10,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(children: children),
    );
  }

  Widget _buildSettingsTile({
    required IconData icon,
    required String title,
    required String subtitle,
    VoidCallback? onTap,
    Color? textColor,
  }) {
    return ListTile(
      contentPadding: const EdgeInsets.symmetric(horizontal: 20, vertical: 8),
      leading: Container(
        padding: const EdgeInsets.all(8),
        decoration: BoxDecoration(
          color: AppColors.primaryGreen.withOpacity(0.1),
          borderRadius: BorderRadius.circular(8),
        ),
        child: Icon(icon, color: AppColors.primaryGreen, size: 20),
      ),
      title: Text(
        title,
        style: GoogleFonts.nunitoSans(
          fontWeight: FontWeight.w600,
          fontSize: 16,
          color: textColor ?? AppColors.onSurface,
        ),
      ),
      subtitle: Text(
        subtitle,
        style: GoogleFonts.nunitoSans(
          fontSize: 14,
          color: AppColors.onSurfaceVariant,
        ),
      ),
      trailing: Icon(
        FeatherIcons.chevronRight,
        color: AppColors.onSurfaceVariant,
        size: 20,
      ),
      onTap: onTap,
    );
  }

  Widget _buildSwitchTile({
    required IconData icon,
    required String title,
    required String subtitle,
    required bool value,
    required ValueChanged<bool> onChanged,
  }) {
    return ListTile(
      contentPadding: const EdgeInsets.symmetric(horizontal: 20, vertical: 8),
      leading: Container(
        padding: const EdgeInsets.all(8),
        decoration: BoxDecoration(
          color: AppColors.primaryGreen.withOpacity(0.1),
          borderRadius: BorderRadius.circular(8),
        ),
        child: Icon(icon, color: AppColors.primaryGreen, size: 20),
      ),
      title: Text(
        title,
        style: GoogleFonts.nunitoSans(
          fontWeight: FontWeight.w600,
          fontSize: 16,
          color: AppColors.onSurface,
        ),
      ),
      subtitle: Text(
        subtitle,
        style: GoogleFonts.nunitoSans(
          fontSize: 14,
          color: AppColors.onSurfaceVariant,
        ),
      ),
      trailing: Switch.adaptive(
        value: value,
        onChanged: onChanged,
        activeColor: AppColors.primaryGreen,
      ),
    );
  }

  Widget _buildSelectTile({
    required IconData icon,
    required String title,
    required String subtitle,
    required VoidCallback onTap,
  }) {
    return ListTile(
      contentPadding: const EdgeInsets.symmetric(horizontal: 20, vertical: 8),
      leading: Container(
        padding: const EdgeInsets.all(8),
        decoration: BoxDecoration(
          color: AppColors.primaryGreen.withOpacity(0.1),
          borderRadius: BorderRadius.circular(8),
        ),
        child: Icon(icon, color: AppColors.primaryGreen, size: 20),
      ),
      title: Text(
        title,
        style: GoogleFonts.nunitoSans(
          fontWeight: FontWeight.w600,
          fontSize: 16,
          color: AppColors.onSurface,
        ),
      ),
      subtitle: Text(
        subtitle,
        style: GoogleFonts.nunitoSans(
          fontSize: 14,
          color: AppColors.primaryGreen,
          fontWeight: FontWeight.w500,
        ),
      ),
      trailing: Icon(
        FeatherIcons.chevronRight,
        color: AppColors.onSurfaceVariant,
        size: 20,
      ),
      onTap: onTap,
    );
  }

  void _showLanguageDialog() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: Text('Select Language'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            'English',
            'Bahasa Indonesia',
          ].map((language) => RadioListTile<String>(
            title: Text(language),
            value: language,
            groupValue: _selectedLanguage,
            onChanged: (value) async {
              if (value != null) {
                setState(() => _selectedLanguage = value);
                await _saveSettings();
                Navigator.pop(context);
                _showSuccessSnackBar('Language changed to $value');
              }
            },
          )).toList(),
        ),
      ),
    );
  }

  void _showThemeDialog() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: Text('Select Theme'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            'Light',
            'Dark',
            'System',
          ].map((theme) => RadioListTile<String>(
            title: Text(theme),
            value: theme,
            groupValue: _selectedTheme,
            onChanged: (value) async {
              if (value != null) {
                setState(() => _selectedTheme = value);
                await _saveSettings();
                Navigator.pop(context);
                _showSuccessSnackBar('Theme changed to $value');
              }
            },
          )).toList(),
        ),
      ),
    );
  }

  void _showComingSoonDialog(String feature) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: Text('Coming Soon'),
        content: Text('$feature feature is coming soon!'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: Text('OK'),
          ),
        ],
      ),
    );
  }

  void _showFeedbackDialog() {
    final TextEditingController feedbackController = TextEditingController();
    
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: Text('Send Feedback'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Text('We value your feedback! Let us know how we can improve.'),
            SizedBox(height: 16),
            TextField(
              controller: feedbackController,
              maxLines: 4,
              decoration: InputDecoration(
                hintText: 'Type your feedback here...',
                border: OutlineInputBorder(),
              ),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: Text('Cancel'),
          ),
          ElevatedButton(
            onPressed: () {
              if (feedbackController.text.isNotEmpty) {
                Navigator.pop(context);
                _showSuccessSnackBar('Thank you for your feedback!');
              }
            },
            child: Text('Send'),
          ),
        ],
      ),
    );
  }

  void _showAboutDialog() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: Text('About Nutrifarm'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('Nutrifarm Mobile App\nVersion 1.0.0\n'),
            Text('Your trusted partner for organic and natural products.'),
            SizedBox(height: 16),
            Text('Features:'),
            Text('• Browse organic products'),
            Text('• Secure online shopping'),
            Text('• Order tracking'),
            Text('• Customer support'),
            SizedBox(height: 16),
            Text('© 2025 Nutrifarm. All rights reserved.'),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: Text('OK'),
          ),
        ],
      ),
    );
  }
}
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.fromLTRB(20, 24, 20, 8),
      child: Text(
        title,
        style: GoogleFonts.nunitoSans(
          fontSize: 14,
          fontWeight: FontWeight.w600,
          color: AppColors.onSurfaceVariant,
          letterSpacing: 0.5,
        ),
      ),
    );
  }

  Widget _buildSettingsCard(List<Widget> children) {
    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: AppColors.onSurface.withOpacity(0.05),
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
    Color? textColor,
    required VoidCallback onTap,
  }) {
    return ListTile(
      leading: Container(
        padding: const EdgeInsets.all(8),
        decoration: BoxDecoration(
          color: (textColor ?? AppColors.primaryGreen).withOpacity(0.1),
          borderRadius: BorderRadius.circular(10),
        ),
        child: Icon(
          icon,
          color: textColor ?? AppColors.primaryGreen,
          size: 20,
        ),
      ),
      title: Text(
        title,
        style: GoogleFonts.nunitoSans(
          fontSize: 16,
          fontWeight: FontWeight.w600,
          color: textColor ?? AppColors.onSurface,
        ),
      ),
      subtitle: Text(
        subtitle,
        style: GoogleFonts.nunitoSans(
          fontSize: 14,
          color: AppColors.onSurfaceVariant,
        ),
      ),
      trailing: Icon(
        FeatherIcons.chevronRight,
        color: AppColors.onSurfaceVariant,
        size: 20,
      ),
      onTap: onTap,
    );
  }

  Widget _buildSwitchTile({
    required IconData icon,
    required String title,
    required String subtitle,
    required bool value,
    required ValueChanged<bool> onChanged,
  }) {
    return ListTile(
      leading: Container(
        padding: const EdgeInsets.all(8),
        decoration: BoxDecoration(
          color: AppColors.primaryGreen.withOpacity(0.1),
          borderRadius: BorderRadius.circular(10),
        ),
        child: Icon(
          icon,
          color: AppColors.primaryGreen,
          size: 20,
        ),
      ),
      title: Text(
        title,
        style: GoogleFonts.nunitoSans(
          fontSize: 16,
          fontWeight: FontWeight.w600,
          color: AppColors.onSurface,
        ),
      ),
      subtitle: Text(
        subtitle,
        style: GoogleFonts.nunitoSans(
          fontSize: 14,
          color: AppColors.onSurfaceVariant,
        ),
      ),
      trailing: Switch.adaptive(
        value: value,
        onChanged: onChanged,
        activeColor: AppColors.primaryGreen,
      ),
    );
  }

  Widget _buildDropdownTile({
    required IconData icon,
    required String title,
    required String value,
    required List<String> options,
    required ValueChanged<String?> onChanged,
  }) {
    return ListTile(
      leading: Container(
        padding: const EdgeInsets.all(8),
        decoration: BoxDecoration(
          color: AppColors.primaryGreen.withOpacity(0.1),
          borderRadius: BorderRadius.circular(10),
        ),
        child: Icon(
          icon,
          color: AppColors.primaryGreen,
          size: 20,
        ),
      ),
      title: Text(
        title,
        style: GoogleFonts.nunitoSans(
          fontSize: 16,
          fontWeight: FontWeight.w600,
          color: AppColors.onSurface,
        ),
      ),
      subtitle: Text(
        value,
        style: GoogleFonts.nunitoSans(
          fontSize: 14,
          color: AppColors.onSurfaceVariant,
        ),
      ),
      trailing: DropdownButton<String>(
        value: value,
        underline: Container(),
        icon: const Icon(
          FeatherIcons.chevronDown,
          size: 16,
        ),
        items: options.map((String option) {
          return DropdownMenuItem<String>(
            value: option,
            child: Text(
              option,
              style: GoogleFonts.nunitoSans(fontSize: 14),
            ),
          );
        }).toList(),
        onChanged: onChanged,
      ),
    );
  }

  void _showLogoutDialog() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        backgroundColor: AppColors.surface,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: Text(
          'Keluar',
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
            onPressed: () {
              Navigator.pop(context);
              // Implement logout logic
              Navigator.pushNamedAndRemoveUntil(context, '/', (route) => false);
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
