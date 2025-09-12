import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;
import '../models/app_notification.dart';
import 'api_service.dart';

class NotificationService extends ChangeNotifier {
  static final NotificationService _instance = NotificationService._internal();
  factory NotificationService() => _instance;
  NotificationService._internal();

  static const String _baseUrl = ApiService.baseUrl;

  Map<String, String> get _headersWithAuth => {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    if (ApiService.authToken != null) 'Authorization': 'Bearer ${ApiService.authToken}',
  };

  bool _loading = false;
  bool get loading => _loading;

  int _unreadCount = 0;
  int get unreadCount => _unreadCount;

  final List<AppNotification> _items = [];
  List<AppNotification> get items => List.unmodifiable(_items);

  int _currentPage = 1;
  int _lastPage = 1;
  final int _perPage = 20;
  bool get hasMore => _currentPage < _lastPage;

  Future<void> initialize() async {
    await refreshCount();
  }

  Future<void> refreshCount({bool unreadOnly = true}) async {
    try {
      final uri = Uri.parse('$_baseUrl/notifications/count?unread=${unreadOnly ? 'true' : 'false'}');
      final resp = await http.get(uri, headers: _headersWithAuth);
      if (resp.statusCode >= 200 && resp.statusCode < 300) {
        final data = json.decode(resp.body);
        _unreadCount = data['count'] ?? 0;
        notifyListeners();
      }
    } catch (_) {}
  }

  Future<void> fetchFirstPage({bool unreadOnly = false}) async {
    _items.clear();
    _currentPage = 1;
    _lastPage = 1;
    notifyListeners();
    await fetchNextPage(unreadOnly: unreadOnly);
  }

  Future<void> fetchNextPage({bool unreadOnly = false}) async {
    if (_loading || (!hasMore && _currentPage != 1)) return;
    _loading = true;
    notifyListeners();
    try {
      final uri = Uri.parse('$_baseUrl/notifications?per_page=$_perPage&page=$_currentPage&unread=${unreadOnly ? 'true' : 'false'}');
      final resp = await http.get(uri, headers: _headersWithAuth);
      if (resp.statusCode >= 200 && resp.statusCode < 300) {
        final map = json.decode(resp.body) as Map<String, dynamic>;
        final List list = (map['data'] as List?) ?? [];
        final meta = map['meta'] as Map<String, dynamic>?;
        _lastPage = meta?['last_page'] ?? 1;
        final newItems = list.map((e) => AppNotification.fromJson(Map<String, dynamic>.from(e))).toList();
        _items.addAll(newItems.cast<AppNotification>());
        _currentPage += 1;
        notifyListeners();
      }
    } catch (e) {
      if (kDebugMode) print('Notifications fetch error: $e');
    } finally {
      _loading = false;
      notifyListeners();
    }
  }

  Future<void> markRead(String id) async {
    try {
      final uri = Uri.parse('$_baseUrl/notifications/$id/read');
      final resp = await http.post(uri, headers: _headersWithAuth);
      if (resp.statusCode >= 200 && resp.statusCode < 300) {
        final idx = _items.indexWhere((n) => n.id == id);
        if (idx != -1) {
          _items[idx] = AppNotification(
            id: _items[idx].id,
            type: _items[idx].type,
            title: _items[idx].title,
            body: _items[idx].body,
            data: _items[idx].data,
            createdAt: _items[idx].createdAt,
            readAt: DateTime.now(),
          );
        }
        _unreadCount = (_unreadCount - 1).clamp(0, 1 << 31);
        notifyListeners();
      }
    } catch (_) {}
  }

  Future<void> markAllRead() async {
    try {
      final uri = Uri.parse('$_baseUrl/notifications/read-all');
      final resp = await http.post(uri, headers: _headersWithAuth);
      if (resp.statusCode >= 200 && resp.statusCode < 300) {
        for (var i = 0; i < _items.length; i++) {
          final n = _items[i];
          if (!n.isRead) {
            _items[i] = AppNotification(
              id: n.id,
              type: n.type,
              title: n.title,
              body: n.body,
              data: n.data,
              createdAt: n.createdAt,
              readAt: DateTime.now(),
            );
          }
        }
        _unreadCount = 0;
        notifyListeners();
      }
    } catch (_) {}
  }

  Future<void> delete(String id) async {
    try {
      final uri = Uri.parse('$_baseUrl/notifications/$id');
      final resp = await http.delete(uri, headers: _headersWithAuth);
      if (resp.statusCode >= 200 && resp.statusCode < 300) {
        _items.removeWhere((n) => n.id == id);
        notifyListeners();
      }
    } catch (_) {}
  }

  // Token registration
  Future<void> registerDeviceToken(String token) async {
    try {
      final uri = Uri.parse('$_baseUrl/notifications/register-token');
      await http.post(
        uri,
        headers: _headersWithAuth,
        body: json.encode({'token': token}),
      );
    } catch (_) {}
  }

  Future<void> unregisterDeviceToken(String token) async {
    try {
      final uri = Uri.parse('$_baseUrl/notifications/unregister-token');
      await http.post(
        uri,
        headers: _headersWithAuth,
        body: json.encode({'token': token}),
      );
    } catch (_) {}
  }

  Future<void> sendTest() async {
    try {
      final uri = Uri.parse('$_baseUrl/notifications/test');
      await http.post(uri, headers: _headersWithAuth);
    } catch (_) {}
  }
}
