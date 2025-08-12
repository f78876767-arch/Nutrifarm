import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import '../pages/store_home_page.dart';
import '../pages/favorites_page.dart';
import '../pages/cart_page_new.dart';
import '../pages/profile_page.dart';
import '../widgets/custom_bottom_nav_bar.dart';

class MainNavigator extends StatefulWidget {
  final int initialIndex;
  
  const MainNavigator({super.key, this.initialIndex = 0});

  @override
  _MainNavigatorState createState() => _MainNavigatorState();
}

class _MainNavigatorState extends State<MainNavigator> {
  late int _currentIndex;
  
  @override
  void initState() {
    super.initState();
    _currentIndex = widget.initialIndex;
  }
  
  final List<Widget> _pages = [
    const StoreHomePage(),
    const FavoritesPage(),
    const CartPageNew(),
    const ProfilePage(),
  ];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: IndexedStack(
        index: _currentIndex,
        children: _pages,
      ),
      bottomNavigationBar: CustomBottomNavBar(
        currentIndex: _currentIndex,
        onTap: (index) {
          setState(() {
            _currentIndex = index;
          });
          HapticFeedback.lightImpact();
        },
      ),
    );
  }
}
