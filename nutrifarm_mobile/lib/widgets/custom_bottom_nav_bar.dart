import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import '../theme/app_theme.dart';
// Removed direct page imports; navigation is handled by parent when onTap is provided
import 'package:remixicon/remixicon.dart';

class CustomBottomNavBar extends StatefulWidget {
  final int currentIndex;
  final ValueChanged<int>? onTap;

  const CustomBottomNavBar({
    super.key,
    required this.currentIndex,
    this.onTap,
  });

  @override
  State<CustomBottomNavBar> createState() => _CustomBottomNavBarState();
}

class _CustomBottomNavBarState extends State<CustomBottomNavBar> {
  int? _animatingIndex;

  void _handleTap(BuildContext context, int index) async {
    HapticFeedback.lightImpact();

    setState(() => _animatingIndex = index);
    await Future.delayed(const Duration(milliseconds: 130));
    if (!mounted) return;
    setState(() => _animatingIndex = null);

    // If a parent handler is provided (e.g., MainNavigator), delegate to it for consistent routing/animation
    if (widget.onTap != null) {
      widget.onTap!(index);
      return;
    }

    // Fallback navigation when used standalone without a parent handler
    switch (index) {
      case 0:
        Navigator.pushReplacementNamed(context, '/home');
        break;
      case 1:
        Navigator.pushReplacementNamed(context, '/favorites');
        break;
      case 2:
        Navigator.pushReplacementNamed(context, '/cart');
        break;
      case 3:
        Navigator.pushReplacementNamed(context, '/orders');
        break;
      case 4:
        Navigator.pushReplacementNamed(context, '/profile');
        break;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: const BorderRadius.only(
          topLeft: Radius.circular(10),
          topRight: Radius.circular(10),
        ),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.1),
            blurRadius: 20,
            offset: const Offset(0, -5),
          ),
        ],
      ),
      child: ClipRRect(
        borderRadius: const BorderRadius.only(
          topLeft: Radius.circular(24),
          topRight: Radius.circular(24),
        ),
        child: BottomNavigationBar(
          currentIndex: widget.currentIndex,
          onTap: (index) => _handleTap(context, index),
          type: BottomNavigationBarType.fixed,
          backgroundColor: Colors.white,
          selectedItemColor: AppColors.primaryGreen,
          unselectedItemColor: Colors.grey[600],
          selectedLabelStyle: const TextStyle(
            fontWeight: FontWeight.w600,
            fontSize: 12,
          ),
          unselectedLabelStyle: const TextStyle(
            fontWeight: FontWeight.w500,
            fontSize: 11,
          ),
          elevation: 0,
          items: [
            _navItem(Remix.home_4_fill, Remix.home_4_line, 0, 'Home'),
            _navItem(Remix.heart_fill, Remix.heart_line, 1, 'Favorites'),
            _navItem(Remix.shopping_cart_fill, Remix.shopping_cart_line, 2, 'Cart'),
            _navItem(Remix.file_list_3_fill, Remix.file_list_3_line, 3, 'History'),
            _navItem(Remix.user_3_fill, Remix.user_3_line, 4, 'Profile'),
          ],
        ),
      ),
    );
  }

  BottomNavigationBarItem _navItem(
    IconData active,
    IconData inactive,
    int index,
    String label,
  ) {
    final bool isActive = widget.currentIndex == index || _animatingIndex == index;
    return BottomNavigationBarItem(
      icon: AnimatedScale(
        scale: isActive ? 1.15 : 1.0,
        duration: const Duration(milliseconds: 160),
        curve: Curves.easeOutBack,
        child: Container(
          padding: const EdgeInsets.all(8),
          decoration: BoxDecoration(
            color: Colors.transparent,
            borderRadius: BorderRadius.circular(5),
          ),
          child: Icon(widget.currentIndex == index ? active : inactive, size: 25),
        ),
      ),
      label: label,
    );
  }
}
