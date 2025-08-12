import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_feather_icons/flutter_feather_icons.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../theme/app_theme.dart';
import '../widgets/custom_bottom_nav_bar.dart';
import '../services/auth_service.dart';
import 'settings_page.dart';
import 'order_history_page.dart';

class ProfilePage extends StatelessWidget {
  const ProfilePage({super.key});

  @override
  Widget build(BuildContext context) {
    return Consumer<AuthService>(
      builder: (context, authService, child) {
        final user = authService.currentUser;
        
        return Scaffold(
          backgroundColor: AppColors.background,
          appBar: AppBar(
            automaticallyImplyLeading: false,
            backgroundColor: AppColors.surface,
            elevation: 0,
            scrolledUnderElevation: 1,
            title: Text(
              'Profile',
              style: GoogleFonts.nunitoSans(
                color: AppColors.primaryGreen,
                fontSize: 24,
                fontWeight: FontWeight.w700,
              ),
            ),
            actions: [
              IconButton(
                icon: const Icon(FeatherIcons.settings, color: AppColors.primaryGreen),
                onPressed: () {
                  HapticFeedback.lightImpact();
                  Navigator.push(
                    context,
                    MaterialPageRoute(builder: (context) => const SettingsPage()),
                  );
                },
              ),
            ],
          ),
          body: SingleChildScrollView(
            child: Column(
              children: [
                // Profile Header
                Container(
                  margin: const EdgeInsets.all(20),
                  padding: const EdgeInsets.all(20),
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
                  child: Row(
                    children: [
                      Stack(
                        children: [
                          CircleAvatar(
                            radius: 35,
                            backgroundColor: AppColors.primaryGreen,
                            child: user?.name != null 
                              ? Text(
                                  user!.name.substring(0, 1).toUpperCase(),
                                  style: GoogleFonts.nunitoSans(
                                    fontSize: 24,
                                    fontWeight: FontWeight.bold,
                                    color: AppColors.onPrimary,
                                  ),
                                )
                              : const Icon(Icons.person, color: AppColors.onPrimary, size: 35),
                          ),
                          Positioned(
                            bottom: 0,
                            right: 0,
                            child: Container(
                              padding: const EdgeInsets.all(4),
                              decoration: BoxDecoration(
                                color: AppColors.success,
                                borderRadius: BorderRadius.circular(20),
                                border: Border.all(color: AppColors.surface, width: 2),
                              ),
                              child: const Icon(
                                FeatherIcons.check,
                                color: AppColors.onPrimary,
                                size: 12,
                              ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(width: 16),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              user?.name ?? 'Guest User',
                              style: GoogleFonts.nunitoSans(
                                fontSize: 20,
                                fontWeight: FontWeight.w700,
                                color: AppColors.onSurface,
                              ),
                            ),
                            const SizedBox(height: 4),
                            Text(
                              user?.email ?? 'Not logged in',
                              style: GoogleFonts.nunitoSans(
                                fontSize: 14,
                                color: AppColors.onSurfaceVariant,
                              ),
                            ),
                            const SizedBox(height: 8),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                              decoration: BoxDecoration(
                                color: AppColors.success.withOpacity(0.1),
                                borderRadius: BorderRadius.circular(20),
                              ),
                              child: Text(
                                user != null ? 'Verified Member' : 'Guest',
                                style: GoogleFonts.nunitoSans(
                                  fontSize: 12,
                                  fontWeight: FontWeight.w600,
                                  color: AppColors.success,
                                ),
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),

                // Stats Section
                Container(
                  margin: const EdgeInsets.symmetric(horizontal: 20, vertical: 10),
                  child: Row(
                    children: [
                      Expanded(child: _buildStatCard('12', 'Orders', FeatherIcons.package)),
                      const SizedBox(width: 12),
                      Expanded(child: _buildStatCard('5', 'Favorites', FeatherIcons.heart)),
                      const SizedBox(width: 12),
                      Expanded(child: _buildStatCard('4.8', 'Rating', FeatherIcons.star)),
                    ],
                  ),
                ),
                            color: AppColors.onSurface,
                          ),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          "john.mobbin@outlook.com",
                          style: GoogleFonts.nunitoSans(
                            color: AppColors.onSurfaceVariant,
                            fontSize: 14,
                            fontWeight: FontWeight.w500,
                          ),
                        ),
                        const SizedBox(height: 8),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                          decoration: BoxDecoration(
                            color: AppColors.primaryGreen.withOpacity(0.1),
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: Text(
                            'Member Premium',
                            style: GoogleFonts.nunitoSans(
                              fontSize: 12,
                              fontWeight: FontWeight.w600,
                              color: AppColors.primaryGreen,
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                  IconButton(
                    icon: const Icon(FeatherIcons.edit3, color: AppColors.primaryGreen),
                    onPressed: () {
                      HapticFeedback.lightImpact();
                      // Navigate to edit profile
                    },
                  ),
                ],
              ),
            ),

            // Quick Stats
            Container(
              margin: const EdgeInsets.symmetric(horizontal: 20),
              padding: const EdgeInsets.all(16),
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
              child: Row(
                children: [
                  _buildStatItem('24', 'Pesanan', FeatherIcons.shoppingBag),
                  _buildStatDivider(),
                  _buildStatItem('12', 'Favorit', FeatherIcons.heart),
                  _buildStatDivider(),
                  _buildStatItem('856', 'Poin', FeatherIcons.star),
                ],
              ),
            ),

            const SizedBox(height: 20),

            // Menu Items
            _buildMenuSection(context, 'Pesanan', [
              _buildMenuItem(
                icon: FeatherIcons.package,
                title: 'Riwayat Pesanan',
                subtitle: 'Lihat semua pesanan Anda',
                onTap: () {
                  HapticFeedback.lightImpact();
                  Navigator.push(
                    context,
                    MaterialPageRoute(builder: (context) => const OrderHistoryPage()),
                  );
                },
              ),
              _buildMenuItem(
                icon: FeatherIcons.clock,
                title: 'Pesanan Pending',
                subtitle: 'Pesanan yang belum selesai',
                badge: '2',
                onTap: () {
                  HapticFeedback.lightImpact();
                },
              ),
            ]),

            _buildMenuSection(context, 'Akun', [
              _buildMenuItem(
                icon: FeatherIcons.mapPin,
                title: 'Alamat',
                subtitle: 'Kelola alamat pengiriman',
                onTap: () {
                  HapticFeedback.lightImpact();
                },
              ),
              _buildMenuItem(
                icon: FeatherIcons.creditCard,
                title: 'Metode Pembayaran',
                subtitle: 'Kartu kredit, e-wallet, dll',
                onTap: () {
                  HapticFeedback.lightImpact();
                },
              ),
            ]),

            _buildMenuSection(context, 'Bantuan', [
              _buildMenuItem(
                icon: FeatherIcons.helpCircle,
                title: 'Pusat Bantuan',
                subtitle: 'FAQ dan panduan',
                onTap: () {
                  HapticFeedback.lightImpact();
                },
              ),
              _buildMenuItem(
                icon: FeatherIcons.messageCircle,
                title: 'Hubungi Kami',
                subtitle: 'Chat dengan customer service',
                onTap: () {
                  HapticFeedback.lightImpact();
                },
              ),
            ]),

            const SizedBox(height: 100), // Space for bottom navigation
          ],
        ),
      ),
      bottomNavigationBar: CustomBottomNavBar(
        currentIndex: 3,
        onTap: (index) {
          if (index != 3) {
            String route = '/home';
            if (index == 1) route = '/favorites';
            if (index == 2) route = '/cart';
            Navigator.pushReplacementNamed(context, route);
          }
          HapticFeedback.lightImpact();
        },
      ),
    );
  }

  Widget _buildStatItem(String value, String label, IconData icon) {
    return Expanded(
      child: Column(
        children: [
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: AppColors.primaryGreen.withOpacity(0.1),
              borderRadius: BorderRadius.circular(12),
            ),
            child: Icon(icon, color: AppColors.primaryGreen, size: 24),
          ),
          const SizedBox(height: 8),
          Text(
            value,
            style: GoogleFonts.nunitoSans(
              fontSize: 18,
              fontWeight: FontWeight.w700,
              color: AppColors.onSurface,
            ),
          ),
          Text(
            label,
            style: GoogleFonts.nunitoSans(
              fontSize: 12,
              color: AppColors.onSurfaceVariant,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildStatDivider() {
    return Container(
      width: 1,
      height: 40,
      color: AppColors.outline.withOpacity(0.3),
      margin: const EdgeInsets.symmetric(horizontal: 16),
    );
  }

  Widget _buildMenuSection(BuildContext context, String title, List<Widget> items) {
    return Column(
      children: [
        Container(
          width: double.infinity,
          padding: const EdgeInsets.fromLTRB(20, 8, 20, 8),
          child: Text(
            title,
            style: GoogleFonts.nunitoSans(
              fontSize: 14,
              fontWeight: FontWeight.w600,
              color: AppColors.onSurfaceVariant,
              letterSpacing: 0.5,
            ),
          ),
        ),
        Container(
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
          child: Column(children: items),
        ),
        const SizedBox(height: 12),
      ],
    );
  }

  Widget _buildMenuItem({
    required IconData icon,
    required String title,
    required String subtitle,
    String? badge,
    required VoidCallback onTap,
  }) {
    return ListTile(
      leading: Container(
        padding: const EdgeInsets.all(10),
        decoration: BoxDecoration(
          color: AppColors.primaryGreen.withOpacity(0.1),
          borderRadius: BorderRadius.circular(12),
        ),
        child: Icon(icon, color: AppColors.primaryGreen, size: 20),
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
      trailing: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          if (badge != null) ...[
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
              decoration: BoxDecoration(
                color: AppColors.error,
                borderRadius: BorderRadius.circular(10),
              ),
              child: Text(
                badge,
                style: GoogleFonts.nunitoSans(
                  fontSize: 12,
                  fontWeight: FontWeight.w600,
                  color: Colors.white,
                ),
              ),
            ),
            const SizedBox(width: 8),
          ],
          const Icon(
            FeatherIcons.chevronRight,
            color: AppColors.onSurfaceVariant,
            size: 20,
          ),
        ],
      ),
      onTap: onTap,
    );
  }
}
