import 'dart:async';
import 'package:uni_links/uni_links.dart';
import 'package:flutter/services.dart';
import 'package:flutter/foundation.dart';
import 'navigation_service.dart';

class DeepLinkService {
  static StreamSubscription? _sub;

  static Future<void> initialize() async {
    // Handle initial link
    try {
      final initial = await getInitialUri();
      if (initial != null) {
        _handleUri(initial);
      }
    } on PlatformException {
      // ignore
    }

    // Listen for incoming links
    _sub?.cancel();
    _sub = uriLinkStream.listen((Uri? uri) {
      if (uri != null) {
        _handleUri(uri);
      }
    }, onError: (e) {
      if (kDebugMode) {
        print('DeepLink error: $e');
      }
    });
  }

  static void _handleUri(Uri uri) {
    if (kDebugMode) print('DeepLink received: $uri');
    final externalId = uri.queryParameters['external_id'];
    final reason = uri.queryParameters['reason'];

    if (uri.scheme == 'nutrifarm') {
      if (uri.host == 'orders') {
        NavigationService.navigateAndReplaceAll('/orders', arguments: {
          'external_id': externalId,
        });
      } else if (uri.host == 'cart') {
        NavigationService.navigateAndReplaceAll('/cart');
        if (reason != null && reason.isNotEmpty) {
          NavigationService.showSnack(reason);
        }
      }
    }
  }

  static Future<void> dispose() async {
    await _sub?.cancel();
    _sub = null;
  }
}
