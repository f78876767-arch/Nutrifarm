import 'package:flutter/foundation.dart';
import 'package:shared_preferences/shared_preferences.dart';

class FavoritesService extends ChangeNotifier {
  static final FavoritesService _instance = FavoritesService._internal();
  factory FavoritesService() => _instance;
  FavoritesService._internal();

  final Set<String> _favoriteIds = {};
  SharedPreferences? _prefs;
  
  Set<String> get favoriteIds => Set.unmodifiable(_favoriteIds);
  
  Future<void> initialize() async {
    _prefs = await SharedPreferences.getInstance();
    final savedFavorites = _prefs?.getStringList('favorites') ?? [];
    _favoriteIds.addAll(savedFavorites);
    notifyListeners();
  }
  
  bool isFavorite(String productId) {
    return _favoriteIds.contains(productId);
  }
  
  Future<void> toggleFavorite(String productId) async {
    if (_favoriteIds.contains(productId)) {
      _favoriteIds.remove(productId);
    } else {
      _favoriteIds.add(productId);
    }
    
    // Save to local storage
    await _prefs?.setStringList('favorites', _favoriteIds.toList());
    notifyListeners();
  }
  
  Future<void> addToFavorites(String productId) async {
    if (!_favoriteIds.contains(productId)) {
      _favoriteIds.add(productId);
      await _prefs?.setStringList('favorites', _favoriteIds.toList());
      notifyListeners();
    }
  }
  
  Future<void> removeFromFavorites(String productId) async {
    if (_favoriteIds.contains(productId)) {
      _favoriteIds.remove(productId);
      await _prefs?.setStringList('favorites', _favoriteIds.toList());
      notifyListeners();
    }
  }
  
  Future<void> clearFavorites() async {
    _favoriteIds.clear();
    await _prefs?.setStringList('favorites', []);
    notifyListeners();
  }
  
  int get favoriteCount => _favoriteIds.length;
}
