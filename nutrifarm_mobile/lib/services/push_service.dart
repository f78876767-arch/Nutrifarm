import 'package:flutter/foundation.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'notification_service.dart';

class PushService {
  static final FlutterLocalNotificationsPlugin _local = FlutterLocalNotificationsPlugin();
  static bool _initialized = false;

  static Future<void> initialize() async {
    if (_initialized) return;
    await _initLocal();
    await _initFirebase();
    _initialized = true;
  }

  static Future<void> _initLocal() async {
    const androidInit = AndroidInitializationSettings('@mipmap/ic_launcher');
    const iosInit = DarwinInitializationSettings();
    const init = InitializationSettings(android: androidInit, iOS: iosInit);
    await _local.initialize(init,
        onDidReceiveNotificationResponse: _onSelectLocalNotification);
  }

  static Future<void> _initFirebase() async {
    try {
      await Firebase.initializeApp();
    } catch (e) {
      if (kDebugMode) print('Firebase init error (maybe already inited): $e');
    }

    final messaging = FirebaseMessaging.instance;

    // iOS permission
    await messaging.requestPermission(alert: true, badge: true, sound: true);

    // Get token and register
    final token = await messaging.getToken();
    if (kDebugMode) print('FCM token: $token');
    if (token != null) {
      await NotificationService().registerDeviceToken(token);
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString('fcm_token', token);
    }

    // Handle refresh
    FirebaseMessaging.instance.onTokenRefresh.listen((newToken) async {
      await NotificationService().registerDeviceToken(newToken);
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString('fcm_token', newToken);
    });

    // Foreground messages
    FirebaseMessaging.onMessage.listen((RemoteMessage message) async {
      final notification = message.notification;
      if (notification != null) {
        await _showLocal(notification.title, notification.body);
      }
    });

    // Tap on background/terminated
    FirebaseMessaging.onMessageOpenedApp.listen((RemoteMessage message) async {
      // If you need deep link handling, integrate with DeepLinkService or NavigationService here
    });
  }

  static Future<void> logoutCleanup() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final token = prefs.getString('fcm_token');
      if (token != null) {
        await NotificationService().unregisterDeviceToken(token);
      }
      await prefs.remove('fcm_token');
    } catch (_) {}
  }

  static Future<void> _showLocal(String? title, String? body) async {
    const android = AndroidNotificationDetails(
      'nutrifarm_channel',
      'Nutrifarm Notifications',
      channelDescription: 'Nutrifarm push',
      importance: Importance.max,
      priority: Priority.high,
    );
    const details = NotificationDetails(android: android, iOS: DarwinNotificationDetails());
    await _local.show(0, title ?? 'Nutrifarm', body ?? '', details);
  }

  static void _onSelectLocalNotification(NotificationResponse response) {
    // Handle local tap if needed (navigate based on payload)
  }
}
