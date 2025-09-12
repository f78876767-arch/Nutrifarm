import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:provider/provider.dart';
import '../models/product.dart';
import '../services/cart_service.dart';
import '../services/favorites_service_api.dart';
import 'skeleton_loading.dart';

class ProductCard extends StatefulWidget {
  final Product product;
  final VoidCallback? onTap;
  final GlobalKey? imageKey; // key for flight animation start
  final VoidCallback? onAddToCart; // callback after successful add
  final bool handleAddToCartInternally; // whether to add to cart internally or just call callback

  const ProductCard({
    super.key, 
    required this.product, 
    this.onTap, 
    this.imageKey, 
    this.onAddToCart,
    this.handleAddToCartInternally = true,
  });

  @override
  State<ProductCard> createState() => _ProductCardState();
}

class _ProductCardState extends State<ProductCard>
    with TickerProviderStateMixin {
  late AnimationController _animationController;
  late Animation<double> _scaleAnimation;
  late AnimationController _addToCartController;
  late Animation<double> _addToCartScale;
  late Animation<Color?> _addToCartColor;
  bool _isPressed = false;
  bool _showAddToCartFeedback = false;

  @override
  void initState() {
    super.initState();
    _animationController = AnimationController(
      duration: const Duration(milliseconds: 150),
      vsync: this,
    );
    _scaleAnimation = Tween<double>(begin: 1.0, end: 0.95).animate(
      CurvedAnimation(parent: _animationController, curve: Curves.easeInOut),
    );
    
    // Add to cart animation
    _addToCartController = AnimationController(
      duration: const Duration(milliseconds: 600),
      vsync: this,
    );
    
    _addToCartScale = Tween<double>(begin: 1.0, end: 1.5).animate(
      CurvedAnimation(parent: _addToCartController, curve: Curves.elasticOut),
    );
    
    _addToCartColor = ColorTween(
      begin: const Color(0xFF1B5E20),
      end: const Color(0xFF4CAF50),
    ).animate(CurvedAnimation(parent: _addToCartController, curve: Curves.easeInOut));
  }

  @override
  void dispose() {
    _animationController.dispose();
    _addToCartController.dispose();
    super.dispose();
  }

  void _onTapDown(TapDownDetails details) {
    setState(() => _isPressed = true);
    _animationController.forward();
    HapticFeedback.lightImpact();
  }

  void _onTapUp(TapUpDetails details) {
    setState(() => _isPressed = false);
    _animationController.reverse();
    if (widget.onTap != null) {
      widget.onTap!();
    }
  }

  void _onTapCancel() {
    setState(() => _isPressed = false);
    _animationController.reverse();
  }

  @override
  Widget build(BuildContext context) {
    return AnimatedBuilder(
      animation: _scaleAnimation,
      builder: (context, child) {
        return Transform.scale(
          scale: _scaleAnimation.value,
          child: GestureDetector(
            onTapDown: _onTapDown,
            onTapUp: _onTapUp,
            onTapCancel: _onTapCancel,
            child: Container(
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(24),
                boxShadow: [
                  BoxShadow(
                    color: _isPressed
                        ? Colors.black.withOpacity(0.15)
                        : Colors.black.withOpacity(0.08),
                    blurRadius: _isPressed ? 25 : 20,
                    offset: Offset(0, _isPressed ? 6 : 4),
                  ),
                  // Additional glow for pressed state
                  if (_isPressed)
                    BoxShadow(
                      color: _getProductColor(
                        widget.product.imageUrl,
                      ).withOpacity(0.3),
                      blurRadius: 30,
                      offset: const Offset(0, 8),
                    ),
                ],
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Product Image with enhanced styling
                  Expanded(
                    flex: 3,
                    child: Container(
                      key: widget.imageKey,
                      width: double.infinity,
                      decoration: const BoxDecoration(
                        borderRadius: BorderRadius.only(
                          topLeft: Radius.circular(24),
                          topRight: Radius.circular(24),
                        ),
                      ),
                      child: Stack(
                        children: [
                          // Product Image
                          Positioned.fill(
                            child: ClipRRect(
                              borderRadius: const BorderRadius.only(
                                topLeft: Radius.circular(24),
                                topRight: Radius.circular(24),
                              ),
                              child: widget.product.imageUrl.isNotEmpty
                                  ? Container(
                                      color: Colors.white,
                                      child: Image.network(
                                        widget.product.imageUrl,
                                        fit: BoxFit.contain,
                                        loadingBuilder:
                                            (context, child, loadingProgress) {
                                              if (loadingProgress == null)
                                                return child;
                                              return SkeletonLoading(
                                                width: double.infinity,
                                                height: double.infinity,
                                                borderRadius: BorderRadius.circular(12),
                                              );
                                            },
                                        errorBuilder:
                                            (context, error, stackTrace) {
                                              return Container(
                                                decoration: BoxDecoration(
                                                  gradient: LinearGradient(
                                                    begin: Alignment.topLeft,
                                                    end: Alignment.bottomRight,
                                                    colors: [
                                                      _getProductColor(
                                                        widget.product.imageUrl,
                                                      ),
                                                      _getProductColor(
                                                        widget.product.imageUrl,
                                                      ).withValues(alpha: 0.7),
                                                    ],
                                                  ),
                                                ),
                                                child: Center(
                                                  child: Icon(
                                                    _getProductIcon(
                                                      widget.product.imageUrl,
                                                    ),
                                                    size: 32,
                                                    color: Colors.white
                                                        .withOpacity(0.8),
                                                  ),
                                                ),
                                              );
                                            },
                                      ),
                                    )
                                  : Container(
                                      decoration: BoxDecoration(
                                        gradient: LinearGradient(
                                          begin: Alignment.topLeft,
                                          end: Alignment.bottomRight,
                                          colors: [
                                            _getProductColor(
                                              widget.product.imageUrl,
                                            ),
                                            _getProductColor(
                                              widget.product.imageUrl,
                                            ).withOpacity(0.7),
                                          ],
                                        ),
                                      ),
                                      child: Center(
                                        child: TweenAnimationBuilder<double>(
                                          duration: const Duration(
                                            milliseconds: 600,
                                          ),
                                          tween: Tween(begin: 0.0, end: 1.0),
                                          builder: (context, value, child) {
                                            return Transform.scale(
                                              scale: 0.8 + (0.2 * value),
                                              child: Icon(
                                                _getProductIcon(
                                                  widget.product.imageUrl,
                                                ),
                                                size: 32,
                                                color: Colors.white.withOpacity(
                                                  0.8,
                                                ),
                                              ),
                                            );
                                          },
                                        ),
                                      ),
                                    ),
                            ),
                          ),
                          if (widget.product.hasDiscount)
                            Positioned(
                              top: 12,
                              left: 12,
                              child: Container(
                                padding: const EdgeInsets.symmetric(
                                  horizontal: 8,
                                  vertical: 4,
                                ),
                                decoration: BoxDecoration(
                                  gradient: const LinearGradient(
                                    colors: [
                                      Color(0xFFE53935),
                                      Color(0xFFFF5252),
                                    ],
                                  ),
                                  borderRadius: BorderRadius.circular(12),
                                  boxShadow: [
                                    BoxShadow(
                                      color: const Color(0xFFE53935).withOpacity(0.3),
                                      blurRadius: 8,
                                      offset: const Offset(0, 2),
                                    ),
                                  ],
                                ),
                                child: Text(
                                  '${((1 - (widget.product.effectivePrice / widget.product.price)) * 100).round()}%',
                                  style: const TextStyle(
                                    color: Colors.white,
                                    fontSize: 10,
                                    fontWeight: FontWeight.bold,
                                  ),
                                ),
                              ),
                            ),
                          Positioned(
                            top: 12,
                            right: 12,
                            child: Consumer<FavoritesServiceApi>(
                              builder: (context, favService, child) {
                                final isFavorite = favService.isFavorite(
                                  widget.product.id,
                                );
                                
                                return GestureDetector(
                                  onTap: () async {
                                    HapticFeedback.lightImpact();
                                    favService.toggleFavorite(widget.product.id, product: widget.product);
                                  },
                                  child: Container(
                                    padding: const EdgeInsets.all(6),
                                    decoration: BoxDecoration(
                                      color: isFavorite
                                          ? const Color(
                                              0xFFFF6B35,
                                            ).withOpacity(0.9)
                                          : Colors.white.withOpacity(0.9),
                                      borderRadius: BorderRadius.circular(12),
                                      boxShadow: [
                                        BoxShadow(
                                          color: Colors.black.withOpacity(
                                            0.1,
                                          ),
                                          blurRadius: 4,
                                          offset: const Offset(0, 2),
                                        ),
                                      ],
                                    ),
                                    child: Icon(
                                      isFavorite
                                          ? Icons.favorite
                                          : Icons.favorite_border,
                                      size: 16,
                                      color: isFavorite
                                          ? Colors.white
                                          : Colors.grey[600],
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

                  // Product Details with enhanced typography
                  Expanded(
                    flex: 2,
                    child: Padding(
                      padding: const EdgeInsets.all(8),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          // Product name
                          Text(
                            widget.product.name,
                            style: const TextStyle(
                              fontSize: 12,
                              fontWeight: FontWeight.w600,
                              color: Color(0xFF1C1B1F),
                              height: 1.1,
                            ),
                            maxLines: 2,
                            overflow: TextOverflow.ellipsis,
                          ),
                          const SizedBox(height: 4),

                          // Sales count with enhanced styling
                          Container(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 6,
                              vertical: 2,
                            ),
                            decoration: BoxDecoration(
                              color: const Color(
                                0xFF1B5E20,
                              ).withOpacity(0.1),
                              borderRadius: BorderRadius.circular(6),
                            ),
                            child: Text(
                              _formatSalesCount(widget.product.id),
                              style: const TextStyle(
                                fontSize: 9,
                                fontWeight: FontWeight.w500,
                                color: Color(0xFF1B5E20),
                              ),
                            ),
                          ),
                          const SizedBox(height: 6),

                          // Rating with enhanced styling
                          Container(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 6,
                              vertical: 2,
                            ),
                            decoration: BoxDecoration(
                              color: const Color(
                                0xFFFFC107,
                              ).withOpacity(0.1),
                              borderRadius: BorderRadius.circular(6),
                            ),
                            child: Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Icon(
                                  Icons.star_rounded,
                                  color: const Color(0xFFFFC107),
                                  size: 12,
                                ),
                                const SizedBox(width: 2),
                                Text(
                                  widget.product.rating.toString(),
                                  style: const TextStyle(
                                    fontSize: 10,
                                    fontWeight: FontWeight.w600,
                                    color: Color(0xFF49454F),
                                  ),
                                ),
                              ],
                            ),
                          ),
                          const Spacer(),

                          // Price section with enhanced design - NEW: Shows variant-based pricing
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    // NEW: Show range if product has multiple variants with different prices
                                    Text(
                                      widget.product.displayPrice,
                                      style: const TextStyle(
                                        fontSize: 13,
                                        fontWeight: FontWeight.bold,
                                        color: Color(
                                          0xFF1B5E20,
                                        ), // Dark green color
                                      ),
                                    ),
                                    // NEW: Show variant count if multiple variants
                                    if (widget.product.variants.isNotEmpty)
                                      Text(
                                        '${widget.product.variants.length} variant${widget.product.variants.length > 1 ? 's' : ''}',
                                        style: const TextStyle(
                                          fontSize: 9,
                                          color: Color(0xFF79747E),
                                        ),
                                      ),
                                  ],
                                ),
                              ),
                              Consumer<CartService>(
                                builder: (context, cartService, child) {
                                  return GestureDetector(
                                    onTap: () async {
                                      HapticFeedback.mediumImpact();
                                      
                                      // Play add to cart animation
                                      setState(() => _showAddToCartFeedback = true);
                                      _addToCartController.forward().then((_) {
                                        _addToCartController.reverse();
                                        setState(() => _showAddToCartFeedback = false);
                                      });
                                      
                                      if (widget.handleAddToCartInternally) {
                                        // Handle add to cart internally (default behavior)
                                        await cartService.addToCart(
                                          widget.product, 
                                          quantity: 1,
                                          variant: widget.product.cheapestVariant,
                                        );
                                        widget.onAddToCart?.call();

                                        if (mounted) {
                                          ScaffoldMessenger.of(context).showSnackBar(
                                            SnackBar(
                                              content: Row(
                                                children: [
                                                  Icon(
                                                    Icons.check_circle,
                                                    color: Colors.white,
                                                    size: 20,
                                                  ),
                                                  const SizedBox(width: 8),
                                                  Expanded(
                                                    child: Text(
                                                      '${widget.product.name} ditambahkan ke keranjang',
                                                      style: const TextStyle(
                                                        color: Colors.white,
                                                      ),
                                                    ),
                                                  ),
                                                ],
                                              ),
                                            backgroundColor: const Color(0xFF1B5E20),
                                            duration: const Duration(seconds: 2),
                                            behavior: SnackBarBehavior.floating,
                                            shape: RoundedRectangleBorder(
                                              borderRadius: BorderRadius.circular(8),
                                            ),
                                            action: SnackBarAction(
                                              label: 'VIEW CART',
                                              textColor: Colors.white,
                                              onPressed: () {
                                                Navigator.pushNamed(context, '/cart');
                                              },
                                            ),
                                          ),
                                        );
                                      } else {
                                        // Just call the callback, don't add to cart internally
                                        widget.onAddToCart?.call();
                                      }
                                    }, // Close onTap method
                                    child: AnimatedBuilder(
                                      animation: Listenable.merge([_addToCartScale, _addToCartColor]),
                                      builder: (context, child) {
                                        return Transform.scale(
                                          scale: _showAddToCartFeedback ? _addToCartScale.value : 1.0,
                                          child: Container(
                                            padding: const EdgeInsets.all(4),
                                            decoration: BoxDecoration(
                                              gradient: LinearGradient(
                                                colors: _showAddToCartFeedback
                                                    ? [
                                                        _addToCartColor.value ?? const Color(0xFF4CAF50),
                                                        const Color(0xFF4CAF50).withOpacity(0.7),
                                                      ]
                                                    : [
                                                        const Color(0xFF1B5E20).withOpacity(0.1),
                                                        const Color(0xFF1B5E20).withOpacity(0.05),
                                                      ],
                                              ),
                                              borderRadius: BorderRadius.circular(8),
                                              border: Border.all(
                                                color: _showAddToCartFeedback
                                                    ? const Color(0xFF4CAF50)
                                                    : const Color(0xFF1B5E20).withOpacity(0.2),
                                                width: 1,
                                              ),
                                              boxShadow: _showAddToCartFeedback
                                                  ? [
                                                      BoxShadow(
                                                        color: const Color(0xFF4CAF50).withOpacity(0.4),
                                                        blurRadius: 8,
                                                        offset: const Offset(0, 2),
                                                      ),
                                                    ]
                                                  : [],
                                            ),
                                            child: Icon(
                                              _showAddToCartFeedback ? Icons.check : Icons.add,
                                              color: _showAddToCartFeedback 
                                                  ? Colors.white
                                                  : const Color(0xFF1B5E20),
                                              size: 12,
                                            ),
                                          ),
                                        );
                                      },
                                    ),
                                  );
                                },
                              ),
                            ],
                          ),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ),
        );
      },
    );
  }

  Color _getProductColor(String imageUrl) {
    switch (imageUrl) {
      case 'headphones':
        return const Color(0xFFFFF3E0);
      case 'macbook':
        return const Color(0xFFF3E5F5);
      case 'chair':
        return const Color(0xFFE3F2FD);
      case 'smartwatch':
        return const Color(0xFFE8F5E8);
      case 'tshirt':
        return const Color(0xFFFCE4EC);
      case 'yoga':
        return const Color(0xFFE0F2F1);
      default:
        return const Color(0xFFF5F5F5);
    }
  }

  IconData _getProductIcon(String imageUrl) {
    switch (imageUrl) {
      case 'headphones':
        return Icons.headphones;
      case 'macbook':
        return Icons.laptop_mac;
      case 'chair':
        return Icons.chair;
      case 'smartwatch':
        return Icons.watch;
      case 'tshirt':
        return Icons.checkroom;
      case 'yoga':
        return Icons.fitness_center;
      default:
        return Icons.shopping_bag;
    }
  }

  String _formatSalesCount(int productId) {
    // Generate pseudo-random sales count based on product ID
    int salesCount = (productId * 123 + 500) % 10000;

    if (salesCount >= 1000) {
      double k = salesCount / 1000;
      return "${k.toStringAsFixed(1)}k+ sold";
    } else {
      return "$salesCount+ sold";
    }
  }
}

// Custom painter for background pattern
class CirclePatternPainter extends CustomPainter {
  final Color color;

  CirclePatternPainter({required this.color});

  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()
      ..color = color
      ..style = PaintingStyle.fill;

    final centerX = size.width / 2;
    final centerY = size.height / 2;

    // Draw decorative circles
    canvas.drawCircle(Offset(centerX + 20, centerY - 20), 15, paint);
    canvas.drawCircle(Offset(centerX - 25, centerY + 15), 10, paint);
    canvas.drawCircle(Offset(centerX + 30, centerY + 25), 8, paint);
    canvas.drawCircle(Offset(centerX - 15, centerY - 30), 12, paint);
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}
