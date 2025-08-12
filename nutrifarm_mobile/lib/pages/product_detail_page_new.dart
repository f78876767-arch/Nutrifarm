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

class _ProductDetailPageNewState extends State<ProductDetailPageNew> {
  int quantity = 1;
  int selectedImageIndex = 0;
  bool showFullDescription = false;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      body: CustomScrollView(
        slivers: [
          // App Bar
          SliverAppBar(
            expandedHeight: 300,
            floating: false,
            pinned: true,
            backgroundColor: AppColors.surface,
            leading: IconButton(
              icon: const Icon(Icons.arrow_back, color: AppColors.onSurface),
              onPressed: () => Navigator.pop(context),
            ),
            actions: [
              Consumer<FavoritesServiceApi>(
                builder: (context, favService, child) {
                  final isFavorite = favService.isFavorite(widget.product.id);
                  return IconButton(
                    icon: Icon(
                      isFavorite ? Icons.favorite : Icons.favorite_border,
                      color: isFavorite ? AppColors.error : AppColors.onSurface,
                    ),
                    onPressed: () {
                      HapticFeedback.lightImpact();
                      favService.toggleFavorite(widget.product.id);
                      
                      ScaffoldMessenger.of(context).showSnackBar(
                        SnackBar(
                          content: Text(
                            isFavorite ? 'Dihapus dari favorit' : 'Ditambahkan ke favorit',
                            style: GoogleFonts.nunitoSans(color: Colors.white),
                          ),
                          backgroundColor: AppColors.primaryGreen,
                          duration: const Duration(seconds: 1),
                        ),
                      );
                    },
                  );
                },
              ),
              Consumer<CartService>(
                builder: (context, cartService, child) {
                  return Stack(
                    children: [
                      IconButton(
                        icon: const Icon(Icons.shopping_bag_outlined, color: AppColors.onSurface),
                        onPressed: () => Navigator.pushNamed(context, '/cart'),
                      ),
                      if (cartService.totalQuantity > 0)
                        Positioned(
                          right: 8,
                          top: 8,
                          child: Container(
                            padding: const EdgeInsets.all(2),
                            decoration: BoxDecoration(
                              color: AppColors.error,
                              borderRadius: BorderRadius.circular(6),
                            ),
                            constraints: const BoxConstraints(
                              minWidth: 16,
                              minHeight: 16,
                            ),
                            child: Text(
                              '${cartService.totalQuantity}',
                              style: const TextStyle(
                                color: Colors.white,
                                fontSize: 12,
                              ),
                              textAlign: TextAlign.center,
                            ),
                          ),
                        ),
                    ],
                  );
                },
              ),
            ],
            flexibleSpace: FlexibleSpaceBar(
              background: Container(
                color: AppColors.surface,
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    const SizedBox(height: 60),
                    // Product Image
                    Container(
                      width: 200,
                      height: 200,
                      decoration: BoxDecoration(
                        color: AppColors.primaryGreen.withOpacity(0.1),
                        borderRadius: BorderRadius.circular(16),
                      ),
                      child: ClipRRect(
                        borderRadius: BorderRadius.circular(16),
                        child: widget.product.imageUrl.isNotEmpty
                            ? Image.network(
                                widget.product.imageUrl,
                                fit: BoxFit.cover,
                                loadingBuilder: (context, child, loadingProgress) {
                                  if (loadingProgress == null) return child;
                                  return Center(
                                    child: CircularProgressIndicator(
                                      value: loadingProgress.expectedTotalBytes != null
                                          ? loadingProgress.cumulativeBytesLoaded /
                                              loadingProgress.expectedTotalBytes!
                                          : null,
                                      color: AppColors.primaryGreen,
                                    ),
                                  );
                                },
                                errorBuilder: (context, error, stackTrace) {
                                  return Icon(
                                    Icons.local_pharmacy,
                                    size: 100,
                                    color: AppColors.primaryGreen.withOpacity(0.7),
                                  );
                                },
                              )
                            : Icon(
                                Icons.local_pharmacy,
                                size: 100,
                                color: AppColors.primaryGreen.withOpacity(0.7),
                              ),
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
          
          // Product Details
          SliverToBoxAdapter(
            child: Container(
              decoration: const BoxDecoration(
                color: AppColors.background,
                borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
              ),
              child: Padding(
                padding: const EdgeInsets.all(24),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Product Name & Rating
                    Row(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                widget.product.name,
                                style: GoogleFonts.nunitoSans(
                                  fontSize: 24,
                                  fontWeight: FontWeight.w700,
                                  color: AppColors.onSurface,
                                ),
                              ),
                              const SizedBox(height: 8),
                              Row(
                                children: [
                                  Row(
                                    children: List.generate(5, (index) {
                                      return Icon(
                                        index < widget.product.rating.floor()
                                            ? Icons.star
                                            : Icons.star_border,
                                        color: AppColors.warning,
                                        size: 16,
                                      );
                                    }),
                                  ),
                                  const SizedBox(width: 4),
                                  Text(
                                    '${widget.product.rating}',
                                    style: GoogleFonts.nunitoSans(
                                      fontSize: 14,
                                      fontWeight: FontWeight.w600,
                                      color: AppColors.onSurfaceVariant,
                                    ),
                                  ),
                                ],
                              ),
                            ],
                          ),
                        ),
                        if ((widget.product.discount ?? 0) > 0)
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                            decoration: BoxDecoration(
                              color: AppColors.error,
                              borderRadius: BorderRadius.circular(12),
                            ),
                            child: Text(
                              '${widget.product.discount}% OFF',
                              style: GoogleFonts.nunitoSans(
                                fontSize: 12,
                                fontWeight: FontWeight.w700,
                                color: Colors.white,
                              ),
                            ),
                          ),
                      ],
                    ),
                    const SizedBox(height: 20),
                    
                    // Price
                    Row(
                      children: [
                        Text(
                          widget.product.formattedPrice,
                          style: GoogleFonts.nunitoSans(
                            fontSize: 28,
                            fontWeight: FontWeight.w700,
                            color: AppColors.primaryGreen,
                          ),
                        ),
                        if (widget.product.hasDiscount) ...[
                          const SizedBox(width: 8),
                          Text(
                            widget.product.formattedOriginalPrice,
                            style: GoogleFonts.nunitoSans(
                              fontSize: 18,
                              color: AppColors.onSurfaceVariant,
                              decoration: TextDecoration.lineThrough,
                            ),
                          ),
                        ],
                      ],
                    ),
                    const SizedBox(height: 20),
                    
                    // Stock Info
                    Container(
                      padding: const EdgeInsets.all(16),
                      decoration: BoxDecoration(
                        color: widget.product.stock > 5 
                            ? AppColors.success.withOpacity(0.1)
                            : AppColors.warning.withOpacity(0.1),
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(
                          color: widget.product.stock > 5 
                              ? AppColors.success.withOpacity(0.3)
                              : AppColors.warning.withOpacity(0.3),
                        ),
                      ),
                      child: Row(
                        children: [
                          Icon(
                            widget.product.stock > 5 
                                ? Icons.check_circle_outline
                                : Icons.warning_outlined,
                            color: widget.product.stock > 5 
                                ? AppColors.success
                                : AppColors.warning,
                            size: 20,
                          ),
                          const SizedBox(width: 8),
                          Text(
                            widget.product.stock > 5 
                                ? 'Stok tersedia (${widget.product.stock} items)'
                                : 'Stok terbatas (${widget.product.stock} items)',
                            style: GoogleFonts.nunitoSans(
                              fontSize: 14,
                              fontWeight: FontWeight.w600,
                              color: widget.product.stock > 5 
                                  ? AppColors.success
                                  : AppColors.warning,
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 24),
                    
                    // Description
                    Text(
                      'Deskripsi Produk',
                      style: GoogleFonts.nunitoSans(
                        fontSize: 18,
                        fontWeight: FontWeight.w700,
                        color: AppColors.onSurface,
                      ),
                    ),
                    const SizedBox(height: 8),
                    Text(
                      showFullDescription 
                          ? (widget.product.description ?? '')
                          : '${(widget.product.description ?? '').substring(0, (widget.product.description ?? '').length > 100 ? 100 : (widget.product.description ?? '').length)}${(widget.product.description ?? '').length > 100 ? "..." : ""}',
                      style: GoogleFonts.nunitoSans(
                        fontSize: 14,
                        color: AppColors.onSurfaceVariant,
                        height: 1.5,
                      ),
                    ),
                    if ((widget.product.description ?? '').length > 100)
                      TextButton(
                        onPressed: () {
                          setState(() {
                            showFullDescription = !showFullDescription;
                          });
                        },
                        child: Text(
                          showFullDescription ? 'Sembunyikan' : 'Selengkapnya',
                          style: GoogleFonts.nunitoSans(
                            color: AppColors.primaryGreen,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                      ),
                    const SizedBox(height: 100), // Space for bottom bar
                  ],
                ),
              ),
            ),
          ),
        ],
      ),
      
      // Bottom Add to Cart Bar
      bottomNavigationBar: Container(
        padding: const EdgeInsets.all(20),
        decoration: BoxDecoration(
          color: AppColors.surface,
          borderRadius: const BorderRadius.vertical(top: Radius.circular(20)),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.1),
              blurRadius: 10,
              offset: const Offset(0, -2),
            ),
          ],
        ),
        child: Row(
          children: [
            // Quantity Selector
            Container(
              decoration: BoxDecoration(
                border: Border.all(color: AppColors.outline.withOpacity(0.3)),
                borderRadius: BorderRadius.circular(12),
              ),
              child: Row(
                children: [
                  GestureDetector(
                    onTap: quantity > 1 ? () {
                      setState(() => quantity--);
                      HapticFeedback.lightImpact();
                    } : null,
                    child: Container(
                      width: 40,
                      height: 40,
                      decoration: BoxDecoration(
                        color: quantity > 1 ? AppColors.primaryGreen : AppColors.outline.withOpacity(0.3),
                        borderRadius: const BorderRadius.only(
                          topLeft: Radius.circular(12),
                          bottomLeft: Radius.circular(12),
                        ),
                      ),
                      child: Icon(
                        Icons.remove,
                        color: quantity > 1 ? Colors.white : AppColors.onSurfaceVariant,
                        size: 20,
                      ),
                    ),
                  ),
                  Container(
                    width: 50,
                    height: 40,
                    color: AppColors.surface,
                    child: Center(
                      child: Text(
                        '$quantity',
                        style: GoogleFonts.nunitoSans(
                          fontSize: 16,
                          fontWeight: FontWeight.w700,
                          color: AppColors.onSurface,
                        ),
                      ),
                    ),
                  ),
                  GestureDetector(
                    onTap: quantity < widget.product.stock ? () {
                      setState(() => quantity++);
                      HapticFeedback.lightImpact();
                    } : null,
                    child: Container(
                      width: 40,
                      height: 40,
                      decoration: BoxDecoration(
                        color: quantity < widget.product.stock 
                            ? AppColors.primaryGreen 
                            : AppColors.outline.withOpacity(0.3),
                        borderRadius: const BorderRadius.only(
                          topRight: Radius.circular(12),
                          bottomRight: Radius.circular(12),
                        ),
                      ),
                      child: Icon(
                        Icons.add,
                        color: quantity < widget.product.stock 
                            ? Colors.white 
                            : AppColors.onSurfaceVariant,
                        size: 20,
                      ),
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(width: 16),
            
            // Add to Cart Button
            Expanded(
              child: Consumer<CartService>(
                builder: (context, cartService, child) {
                  final isInCart = cartService.isInCart(widget.product.id);
                  
                  return ElevatedButton(
                    onPressed: () {
                      HapticFeedback.mediumImpact();
                      cartService.addToCart(widget.product, quantity: quantity);
                      
                      ScaffoldMessenger.of(context).showSnackBar(
                        SnackBar(
                          content: Text(
                            isInCart 
                                ? 'Jumlah produk diperbarui di keranjang'
                                : 'Produk ditambahkan ke keranjang',
                            style: GoogleFonts.nunitoSans(color: Colors.white),
                          ),
                          backgroundColor: AppColors.primaryGreen,
                          action: SnackBarAction(
                            label: 'Lihat Keranjang',
                            textColor: Colors.white,
                            onPressed: () {
                              Navigator.pushNamed(context, '/cart');
                            },
                          ),
                        ),
                      );
                    },
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppColors.primaryGreen,
                      foregroundColor: Colors.white,
                      minimumSize: const Size(0, 48),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                      elevation: 0,
                    ),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        const Icon(Icons.shopping_bag_outlined, size: 20),
                        const SizedBox(width: 8),
                        Text(
                          isInCart 
                              ? 'Update Cart - ${widget.product.formatTotalPrice(quantity)}'
                              : 'Add to Cart - ${widget.product.formatTotalPrice(quantity)}',
                          style: GoogleFonts.nunitoSans(
                            fontSize: 14,
                            fontWeight: FontWeight.w700,
                          ),
                        ),
                      ],
                    ),
                  );
                },
              ),
            ),
          ],
        ),
      ),
    );
  }
}
