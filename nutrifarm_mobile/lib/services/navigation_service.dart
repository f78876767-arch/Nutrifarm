import 'package:flutter/material.dart';
import '../widgets/app_alert.dart';

class NavigationService {
  static final GlobalKey<NavigatorState> navigatorKey = GlobalKey<NavigatorState>();

  static Future<T?>? navigateTo<T extends Object?>(String routeName, {Object? arguments}) {
    return navigatorKey.currentState?.pushNamed<T>(routeName, arguments: arguments);
  }

  static Future<T?>? navigateAndReplaceAll<T extends Object?>(String routeName, {Object? arguments}) {
    return navigatorKey.currentState?.pushNamedAndRemoveUntil<T>(routeName, (route) => false, arguments: arguments);
  }

  static void showSnack(String message) {
    final ctx = navigatorKey.currentContext;
    if (ctx != null) {
      AppAlert.showInfo(ctx, message);
    }
  }
}
