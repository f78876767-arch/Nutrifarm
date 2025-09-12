import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'theme/app_theme.dart';
import 'widgets/main_navigator.dart';
// Removed unused onboarding import for development build
import 'pages/loading_screen.dart';
import 'pages/register_page.dart';
import 'pages/login_page.dart';
import 'pages/verify_code_page.dart';
import 'services/cart_service.dart';
import 'services/favorites_service_api.dart';
import 'services/auth_service.dart';
import 'services/user_service.dart';
import 'services/order_service.dart';
import 'services/search_service.dart';
import 'data/product_data.dart';

void main() {
  runApp(const MyApp());
}

class MyApp extends StatefulWidget {
  const MyApp({super.key});

  @override
  State<MyApp> createState() => _MyAppState();
}

class _MyAppState extends State<MyApp> {
  bool _isInitialized = false;

  @override
  void initState() {
    super.initState();
    _initializeApp();
  }

  Future<void> _initializeApp() async {
    try {
      // Initialize services
      await AuthService().initialize();
      await FavoritesServiceApi().initialize();
      await UserService().initialize();
      await OrderService().initialize();
      
      // Initialize product data from API
      await ProductData.initialize();
      
      setState(() {
        _isInitialized = true;
      });
    } catch (e) {
      // If initialization fails, still show the app
      print('Failed to initialize app: $e');
      setState(() {
        _isInitialized = true;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    // Show loading screen while initializing
    if (!_isInitialized) {
      return MaterialApp(
        title: 'Nutrifarm Store',
        debugShowCheckedModeBanner: false,
        theme: AppTheme.lightTheme,
        home: const LoadingScreen(),
      );
    }

    return MultiProvider(
      providers: [
        ChangeNotifierProvider(create: (_) => AuthService()),
        ChangeNotifierProvider(create: (_) => CartService()),
        ChangeNotifierProvider(create: (_) => FavoritesServiceApi()),
        ChangeNotifierProvider(create: (_) => UserService()),
        ChangeNotifierProvider(create: (_) => OrderService()),
        ChangeNotifierProvider(create: (_) => SearchService()),
      ],
      child: MaterialApp(
        title: 'Nutrifarm Store',
        debugShowCheckedModeBanner: false,
        theme: AppTheme.lightTheme,
        initialRoute: '/home', // DEVELOPMENT: Skip auth, go directly to home
        routes: {
          '/': (context) => const MainNavigator(), // DEVELOPMENT: Direct to home
          '/login': (context) => const LoginPage(),
          '/register': (context) => const RegisterPage(),
          '/home': (context) => const MainNavigator(),
          '/favorites': (context) => const MainNavigator(initialIndex: 1),
          '/cart': (context) => const MainNavigator(initialIndex: 2),
          // Align with profile tab index 4
          '/profile': (context) => const MainNavigator(initialIndex: 4),
        },
        onGenerateRoute: (settings) {
          if (settings.name == '/verify') {
            final args = settings.arguments as Map<String, dynamic>?;
            final email = args?['email'] as String? ?? '';
            return MaterialPageRoute(
              builder: (context) => VerifyCodePage(email: email),
            );
          }
          return null;
        },
      ),
    );
  }
}
