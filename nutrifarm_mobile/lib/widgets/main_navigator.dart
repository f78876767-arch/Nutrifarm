import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import '../pages/store_home_page.dart';
import '../pages/favorites_page.dart';
import '../pages/cart_page.dart';
import '../pages/order_history_page.dart';
import '../pages/profile_page_new.dart'; // Use the new themed profile page
import '../widgets/custom_bottom_nav_bar.dart';

class MainNavigator extends StatefulWidget {
  final int initialIndex;
  
  const MainNavigator({super.key, this.initialIndex = 0});

  @override
  _MainNavigatorState createState() => _MainNavigatorState();
}

class _MainNavigatorState extends State<MainNavigator> {
  late int _currentIndex;
  late final PageController _pageController;
  
  @override
  void initState() {
    super.initState();
    _currentIndex = widget.initialIndex;
    _pageController = PageController(initialPage: _currentIndex);
  }

  @override
  void dispose() {
    _pageController.dispose();
    super.dispose();
  }
  
  final List<Widget> _pages = const [
    StoreHomePage(),
    FavoritesPage(),
    CartPage(),
    OrderHistoryPage(),
    ProfilePage(), // now from profile_page_new.dart
  ];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: PageView(
        controller: _pageController,
        physics: const NeverScrollableScrollPhysics(), // lock swipe, nav via bottom bar
        children: _pages,
      ),
      bottomNavigationBar: CustomBottomNavBar(
        currentIndex: _currentIndex,
        onTap: (index) {
          if (index == _currentIndex) {
            HapticFeedback.selectionClick();
            return;
          }
          setState(() {
            _currentIndex = index;
          });
          // Smooth slide animation between tabs
          _pageController.animateToPage(
            index,
            duration: const Duration(milliseconds: 260),
            curve: Curves.easeOutCubic,
          );
          HapticFeedback.lightImpact();
        },
      ),
    );
  }
}
