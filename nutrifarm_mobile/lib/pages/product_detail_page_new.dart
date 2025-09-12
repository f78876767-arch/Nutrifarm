import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:provider/provider.dart';
import '../theme/app_theme.dart';
import '../models/product.dart';
import '../services/cart_service.dart';
import '../services/favorites_service_api.dart';
import 'package:google_fonts/google_fonts.dart';

class ProductDetailPageNew extends StatefulWidget {
  final Product product;
  const ProductDetailPageNew({super.key, required this.product});
  @override
  State<ProductDetailPageNew> createState() => _ProductDetailPageNewState();
}

class _ProductDetailPageNewState extends State<ProductDetailPageNew> with TickerProviderStateMixin {
  int quantity = 1;
  int selectedImageIndex = 0;
  bool showFullDescription = false;
  bool isImageLoading = true;
  Variant? selectedVariant;
  late AnimationController _priceAnimationController;
  late Animation<double> _priceAnimation;

  @override
  void initState() {
    super.initState();
    if (widget.product.variants.isNotEmpty) {
      selectedVariant = widget.product.cheapestVariant ?? widget.product.variants.first;
    }
    
    _priceAnimationController = AnimationController(
      duration: const Duration(milliseconds: 300),
      vsync: this,
    );
    _priceAnimation = CurvedAnimation(parent: _priceAnimationController, curve: Curves.easeInOut);
  }

  @override
  void dispose() {
    _priceAnimationController.dispose();
    super.dispose();
  }

  // NEW: Use variant-based pricing
  double get effectivePrice => selectedVariant?.effectivePrice ?? widget.product.minPrice;
  double get originalPrice => selectedVariant?.originalPrice ?? widget.product.price;
  bool get hasDiscount => selectedVariant?.hasDiscount ?? widget.product.hasDiscount;
  String get formattedEffectivePrice => 'Rp ${effectivePrice.toStringAsFixed(0).replaceAllMapped(RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))'), (m) => '${m[1]}.')}';
  String get formattedOriginalPrice => 'Rp ${originalPrice.toStringAsFixed(0).replaceAllMapped(RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))'), (m) => '${m[1]}.')}';

  void _selectVariant(Variant variant) {
    setState(() => selectedVariant = variant);
    _priceAnimationController.forward().then((_) => _priceAnimationController.reverse());
    HapticFeedback.lightImpact();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white,
      body: SafeArea(
        child: CustomScrollView(
        slivers: [
          // Hero Image Section with modern design
          SliverAppBar(
            expandedHeight: 400,
            floating: false,
            pinned: true,
            backgroundColor: Colors.white,
            elevation: 0,
            leading: Container(
              margin: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(12),
                boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.1), blurRadius: 8, offset: const Offset(0, 2))],
              ),
              child: IconButton(
                icon: const Icon(Icons.arrow_back_ios_new, color: Colors.black87, size: 20),
                onPressed: () => Navigator.pop(context),
              ),
            ),
            actions: [
              Container(
                margin: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(12),
                  boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.1), blurRadius: 8, offset: const Offset(0, 2))],
                ),
                child: Consumer<FavoritesServiceApi>(
                  builder: (context, favService, child) {
                    final isFavorite = favService.isFavorite(widget.product.id);
                    return IconButton(
                      icon: Icon(
                        isFavorite ? Icons.favorite : Icons.favorite_border,
                        color: isFavorite ? Colors.red : Colors.black54,
                        size: 20,
                      ),
                      onPressed: () {
                        HapticFeedback.lightImpact();
                        favService.toggleFavorite(widget.product.id, product: widget.product);
                      },
                    );
                  },
                ),
              ),
              Container(
                margin: const EdgeInsets.only(right: 16, top: 8, bottom: 8),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(12),
                  boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.1), blurRadius: 8, offset: const Offset(0, 2))],
                ),
                child: IconButton(
                  icon: const Icon(Icons.share, color: Colors.black54, size: 20),
                  onPressed: () {
                    HapticFeedback.lightImpact();
                    // Share functionality
                  },
                ),
              ),
            ],
            flexibleSpace: FlexibleSpaceBar(
              background: Container(
                decoration: const BoxDecoration(
                  gradient: LinearGradient(
                    begin: Alignment.topCenter,
                    end: Alignment.bottomCenter,
                    colors: [Color(0xFFF8F9FA), Colors.white],
                  ),
                ),
                child: Stack(
                  children: [
                    // Main Product Image
                    Center(
                      child: Container(
                        margin: const EdgeInsets.only(top: 100),
                        width: 280,
                        height: 280,
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(20),
                          boxShadow: [
                            BoxShadow(
                              color: Colors.black.withOpacity(0.08),
                              blurRadius: 20,
                              offset: const Offset(0, 10),
                            ),
                          ],
                        ),
                        child: ClipRRect(
                          borderRadius: BorderRadius.circular(20),
                          child: widget.product.imageUrl.isNotEmpty
                              ? Image.network(
                                  widget.product.imageUrl,
                                  fit: BoxFit.cover,
                                  loadingBuilder: (context, child, loadingProgress) {
                                    if (loadingProgress == null) {
                                      setState(() => isImageLoading = false);
                                      return child;
                                    }
                                    return Center(
                                      child: CircularProgressIndicator(
                                        value: loadingProgress.expectedTotalBytes != null
                                            ? loadingProgress.cumulativeBytesLoaded / loadingProgress.expectedTotalBytes!
                                            : null,
                                        color: AppColors.primaryGreen,
                                        strokeWidth: 3,
                                      ),
                                    );
                                  },
                                  errorBuilder: (_, __, ___) => Container(
                                    decoration: BoxDecoration(
                                      gradient: LinearGradient(
                                        colors: [AppColors.primaryGreen.withOpacity(0.1), AppColors.primaryGreen.withOpacity(0.05)],
                                      ),
                                    ),
                                    child: Icon(Icons.local_pharmacy, size: 80, color: AppColors.primaryGreen.withOpacity(0.6)),
                                  ),
                                )
                              : Container(
                                  decoration: BoxDecoration(
                                    gradient: LinearGradient(
                                      colors: [AppColors.primaryGreen.withOpacity(0.1), AppColors.primaryGreen.withOpacity(0.05)],
                                    ),
                                  ),
                                  child: Icon(Icons.local_pharmacy, size: 80, color: AppColors.primaryGreen.withOpacity(0.6)),
                                ),
                        ),
                      ),
                    ),
                    // Discount Badge
                    if (hasDiscount)
                      Positioned(
                        top: 120,
                        left: 40,
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                          decoration: BoxDecoration(
                            gradient: const LinearGradient(colors: [Color(0xFFFF6B35), Color(0xFFFF8A50)]),
                            borderRadius: BorderRadius.circular(20),
                            boxShadow: [BoxShadow(color: const Color(0xFFFF6B35).withOpacity(0.4), blurRadius: 8, offset: const Offset(0, 3))],
                          ),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              const Icon(Icons.local_fire_department, color: Colors.white, size: 16),
                              const SizedBox(width: 4),
                              Text(
                                '${((originalPrice - effectivePrice) / originalPrice * 100).round()}% OFF',
                                style: const TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.bold),
                              ),
                            ],
                          ),
                        ),
                      ),
                  ],
                ),
              ),
            ),
          ),
          // Product Info Section
          SliverToBoxAdapter(
            child: Container(
              decoration: const BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.vertical(top: Radius.circular(30)),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Product Name & Rating Section
                  Container(
                    padding: const EdgeInsets.fromLTRB(24, 24, 24, 0),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          widget.product.name,
                          style: GoogleFonts.inter(
                            fontSize: 24,
                            fontWeight: FontWeight.w700,
                            color: const Color(0xFF1A1D1F),
                            height: 1.2,
                          ),
                        ),
                        const SizedBox(height: 12),
                        Row(
                          children: [
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                              decoration: BoxDecoration(
                                color: const Color(0xFFFFF3CD),
                                borderRadius: BorderRadius.circular(6),
                                border: Border.all(color: const Color(0xFFFFEC8B)),
                              ),
                              child: Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  const Icon(Icons.star, color: Color(0xFFFFA726), size: 14),
                                  const SizedBox(width: 4),
                                  Text(
                                    '${widget.product.rating}',
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
                            if (hasDiscount)
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                decoration: BoxDecoration(
                                  gradient: const LinearGradient(colors: [Color(0xFFFF4757), Color(0xFFFF3838)]),
                                  borderRadius: BorderRadius.circular(6),
                                ),
                                child: Text(
                                  'HEMAT ${((originalPrice - effectivePrice) / 1000).round()}K',
                                  style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.bold),
                                ),
                              ),
                          ],
                        ),
                      ],
                    ),
                  ),

                  // Price Section with Animation
                  Container(
                    padding: const EdgeInsets.fromLTRB(24, 20, 24, 0),
                    child: AnimatedBuilder(
                      animation: _priceAnimation,
                      builder: (context, child) {
                        return Transform.scale(
                          scale: 1.0 + (_priceAnimation.value * 0.05),
                          child: Row(
                            crossAxisAlignment: CrossAxisAlignment.end,
                            children: [
                              Text(
                                formattedEffectivePrice,
                                style: GoogleFonts.inter(
                                  fontSize: 32,
                                  fontWeight: FontWeight.w800,
                                  color: AppColors.primaryGreen,
                                  height: 1.0,
                                ),
                              ),
                              if (hasDiscount) ...[
                                const SizedBox(width: 12),
                                Column(
                                  children: [
                                    Text(
                                      formattedOriginalPrice,
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
                        );
                      },
                    ),
                  ),

                  // Stock Status Section
                  Container(
                    margin: const EdgeInsets.fromLTRB(24, 16, 24, 0),
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: widget.product.stock > 10 ? const Color(0xFFF0FDF4) : const Color(0xFFFEF3C7),
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(
                        color: widget.product.stock > 10 ? const Color(0xFFBBF7D0) : const Color(0xFFFDE68A),
                      ),
                    ),
                    child: Row(
                      children: [
                        Icon(
                          widget.product.stock > 10 ? Icons.inventory_2 : Icons.warning_rounded,
                          color: widget.product.stock > 10 ? const Color(0xFF059669) : const Color(0xFFD97706),
                          size: 20,
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Text(
                            widget.product.stock > 10
                                ? 'Stok tersedia (${widget.product.stock} tersisa)'
                                : widget.product.stock > 0
                                    ? 'Stok terbatas! Hanya ${widget.product.stock} tersisa'
                                    : 'Stok habis',
                            style: GoogleFonts.inter(
                              fontSize: 14,
                              fontWeight: FontWeight.w600,
                              color: widget.product.stock > 10 ? const Color(0xFF059669) : const Color(0xFFD97706),
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                  // Variant Selector Section
                  if (widget.product.variants.isNotEmpty) ...[
                    Container(
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
                            children: widget.product.variants.map((variant) {
                              final isSelected = selectedVariant?.id == variant.id;
                              return GestureDetector(
                                onTap: () => _selectVariant(variant),
                                child: AnimatedContainer(
                                  duration: const Duration(milliseconds: 200),
                                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                                  decoration: BoxDecoration(
                                    color: isSelected ? AppColors.primaryGreen : Colors.white,
                                    borderRadius: BorderRadius.circular(12),
                                    border: Border.all(
                                      color: isSelected ? AppColors.primaryGreen : const Color(0xFFE5E7EB),
                                      width: 2,
                                    ),
                                    boxShadow: isSelected
                                        ? [BoxShadow(color: AppColors.primaryGreen.withOpacity(0.2), blurRadius: 8, offset: const Offset(0, 4))]
                                        : [BoxShadow(color: Colors.black.withOpacity(0.05), blurRadius: 4, offset: const Offset(0, 2))],
                                  ),
                                  child: Column(
                                    children: [
                                      Text(
                                        variant.displayName,
                                        style: GoogleFonts.inter(
                                          fontSize: 14,
                                          fontWeight: FontWeight.w600,
                                          color: isSelected ? Colors.white : const Color(0xFF374151),
                                        ),
                                      ),
                                      if (variant.effectivePrice > 0) ...[
                                        const SizedBox(height: 4),
                                        Text(
                                          variant.formattedPrice,
                                          style: GoogleFonts.inter(
                                            fontSize: 12,
                                            fontWeight: FontWeight.w500,
                                            color: isSelected ? Colors.white.withOpacity(0.9) : AppColors.primaryGreen,
                                          ),
                                        ),
                                      ],
                                    ],
                                  ),
                                ),
                              );
                            }).toList(),
                          ),
                        ],
                      ),
                    ),
                  ],

                  // Product Features Section
                  Container(
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
                        _buildFeatureItem(Icons.verified, 'Kualitas Premium', 'Produk berkualitas tinggi dan terjamin'),
                        _buildFeatureItem(Icons.local_shipping, 'Pengiriman Cepat', 'Dikirim dalam 1-2 hari kerja'),
                        _buildFeatureItem(Icons.support_agent, 'Customer Support 24/7', 'Tim support siap membantu Anda'),
                        _buildFeatureItem(Icons.assignment_return, 'Garansi Kepuasan', 'Jaminan uang kembali 100%'),
                      ],
                    ),
                  ),

                  // Description Section
                  Container(
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
                        Container(
                          padding: const EdgeInsets.all(16),
                          decoration: BoxDecoration(
                            color: const Color(0xFFF9FAFB),
                            borderRadius: BorderRadius.circular(12),
                            border: Border.all(color: const Color(0xFFE5E7EB)),
                          ),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                showFullDescription
                                    ? (widget.product.description ?? 'Deskripsi tidak tersedia')
                                    : _getTruncatedDescription(),
                                style: GoogleFonts.inter(
                                  fontSize: 14,
                                  color: const Color(0xFF4B5563),
                                  height: 1.6,
                                ),
                              ),
                              if (_shouldShowReadMore())
                                Padding(
                                  padding: const EdgeInsets.only(top: 8),
                                  child: GestureDetector(
                                    onTap: () => setState(() => showFullDescription = !showFullDescription),
                                    child: Text(
                                      showFullDescription ? 'Sembunyikan' : 'Selengkapnya',
                                      style: GoogleFonts.inter(
                                        color: AppColors.primaryGreen,
                                        fontWeight: FontWeight.w600,
                                        fontSize: 14,
                                      ),
                                    ),
                                  ),
                                ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),

                  const SizedBox(height: 120), // Space for bottom bar
                ],
              ),
            ),
          ),
        ],
        ),
      ),
      // Modern Bottom Navigation Bar
      bottomNavigationBar: Container(
        decoration: BoxDecoration(
          color: Colors.white,
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.1),
              blurRadius: 20,
              offset: const Offset(0, -5),
            ),
          ],
        ),
        child: SafeArea(
          child: Padding(
            padding: const EdgeInsets.all(20),
            child: Row(
              children: [
                // Quantity Selector
                Container(
                  decoration: BoxDecoration(
                    border: Border.all(color: const Color(0xFFE5E7EB)),
                    borderRadius: BorderRadius.circular(16),
                  ),
                  child: Row(
                    children: [
                      _buildQuantityButton(
                        icon: Icons.remove,
                        enabled: quantity > 1,
                        onTap: () {
                          if (quantity > 1) {
                            setState(() => quantity--);
                            HapticFeedback.lightImpact();
                          }
                        },
                        isDecrease: true,
                      ),
                      Container(
                        width: 60,
                        height: 48,
                        alignment: Alignment.center,
                        child: Text(
                          '$quantity',
                          style: GoogleFonts.inter(
                            fontSize: 18,
                            fontWeight: FontWeight.w700,
                            color: const Color(0xFF1A1D1F),
                          ),
                        ),
                      ),
                      _buildQuantityButton(
                        icon: Icons.add,
                        enabled: quantity < widget.product.stock,
                        onTap: () {
                          if (quantity < widget.product.stock) {
                            setState(() => quantity++);
                            HapticFeedback.lightImpact();
                          }
                        },
                        isDecrease: false,
                      ),
                    ],
                  ),
                ),
                const SizedBox(width: 16),
                // Add to Cart Button
                Expanded(
                  child: Consumer<CartService>(
                    builder: (context, cartService, child) {
                      final isInCart = cartService.isInCart(widget.product.id, variant: selectedVariant);
                      return Container(
                        height: 48,
                        child: ElevatedButton(
                          onPressed: widget.product.stock > 0
                              ? () {
                                  HapticFeedback.mediumImpact();
                                  cartService.addToCart(widget.product, quantity: quantity, variant: selectedVariant);
                                  
                                  ScaffoldMessenger.of(context).showSnackBar(
                                    SnackBar(
                                      content: Row(
                                        children: [
                                          const Icon(Icons.check_circle, color: Colors.white, size: 20),
                                          const SizedBox(width: 12),
                                          Expanded(
                                            child: Text(
                                              isInCart ? 'Jumlah diperbarui di keranjang' : 'Berhasil ditambahkan ke keranjang',
                                              style: GoogleFonts.inter(color: Colors.white, fontWeight: FontWeight.w500),
                                            ),
                                          ),
                                        ],
                                      ),
                                      backgroundColor: AppColors.primaryGreen,
                                      behavior: SnackBarBehavior.floating,
                                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                      action: SnackBarAction(
                                        label: 'LIHAT',
                                        textColor: Colors.white,
                                        onPressed: () => Navigator.pushNamed(context, '/cart'),
                                      ),
                                    ),
                                  );
                                }
                              : null,
                          style: ElevatedButton.styleFrom(
                            backgroundColor: AppColors.primaryGreen,
                            foregroundColor: Colors.white,
                            elevation: 0,
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                            disabledBackgroundColor: const Color(0xFFE5E7EB),
                            disabledForegroundColor: const Color(0xFF9CA3AF),
                          ),
                          child: Row(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Icon(
                                isInCart ? Icons.refresh : Icons.shopping_bag,
                                size: 20,
                              ),
                              const SizedBox(width: 8),
                              Text(
                                widget.product.stock > 0
                                    ? '${isInCart ? 'Update' : 'Tambah'} • ${(effectivePrice * quantity).toStringAsFixed(0).replaceAllMapped(RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))'), (m) => 'Rp ${m[1]}.')}'
                                    : 'Stok Habis',
                                style: GoogleFonts.inter(
                                  fontSize: 16,
                                  fontWeight: FontWeight.w700,
                                ),
                              ),
                            ],
                          ),
                        ),
                      );
                    },
                  ),
                ),
              ],
            ),
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
      borderRadius: BorderRadius.circular(isDecrease ? 16 : 16),
      child: Container(
        width: 48,
        height: 48,
        decoration: BoxDecoration(
          color: enabled ? AppColors.primaryGreen : const Color(0xFFF3F4F6),
          borderRadius: BorderRadius.only(
            topLeft: Radius.circular(isDecrease ? 16 : 0),
            bottomLeft: Radius.circular(isDecrease ? 16 : 0),
            topRight: Radius.circular(isDecrease ? 0 : 16),
            bottomRight: Radius.circular(isDecrease ? 0 : 16),
          ),
        ),
        child: Icon(
          icon,
          color: enabled ? Colors.white : const Color(0xFF9CA3AF),
          size: 20,
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
    final salesCount = (widget.product.id * 47 + 230) % 1000 + 50;
    return salesCount.toString();
  }

  String _getTruncatedDescription() {
    final description = widget.product.description ?? 'Deskripsi tidak tersedia';
    if (description.length <= 150) return description;
    return '${description.substring(0, 150)}...';
  }

  bool _shouldShowReadMore() {
    final description = widget.product.description ?? '';
    return description.length > 150;
  }
}
