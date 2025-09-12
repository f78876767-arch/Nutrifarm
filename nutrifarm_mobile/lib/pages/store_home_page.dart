import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:provider/provider.dart';
import '../widgets/product_card.dart';
import '../data/product_data.dart';
import '../models/product.dart';
import '../theme/app_theme.dart';
import '../pages/product_detail_page.dart';
import '../pages/search_page.dart';
import '../pages/cart_page.dart';
import '../pages/categories_page.dart';
import '../pages/notifications_page.dart';
import '../utils/page_transitions.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:flutter_feather_icons/flutter_feather_icons.dart';
import 'package:smooth_page_indicator/smooth_page_indicator.dart';
import 'dart:async';
import 'dart:ui' show lerpDouble;
import '../services/cart_service.dart';
import '../services/favorites_service_api.dart';
import '../services/api_service.dart';
import '../services/notification_service.dart';

class StoreHomePage extends StatefulWidget {
  const StoreHomePage({super.key});

  @override
  _StoreHomePageState createState() => _StoreHomePageState();
}

class _StoreHomePageState extends State<StoreHomePage> with SingleTickerProviderStateMixin {
  // Fields
  final List<String> _bannerImages = [
    'assets/images/banner-1.png',
    'assets/images/banner-2.png',
  ];
  int _currentBanner = 0;
  final PageController _pageController = PageController();
  Timer? _bannerTimer;
  String selectedCategory = 'All';
  final ScrollController _scrollController = ScrollController();
  List<Product> _products = [];
  List<Product> _discounted = [];
  List<Product> _regular = [];
  List<String> _categories = ['All'];
  bool _isLoading = true;
  String? _errorMessage;
  final GlobalKey _cartIconKey = GlobalKey();
  OverlayEntry? _flightEntry;
  final Map<String, GlobalKey> _productKeys = {};
  DateTime? _lastFetch;

  GlobalKey _getProductKey(String identifier) {
    if (!_productKeys.containsKey(identifier)) {
      _productKeys[identifier] = GlobalKey(debugLabel: identifier);
    }
    return _productKeys[identifier]!;
  }

  @override
  void initState() {
    super.initState();
    _loadProducts();
    _scrollController.addListener(() => setState(() {}));
    _bannerTimer = Timer.periodic(const Duration(seconds: 5), (timer) {
      if (!mounted) return;
      int nextPage = (_currentBanner + 1) % _bannerImages.length;
      _pageController.animateToPage(
        nextPage,
        duration: const Duration(milliseconds: 400),
        curve: Curves.easeInOut,
      );
    });
  }

  Future<void> _loadProducts() async {
    try {
      setState(() { _isLoading = true; _errorMessage = null; });
      final segmentedDiscounted = await ProductData.getDiscountedSegmented();
      final segmentedRegular = await ProductData.getRegularSegmented();
      final categories = await ProductData.getCategories();
      if (!mounted) return;
      setState(() {
        _discounted = segmentedDiscounted;
        _regular = segmentedRegular;
        _products = [..._discounted, ..._regular];
        _categories = categories;
        _isLoading = false;
        _lastFetch = DateTime.now();
      });
      // Enrich visible products with full details (to populate variants for price ranges)
      unawaited(_enrichProductsWithVariants(limit: 24));
    } catch (e) {
      if (!mounted) return;
      setState(() { _isLoading = false; _errorMessage = 'Failed to load products: ${e.toString()}'; });
    }
  }

  Future<void> _enrichProductsWithVariants({int limit = 24}) async {
    try {
      // Take first [limit] products to enrich
      final targets = _products.take(limit).where((p) => p.variants.isEmpty).toList();
      if (targets.isEmpty) return;

      // Fetch details concurrently but with simple throttling to avoid spamming backend
      final futures = <Future<Product>>[];
      for (final p in targets) {
        futures.add(ApiService.getProduct(p.id));
      }
      final enrichedList = await Future.wait(futures);
      if (!mounted) return;

      // Map by id for quick lookup
      final byId = {for (final p in enrichedList) p.id: p};

      setState(() {
        _discounted = _discounted.map((p) => byId[p.id] ?? p).toList();
        _regular = _regular.map((p) => byId[p.id] ?? p).toList();
        _products = [..._discounted, ..._regular];
      });
    } catch (e) {
      // Silent fail; keep initial lists
      debugPrint('Variant enrichment failed: $e');
    }
  }

  @override
  void dispose() {
    _bannerTimer?.cancel();
    _pageController.dispose();
    _scrollController.dispose();
    try { if (_flightEntry != null && _flightEntry!.mounted) { _flightEntry!.remove(); } } catch (_) {}
    _flightEntry = null;
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final scheme = Theme.of(context).colorScheme;
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final filteredProducts = selectedCategory == 'All'
        ? _products
        : _products.where((p) => p.categories.any((c) => c.name.toLowerCase() == selectedCategory.toLowerCase())).toList();

    return Scaffold(
      backgroundColor: Theme.of(context).scaffoldBackgroundColor,
      body: _isLoading
          ? _buildLoadingState()
          : _errorMessage != null
              ? _buildErrorState()
              : RefreshIndicator(
                  onRefresh: () async {
                    ProductData.clearCache();
                    await _loadProducts();
                    await Provider.of<CartService>(context, listen: false).loadCart();
                    await Provider.of<NotificationService>(context, listen: false).refreshCount();
                  },
                  child: CustomScrollView(
                    controller: _scrollController,
                    slivers: [
                      SliverToBoxAdapter(
                        child: SizedBox(
                          height: 0,
                          child: const SizedBox.shrink(),
                        ),
                      ),

                      // AppBar
                      SliverAppBar(
                        pinned: true,
                        backgroundColor: Theme.of(context).appBarTheme.backgroundColor,
                        elevation: 0,
                        centerTitle: false,
                        automaticallyImplyLeading: false,
                        title: SafeArea(
                          bottom: false,
                          child: Container(
                            padding: const EdgeInsets.symmetric(horizontal: 0, vertical: 0),
                            child: Container(
                              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                              decoration: BoxDecoration(
                                color: scheme.surface,
                                borderRadius: BorderRadius.circular(12),
                                boxShadow: isDark
                                    ? []
                                    : [
                                        BoxShadow(
                                          color: AppColors.primaryGreen.withOpacity(0.08),
                                          blurRadius: 6,
                                          offset: const Offset(0, 2),
                                        ),
                                      ],
                              ),
                              child: Row(
                                children: [
                                  Icon(FeatherIcons.search, color: isDark ? Colors.white70 : Colors.black87, size: 18),
                                  const SizedBox(width: 8),
                                  Expanded(
                                    child: GestureDetector(
                                      onTap: () => Navigator.push(
                                        context,
                                        PageTransitions.fadeWithScale(const SearchPage()),
                                      ),
                                      child: Text(
                                        'Search for products...',
                                        style: TextStyle(
                                          color: isDark ? Colors.white70 : Colors.black87,
                                          fontSize: 13,
                                          fontWeight: FontWeight.w400,
                                        ),
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ),
                        ),
                        actions: [
                          Consumer<CartService>(
                            builder: (context, cartService, _) => Stack(
                              children: [
                                IconButton(
                                  key: _cartIconKey,
                                  icon: Icon(FeatherIcons.shoppingBag, color: isDark ? Colors.white : Colors.black),
                                  onPressed: () => Navigator.push(
                                    context,
                                    PageTransitions.slideFromRight(const CartPage()),
                                  ),
                                ),
                                if (cartService.totalQuantity > 0)
                                  Positioned(
                                    right: 8,
                                    top: 8,
                                    child: AnimatedSwitcher(
                                      duration: const Duration(milliseconds: 250),
                                      transitionBuilder: (c, a) => ScaleTransition(scale: a, child: c),
                                      child: Container(
                                        key: ValueKey(cartService.totalQuantity),
                                        padding: const EdgeInsets.all(2),
                                        decoration: BoxDecoration(color: Colors.red, borderRadius: BorderRadius.circular(10)),
                                        constraints: const BoxConstraints(minWidth: 16, minHeight: 16),
                                        child: Text(
                                          '${cartService.totalQuantity}',
                                          style: GoogleFonts.nunitoSans(
                                            color: Colors.white,
                                            fontSize: 10,
                                            fontWeight: FontWeight.w600,
                                          ),
                                          textAlign: TextAlign.center,
                                        ),
                                      ),
                                    ),
                                  ),
                              ],
                            ),
                          ),
                          Consumer<NotificationService>(
                            builder: (context, notif, _) => Stack(
                              children: [
                                IconButton(
                                  icon: Icon(FeatherIcons.bell, color: isDark ? Colors.white : Colors.black),
                                  onPressed: () async {
                                    await Navigator.push(
                                      context,
                                      PageTransitions.slideFromBottom(const NotificationsPage()),
                                    );
                                    // Refresh count after returning
                                    await notif.refreshCount();
                                  },
                                ),
                                if (notif.unreadCount > 0)
                                  Positioned(
                                    right: 12,
                                    top: 12,
                                    child: Container(
                                      width: 8,
                                      height: 8,
                                      decoration: const BoxDecoration(color: Colors.red, shape: BoxShape.circle),
                                    ),
                                  ),
                              ],
                            ),
                          ),
                          const SizedBox(width: 8),
                        ],
                      ),

                      // Content
                      SliverToBoxAdapter(
                        child: SafeArea(
                          top: false,
                          child: Column(
                            children: [
                              // Banner
                              Padding(
                                padding: const EdgeInsets.symmetric(horizontal: 16),
                                child: SizedBox(
                                  height: 140,
                                  width: double.infinity,
                                  child: Stack(
                                    alignment: Alignment.bottomCenter,
                                    children: [
                                      ClipRRect(
                                        borderRadius: BorderRadius.circular(16),
                                        child: PageView.builder(
                                          controller: _pageController,
                                          itemCount: _bannerImages.length,
                                          onPageChanged: (i) => setState(() => _currentBanner = i),
                                          itemBuilder: (context, i) => Image.asset(
                                            _bannerImages[i],
                                            width: double.infinity,
                                            fit: BoxFit.cover,
                                          ),
                                        ),
                                      ),
                                      Positioned(
                                        bottom: 10,
                                        left: 0,
                                        right: 0,
                                        child: Center(
                                          child: SmoothPageIndicator(
                                            controller: _pageController,
                                            count: _bannerImages.length,
                                            effect: ExpandingDotsEffect(
                                              dotHeight: 8,
                                              dotWidth: 8,
                                              spacing: 6,
                                              expansionFactor: 3,
                                              dotColor: isDark ? Colors.white.withOpacity(0.3) : Colors.white.withOpacity(0.7),
                                              activeDotColor: AppColors.primaryGreen,
                                            ),
                                          ),
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                              ),
                              const SizedBox(height: 24),

                              // Discount Section
                              if (_discounted.isNotEmpty) ...[
                                Padding(
                                  padding: const EdgeInsets.symmetric(horizontal: 16),
                                  child: Row(
                                    children: [
                                      Text('Lagi diskon nih!', style: GoogleFonts.nunitoSans(fontWeight: FontWeight.w700, fontSize: 18, color: scheme.onSurface, letterSpacing: 1.2)),
                                      const SizedBox(width: 8),
                                      Container(
                                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                                        decoration: BoxDecoration(color: AppColors.primaryGreen.withOpacity(0.08), borderRadius: BorderRadius.circular(8)),
                                        child: Text('Limited time', style: GoogleFonts.nunitoSans(fontWeight: FontWeight.w600, fontSize: 12, color: AppColors.primaryGreen)),
                                      ),
                                      const Spacer(),
                                      GestureDetector(
                                        onTap: () => Navigator.push(context, PageTransitions.zoomTransition(const CategoriesPage())),
                                        child: Text('See All', style: GoogleFonts.nunitoSans(fontWeight: FontWeight.w600, fontSize: 14, color: AppColors.primaryGreen)),
                                      ),
                                    ],
                                  ),
                                ),
                                const SizedBox(height: 12),
                                SizedBox(
                                  height: 260, // increased to avoid bottom overflow on content-rich cards
                                  child: ListView.separated(
                                    scrollDirection: Axis.horizontal,
                                    padding: const EdgeInsets.only(left: 16),
                                    itemCount: _discounted.length > 8 ? 8 : _discounted.length,
                                    separatorBuilder: (_, __) => const SizedBox(width: 12),
                                    itemBuilder: (context, index) => _buildSaleCard(_discounted[index], index),
                                  ),
                                ),
                                const SizedBox(height: 24),
                              ],

                              // Categories
                              Padding(
                                padding: const EdgeInsets.symmetric(horizontal: 16),
                                child: Row(
                                  children: [
                                    Text('Categories', style: GoogleFonts.nunitoSans(fontWeight: FontWeight.w700, fontSize: 18, color: scheme.onSurface)),
                                    const Spacer(),
                                  ],
                                ),
                              ),
                              const SizedBox(height: 12),
                              SizedBox(
                                height: 50,
                                child: ListView(
                                  scrollDirection: Axis.horizontal,
                                  padding: const EdgeInsets.only(left: 16),
                                  children: _categories.map((category) {
                                    final isSelected = selectedCategory == category;
                                    return Container(
                                      margin: const EdgeInsets.only(right: 12),
                                      child: FilterChip(
                                        selected: isSelected,
                                        label: Text(
                                          category,
                                          style: GoogleFonts.nunitoSans(
                                            fontWeight: FontWeight.w700,
                                            color: isSelected ? AppColors.onPrimary : scheme.onSurface,
                                          ),
                                        ),
                                        onSelected: (_) => setState(() => selectedCategory = category),
                                        backgroundColor: scheme.surfaceVariant,
                                        selectedColor: AppColors.primaryGreen,
                                        checkmarkColor: Colors.white,
                                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(25)),
                                        elevation: isSelected ? 4 : 0,
                                        shadowColor: isSelected ? AppColors.primaryGreen.withOpacity(0.3) : null,
                                      ),
                                    );
                                  }).toList(),
                                ),
                              ),
                              const SizedBox(height: 20),

                              // Products Grid Title
                              Padding(
                                padding: const EdgeInsets.symmetric(horizontal: 16),
                                child: Row(
                                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                  children: [
                                    Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        Text('Fresh Products', style: GoogleFonts.nunitoSans(fontWeight: FontWeight.w700, fontSize: 18, color: scheme.onSurface)),
                                        Text(
                                          selectedCategory == 'All' ? 'All fresh items' : selectedCategory,
                                          style: GoogleFonts.nunitoSans(fontWeight: FontWeight.w500, fontSize: 14, color: scheme.onSurfaceVariant),
                                        ),
                                      ],
                                    ),
                                    Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                                      decoration: BoxDecoration(
                                        gradient: LinearGradient(colors: [
                                          AppColors.primaryGreen.withOpacity(0.1),
                                          AppColors.accentGreen.withOpacity(0.1),
                                        ]),
                                        borderRadius: BorderRadius.circular(20),
                                      ),
                                      child: Row(
                                        mainAxisSize: MainAxisSize.min,
                                        children: [
                                          Container(
                                            width: 8,
                                            height: 8,
                                            decoration: BoxDecoration(color: AppColors.success, borderRadius: BorderRadius.circular(4)),
                                          ),
                                          const SizedBox(width: 6),
                                          Text(
                                            '${filteredProducts.length} items',
                                            style: GoogleFonts.nunitoSans(
                                              color: Theme.of(context).brightness == Brightness.dark ? Colors.white : AppColors.primaryGreen,
                                              fontWeight: FontWeight.w600,
                                              fontSize: 13,
                                            ),
                                          ),
                                        ],
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                              const SizedBox(height: 8),
                              Padding(
                                padding: const EdgeInsets.symmetric(horizontal: 16),
                                child: GridView.builder(
                                  shrinkWrap: true,
                                  physics: const NeverScrollableScrollPhysics(),
                                  gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                                    crossAxisCount: 2,
                                    childAspectRatio: 0.65,
                                    crossAxisSpacing: 16,
                                    mainAxisSpacing: 16,
                                  ),
                                  itemCount: filteredProducts.length,
                                  itemBuilder: (context, index) {
                                    final product = filteredProducts[index];
                                    final keyId = 'grid_product_${product.id}_$index';
                                    final productKey = _getProductKey(keyId);
                                    return ProductCard(
                                      product: product,
                                      imageKey: productKey,
                                      handleAddToCartInternally: false, // Let us handle variant selection
                                      onAddToCart: () async {
                                        // Check if product has variants, if so show selection modal
                                        Product productWithVariants = product;
                                        if (product.variants.isEmpty) {
                                          try {
                                            print('🔄 Fetching full product details for ${product.name} (ID: ${product.id})');
                                            productWithVariants = await ApiService.getProduct(product.id);
                                            print('✅ Full product loaded with ${productWithVariants.variants.length} variants');
                                          } catch (e) {
                                            print('❌ Failed to load full product details: $e');
                                            productWithVariants = product;
                                          }
                                        }
                                        
                                        if (productWithVariants.variants.isNotEmpty) {
                                          _showVariantSelectionModal(productWithVariants);
                                        } else {
                                          // No variants, add directly to cart
                                          final cartService = Provider.of<CartService>(context, listen: false);
                                          await cartService.addToCart(product, quantity: 1);
                                          _startFlight(productKey, product);
                                          _showAddedToCartSnackBar(product.name, null);
                                        }
                                      },
                                      onTap: () => Navigator.push(
                                        context,
                                        PageTransitions.elasticTransition(ProductDetailPage(product: product)),
                                      ),
                                    );
                                  },
                                ),
                              ),
                              const SizedBox(height: 24),
                              if (_lastFetch != null)
                                Padding(
                                  padding: const EdgeInsets.only(left: 16, bottom: 8),
                                  child: Align(
                                    alignment: Alignment.centerLeft,
                                    child: Text(
                                      'Update ${DateTime.now().difference(_lastFetch!).inMinutes}m lalu',
                                      style: GoogleFonts.nunitoSans(fontSize: 11, color: AppColors.outline),
                                    ),
                                  ),
                                ),
                            ],
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
    );
  }

  Widget _buildSaleCard(Product product, int index) {
    final discountPercent = product.hasDiscount
        ? (((product.price - (product.discountPrice ?? product.price)) / product.price) * 100).round()
        : null;
    final keyId = 'sale_product_${product.id}_$index';
    final saleKey = _getProductKey(keyId);

    return GestureDetector(
      onTap: () {
        Navigator.push(context, PageTransitions.flipTransition(ProductDetailPage(product: product)));
        HapticFeedback.lightImpact();
      },
      child: Container(
        width: 165,
        decoration: BoxDecoration(
          color: Theme.of(context).colorScheme.surface,
          borderRadius: BorderRadius.circular(20),
          boxShadow: [
            if (Theme.of(context).brightness != Brightness.dark)
              BoxShadow(color: Colors.black.withOpacity(0.08), blurRadius: 20, offset: const Offset(0, 8))
          ],
          border: Border.all(color: AppColors.primaryGreen.withOpacity(0.1), width: 1.5),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Stack(
              children: [
                // Image / thumbnail area refined
                Container(
                  key: saleKey,
                  height: 100, // back to original balance
                  width: double.infinity,
                  decoration: BoxDecoration(
                    gradient: LinearGradient(
                      begin: Alignment.topLeft,
                      end: Alignment.bottomRight,
                      colors: [
                        _getProductColor(product.imageUrl),
                        _getProductColor(product.imageUrl).withOpacity(0.7),
                      ],
                    ),
                    borderRadius: const BorderRadius.only(topLeft: Radius.circular(20), topRight: Radius.circular(20)),
                  ),
                  child: ClipRRect(
                    borderRadius: const BorderRadius.only(topLeft: Radius.circular(20), topRight: Radius.circular(20)),
                    child: product.imageUrl.isNotEmpty
                        ? Padding(
                            padding: const EdgeInsets.all(6),
                            child: Image.network(
                              product.imageUrl,
                              fit: BoxFit.contain,
                              errorBuilder: (c, e, s) => Center(
                                child: Icon(_getProductIcon(product.imageUrl), size: 40, color: Theme.of(context).brightness == Brightness.dark ? Colors.white70 : Colors.black87),
                              ),
                            ),
                          )
                        : Center(
                            child: Icon(_getProductIcon(product.imageUrl), size: 40, color: Theme.of(context).brightness == Brightness.dark ? Colors.white70 : Colors.black87),
                          ),
                  ),
                ),
                if (discountPercent != null)
                  Positioned(
                    top: 8,
                    left: 8,
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                      decoration: BoxDecoration(
                        gradient: const LinearGradient(colors: [Color(0xFFE53935), Color(0xFFFF5252)]),
                        borderRadius: BorderRadius.circular(12),
                        boxShadow: [
                          BoxShadow(
                            color: const Color(0xFFE53935).withOpacity(0.35),
                            blurRadius: 8,
                            offset: const Offset(0, 2),
                          ),
                        ],
                      ),
                      child: Text('$discountPercent%', style: GoogleFonts.nunitoSans(color: Colors.white, fontWeight: FontWeight.w700, fontSize: 10, letterSpacing: 0.5)),
                    ),
                  ),
                Positioned(
                  top: 8,
                  right: 8,
                  child: Consumer<FavoritesServiceApi>(
                    builder: (context, favService, child) {
                      final isFav = favService.isFavorite(product.id);
                      return GestureDetector(
                        onTap: () {
                          HapticFeedback.lightImpact();
                          favService.toggleFavorite(product.id, product: product);
                        },
                        child: Container(
                          padding: const EdgeInsets.all(6),
                          decoration: BoxDecoration(
                            color: Colors.white.withOpacity(0.9), 
                            borderRadius: BorderRadius.circular(20)
                          ),
                          child: Icon(
                            isFav ? Icons.favorite : Icons.favorite_border, 
                            color: isFav ? Colors.red : AppColors.primaryGreen, 
                            size: 16
                          ),
                        ),
                      );
                    },
                  ),
                ),
                // Aesthetic overlays: variant range/category chip and rating pill
                if (_variantRange(product) != null)
                  Positioned(
                    bottom: 8,
                    left: 8,
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                      decoration: BoxDecoration(
                        color: Colors.white.withOpacity(0.9),
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: Text(
                        _variantRange(product)!,
                        style: GoogleFonts.nunitoSans(fontSize: 10, fontWeight: FontWeight.w600, color: Theme.of(context).colorScheme.onSurface),
                      ),
                    ),
                  )
                else if (product.categories.isNotEmpty)
                  Positioned(
                    bottom: 8,
                    left: 8,
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                      decoration: BoxDecoration(
                        color: Colors.white.withOpacity(0.9),
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: Text(
                        product.categories.first.name,
                        style: GoogleFonts.nunitoSans(fontSize: 10, fontWeight: FontWeight.w600, color: Theme.of(context).colorScheme.onSurface),
                      ),
                    ),
                  ),
                Positioned(
                  bottom: 8,
                  right: 8,
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 3),
                    decoration: BoxDecoration(
                      color: Theme.of(context).brightness == Brightness.dark ? Colors.black.withOpacity(0.6) : Colors.black.withOpacity(0.6),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        const Icon(Icons.star, size: 10, color: Colors.amber),
                        const SizedBox(width: 3),
                        Text(
                          product.rating.toStringAsFixed(1),
                          style: GoogleFonts.nunitoSans(color: Colors.white, fontSize: 10, fontWeight: FontWeight.w700),
                        ),
                      ],
                    ),
                  ),
                ),
              ],
            ),
            Expanded(
              child: Padding(
                padding: const EdgeInsets.all(12),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    SizedBox(
                      height: 32,
                      child: Text(product.name, maxLines: 2, overflow: TextOverflow.ellipsis, style: GoogleFonts.nunitoSans(fontWeight: FontWeight.w700, fontSize: 13, height: 1.2, color: Theme.of(context).colorScheme.onSurface)),
                    ),
                    const SizedBox(height: 8),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              // Use variant price range if available
                              Text(
                                product.variants.isNotEmpty ? product.displayPrice : _formatCurrency(product.discountPrice ?? product.price),
                                style: GoogleFonts.nunitoSans(fontWeight: FontWeight.w800, fontSize: 15, color: AppColors.primaryGreen),
                              ),
                              if (product.hasDiscount && product.variants.isEmpty)
                                Text(_formatCurrency(product.price), style: GoogleFonts.nunitoSans(fontWeight: FontWeight.w600, fontSize: 11, color: Theme.of(context).brightness == Brightness.dark ? Colors.white60 : Colors.grey, decoration: TextDecoration.lineThrough)),
                            ],
                          ),
                        ),
                        Consumer<CartService>(
                          builder: (context, cartService, child) => GestureDetector(
                            onTap: () async {
                              HapticFeedback.mediumImpact();
                              
                              // Debug: Check if product has variants
                              print('🔍 Product ${product.name} has ${product.variants.length} variants');
                              
                              // If product doesn't have variants loaded, try to fetch full product details
                              Product productWithVariants = product;
                              if (product.variants.isEmpty) {
                                try {
                                  print('🔄 Fetching full product details for ${product.name} (ID: ${product.id})');
                                  productWithVariants = await ApiService.getProduct(product.id);
                                  print('✅ Full product loaded with ${productWithVariants.variants.length} variants');
                                } catch (e) {
                                  print('❌ Failed to load full product details: $e');
                                  // Fall back to original product
                                  productWithVariants = product;
                                }
                              }
                              
                              if (productWithVariants.variants.isNotEmpty) {
                                for (var variant in productWithVariants.variants) {
                                  print('   - Variant: ${variant.name} (${variant.value}${variant.unit})');
                                }
                              }
                              
                              // If product has variants, show variant selection modal
                              if (productWithVariants.variants.isNotEmpty) {
                                _showVariantSelectionModal(productWithVariants);
                              } else {
                                // No variants, add directly to cart
                                await cartService.addToCart(product, quantity: 1);
                                _startFlight(saleKey, product);
                                _showAddedToCartSnackBar(product.name, null);
                              }
                            },
                            child: Container(
                              padding: const EdgeInsets.all(8),
                              decoration: BoxDecoration(
                                gradient: LinearGradient(colors: [AppColors.primaryGreen, AppColors.primaryGreen.withOpacity(0.8)]),
                                borderRadius: BorderRadius.circular(12),
                                boxShadow: [
                                  BoxShadow(color: AppColors.primaryGreen.withOpacity(0.3), blurRadius: 6, offset: const Offset(0, 2)),
                                ],
                              ),
                              child: const Icon(Icons.add, color: Colors.white, size: 16),
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 8),
                    Container(
                      width: double.infinity,
                      height: 26,
                      decoration: BoxDecoration(
                        color: AppColors.primaryGreen.withOpacity(0.1),
                        borderRadius: BorderRadius.circular(8),
                        border: Border.all(color: AppColors.primaryGreen.withOpacity(0.3), width: 1),
                      ),
                      child: Center(
                        child: Row(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Icon(Icons.local_fire_department, color: AppColors.red, size: 14),
                            const SizedBox(width: 4),
                            Text('${_getQuantitySold(index)} sold', style: GoogleFonts.nunitoSans(color: AppColors.primaryGreen, fontWeight: FontWeight.w700, fontSize: 10)),
                          ],
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  // Build a friendly variant range text for overlay chip
  String? _variantRange(Product product) {
    if (product.variants.isEmpty) return null;
    try {
      final values = product.variants.map((v) => v.value).toList()..sort();
      final unit = product.variants.first.unit;
      if (values.isEmpty) return null;
      final minVal = values.first;
      final maxVal = values.last;
      if (minVal == maxVal) {
        return '${minVal}${unit}';
      }
      return '${minVal} - ${maxVal}${unit}';
    } catch (_) {
      return null;
    }
  }

  String _formatCurrency(double value) {
    final intVal = value.round();
    final str = intVal.toString();
    final buffer = StringBuffer();
    for (int i = 0; i < str.length; i++) {
      final reverseIndex = str.length - 1 - i;
      buffer.write(str[i]);
      if (reverseIndex % 3 == 0 && i != str.length - 1) buffer.write('.');
    }
    return 'Rp$buffer';
  }

  Widget _buildLoadingState() {
    return Scaffold(
      backgroundColor: AppColors.background,
      body: const Center(
        child: CircularProgressIndicator(color: AppColors.primaryGreen),
      ),
    );
  }

  Widget _buildErrorState() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.error_outline, size: 64, color: Colors.grey[400]),
          const SizedBox(height: 16),
          Text('Unable to load products', style: GoogleFonts.nunitoSans(fontSize: 18, fontWeight: FontWeight.w600, color: Colors.grey[600])),
          const SizedBox(height: 8),
          Text(_errorMessage ?? 'Please check your internet connection', style: GoogleFonts.nunitoSans(fontSize: 14, color: Colors.grey[500]), textAlign: TextAlign.center),
          const SizedBox(height: 24),
          ElevatedButton.icon(
            onPressed: _loadProducts,
            icon: const Icon(Icons.refresh),
            label: const Text('Try Again'),
            style: ElevatedButton.styleFrom(backgroundColor: AppColors.primaryGreen, foregroundColor: Colors.white, padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12)),
          ),
          const SizedBox(height: 16),
          Text('Note: Make sure your backend server is running', style: GoogleFonts.nunitoSans(fontSize: 12, color: Colors.orange[600], fontStyle: FontStyle.italic), textAlign: TextAlign.center),
        ],
      ),
    );
  }

  void _startFlight(GlobalKey imageKey, Product product) {
    final overlay = Overlay.of(context);
    final imageContext = imageKey.currentContext;
    final cartContext = _cartIconKey.currentContext;
    if (imageContext == null || cartContext == null) return;
    final imageBox = imageContext.findRenderObject() as RenderBox;
    final cartBox = cartContext.findRenderObject() as RenderBox;
    final start = imageBox.localToGlobal(Offset.zero);
    final end = cartBox.localToGlobal(Offset.zero);
    final size = imageBox.size;
    try { if (_flightEntry != null && _flightEntry!.mounted) { _flightEntry!.remove(); } } catch (_) {}
    _flightEntry = null;
    late OverlayEntry entry;
    entry = OverlayEntry(
      builder: (_) => _FlightWidget(
        start: start,
        end: end,
        size: size,
        product: product,
        onDone: () {
          try { if (entry.mounted) entry.remove(); } catch (_) {}
        },
      ),
    );
    _flightEntry = entry;
    overlay.insert(entry);
  }

  // Helper methods (restored)
  Color _getProductColor(String imageUrl) {
    switch (imageUrl) {
      case 'headphones': return const Color(0xFFFFF3E0);
      case 'macbook': return const Color(0xFFF3E5F5);
      case 'chair': return const Color(0xFFE3F2FD);
      case 'smartwatch': return const Color(0xFFE8F5E8);
      case 'tshirt': return const Color(0xFFFCE4EC);
      case 'yoga': return const Color(0xFFE0F2F1);
      default: return const Color(0xFFF5F5F5);
    }
  }
  IconData _getProductIcon(String imageUrl) {
    switch (imageUrl) {
      case 'headphones': return Icons.headphones;
      case 'macbook': return Icons.laptop_mac;
      case 'chair': return Icons.chair;
      case 'smartwatch': return Icons.watch;
      case 'tshirt': return Icons.checkroom;
      case 'yoga': return Icons.fitness_center;
      default: return Icons.shopping_bag;
    }
  }
  int _getQuantitySold(int index) { final quantitiesSold = [147,89,203,156,78,245,312,134]; return quantitiesSold[index % quantitiesSold.length]; }

  void _showVariantSelectionModal(Product product) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Theme.of(context).colorScheme.surface,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.only(
          topLeft: Radius.circular(24),
          topRight: Radius.circular(24),
        ),
      ),
      builder: (context) {
        int? selectedIndex;
        final scheme = Theme.of(context).colorScheme;
        final isDark = Theme.of(context).brightness == Brightness.dark;
        return StatefulBuilder(
          builder: (context, setState) {
            return SafeArea(
              top: false,
              child: Container(
                constraints: BoxConstraints(
                  maxHeight: MediaQuery.of(context).size.height * 0.75,
                ),
                decoration: BoxDecoration(
                  color: scheme.surface,
                  borderRadius: const BorderRadius.only(
                    topLeft: Radius.circular(24),
                    topRight: Radius.circular(24),
                  ),
                  boxShadow: isDark
                      ? []
                      : const [
                          BoxShadow(color: Color(0x1A000000), blurRadius: 20, offset: Offset(0, -6)),
                        ],
                ),
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const SizedBox(height: 8),
                    Center(
                      child: Container(
                        width: 36,
                        height: 4,
                        decoration: BoxDecoration(
                          color: isDark ? Colors.white24 : Colors.black12,
                          borderRadius: BorderRadius.circular(4),
                        ),
                      ),
                    ),
                    // Header
                    Padding(
                      padding: const EdgeInsets.fromLTRB(20, 16, 12, 16),
                      child: Row(
                        crossAxisAlignment: CrossAxisAlignment.center,
                        children: [
                          Container(
                            width: 40,
                            height: 40,
                            decoration: BoxDecoration(
                              color: AppColors.primaryGreen.withOpacity(0.08),
                              borderRadius: BorderRadius.circular(12),
                              border: Border.all(color: AppColors.primaryGreen.withOpacity(0.15)),
                            ),
                            child: const Icon(Icons.tune, color: AppColors.primaryGreen, size: 22),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text('Pilih Varian', style: GoogleFonts.nunitoSans(fontSize: 18, fontWeight: FontWeight.w800, color: scheme.onSurface)),
                                const SizedBox(height: 2),
                                Text(product.name, maxLines: 1, overflow: TextOverflow.ellipsis, style: GoogleFonts.nunitoSans(fontSize: 13, fontWeight: FontWeight.w600, color: scheme.onSurfaceVariant)),
                              ],
                            ),
                          ),
                          IconButton(
                            onPressed: () => Navigator.pop(context),
                            icon: Icon(Icons.close, color: scheme.onSurfaceVariant),
                          ),
                        ],
                      ),
                    ),
                    const Divider(height: 1),
                    // Variants list
                    SizedBox(
                      height: 320,
                      child: ListView.separated(
                        padding: const EdgeInsets.fromLTRB(16, 12, 16, 12),
                        itemCount: product.variants.length,
                        separatorBuilder: (_, __) => const SizedBox(height: 10),
                        itemBuilder: (context, index) {
                          final v = product.variants[index];
                          final selected = selectedIndex == index;
                          return InkWell(
                            borderRadius: BorderRadius.circular(16),
                            onTap: () => setState(() => selectedIndex = index),
                            child: Container(
                              padding: const EdgeInsets.all(14),
                              decoration: BoxDecoration(
                                color: selected ? AppColors.primaryGreen.withOpacity(0.06) : scheme.surface,
                                borderRadius: BorderRadius.circular(16),
                                border: Border.all(color: selected ? AppColors.primaryGreen : const Color(0xFFEAEAEA), width: selected ? 1.5 : 1),
                                boxShadow: [
                                  if (!isDark)
                                    BoxShadow(color: Colors.black.withOpacity(0.04), blurRadius: 10, offset: const Offset(0, 3))
                                ],
                              ),
                              child: Row(
                                children: [
                                  Container(
                                    width: 44,
                                    height: 44,
                                    decoration: BoxDecoration(
                                      color: AppColors.primaryGreen.withOpacity(0.08),
                                      borderRadius: BorderRadius.circular(12),
                                    ),
                                    child: const Icon(Icons.inventory_2, color: AppColors.primaryGreen, size: 22),
                                  ),
                                  const SizedBox(width: 12),
                                  Expanded(
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        Row(
                                          children: [
                                            Expanded(
                                              child: Text(v.name, maxLines: 1, overflow: TextOverflow.ellipsis, style: GoogleFonts.nunitoSans(fontSize: 15, fontWeight: FontWeight.w700, color: scheme.onSurface)),
                                            ),
                                            Text(_formatCurrency(v.effectivePrice), style: GoogleFonts.nunitoSans(fontSize: 14, fontWeight: FontWeight.w800, color: AppColors.primaryGreen)),
                                          ],
                                        ),
                                        const SizedBox(height: 4),
                                        Text('${v.value}${v.unit}', style: GoogleFonts.nunitoSans(fontSize: 12, color: scheme.onSurfaceVariant, fontWeight: FontWeight.w600)),
                                      ],
                                    ),
                                  ),
                                  const SizedBox(width: 12),
                                  Radio<int>(
                                    value: index,
                                    groupValue: selectedIndex,
                                    onChanged: (val) => setState(() => selectedIndex = val),
                                    activeColor: AppColors.primaryGreen,
                                  ),
                                ],
                              ),
                            ),
                          );
                        },
                      ),
                    ),
                    const Divider(height: 1),
                    // Actions
                    Padding(
                      padding: const EdgeInsets.fromLTRB(16, 12, 16, 20),
                      child: Row(
                        children: [
                          Expanded(
                            child: OutlinedButton(
                              onPressed: () => Navigator.pop(context),
                              style: OutlinedButton.styleFrom(
                                foregroundColor: scheme.onSurface,
                                side: BorderSide(color: Theme.of(context).dividerColor.withOpacity(0.2)),
                                padding: const EdgeInsets.symmetric(vertical: 14),
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                              ),
                              child: Text('Batal', style: GoogleFonts.nunitoSans(fontWeight: FontWeight.w700)),
                            ),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: ElevatedButton(
                              onPressed: selectedIndex == null
                                  ? null
                                  : () {
                                      Navigator.pop(context);
                                      _addVariantToCart(product, product.variants[selectedIndex!]);
                                    },
                              style: ElevatedButton.styleFrom(
                                backgroundColor: AppColors.primaryGreen,
                                foregroundColor: Colors.white,
                                padding: const EdgeInsets.symmetric(vertical: 14),
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                elevation: 0,
                              ),
                              child: Text('Tambah', style: GoogleFonts.nunitoSans(fontWeight: FontWeight.w800)),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
            );
          },
        );
      },
    );
  }

  Future<void> _addVariantToCart(Product product, Variant variant) async {
    try {
      final cartService = Provider.of<CartService>(context, listen: false);
      await cartService.addToCart(product, quantity: 1, variant: variant);
      _showAddedToCartSnackBar(product.name, variant.name);
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Gagal menambahkan ke keranjang: $e'),
          backgroundColor: Colors.red,
        ),
      );
    }
  }

  void _showAddedToCartSnackBar(String productName, String? variantName) {
    final variantText = variantName != null ? ' (${variantName})' : '';
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text('${productName}${variantText} berhasil ditambahkan ke keranjang'),
        backgroundColor: Colors.green,
        duration: const Duration(seconds: 2),
        action: SnackBarAction(
          label: 'Lihat',
          textColor: Colors.white,
          onPressed: () {
            Navigator.pushNamed(context, '/cart');
          },
        ),
      ),
    );
  }
}

class _FlightWidget extends StatefulWidget {
  final Offset start; 
  final Offset end; 
  final Size size; 
  final Product product; 
  final VoidCallback onDone;
  
  const _FlightWidget({
    required this.start, 
    required this.end, 
    required this.size, 
    required this.product, 
    required this.onDone
  });
  
  @override State<_FlightWidget> createState() => _FlightWidgetState();
}

class _FlightWidgetState extends State<_FlightWidget> with SingleTickerProviderStateMixin {
  // Helpers moved here for flight animation color/icon reuse
  Color _flightColor(String imageUrl) {
    switch (imageUrl) {
      case 'headphones': return const Color(0xFFFFF3E0);
      case 'macbook': return const Color(0xFFF3E5F5);
      case 'chair': return const Color(0xFFE3F2FD);
      case 'smartwatch': return const Color(0xFFE8F5E8);
      case 'tshirt': return const Color(0xFFFCE4EC);
      case 'yoga': return const Color(0xFFE0F2F1);
      default: return const Color(0xFFF5F5F5);
    }
  }
  IconData _flightIcon(String imageUrl) {
    switch (imageUrl) {
      case 'headphones': return Icons.headphones;
      case 'macbook': return Icons.laptop_mac;
      case 'chair': return Icons.chair;
      case 'smartwatch': return Icons.watch;
      case 'tshirt': return Icons.checkroom;
      case 'yoga': return Icons.fitness_center;
      default: return Icons.shopping_bag;
    }
  }

  late final AnimationController _controller = AnimationController(
    vsync: this, 
    duration: const Duration(milliseconds: 800)
  );
  
  @override
  void initState() {
    super.initState();
    _controller.addStatusListener((status) {
      if (status == AnimationStatus.completed && mounted) {
        widget.onDone();
      }
    });
    _controller.forward();
  }
  
  @override 
  void dispose() {
    _controller.dispose();
    super.dispose();
  }
  
  @override 
  Widget build(BuildContext context) {
    return AnimatedBuilder(
      animation: _controller,
      builder: (context, child) {
        final t = Curves.easeInOutCubic.transform(_controller.value);
        final x = lerpDouble(widget.start.dx, widget.end.dx, t)!;
        final midY = (widget.start.dy < widget.end.dy ? widget.start.dy : widget.end.dy) - 120;
        final y = _bezierCurve(widget.start.dy, midY, widget.end.dy, t);
        final scale = lerpDouble(1.0, 0.3, t)!;
        final opacity = 1.0 - (t * 0.3);
        
        return Positioned(
          left: x - (widget.size.width * scale / 2),
          top: y - (widget.size.height * scale / 2),
          child: Transform.scale(
            scale: scale,
            child: Opacity(
              opacity: opacity,
              child: Container(
                width: widget.size.width,
                height: widget.size.height,
                decoration: BoxDecoration(
                  gradient: LinearGradient(
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                    colors: [
                      _flightColor(widget.product.imageUrl),
                      _flightColor(widget.product.imageUrl).withOpacity(0.7),
                    ],
                  ),
                  borderRadius: BorderRadius.circular(16),
                ),
                child: Center(
                  child: Icon(_flightIcon(widget.product.imageUrl), size: 28, color: Colors.black87),
                ),
              ),
            ),
          ),
        );
      },
    );
  }
  
  double _bezierCurve(double p0, double p1, double p2, double t) {
    return (1 - t) * (1 - t) * p0 + 2 * (1 - t) * t * p1 + t * t * p2;
  }
}
