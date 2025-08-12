import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import '../theme/app_theme.dart';
import '../pages/categories_page.dart';
import '../pages/favorites_page.dart';
import '../pages/cart_page.dart';
import '../utils/page_transitions.dart';
import 'package:remixicon/remixicon.dart';

class CustomBottomNavBar extends StatelessWidget {
  final int currentIndex;
  final ValueChanged<int> onTap;

  const CustomBottomNavBar({
    super.key,
    required this.currentIndex,
    required this.onTap,
  });

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
          currentIndex: currentIndex,
          onTap: (index) {
            HapticFeedback.lightImpact();
            if (index == 1) {
              Navigator.push(
                context,
                PageTransitions.rotationFadeTransition(const FavoritesPage()),
              );
            } else if (index == 2) {
              Navigator.push(
                context,
                PageTransitions.slideFromRight(const CartPage()),
              );
            } else {
              onTap(index);
            }
          },
          type: BottomNavigationBarType.fixed,
          backgroundColor: Colors.white,
          selectedItemColor: AppColors.primaryGreen,
          unselectedItemColor: Colors.grey[600],
          selectedLabelStyle: AppTextStyles.labelMedium.copyWith(
            fontWeight: FontWeight.w600,
            fontSize: 12,
          ),
          unselectedLabelStyle: AppTextStyles.labelMedium.copyWith(
            fontWeight: FontWeight.w500,
            fontSize: 11,
          ),
          elevation: 0,
          items: [
            _navItem(Remix.home_4_fill, Remix.home_4_line, 0, 'Home'),
            _navItem(Remix.heart_fill, Remix.heart_line, 1, 'Favorites'),
            _navItem(Remix.shopping_cart_fill, Remix.shopping_cart_line, 2, 'Cart'),
            _navItem(Remix.user_3_fill, Remix.user_3_line, 3, 'Profile'),
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
    return BottomNavigationBarItem(
      icon: Container(
        padding: const EdgeInsets.all(8),
        decoration: BoxDecoration(
          color: Colors.transparent,
          borderRadius: BorderRadius.circular(5),
        ),
        child: Icon(currentIndex == index ? active : inactive, size: 25),
      ),
      label: label,
    );
  }
}
