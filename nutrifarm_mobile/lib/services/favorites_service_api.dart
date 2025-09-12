import 'dart:async';
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
  SharedPreferences? _prefs;
  
  // Debouncing mechanism to prevent spam
  final Map<int, Timer> _debounceTimers = {};
  final Map<int, bool> _pendingToggles = {};
  static const Duration _debounceDuration = Duration(milliseconds: 500);
  
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
        await _loadOfflineFavorites();
        print('📱 Loaded ${_favoriteProductIds.length} favorite IDs and ${_favoriteProducts.length} products from offline storage');
        return;
      }
      
      print('🔄 Loading favorites from server...');
      _favoriteProducts = await ApiService.getFavorites();
      _favoriteProductIds.clear();
      _favoriteProductIds.addAll(_favoriteProducts.map((p) => p.id));
      print('✅ Server favorites loaded successfully: ${_favoriteProducts.length} items');
      
      // Save to offline storage
      await _saveOfflineFavorites();
    } catch (e) {
      print('❌ Error loading favorites: $e');
      if (e.toString().contains('Unauthenticated')) {
        print('🔑 Authentication required - switching to offline mode');
      }
      // Don't clear favorites on error, keep existing state
      // Load from offline storage if API fails
      await _loadOfflineFavorites();
      print('📱 Fallback: Loaded ${_favoriteProductIds.length} favorite IDs and ${_favoriteProducts.length} products from offline storage');
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
    
    // If we have offline favorite IDs but no Product objects, try to fetch them
    if (_favoriteProductIds.isNotEmpty && _favoriteProducts.isEmpty) {
      try {
        print('🔄 Loading product details for ${_favoriteProductIds.length} offline favorites...');
        // Fetch all products and filter to get favorites
        final allProducts = await ApiService.getProducts();
        _favoriteProducts = allProducts.where((product) => _favoriteProductIds.contains(product.id)).toList();
        print('✅ Loaded ${_favoriteProducts.length} favorite product details for offline mode');
      } catch (e) {
        print('❌ Failed to load product details for offline favorites: $e');
        // Keep the IDs but favorites page will show empty
      }
    }
  }
  
  // Check if product is favorite
  bool isFavorite(int productId) {
    return _favoriteProductIds.contains(productId);
  }
  
  // Toggle favorite status with debouncing to prevent spam
  Future<void> toggleFavorite(int productId, {Product? product}) async {
    if (_isLoading) return; // Prevent actions during loading
    
    // Cancel any existing timer for this product
    _debounceTimers[productId]?.cancel();
    
    // Optimistically update UI immediately
    final wasIsFavorite = _favoriteProductIds.contains(productId);
    if (wasIsFavorite) {
      _favoriteProductIds.remove(productId);
      _favoriteProducts.removeWhere((p) => p.id == productId);
      print('💖 Removed from favorites (UI): Product $productId');
    } else {
      _favoriteProductIds.add(productId);
      // Add full product object if provided and not already present
      if (product != null && !_favoriteProducts.any((p) => p.id == productId)) {
        _favoriteProducts.add(product);
      }
      print('💖 Added to favorites (UI): Product $productId');
    }
    notifyListeners();
    
    // Save to offline storage immediately
    await _saveOfflineFavorites();
    print('💾 Favorite ${wasIsFavorite ? 'removed from' : 'added to'} offline storage: Product $productId');
    
    // Store the final desired state
    _pendingToggles[productId] = !wasIsFavorite;
    
    // Set up debounced API call
    _debounceTimers[productId] = Timer(_debounceDuration, () async {
      await _performToggleApiCall(productId);
    });
  }
  
  // Internal method to perform the actual API call after debounce
  Future<void> _performToggleApiCall(int productId) async {
    try {
      // Check if user is authenticated before making API calls
      if (ApiService.authToken == null) {
        print('❌ User not authenticated - favorites stored offline only');
        _pendingToggles.remove(productId);
        return;
      }
      
      // Get the final desired state
      final shouldBeFavorite = _pendingToggles[productId] ?? false;
      final currentlyIsFavorite = _favoriteProductIds.contains(productId);
      
      // Only make API call if the state changed from what we expect
      if (shouldBeFavorite == currentlyIsFavorite) {
        print('🔄 Syncing favorite with server for product $productId...');
        
        try {
          final success = await ApiService.toggleFavorite(productId);
          if (success) {
            print('✅ Server sync successful for product $productId');
            // Note: We don't reload favorites here to avoid overriding user's rapid changes
          } else {
            throw Exception('API returned false');
          }
        } catch (e) {
          print('❌ API sync failed for product $productId: $e');
          if (e.toString().contains('Unauthenticated')) {
            print('🔑 Authentication required for server sync');
          }
          
          // Revert UI state on API failure
          if (shouldBeFavorite) {
            _favoriteProductIds.remove(productId);
            _favoriteProducts.removeWhere((p) => p.id == productId);
          } else {
            _favoriteProductIds.add(productId);
          }
          notifyListeners();
          await _saveOfflineFavorites();
        }
      }
      
      // Clean up
      _pendingToggles.remove(productId);
      _debounceTimers.remove(productId);
      
    } catch (e) {
      print('Error in toggle API call: $e');
      _pendingToggles.remove(productId);
      _debounceTimers.remove(productId);
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
      final success = await ApiService.removeFromFavorites(productId);
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
  
  // Clean up method to cancel all pending timers
  void dispose() {
    for (final timer in _debounceTimers.values) {
      timer.cancel();
    }
    _debounceTimers.clear();
    _pendingToggles.clear();
    super.dispose();
  }
  
  // Method to check if a product has pending toggle action
  bool hasActivePendingToggle(int productId) {
    return _debounceTimers.containsKey(productId) && (_debounceTimers[productId]?.isActive ?? false);
  }
}
