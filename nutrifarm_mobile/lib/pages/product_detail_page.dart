import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:provider/provider.dart';
import '../models/product.dart';
import '../theme/app_theme.dart';
import '../data/product_data.dart';
import '../services/cart_service.dart';
import '../services/favorites_service_api.dart';
import 'package:google_fonts/google_fonts.dart';

class ProductDetailPage extends StatefulWidget {
  final Product product;
  const ProductDetailPage({super.key, required this.product});
  @override
  State<ProductDetailPage> createState() => _ProductDetailPageState();
}

class _ProductDetailPageState extends State<ProductDetailPage>
    with TickerProviderStateMixin {
  Product? _product; // refreshed product
  bool _descExpanded = false;
  int _quantity = 1;
  Variant? _selectedVariant;
  late AnimationController _priceAnimationController;
  late Animation<double> _priceAnimation;

  @override
  void initState() {
    super.initState();
    _product = widget.product;
    _loadLatest();
    _preselectVariant();

    _priceAnimationController = AnimationController(
      duration: const Duration(milliseconds: 300),
      vsync: this,
    );
    _priceAnimation = CurvedAnimation(
      parent: _priceAnimationController,
      curve: Curves.easeInOut,
    );
  }

  @override
  void dispose() {
    _priceAnimationController.dispose();
    super.dispose();
  }

  void _preselectVariant() {
    if (_product!.variants.isNotEmpty) {
      // Select first variant (usually smallest size) instead of cheapest
      _selectedVariant = _product!.variants.first;
      print('🔥 VARIANT DEBUG: Preselected variant - ID: ${_selectedVariant?.id}, Name: ${_selectedVariant?.displayName}, Price: ${_selectedVariant?.effectivePrice}');
    } else {
      print('🔥 VARIANT DEBUG: No variants available for product: ${_product!.name}');
    }
  }

  Future<void> _loadLatest() async {
    try {
      final fresh = await ProductData.getProduct(widget.product.id);
      if (fresh != null && mounted) {
        setState(() {
          _product = fresh;
          _preselectVariant();
        });
      }
    } catch (_) {}
  }

  // NEW: Get effective price from selected variant or product
  double get _basePrice =>
      _selectedVariant?.effectivePrice ?? _product!.minPrice;
  bool get _hasDiscount =>
      _selectedVariant?.hasDiscount ?? _product!.hasDiscount;
  double get _discountPercent =>
      _selectedVariant?.discountPercentage ?? (_product!.discount ?? 0);
  double get _total => _basePrice * _quantity;

  String _format(double v) {
    final s = v.round().toString();
    final buf = StringBuffer();
    for (int i = 0; i < s.length; i++) {
      final r = s.length - 1 - i;
      buf.write(s[i]);
      if (r % 3 == 0 && i != s.length - 1) buf.write('.');
    }
    return 'Rp$buf';
  }

  void _selectVariant(Variant variant) {
    print('🔥 VARIANT DEBUG: Selecting variant - ID: ${variant.id}, Name: ${variant.displayName}, Price: ${variant.effectivePrice}');
    setState(() => _selectedVariant = variant);
    _priceAnimationController.forward().then(
      (_) => _priceAnimationController.reverse(),
    );
    HapticFeedback.lightImpact();
  }

  @override
  Widget build(BuildContext context) {
    final product = _product!;
    return Scaffold(
      // Set full white background to remove gray backdrop behind rounded white container
      backgroundColor: Colors.white,
      body: CustomScrollView(
        slivers: [
          SliverAppBar(
            expandedHeight: 420,
            pinned: false,
            floating: false,
            automaticallyImplyLeading: false,
            backgroundColor: Colors.transparent,
            elevation: 0,
            flexibleSpace: FlexibleSpaceBar(
              background: _buildModernHeroImage(context, product),
            ),
          ),
          SliverToBoxAdapter(
            child: Transform.translate(
              offset: const Offset(
                0,
                -26,
              ), // lift card to overlap image slightly
              child: Container(
                decoration: const BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.vertical(top: Radius.circular(42)),
                  // reduce shadow so edge not gray
                  boxShadow: [
                    BoxShadow(
                      color: Color(0x0A000000),
                      blurRadius: 12,
                      offset: Offset(0, 4),
                    ),
                  ],
                ),
                child: Padding(
                  padding: const EdgeInsets.only(
                    top: 34,
                  ), // extra space since lifted
                  child: _buildContentSection(product),
                ),
              ),
            ),
          ),
          const SliverToBoxAdapter(child: SizedBox(height: 120)),
        ],
      ),
      bottomNavigationBar: _buildModernBottomBar(),
    ); // end Scaffold
  }

  Widget _buildContentSection(Product product) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // Product Name & Rating Section
        Padding(
          padding: const EdgeInsets.fromLTRB(24, 0, 24, 0),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                product.name,
                maxLines: 3,
                softWrap: true,
                overflow: TextOverflow.ellipsis,
                style: GoogleFonts.inter(
                  fontSize: 24,
                  fontWeight: FontWeight.w700,
                  color: const Color(0xFF1A1D1F),
                  height: 1.15,
                ),
              ),
              const SizedBox(height: 12),
              Row(
                children: [
                  Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 8,
                      vertical: 4,
                    ),
                    decoration: BoxDecoration(
                      color: const Color(0xFFFFF3CD),
                      borderRadius: BorderRadius.circular(6),
                      border: Border.all(color: const Color(0xFFFFEC8B)),
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        const Icon(
                          Icons.star,
                          color: Color(0xFFFFA726),
                          size: 14,
                        ),
                        const SizedBox(width: 4),
                        Text(
                          '${product.rating}',
                          style: GoogleFonts.inter(
                            fontSize: 12,
                            fontWeight: FontWeight.w600,
                            color: const Color(0xFF8B5A00),
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(width: 12),
                  Text(
                    'Terjual ${_getSalesCount()}+',
                    style: GoogleFonts.inter(
                      fontSize: 14,
                      color: const Color(0xFF6B7280),
                    ),
                  ),
                  const Spacer(),
                  if (_hasDiscount)
                    Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 8,
                        vertical: 4,
                      ),
                      decoration: BoxDecoration(
                        gradient: const LinearGradient(
                          colors: [Color(0xFFFF4757), Color(0xFFFF3838)],
                        ),
                        borderRadius: BorderRadius.circular(6),
                      ),
                      child: Text(
                        'HEMAT ${((_basePrice < (_selectedVariant?.originalPrice ?? product.price) ? ((_selectedVariant?.originalPrice ?? product.price) - _basePrice) : 0) / 1000).round()}K',
                        style: const TextStyle(
                          color: Colors.white,
                          fontSize: 11,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ),
                ],
              ),
            ],
          ),
        ),
        // Price Section
        Padding(
          padding: const EdgeInsets.fromLTRB(24, 20, 24, 0),
          child: AnimatedBuilder(
            animation: _priceAnimation,
            builder: (context, child) => Transform.scale(
              scale: 1 + (_priceAnimation.value * .05),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.end,
                children: [
                  Text(
                    _format(_basePrice),
                    style: GoogleFonts.inter(
                      fontSize: 32,
                      fontWeight: FontWeight.w800,
                      color: const Color(0xFF111827), // black for stronger emphasis
                      height: 1,
                    ),
                  ),
                  if (_hasDiscount) ...[
                    const SizedBox(width: 12),
                    Column(
                      children: [
                        Text(
                          _format(
                            _selectedVariant?.originalPrice ?? product.price,
                          ),
                          style: GoogleFonts.inter(
                            fontSize: 16,
                            color: const Color(0xFF9CA3AF),
                            decoration: TextDecoration.lineThrough,
                            decorationColor: const Color(0xFF9CA3AF),
                          ),
                        ),
                        const SizedBox(height: 4),
                      ],
                    ),
                  ],
                ],
              ),
            ),
          ),
        ),
        // Stock (Tokped/Shopee-like meter)
        Padding(
          padding: const EdgeInsets.fromLTRB(24, 16, 24, 0),
          child: _buildStockStatus(product),
        ),
        // Variants
        if (product.variants.isNotEmpty)
          Padding(
            padding: const EdgeInsets.fromLTRB(24, 24, 24, 0),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Pilih Varian',
                  style: GoogleFonts.inter(
                    fontSize: 18,
                    fontWeight: FontWeight.w700,
                    color: const Color(0xFF1A1D1F),
                  ),
                ),
                const SizedBox(height: 16),
                Wrap(
                  spacing: 12,
                  runSpacing: 12,
                  children: product.variants.map((variant) {
                    final isSelected = _selectedVariant?.id == variant.id;
                    return GestureDetector(
                      onTap: () => _selectVariant(variant),
                      child: AnimatedContainer(
                        duration: const Duration(milliseconds: 220),
                        curve: Curves.easeOutCubic,
                        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                        decoration: BoxDecoration(
                          color: Colors.white, // no green fill, only border
                          borderRadius: BorderRadius.circular(18),
                          border: Border.all(
                            color: isSelected ? AppColors.primaryGreen : const Color(0xFFE5E7EB),
                            width: isSelected ? 2 : 1,
                          ),
                          boxShadow: isSelected
                              ? [
                                  BoxShadow(
                                    color: AppColors.primaryGreen.withOpacity(.22),
                                    blurRadius: 12,
                                    offset: const Offset(0, 6),
                                  ),
                                ]
                              : const [
                                  BoxShadow(
                                    color: Color(0x0A000000),
                                    blurRadius: 6,
                                    offset: Offset(0, 2),
                                  ),
                                ],
                        ),
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            AnimatedContainer(
                              duration: const Duration(milliseconds: 220),
                              width: 18,
                              height: 18,
                              decoration: BoxDecoration(
                                color: isSelected ? AppColors.primaryGreen : Colors.transparent,
                                shape: BoxShape.circle,
                                border: Border.all(color: isSelected ? AppColors.primaryGreen : const Color(0xFFCBD5E1)),
                              ),
                              child: const Icon(Icons.check, size: 12, color: Colors.white),
                            ),
                            const SizedBox(width: 8),
                            Column(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Row(
                                  mainAxisSize: MainAxisSize.min,
                                  children: [
                                    Text(
                                      variant.displayName,
                                      style: GoogleFonts.inter(
                                        fontSize: 14,
                                        fontWeight: FontWeight.w700,
                                        letterSpacing: .2,
                                        color: isSelected ? AppColors.primaryGreen : const Color(0xFF1F2933),
                                      ),
                                    ),
                                  ],
                                ),
                                if (variant.effectivePrice > 0) ...[
                                  const SizedBox(height: 4),
                                  Text(
                                    variant.formattedPrice,
                                    style: GoogleFonts.inter(
                                      fontSize: 12,
                                      fontWeight: FontWeight.w600,
                                      color: isSelected ? AppColors.primaryGreen : const Color(0xFF6B7280),
                                    ),
                                  ),
                                ],
                              ],
                            ),
                          ],
                        ),
                      ),
                    );
                  }).toList(),
                ),
              ],
            ),
          ),
        // Features
        Padding(
          padding: const EdgeInsets.fromLTRB(24, 32, 24, 0),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                'Keunggulan Produk',
                style: GoogleFonts.inter(
                  fontSize: 18,
                  fontWeight: FontWeight.w700,
                  color: const Color(0xFF1A1D1F),
                ),
              ),
              const SizedBox(height: 16),
              _buildFeatureItem(
                Icons.verified,
                'Kualitas Premium',
                'Produk berkualitas tinggi dan terjamin',
              ),
              _buildFeatureItem(
                Icons.local_shipping,
                'Pengiriman Cepat',
                'Dikirim dalam 1-2 hari kerja',
              ),
              _buildFeatureItem(
                Icons.support_agent,
                'Customer Support 24/7',
                'Tim support siap membantu Anda',
              ),
              _buildFeatureItem(
                Icons.assignment_return,
                'Garansi Kepuasan',
                'Jaminan uang kembali 100%',
              ),
            ],
          ),
        ),
        // Description
        Padding(
          padding: const EdgeInsets.fromLTRB(24, 32, 24, 0),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                'Deskripsi Produk',
                style: GoogleFonts.inter(
                  fontSize: 18,
                  fontWeight: FontWeight.w700,
                  color: const Color(0xFF1A1D1F),
                ),
              ),
              const SizedBox(height: 12),
              _buildFormattedDescription(product.description),
            ],
          ),
        ),
      ],
    );
  }

  Widget _buildModernHeroImage(BuildContext context, Product product) {
    final topInset = MediaQuery.of(context).padding.top;
    return Stack(
      fit: StackFit.expand,
      children: [
        Container(
          color: const Color(0xFFF5F6F7),
          child: product.imageUrl.isNotEmpty
              ? Hero(
                  tag: 'product_${product.id}',
                  child: Image.network(
                    product.imageUrl,
                    fit: BoxFit.cover,
                    errorBuilder: (_, __, ___) => _buildHeroPlaceholder(),
                  ),
                )
              : _buildHeroPlaceholder(),
        ),
        Positioned(
          top: 0,
            left: 0,
            right: 0,
            child: Container(
              height: topInset + 110,
              decoration: const BoxDecoration(
                gradient: LinearGradient(
                  begin: Alignment.topCenter,
                  end: Alignment.bottomCenter,
                  colors: [Colors.black54, Colors.transparent],
                ),
              ),
            ),
          ),
        // removed curved overlay for a cleaner overlap; handled by translated content container
        Positioned(
          top: topInset + 12,
          right: 12,
          child: Row(
            children: [
              // Favorite button now reacts to state change
              Builder(
                builder: (context) {
                  final favorites = context.watch<FavoritesServiceApi>();
                  final isFav = favorites.isFavorite(product.id);
                  return AnimatedScale(
                    duration: const Duration(milliseconds: 220),
                    scale: isFav ? 1.08 : 1.0,
                    curve: Curves.easeOutBack,
                    child: _circleBtn(
                      icon: isFav ? Icons.favorite : Icons.favorite_border,
                      onTap: () {
                        HapticFeedback.selectionClick();
                        favorites.toggleFavorite(product.id, product: product);
                      },
                      color: isFav ? Colors.redAccent : Colors.black54,
                    ),
                  );
                },
              ),
              const SizedBox(width: 10),
              _circleBtn(
                icon: Icons.share,
                onTap: () {
                  HapticFeedback.lightImpact();
                },
              ),
            ],
          ),
        ),
        if (_hasDiscount)
          Positioned(
            top: topInset + 60,
            left: 16,
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
              decoration: BoxDecoration(
                gradient: const LinearGradient(
                  colors: [Color(0xFFFF6B35), Color(0xFFFF8A50)],
                ),
                borderRadius: BorderRadius.circular(20),
                boxShadow: [
                  BoxShadow(
                    color: const Color(0xFFFF6B35).withOpacity(0.4),
                    blurRadius: 8,
                    offset: const Offset(0, 3),
                  ),
                ],
              ),
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  const Icon(
                    Icons.local_fire_department,
                    color: Colors.white,
                    size: 16,
                  ),
                  const SizedBox(width: 4),
                  Text(
                    '${_discountPercent.round()}%',
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 12,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ],
              ),
            ),
          ),
        // Back button (custom) top-left
        Positioned(
          top: topInset + 12,
          left: 12,
          child: _circleBtn(
            icon: Icons.arrow_back_ios_new,
            onTap: () => Navigator.of(context).maybePop(),
            color: Colors.black87,
          ),
        ),
      ],
    );
  }

  Widget _buildHeroPlaceholder() {
    return Container(
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [
            AppColors.primaryGreen.withOpacity(.15),
            AppColors.primaryGreen.withOpacity(.05),
          ],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
      ),
      child: Icon(
        Icons.image_not_supported,
        size: 90,
        color: AppColors.primaryGreen.withOpacity(.6),
      ),
    );
  }

  Widget _buildModernBottomBar() {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.08),
            blurRadius: 24,
            offset: const Offset(0, -4),
          ),
        ],
      ),
      child: SafeArea(
        top: false,
        child: Padding(
          padding: const EdgeInsets.fromLTRB(14, 10, 14, 10),
          child: Row(
            children: [
              // Qty selector (compact pill)
              Container(
                decoration: BoxDecoration(
                  border: Border.all(color: const Color(0xFFE5E7EB)),
                  borderRadius: BorderRadius.circular(14),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    _buildQuantityButton(
                      icon: Icons.remove,
                      enabled: _quantity > 1,
                      onTap: () {
                        if (_quantity > 1) {
                          setState(() => _quantity--);
                          HapticFeedback.lightImpact();
                        }
                      },
                      isDecrease: true,
                    ),
                    SizedBox(
                      width: 42,
                      height: 36,
                      child: Center(
                        child: Text(
                          '$_quantity',
                          style: GoogleFonts.inter(
                            fontSize: 15,
                            fontWeight: FontWeight.w700,
                            color: const Color(0xFF1A1D1F),
                          ),
                        ),
                      ),
                    ),
                    _buildQuantityButton(
                      icon: Icons.add,
                      enabled: _quantity < (_selectedVariant?.stock ?? _product!.stock),
                      onTap: () {
                        final maxStock = _selectedVariant?.stock ?? _product!.stock;
                        if (_quantity < maxStock) {
                          setState(() => _quantity++);
                          HapticFeedback.lightImpact();
                        }
                      },
                      isDecrease: false,
                    ),
                  ],
                ),
              ),
              const SizedBox(width: 12),
              // Add / Update button
              Expanded(
                child: AnimatedSwitcher(
                  duration: const Duration(milliseconds: 250),
                  child: (_selectedVariant?.stock ?? _product!.stock) > 0
                      ? _AddButton(
                          key: ValueKey(
                            'btn_${_selectedVariant?.id}_${_quantity}_${_selectedVariant?.stock ?? _product!.stock}',
                          ),
                          isUpdate: context.read<CartService>().isInCart(
                            _product!.id,
                            variant: _selectedVariant,
                          ),
                          labelPrice: _format(_total),
                          onTap: () async {
                            HapticFeedback.mediumImpact();
                            print('🔥 PRODUCT DETAIL DEBUG: Adding to cart - Product: ${_product!.name}, Selected Variant: ${_selectedVariant?.displayName ?? 'none'}, Quantity: $_quantity');
                            context.read<CartService>().addToCart(
                              _product!,
                              quantity: _quantity,
                              variant: _selectedVariant,
                            );
                            if (!mounted) return;
                            ScaffoldMessenger.of(context).showSnackBar(
                              SnackBar(
                                content: Text(
                                  _selectedVariant != null
                                    ? 'Ditambahkan ke keranjang: ${_product!.name} (${_selectedVariant!.displayName})'
                                    : 'Ditambahkan ke keranjang: ${_product!.name}',
                                  style: GoogleFonts.inter(
                                    fontWeight: FontWeight.w600,
                                  ),
                                ),
                                backgroundColor: AppColors.primaryGreen,
                                behavior: SnackBarBehavior.floating,
                                shape: RoundedRectangleBorder(
                                  borderRadius: BorderRadius.circular(14),
                                ),
                                duration: const Duration(seconds: 2),
                              ),
                            );
                          },
                        )
                      : Container(
                          key: const ValueKey('out_of_stock'),
                          height: 52,
                          decoration: BoxDecoration(
                            color: const Color(0xFFF3F4F6),
                            borderRadius: BorderRadius.circular(18),
                          ),
                          child: Center(
                            child: Text(
                              'Stok Habis',
                              style: GoogleFonts.inter(
                                fontWeight: FontWeight.w700,
                                color: Colors.grey[500],
                              ),
                            ),
                          ),
                        ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildQuantityButton({
    required IconData icon,
    required bool enabled,
    required VoidCallback onTap,
    required bool isDecrease,
  }) {
    return InkWell(
      onTap: enabled ? onTap : null,
      borderRadius: BorderRadius.circular(isDecrease ? 12 : 12),
      child: Container(
        width: 36,
        height: 36,
        decoration: BoxDecoration(
          color: enabled ? AppColors.primaryGreen : const Color(0xFFF3F4F6),
          borderRadius: BorderRadius.only(
            topLeft: Radius.circular(isDecrease ? 12 : 0),
            bottomLeft: Radius.circular(isDecrease ? 12 : 0),
            topRight: Radius.circular(isDecrease ? 0 : 12),
            bottomRight: Radius.circular(isDecrease ? 0 : 12),
          ),
        ),
        child: Icon(
          icon,
          color: enabled ? Colors.white : const Color(0xFF9CA3AF),
          size: 18,
        ),
      ),
    );
  }

  Widget _buildFeatureItem(IconData icon, String title, String description) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 16),
      child: Row(
        children: [
          Container(
            width: 40,
            height: 40,
            decoration: BoxDecoration(
              color: AppColors.primaryGreen.withOpacity(0.1),
              borderRadius: BorderRadius.circular(8),
            ),
            child: Icon(icon, color: AppColors.primaryGreen, size: 20),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: GoogleFonts.inter(
                    fontSize: 14,
                    fontWeight: FontWeight.w600,
                    color: const Color(0xFF1A1D1F),
                  ),
                ),
                Text(
                  description,
                  style: GoogleFonts.inter(
                    fontSize: 12,
                    color: const Color(0xFF6B7280),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  String _getSalesCount() {
    // Generate pseudo-random sales count based on product ID
    final salesCount = (_product!.id * 47 + 230) % 1000 + 50;
    return salesCount.toString();
  }

  Widget _circleBtn({
    required IconData icon,
    required VoidCallback onTap,
    Color color = Colors.black,
    bool loading = false,
  }) {
    return GestureDetector(
      onTap: loading ? null : onTap,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        width: 42,
        height: 42,
        decoration: BoxDecoration(
          color: Colors.white,
          shape: BoxShape.circle,
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(.07),
              blurRadius: 10,
              offset: const Offset(0, 4),
            ),
          ],
          border: Border.all(color: Colors.grey[200]!),
        ),
        child: Center(
          child: loading
              ? const SizedBox(
                  width: 18,
                  height: 18,
                  child: CircularProgressIndicator(strokeWidth: 2),
                )
              : Icon(icon, size: 20, color: color),
        ),
      ),
    );
  }

  Widget _buildStockStatus(Product product) {
    final stock = (_selectedVariant?.stock ?? product.stock);
    final pct = (stock.clamp(0, 100)) / 100.0;

    late Color bg;
    late Color bar;
    late Color txt;
    late IconData icon;
    late String label;
    if (stock <= 0) {
      bg = const Color(0xFFFFF1F2);
      bar = const Color(0xFFEF4444);
      txt = const Color(0xFFB91C1C);
      icon = Icons.cancel;
      label = 'Stok habis';
    } else if (stock <= 5) {
      bg = const Color(0xFFFFF7ED);
      bar = const Color(0xFFF97316);
      txt = const Color(0xFFB45309);
      icon = Icons.local_fire_department;
      label = 'Hampir habis! Sisa $stock';
    } else if (stock <= 20) {
      bg = const Color(0xFFFEFCE8);
      bar = const Color(0xFFF59E0B);
      txt = const Color(0xFF92400E);
      icon = Icons.warning_amber_rounded;
      label = 'Stok menipis • $stock tersisa';
    } else {
      bg = const Color(0xFFF0FDF4);
      bar = const Color(0xFF10B981);
      txt = const Color(0xFF065F46);
      icon = Icons.inventory_2;
      label = 'Stok tersedia • $stock tersedia';
    }

    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: bg,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: bar.withOpacity(.25)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(icon, size: 18, color: txt),
              const SizedBox(width: 8),
              Expanded(
                child: Text(
                  label,
                  style: GoogleFonts.inter(
                    fontSize: 14,
                    fontWeight: FontWeight.w700,
                    color: txt,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),
          ClipRRect(
            borderRadius: BorderRadius.circular(8),
            child: LayoutBuilder(
              builder: (context, constraints) {
                final width = constraints.maxWidth;
                return Stack(
                  children: [
                    Container(height: 8, width: width, color: Colors.white.withOpacity(.6)),
                    AnimatedContainer(
                      duration: const Duration(milliseconds: 350),
                      curve: Curves.easeOut,
                      height: 8,
                      width: width * pct,
                      decoration: BoxDecoration(
                        gradient: LinearGradient(
                          colors: [bar.withOpacity(.7), bar],
                          begin: Alignment.centerLeft,
                          end: Alignment.centerRight,
                        ),
                      ),
                    ),
                  ],
                );
              },
            ),
          ),
        ],
      ),
    );
  }

  // Build a clean, borderless description with basic bullet/paragraph formatting
  Widget _buildFormattedDescription(String? description) {
    final raw = (description == null || description.trim().isEmpty)
        ? 'Deskripsi tidak tersedia'
        : description.trim();
    // Split description into lines/paragraphs
    final allLines = raw
        .split(RegExp(r'\r?\n'))
        .map((e) => e.trim())
        .where((e) => e.isNotEmpty)
        .toList();

    const maxVisible = 8; // show up to 8 lines then expand
    final needsMore = allLines.length > maxVisible;
    final lines = _descExpanded ? allLines : allLines.take(maxVisible).toList();

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        ...lines.map(_buildDescLine),
        if (needsMore)
          Padding(
            padding: const EdgeInsets.only(top: 8),
            child: GestureDetector(
              onTap: () => setState(() => _descExpanded = !_descExpanded),
              child: Text(
                _descExpanded ? 'Sembunyikan' : 'Selengkapnya',
                style: GoogleFonts.inter(
                  color: AppColors.primaryGreen,
                  fontWeight: FontWeight.w600,
                  fontSize: 14,
                ),
              ),
            ),
          ),
      ],
    );
  }

  Widget _buildDescLine(String line) {
    // Detect bullet markers like -, *, •, or 1.
    final bulletPattern = RegExp(r'^(?:[-*•]|\d+\.)\s+');
    final isBullet = bulletPattern.hasMatch(line);
    final clean = line.replaceFirst(bulletPattern, '').trim();

    // Detect key: value pairs and style the key
    final kv = RegExp(r'^(.*?)\s*:\s*(.+)\$');
    final match = kv.firstMatch(clean);

    Widget textWidget;
    if (match != null) {
      final key = match.group(1) ?? '';
      final value = match.group(2) ?? '';
      textWidget = Text.rich(
        TextSpan(
          children: [
            TextSpan(
              text: '$key: ',
              style: GoogleFonts.inter(
                fontSize: 14,
                fontWeight: FontWeight.w700,
                color: const Color(0xFF111827),
              ),
            ),
            TextSpan(
              text: value,
              style: GoogleFonts.inter(
                fontSize: 14,
                color: const Color(0xFF4B5563),
              ),
            ),
          ],
        ),
      );
    } else {
      textWidget = Text(
        clean,
        style: GoogleFonts.inter(
          fontSize: 14,
          color: const Color(0xFF4B5563),
          height: 1.6,
        ),
      );
    }

    if (isBullet) {
      return Padding(
        padding: const EdgeInsets.only(bottom: 8),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              margin: const EdgeInsets.only(top: 8),
              width: 6,
              height: 6,
              decoration: const BoxDecoration(
                color: Color(0xFF9CA3AF),
                shape: BoxShape.circle,
              ),
            ),
            const SizedBox(width: 8),
            Expanded(child: textWidget),
          ],
        ),
      );
    }
    return Padding(
      padding: const EdgeInsets.only(bottom: 10),
      child: textWidget,
    );
  }
}

class _AddButton extends StatelessWidget {
  final bool isUpdate;
  final String labelPrice;
  final VoidCallback onTap;
  const _AddButton({
    super.key,
    required this.isUpdate,
    required this.labelPrice,
    required this.onTap,
  });
  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        height: 52,
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(18),
          color: AppColors.primaryGreen, // solid color, no gradient
          boxShadow: [
            BoxShadow(color: AppColors.primaryGreen.withOpacity(.28), blurRadius: 14, offset: const Offset(0, 6)),
          ],
        ),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              isUpdate ? Icons.refresh : Icons.add_shopping_cart,
              size: 18,
              color: Colors.white,
            ),
            const SizedBox(width: 6),
            Flexible(
              child: Text(
                '${isUpdate ? 'Update Keranjang' : 'Tambah ke Keranjang'} • $labelPrice',
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
                style: GoogleFonts.inter(
                  fontSize: 14,
                  fontWeight: FontWeight.w700,
                  color: Colors.white,
                  letterSpacing: .2,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
