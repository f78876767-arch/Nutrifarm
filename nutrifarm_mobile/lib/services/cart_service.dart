import 'package:flutter/foundation.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'dart:convert';
import 'dart:async';
import '../models/product.dart';
import 'api_service.dart';

class CartItem {
  final String id; // internal id (cart item id if known)
  final Product product;
  final Variant? selectedVariant; // Selected product variant
  int quantity;
  final DateTime addedAt;
  final int? serverCartItemId; // numeric id from backend

  CartItem({
    required this.id,
    required this.product,
    this.selectedVariant,
    this.quantity = 1,
    DateTime? addedAt,
    this.serverCartItemId,
  }) : addedAt = addedAt ?? DateTime.now();

  // Get effective price considering variant
  double get effectivePrice {
    if (selectedVariant != null) {
      return selectedVariant!.effectivePrice;
    }
    return product.discountPrice ?? product.price;
  }

  // Get display name with variant
  String get displayName {
    if (selectedVariant != null) {
      return '${product.name} (${selectedVariant!.displayName})';
    }
    return product.name;
  }

  double get totalPrice => effectivePrice * quantity;
  double get totalOriginalPrice => product.price * quantity;
  double get totalSavings => totalOriginalPrice - totalPrice;

  Map<String, dynamic> toJson() => {
    'id': id,
    'product': product.toJson(),
    'selected_variant': selectedVariant?.toJson(),
    'quantity': quantity,
    'addedAt': addedAt.toIso8601String(),
    'serverCartItemId': serverCartItemId,
  };

  factory CartItem.fromJson(Map<String, dynamic> json) => CartItem(
    id: json['id'],
    product: Product.fromJson(json['product']),
    selectedVariant: json['selected_variant'] != null ? Variant.fromJson(json['selected_variant']) : null,
    quantity: json['quantity'],
    addedAt: DateTime.parse(json['addedAt']),
    serverCartItemId: json['serverCartItemId'],
  );
}

class CartService extends ChangeNotifier {
  static final CartService _instance = CartService._internal();
  factory CartService() => _instance;
  CartService._internal() { _initializeCart(); }

  final List<CartItem> _items = [];
  SharedPreferences? _prefs;
  bool _isLoading = false;
  bool _isUpdating = false; // New state for cart operations
  bool _useOfflineMode = false;
  
  // Debouncing mechanism to prevent API spam
  final Map<int, Timer> _updateTimers = {};
  static const Duration _debounceDelay = Duration(milliseconds: 500); // 500ms delay

  List<CartItem> get items => List.unmodifiable(_items);
  int get itemCount => _items.length;
  int get totalQuantity => _items.fold(0, (sum, item) => sum + item.quantity);
  bool get isLoading => _isLoading;
  bool get isUpdating => _isUpdating; // Expose updating state
  double get subtotal => _items.fold(0.0, (sum, item) => sum + item.totalPrice);
  double get totalSavings => _items.fold(0.0, (sum, item) => sum + item.totalSavings);

  Future<void> _initializeCart() async {
    try {
      _prefs = await SharedPreferences.getInstance();
      await loadCart();
    } catch (e) {
      print('❌ Failed to initialize cart: $e');
    }
  }

  Future<void> loadCart() async {
    if (_isLoading) return;
    _isLoading = true;
    notifyListeners();
    try {
      if (ApiService.authToken != null) {
        print('🔄 Loading cart from server...');
        final cartResponse = await ApiService.getCart();
        print('🔥 CART DEBUG: Raw server response: $cartResponse');
        
        _items.clear();
        // Flexible parsing of various backend formats
        dynamic raw = cartResponse; // could be Map or List
        List<dynamic> cartData = [];
        if (raw is Map<String, dynamic>) {
          print('🔥 CART DEBUG: Response is Map, keys: ${raw.keys}');
          if (raw['data'] is Map<String, dynamic>) {
            final dataMap = raw['data'] as Map<String, dynamic>;
            if (dataMap['items'] is List) {
              cartData = dataMap['items'];
              print('🔥 CART DEBUG: Found data.items array with ${cartData.length} items');
            }
          } else if (raw['data'] is List) {
            cartData = raw['data'];
            print('🔥 CART DEBUG: Found data array with ${cartData.length} items');
          } else if (raw['items'] is List) {
            cartData = raw['items'];
          } else if (raw['cart'] is List) {
            cartData = raw['cart'];
          } else if (raw['cart_items'] is List) {
            cartData = raw['cart_items'];
          } else if (raw['success'] == true && raw['data'] == null) {
            cartData = [];
          }
        } else if (raw is List) {
          cartData = raw;
          print('🔥 CART DEBUG: Response is direct array with ${cartData.length} items');
        }
        if (cartData.isEmpty) {
          print('ℹ️ Server cart empty or unrecognized format: keys=${raw is Map ? raw.keys : 'list length ${(raw as List?)?.length ?? 0}'}');
        }
        for (var item in cartData) {
          try {
            if (item is Map<String, dynamic>) {
              print('🔥 CART DEBUG: Parsing cart item: ${item.keys}');
              final productJson = item['product'] is Map<String, dynamic> ? item['product'] : item;
              print('🔥 CART DEBUG: Product JSON fields: ${productJson.keys}');
              final product = Product.fromJson(productJson);
              print('🔥 CART DEBUG: Parsed product - name: ${product.name}, price: ${product.price}');
              final qty = item['quantity'] ?? item['qty'] ?? 1;
              final serverId = item['id'] ?? item['cart_item_id'];
              print('🔥 CART DEBUG: Found serverId: $serverId (type: ${serverId.runtimeType})');
              
              // Parse variant if exists in server response
              Variant? selectedVariant;
              if (item['variant'] != null && item['variant'] is Map<String, dynamic>) {
                try {
                  selectedVariant = Variant.fromJson(item['variant'] as Map<String, dynamic>);
                  print('🔥 CART DEBUG: Found variant: ${selectedVariant.displayName}');
                } catch (e) {
                  print('⚠️ Error parsing variant: $e');
                }
              } else if (item['variant_id'] != null) {
                // If only variant_id is provided, find variant in product
                final variantId = item['variant_id'] is int ? item['variant_id'] : int.tryParse(item['variant_id'].toString());
                if (variantId != null) {
                  selectedVariant = product.variants.where((v) => v.id == variantId).firstOrNull;
                  if (selectedVariant != null) {
                    print('🔥 CART DEBUG: Found variant by ID: ${selectedVariant.displayName}');
                  }
                }
              }
              
              _items.add(CartItem(
                id: (serverId ?? DateTime.now().microsecondsSinceEpoch).toString(),
                product: product,
                selectedVariant: selectedVariant,
                quantity: qty is int ? qty : int.tryParse(qty.toString()) ?? 1,
                addedAt: DateTime.tryParse(item['created_at']?.toString() ?? '') ?? DateTime.now(),
                serverCartItemId: serverId is int ? serverId : int.tryParse(serverId.toString()),
              ));
              
              print('🔥 CART DEBUG: Added cart item - product: ${product.name}, variant: ${selectedVariant?.displayName ?? 'none'}, serverCartItemId: ${serverId is int ? serverId : int.tryParse(serverId.toString())}');
            }
          } catch (e) {
            print('❌ Error parsing cart item: $e');
          }
        }
        // Enrich products that have price == 0 (minimal payload from backend)
        final needingEnrichment = _items.where((c) => c.product.price == 0).toList();
        print('🔥 CART DEBUG: Checking enrichment - Total items: ${_items.length}, Items needing enrichment: ${needingEnrichment.length}');
        for (final item in _items) {
          print('🔥 CART DEBUG: Item ${item.product.name} - price: ${item.product.price}, variant: ${item.selectedVariant?.displayName ?? 'none'}');
        }
        if (needingEnrichment.isNotEmpty) {
          print('🔍 Enriching ${needingEnrichment.length} cart products missing price...');
          for (final ci in needingEnrichment) {
            try {
              final full = await ApiService.getProduct(ci.product.id);
              final idx = _items.indexOf(ci);
              if (idx >= 0) {
                // IMPORTANT: Preserve serverCartItemId AND selectedVariant when enriching
                _items[idx] = CartItem(
                  id: ci.id,
                  product: full,
                  selectedVariant: ci.selectedVariant, // ⚠️ PRESERVE VARIANT!
                  quantity: ci.quantity,
                  addedAt: ci.addedAt,
                  serverCartItemId: ci.serverCartItemId, // Keep the original server ID!
                );
                print('🔥 CART DEBUG: Enriched ${full.name}, preserved serverCartItemId: ${ci.serverCartItemId}, preserved variant: ${ci.selectedVariant?.displayName ?? 'none'}');
              }
            } catch (e) {
              print('⚠️ Failed to enrich product ${ci.product.id}: $e');
            }
          }
        }
        print('✅ Server cart loaded: ${_items.length} items (subtotal=${subtotal.toStringAsFixed(2)})');
        
        // Debug: Check final serverCartItemIds
        for (final item in _items) {
          print('🔥 CART DEBUG FINAL: ${item.product.name} -> serverCartItemId: ${item.serverCartItemId}');
        }
        
        await _saveOfflineCart();
        _useOfflineMode = false;
      } else {
        print('❌ User not authenticated - using offline cart only');
        _useOfflineMode = true;
        await _loadOfflineCart();
      }
    } catch (e) {
      print('❌ Error loading cart: $e');
      _useOfflineMode = true;
      await _loadOfflineCart();
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> _loadOfflineCart() async {
    final cartJson = _prefs?.getString('offline_cart') ?? '[]';
    try {
      final decoded = json.decode(cartJson);
      final List<dynamic> cartData = decoded is List ? decoded : (decoded is Map<String, dynamic> && decoded['items'] is List ? decoded['items'] : []);
      _items.clear();
      _items.addAll(cartData.map((json) => CartItem.fromJson(json)));
      print('📱 Loaded ${_items.length} items from offline cart');
    } catch (e) {
      print('❌ Error loading offline cart: $e');
      _items.clear();
    }
  }

  Future<void> _saveOfflineCart() async {
    try {
      final cartJson = json.encode(_items.map((item) => item.toJson()).toList());
      await _prefs?.setString('offline_cart', cartJson);
    } catch (e) {
      print('❌ Error saving offline cart: $e');
    }
  }
  
  bool isInCart(int productId, {Variant? variant}) {
    if (variant != null) {
      return _items.any((item) => item.product.id == productId && item.selectedVariant?.id == variant.id);
    }
    return _items.any((item) => item.product.id == productId);
  }
  
  CartItem? getCartItem(int productId, {Variant? variant}) { 
    try { 
      if (variant != null) {
        return _items.firstWhere((item) => item.product.id == productId && item.selectedVariant?.id == variant.id);
      }
      return _items.firstWhere((item) => item.product.id == productId); 
    } catch (_) { 
      return null; 
    } 
  }

  Future<void> addToCart(Product product, {int quantity = 1, Variant? variant}) async {
    print('🛒 addToCart called for product ${product.id} qty=$quantity variant=${variant?.displayName} (stock=${product.stock})');
    print('🔥 CART DEBUG: Variant details - ID: ${variant?.id}, Name: ${variant?.displayName}, Price: ${variant?.effectivePrice}');
    print('🔥 CART DEBUG: Auth token present: ${ApiService.authToken != null}');
    print('🔥 CART DEBUG: Use offline mode: $_useOfflineMode');
    
    final existingIndex = _items.indexWhere((item) => item.product.id == product.id && item.selectedVariant?.id == variant?.id);
    final unlimited = product.stock <= 0; // treat 0/unknown stock as unlimited for now
    if (existingIndex >= 0) {
      print('🔄 Product with variant already in cart, updating quantity');
      final newQuantity = _items[existingIndex].quantity + quantity;
      if (unlimited || newQuantity <= product.stock) {
        await updateQuantityForVariant(product.id, newQuantity, variant: variant);
      } else {
        print('⚠️ Desired quantity exceeds stock (${product.stock})');
      }
    } else {
      if (unlimited || quantity <= product.stock) {
        print('➕ Adding new cart item locally with variant: ${variant?.displayName}');
        final cartItem = CartItem(
          id: DateTime.now().millisecondsSinceEpoch.toString(),
          product: product,
          selectedVariant: variant,
          quantity: quantity,
        );
        _items.add(cartItem);
        print('📦 Cart now has ${_items.length} items (totalQty=$totalQuantity), notifying listeners');
        notifyListeners();
        await _saveOfflineCart();
        if (!_useOfflineMode && ApiService.authToken != null) {
          try {
            print('🔥 CART DEBUG: Calling ApiService.addToCart...');
            final synced = await ApiService.addToCart(
              product.id, 
              quantity: quantity,
              variantId: variant?.id,
            );
            print('🌐 Server sync result: $synced for product ${product.id}');
            if (synced['cart_item_id'] != null) {
              final idx = _items.indexWhere((c) => c.product.id == product.id && c.selectedVariant?.id == variant?.id);
              if (idx >= 0) {
                final existing = _items[idx];
                _items[idx] = CartItem(
                  id: synced['cart_item_id'].toString(),
                  product: existing.product,
                  selectedVariant: existing.selectedVariant,
                  quantity: existing.quantity,
                  addedAt: existing.addedAt,
                  serverCartItemId: synced['cart_item_id'] is int ? synced['cart_item_id'] : int.tryParse(synced['cart_item_id'].toString()),
                );
                await _saveOfflineCart();
                notifyListeners();
                print('🔥 CART DEBUG: Updated cart item with serverCartItemId: ${_items[idx].serverCartItemId}');
              }
            } else {
              print('🔥 CART DEBUG: No cart_item_id in response, forcing reload to get server IDs');
              await loadCart();
            }
          } catch (e) {
            print('❌ Failed server sync addToCart: $e');
          }
        } else {
          print('📴 Offline mode - skip server sync');
        }
      } else {
        print('⚠️ Quantity exceeds stock, not added');
      }
    }
  }
  
  Future<void> removeFromCart(int productId) async {
    final removedItem = _items.where((item) => item.product.id == productId).firstOrNull;
    _items.removeWhere((item) => item.product.id == productId);
    notifyListeners();
    await _saveOfflineCart();
    if (!_useOfflineMode && ApiService.authToken != null && removedItem != null) {
      try {
        final idForServer = removedItem.serverCartItemId;
        if (idForServer != null) {
          await ApiService.removeFromCart(idForServer);
          print('✅ Removed from server cart: ${removedItem.product.name} (cart_item_id=$idForServer)');
          // Don't reload after successful remove - local state already updated
        } else {
          print('⚠️ No serverCartItemId for removal, forcing reload');
          await loadCart();
        }
      } catch (e) { print('❌ Failed to remove from server cart: $e'); }
    }
  }

  Future<void> removeFromCartWithVariant(int productId, {Variant? variant}) async {
    final removedItem = _items.where((item) => item.product.id == productId && item.selectedVariant?.id == variant?.id).firstOrNull;
    _items.removeWhere((item) => item.product.id == productId && item.selectedVariant?.id == variant?.id);
    notifyListeners();
    await _saveOfflineCart();
    if (!_useOfflineMode && ApiService.authToken != null && removedItem != null) {
      try {
        final idForServer = removedItem.serverCartItemId;
        if (idForServer != null) {
          await ApiService.removeFromCart(idForServer);
          print('✅ Removed from server cart: ${removedItem.displayName} (cart_item_id=$idForServer)');
          // Don't reload after successful remove - local state already updated
        } else {
          print('⚠️ No serverCartItemId for removal, forcing reload');
          await loadCart();
        }
      } catch (e) { print('❌ Failed to remove from server cart: $e'); }
    }
  }

  Future<void> updateQuantity(int productId, int newQuantity) async {
    if (newQuantity <= 0) { 
      await removeFromCart(productId); 
      return; 
    }
    
    final itemIndex = _items.indexWhere((item) => item.product.id == productId);
    if (itemIndex >= 0) {
      final cartItem = _items[itemIndex];
      final product = cartItem.product;
      final serverId = cartItem.serverCartItemId;
      
      final unlimited = product.stock <= 0;
      final maxQuantity = unlimited ? 9999 : product.stock;
      final clamped = newQuantity.clamp(1, maxQuantity);
      
      // Update local state immediately for smooth UX
      _items[itemIndex].quantity = clamped;
      notifyListeners();
      await _saveOfflineCart();
      
      // Cancel any existing timer for this product
      _updateTimers[productId]?.cancel();
      
      // Update server with debouncing - only send API after user stops clicking
      if (!_useOfflineMode && ApiService.authToken != null && serverId != null) {
        _updateTimers[productId] = Timer(_debounceDelay, () async {
          _isUpdating = true;
          notifyListeners();
          
          try {
            print('🔄 Debounced API call: updating product $productId to quantity $clamped');
            final ok = await ApiService.updateCartQuantity(serverId, clamped);
            if (ok) {
              print('✅ Cart quantity updated on server (debounced)');
            }
          } catch (e) {
            print('❌ Failed to sync cart to server: $e');
            // Only reload if there's an error to resync
            await loadCart();
          } finally {
            _isUpdating = false;
            _updateTimers.remove(productId);
            notifyListeners();
          }
        });
      } else if (!_useOfflineMode && ApiService.authToken != null && serverId == null) {
        // If no server ID, we need to reload to sync - but also debounce this
        _updateTimers[productId] = Timer(_debounceDelay, () async {
          print('⚠️ Missing server cart ID, reloading to sync (debounced)');
          await loadCart();
          _updateTimers.remove(productId);
        });
      }
    }
  }

  Future<void> updateQuantityForVariant(int productId, int newQuantity, {Variant? variant}) async {
    if (newQuantity <= 0) { 
      await removeFromCartWithVariant(productId, variant: variant); 
      return; 
    }
    
    final itemIndex = _items.indexWhere((item) => item.product.id == productId && item.selectedVariant?.id == variant?.id);
    if (itemIndex >= 0) {
      final cartItem = _items[itemIndex];
      final product = cartItem.product;
      final serverId = cartItem.serverCartItemId;
      
      final unlimited = product.stock <= 0;
      final maxQuantity = unlimited ? 9999 : product.stock;
      final clamped = newQuantity.clamp(1, maxQuantity);
      
      // Update local state immediately for smooth UX
      _items[itemIndex].quantity = clamped;
      notifyListeners();
      await _saveOfflineCart();
      
      // Cancel any existing timer for this product+variant
      _updateTimers[productId]?.cancel();
      
      // Update server with debouncing - only send API after user stops clicking
      if (!_useOfflineMode && ApiService.authToken != null && serverId != null) {
        _updateTimers[productId] = Timer(_debounceDelay, () async {
          _isUpdating = true;
          notifyListeners();
          
          try {
            print('🔄 Debounced API call: updating product $productId variant ${variant?.displayName} to quantity $clamped');
            final ok = await ApiService.updateCartQuantity(serverId, clamped);
            if (ok) {
              print('✅ Cart quantity updated on server (debounced)');
            }
          } catch (e) {
            print('❌ Failed to sync cart to server: $e');
            // Only reload if there's an error to resync
            await loadCart();
          } finally {
            _isUpdating = false;
            _updateTimers.remove(productId);
            notifyListeners();
          }
        });
      } else if (!_useOfflineMode && ApiService.authToken != null && serverId == null) {
        // If no server ID, we need to reload to sync - but also debounce this
        _updateTimers[productId] = Timer(_debounceDelay, () async {
          print('⚠️ Missing server cart ID, reloading to sync (debounced)');
          await loadCart();
          _updateTimers.remove(productId);
        });
      }
    }
  }

  Future<void> clearCart() async {
    _items.clear();
    notifyListeners();
    await _saveOfflineCart();
    // Server clear disabled (no matching endpoint exposed in ApiService now)
  }

  Future<void> increaseQuantity(int productId, {Variant? variant}) async {
    final item = getCartItem(productId, variant: variant);
    if (item != null) {
      final unlimited = item.product.stock <= 0;
      if (unlimited || item.quantity < item.product.stock) {
        if (variant != null) {
          await updateQuantityForVariant(productId, item.quantity + 1, variant: variant);
        } else {
          await updateQuantity(productId, item.quantity + 1);
        }
      } else {
        print('⚠️ Cannot increase quantity: stock limit reached (${item.product.stock})');
      }
    }
  }

  Future<void> decreaseQuantity(int productId, {Variant? variant}) async {
    final item = getCartItem(productId, variant: variant);
    if (item != null) {
      if (variant != null) {
        await updateQuantityForVariant(productId, item.quantity - 1, variant: variant);
      } else {
        await updateQuantity(productId, item.quantity - 1);
      }
    }
  }
  
  // Check if there are pending updates for a specific product
  bool hasPendingUpdate(int productId) {
    return _updateTimers.containsKey(productId);
  }
  
  // Check if there are any pending updates
  bool get hasPendingUpdates => _updateTimers.isNotEmpty;
  
  // Clean up any pending timers when disposing
  @override
  void dispose() {
    _cancelAllTimers();
    super.dispose();
  }
  
  void _cancelAllTimers() {
    for (var timer in _updateTimers.values) {
      timer.cancel();
    }
    _updateTimers.clear();
  }
  
  // Method to force immediate sync (useful for checkout or app backgrounding)
  Future<void> forceSyncPendingUpdates() async {
    if (_updateTimers.isNotEmpty) {
      print('🔄 Force syncing ${_updateTimers.length} pending updates...');
      // Cancel all timers and trigger their callbacks immediately
      final pendingUpdates = Map<int, Timer>.from(_updateTimers);
      _cancelAllTimers();
      
      for (var productId in pendingUpdates.keys) {
        final itemIndex = _items.indexWhere((item) => item.product.id == productId);
        if (itemIndex >= 0) {
          final cartItem = _items[itemIndex];
          final serverId = cartItem.serverCartItemId;
          if (serverId != null && !_useOfflineMode && ApiService.authToken != null) {
            try {
              await ApiService.updateCartQuantity(serverId, cartItem.quantity);
              print('✅ Force synced product $productId');
            } catch (e) {
              print('❌ Failed to force sync product $productId: $e');
            }
          }
        }
      }
    }
  }
}
