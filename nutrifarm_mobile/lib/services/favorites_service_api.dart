import 'package:flutter/foundation.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../models/product.dart';
import 'api_service.dart';

class FavoritesServiceApi extends ChangeNotifier {
  static final FavoritesServiceApi _instance = FavoritesServiceApi._internal();
  factory FavoritesServiceApi() => _instance;
  FavoritesServiceApi._internal();

  List<Product> _favoriteProducts = [];
  final Set<int> _favoriteProductIds = {};
  bool _isLoading = false;
  bool _useOfflineMode = false;
  SharedPreferences? _prefs;
  
  List<Product> get favoriteProducts => List.unmodifiable(_favoriteProducts);
  Set<int> get favoriteProductIds => Set.unmodifiable(_favoriteProductIds);
  bool get isLoading => _isLoading;
  int get favoriteCount => _favoriteProducts.length;
  
  // Initialize and load favorites from API
  Future<void> initialize() async {
    _prefs = await SharedPreferences.getInstance();
    
    // Load offline favorites first
    final offlineFavoriteIds = _prefs?.getStringList('offline_favorites') ?? [];
    _favoriteProductIds.addAll(offlineFavoriteIds.map((id) => int.parse(id)));
    notifyListeners();
    
    // Then try to load from API
    await loadFavorites();
  }
  
  // Load favorites from API
  Future<void> loadFavorites() async {
    if (_isLoading) return; // Prevent concurrent loads
    
    _isLoading = true;
    notifyListeners();
    
    try {
      if (ApiService.authToken == null) {
        print('❌ User not authenticated - using offline favorites only');
        _useOfflineMode = true;
        await _loadOfflineFavorites();
        print('📱 Loaded ${_favoriteProductIds.length} favorites from offline storage');
        return;
      }
      
      print('🔄 Loading favorites from server...');
      _favoriteProducts = await ApiService.getFavorites();
      _favoriteProductIds.clear();
      _favoriteProductIds.addAll(_favoriteProducts.map((p) => p.id));
      _useOfflineMode = false;
      print('✅ Server favorites loaded successfully: ${_favoriteProducts.length} items');
      
      // Save to offline storage
      await _saveOfflineFavorites();
    } catch (e) {
      print('❌ Error loading favorites: $e');
      if (e.toString().contains('Unauthenticated')) {
        print('🔑 Authentication required - switching to offline mode');
      }
      _useOfflineMode = true;
      // Don't clear favorites on error, keep existing state
      // Load from offline storage if API fails
      await _loadOfflineFavorites();
      print('📱 Fallback: Loaded ${_favoriteProductIds.length} favorites from offline storage');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }
  
  // Save favorites to offline storage
  Future<void> _saveOfflineFavorites() async {
    final favoriteIds = _favoriteProductIds.map((id) => id.toString()).toList();
    await _prefs?.setStringList('offline_favorites', favoriteIds);
  }
  
  // Load favorites from offline storage
  Future<void> _loadOfflineFavorites() async {
    final offlineFavoriteIds = _prefs?.getStringList('offline_favorites') ?? [];
    _favoriteProductIds.clear();
    _favoriteProductIds.addAll(offlineFavoriteIds.map((id) => int.parse(id)));
  }
  
  // Check if product is favorite
  bool isFavorite(int productId) {
    return _favoriteProductIds.contains(productId);
  }
  
  // Toggle favorite status
  Future<void> toggleFavorite(int productId) async {
    if (_isLoading) return; // Prevent actions during loading
    
    try {
      // Check if user is authenticated before making API calls
      if (ApiService.authToken == null) {
        print('❌ User not authenticated - favorites stored offline only');
        _useOfflineMode = true;
      }
      
      // Optimistically update UI first
      final wasIsFavorite = _favoriteProductIds.contains(productId);
      if (wasIsFavorite) {
        _favoriteProductIds.remove(productId);
        _favoriteProducts.removeWhere((p) => p.id == productId);
      } else {
        _favoriteProductIds.add(productId);
      }
      notifyListeners();
      
      // Save to offline storage immediately
      await _saveOfflineFavorites();
      print('💾 Favorite ${wasIsFavorite ? 'removed from' : 'added to'} offline storage: Product $productId');
      
      // Try to sync with API if not in offline mode and user is authenticated
      if (!_useOfflineMode && ApiService.authToken != null) {
        try {
          print('🔄 Syncing favorite with server...');
          final success = await ApiService.toggleFavorite(productId);
          if (success) {
            print('✅ Server sync successful');
            // Reload to get accurate server state
            await loadFavorites();
          } else {
            throw Exception('API returned false');
          }
        } catch (e) {
          print('❌ API sync failed, staying in offline mode: $e');
          if (e.toString().contains('Unauthenticated')) {
            print('🔑 Authentication required for server sync');
          }
          _useOfflineMode = true;
        }
      }
    } catch (e) {
      print('Error toggling favorite: $e');
      // Don't rethrow to avoid breaking the UI
    }
  }
  
  // Add product to favorites
  Future<void> addToFavorites(int productId) async {
    try {
      final success = await ApiService.addToFavorites(productId);
      if (success) {
        // Reload favorites to get updated state
        await loadFavorites();
      }
    } catch (e) {
      print('Error adding to favorites: $e');
      rethrow;
    }
  }
  
  // Remove product from favorites
  Future<void> removeFromFavorites(int productId) async {
    try {
      // For now, we'll use toggle since we don't have the favorite_id stored
      // In a real implementation, you'd store the favorite_id with each favorite
      final success = await ApiService.toggleFavorite(productId);
      if (success) {
        // Reload favorites to get updated state
        await loadFavorites();
      }
    } catch (e) {
      print('Error removing from favorites: $e');
      rethrow;
    }
  }
  
  // Clear all favorites (remove all from API)
  Future<void> clearFavorites() async {
    try {
      // Remove each favorite one by one
      for (final product in _favoriteProducts) {
        await ApiService.toggleFavorite(product.id);
      }
      // Reload to get updated state
      await loadFavorites();
    } catch (e) {
      print('Error clearing favorites: $e');
      rethrow;
    }
  }
}
