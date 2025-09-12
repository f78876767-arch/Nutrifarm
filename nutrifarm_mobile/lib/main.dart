import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:intl/intl.dart';
import 'package:intl/date_symbol_data_local.dart';
import 'theme/app_theme.dart';
import 'widgets/main_navigator.dart';
import 'pages/loading_screen.dart';
import 'pages/register_page.dart';
import 'pages/login_page.dart';
import 'pages/verify_code_page.dart';
import 'pages/onboarding_screen.dart';
import 'pages/checkout_result_page.dart';
import 'services/cart_service.dart';
import 'services/favorites_service_api.dart';
import 'services/auth_service.dart';
import 'services/user_service.dart';
import 'services/order_service.dart';
import 'services/search_service.dart';
import 'services/address_service.dart';
import 'services/checkout_service.dart';
import 'data/product_data.dart';
import 'services/navigation_service.dart';
import 'services/deep_link_service.dart';
import 'services/notification_service.dart';
import 'services/push_service.dart';
import 'services/settings_service.dart';

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();
  // Initialize Indonesian locale for intl date/number formatting
  try {
    await initializeDateFormatting('id_ID', null);
    Intl.defaultLocale = 'id_ID';
  } catch (_) {
    // Fallback silently; pages also provide explicit locale where needed
  }
  // Initialize notifications (badge/count) but push token will be registered on login
  await NotificationService().initialize();
  // Load saved theme mode
  await SettingsService.loadThemeMode();
  runApp(const MyApp());
}

class MyApp extends StatefulWidget {
  const MyApp({super.key});

  @override
  State<MyApp> createState() => _MyAppState();
}

class _MyAppState extends State<MyApp> {
  bool _isInitialized = false;
  bool _firstLaunch = false;

  @override
  void initState() {
    super.initState();
    _initializeApp();
  }

  Future<void> _initializeApp() async {
    try {
      print('🚀 Starting app initialization...');
      await AuthService().initialize();
      await FavoritesServiceApi().initialize();
      await UserService().initialize();
      await OrderService().initialize();
      await AddressService().initialize();
      print('📦 Initializing product data...');
      await ProductData.initialize();
      // Deep link init
      await DeepLinkService.initialize();
      // Detect first launch
      final prefs = await SharedPreferences.getInstance();
      _firstLaunch = prefs.getBool('has_seen_onboarding') != true;
      if (_firstLaunch) {
        await prefs.setBool('has_seen_onboarding', true);
      }
      // Init push notifications if enabled previously
      final enabledPush = prefs.getBool('notifications_enabled') ?? false;
      if (enabledPush) {
        await SettingsService.initializeNotifications();
        await PushService.initialize();
      }
      setState(() { _isInitialized = true; });
    } catch (e) {
      print('❌ Failed to initialize app: $e');
      setState(() { _isInitialized = true; });
    }
  }

  @override
  Widget build(BuildContext context) {
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
        ChangeNotifierProvider(create: (_) => AddressService()),
        ChangeNotifierProvider(create: (_) => CheckoutService()),
        ChangeNotifierProvider(create: (_) => NotificationService()),
      ],
      child: Consumer<AuthService>(
        builder: (context, auth, _) {
          // TEMP: Disable login gating while backend is offline
          // If first launch, show onboarding; otherwise go straight to home
          final Widget start = _firstLaunch
              ? const OnboardingScreen()
              : const MainNavigator();
          return ValueListenableBuilder<ThemeMode>(
            valueListenable: SettingsService.themeModeNotifier,
            builder: (context, mode, __) {
              return MaterialApp(
                title: 'Nutrifarm Store',
                debugShowCheckedModeBanner: false,
                theme: AppTheme.lightTheme,
                darkTheme: AppTheme.darkTheme,
                themeMode: mode,
                navigatorKey: NavigationService.navigatorKey,
                home: start,
                routes: {
                  '/login': (context) => const LoginPage(),
                  '/register': (context) => const RegisterPage(),
                  '/home': (context) => const MainNavigator(),
                  '/favorites': (context) => const MainNavigator(initialIndex: 1),
                  '/cart': (context) => const MainNavigator(initialIndex: 2),
                  '/orders': (context) => const MainNavigator(initialIndex: 3),
                  '/profile': (context) => const MainNavigator(initialIndex: 4),
                },
                onGenerateRoute: (settings) {
                  if (settings.name == '/verify') {
                    final args = settings.arguments as Map<String, dynamic>?;
                    final email = args?['email'] as String? ?? '';
                    return MaterialPageRoute(
                      builder: (context) => VerifyCodePage(email: email),
                    );
                  } else if (settings.name == '/checkout-result') {
                    final args = settings.arguments as Map<String, dynamic>?;
                    final success = args?['success'] as bool? ?? false;
                    final transactionId = args?['transaction_id'] as String?;
                    final message = args?['message'] as String?;
                    return MaterialPageRoute(
                      builder: (context) => CheckoutResultPage(
                        success: success,
                        transactionId: transactionId,
                        message: message,
                      ),
                    );
                  }
                  return null;
                },
              );
            },
          );
        },
      ),
    );
  }
}
