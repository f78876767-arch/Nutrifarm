import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import '../widgets/product_card.dart' hide Container;
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

class StoreHomePage extends StatefulWidget {
  const StoreHomePage({super.key});

  @override
  _StoreHomePageState createState() => _StoreHomePageState();
}

class _StoreHomePageState extends State<StoreHomePage> {
  final List<String> _bannerImages = [
    'assets/images/banner-1.png',
    'assets/images/banner-2.png',
  ];
  int _currentBanner = 0;
  final PageController _pageController = PageController();
  Timer? _bannerTimer;
  String selectedCategory = 'All';
  final ScrollController _scrollController = ScrollController();
  
  // Product loading state
  List<Product> _products = [];
  List<String> _categories = ['All'];
  bool _isLoading = true;
  String? _errorMessage;
  @override
  void initState() {
    super.initState();
    _loadProducts();
    _scrollController.addListener(() {
      setState(() {
        // No background opacity needed
      });
    });
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
      setState(() {
        _isLoading = true;
        _errorMessage = null;
      });

      // Load products and categories
      final products = await ProductData.getProducts();
      final categories = await ProductData.getCategories();

      if (mounted) {
        setState(() {
          _products = products;
          _categories = categories;
          _isLoading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _isLoading = false;
          _errorMessage = 'Failed to load products: ${e.toString()}';
        });
      }
    }
  }

  @override
  void dispose() {
    _bannerTimer?.cancel();
    _pageController.dispose();
    _scrollController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    // Filter products based on selected category
    var filteredProducts = selectedCategory == 'All'
        ? _products
        : _products.where((product) =>
            product.categories.any((cat) => 
              cat.name.toLowerCase() == selectedCategory.toLowerCase()
            )
          ).toList();

    return Scaffold(
      backgroundColor: AppColors.background,
      body: _isLoading
          ? _buildLoadingState()
          : _errorMessage != null
              ? _buildErrorState()
              : CustomScrollView(
        controller: _scrollController,
        slivers: [
          SliverAppBar(
            pinned: true,
            backgroundColor: Colors.white,
            elevation: 0,
            centerTitle: false,
            title: SafeArea(
              bottom: false,
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 0, vertical: 0),
                child: Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 10,
                    vertical: 6,
                  ),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(12),
                    boxShadow: [
                      BoxShadow(
                        color: AppColors.primaryGreen.withOpacity(0.08),
                        blurRadius: 6,
                        offset: const Offset(0, 2),
                      ),
                    ],
                  ),
                  child: Row(
                    children: [
                      const Icon(
                        FeatherIcons.search,
                        color: Colors.black87,
                        size: 18,
                      ),
                      const SizedBox(width: 8),
                      Expanded(
                        child: GestureDetector(
                          onTap: () {
                            Navigator.push(
                              context,
                              PageTransitions.fadeWithScale(const SearchPage()),
                            );
                          },
                          child: const Text(
                            'Search for products...',
                            style: TextStyle(
                              color: Colors.black87,
                              fontSize: 13,
                              fontWeight: FontWeight.w400,
                            ),
                          ),
                        ),
                      ),
                      Container(
                        padding: const EdgeInsets.all(6),
                        decoration: BoxDecoration(
                          color: AppColors.primaryGreen.withOpacity(0.1),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: Icon(
                          FeatherIcons.sliders,
                          color: AppColors.primaryGreen,
                          size: 14,
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ),
            actions: [
              Stack(
                children: [
                  IconButton(
                    icon: const Icon(
                      FeatherIcons.shoppingBag,
                      color: Colors.black,
                    ),
                    onPressed: () {
                      Navigator.push(
                        context,
                        PageTransitions.slideFromRight(const CartPage()),
                      );
                    },
                  ),
                  Positioned(
                    right: 8,
                    top: 8,
                    child: Container(
                      padding: const EdgeInsets.all(2),
                      decoration: BoxDecoration(
                        color: Colors.red,
                        borderRadius: BorderRadius.circular(10),
                      ),
                      constraints: const BoxConstraints(
                        minWidth: 16,
                        minHeight: 16,
                      ),
                      child: Text(
                        '3',
                        style: GoogleFonts.nunitoSans(
                          color: Colors.white,
                          fontSize: 10,
                          fontWeight: FontWeight.w600,
                        ),
                        textAlign: TextAlign.center,
                      ),
                    ),
                  ),
                ],
              ),
              Stack(
                children: [
                  IconButton(
                    icon: const Icon(FeatherIcons.bell, color: Colors.black),
                    onPressed: () {
                      Navigator.push(
                        context,
                        PageTransitions.slideFromBottom(
                          const NotificationsPage(),
                        ),
                      );
                    },
                  ),
                  Positioned(
                    right: 12,
                    top: 12,
                    child: Container(
                      width: 8,
                      height: 8,
                      decoration: BoxDecoration(
                        color: Colors.red,
                        shape: BoxShape.circle,
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(width: 8),
            ],
          ),
          SliverToBoxAdapter(
            child: SafeArea(
              top: false,
              left: false,
              right: false,
              bottom: true,
              child: Padding(
                padding: EdgeInsets.zero,
                child: Column(
                  children: [
                    // 🖼️ Banner Carousel
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
                                onPageChanged: (i) {
                                  setState(() => _currentBanner = i);
                                },
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
                                    dotColor: Colors.white.withOpacity(0.7),
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

                    // 💸 DISCOUNT Section
                    Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 16),
                      child: Row(
                        children: [
                          Text(
                            'Lagi diskon nih!',
                            style: GoogleFonts.nunitoSans(
                              fontWeight: FontWeight.w700,
                              fontSize: 18,
                              color: AppColors.black,
                              letterSpacing: 1.2,
                            ),
                          ),
                          const SizedBox(width: 8),
                          Container(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 8,
                              vertical: 2,
                            ),
                            decoration: BoxDecoration(
                              color: AppColors.primaryGreen.withOpacity(0.08),
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: Text(
                              'Limited time',
                              style: GoogleFonts.nunitoSans(
                                fontWeight: FontWeight.w600,
                                fontSize: 12,
                                color: AppColors.primaryGreen,
                              ),
                            ),
                          ),
                          const Spacer(),
                          GestureDetector(
                            onTap: () {
                              Navigator.push(
                                context,
                                PageTransitions.zoomTransition(
                                  const CategoriesPage(),
                                ),
                              );
                            },
                            child: Text(
                              'See All',
                              style: GoogleFonts.nunitoSans(
                                fontWeight: FontWeight.w600,
                                fontSize: 14,
                                color: AppColors.primaryGreen,
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 12),
                    SizedBox(
                      height:
                          240, // Increased height to accommodate longer titles
                      child: ListView.separated(
                        scrollDirection: Axis.horizontal,
                        padding: const EdgeInsets.only(left: 16),
                        itemCount: filteredProducts.length > 8
                            ? 8
                            : filteredProducts.length,
                        separatorBuilder: (_, __) => const SizedBox(width: 12),
                        itemBuilder: (context, index) {
                          final product = filteredProducts[index];
                          return _buildSaleCard(product, index);
                        },
                      ),
                    ),
                    const SizedBox(height: 24),

                    // 🏷️ Category Chips
                    Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 16),
                      child: Row(
                        children: [
                          Text(
                            'Categories',
                            style: GoogleFonts.nunitoSans(
                              fontWeight: FontWeight.w700,
                              fontSize: 18,
                              color: Colors.black,
                            ),
                          ),
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
                                  fontWeight: isSelected
                                      ? FontWeight.w600
                                      : FontWeight.w500,
                                  color: isSelected
                                      ? AppColors.onPrimary
                                      : AppColors.onSurface,
                                ),
                              ),
                              onSelected: (_) {
                                setState(() => selectedCategory = category);
                              },
                              backgroundColor: AppColors.surfaceVariant,
                              selectedColor: AppColors.primaryGreen,
                              labelStyle: AppTextStyles.labelLarge.copyWith(
                                color: isSelected
                                    ? AppColors.onPrimary
                                    : AppColors.onSurface,
                                fontWeight: isSelected
                                    ? FontWeight.w600
                                    : FontWeight.w500,
                              ),
                              shape: RoundedRectangleBorder(
                                borderRadius: BorderRadius.circular(25),
                              ),
                              elevation: isSelected ? 4 : 0,
                              shadowColor: isSelected
                                  ? AppColors.primaryGreen.withOpacity(0.3)
                                  : null,
                            ),
                          );
                        }).toList(),
                      ),
                    ),
                    const SizedBox(height: 20),
                    // 🛍️ Products
                    Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 16),
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                'Fresh Products',
                                style: GoogleFonts.nunitoSans(
                                  fontWeight: FontWeight.w700,
                                  fontSize: 18,
                                  color: Colors.black,
                                ),
                              ),
                              Text(
                                selectedCategory == 'All'
                                    ? 'All fresh items'
                                    : selectedCategory,
                                style: GoogleFonts.nunitoSans(
                                  fontWeight: FontWeight.w500,
                                  fontSize: 14,
                                  color: AppColors.outline,
                                ),
                              ),
                            ],
                          ),
                          Container(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 12,
                              vertical: 6,
                            ),
                            decoration: BoxDecoration(
                              gradient: LinearGradient(
                                colors: [
                                  AppColors.primaryGreen.withOpacity(0.1),
                                  AppColors.accentGreen.withOpacity(0.1),
                                ],
                              ),
                              borderRadius: BorderRadius.circular(20),
                            ),
                            child: Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Container(
                                  width: 8,
                                  height: 8,
                                  decoration: BoxDecoration(
                                    color: AppColors.success,
                                    borderRadius: BorderRadius.circular(4),
                                  ),
                                ),
                                const SizedBox(width: 6),
                                Text(
                                  '${filteredProducts.length} items',
                                  style: GoogleFonts.nunitoSans(
                                    color: AppColors.primaryGreen,
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
                        gridDelegate:
                            const SliverGridDelegateWithFixedCrossAxisCount(
                              crossAxisCount: 2,
                              childAspectRatio: 0.65,
                              crossAxisSpacing: 16,
                              mainAxisSpacing: 16,
                            ),
                        itemCount: filteredProducts.length,
                        itemBuilder: (context, index) {
                          final product = filteredProducts[index];
                          return ProductCard(
                            product: product,
                            onTap: () {
                              Navigator.push(
                                context,
                                PageTransitions.elasticTransition(
                                  ProductDetailPage(product: product),
                                ),
                              );
                            },
                          );
                        },
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSaleCard(product, int index) {
    // Calculate different discount percentages for demo
    final discountPercentages = [20, 15, 30, 25, 10, 35, 40, 18];
    final discountPercent =
        discountPercentages[index % discountPercentages.length];

    return GestureDetector(
      onTap: () {
        Navigator.push(
          context,
          PageTransitions.flipTransition(ProductDetailPage(product: product)),
        );
        HapticFeedback.lightImpact();
      },
      child: Container(
        width: 165,
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(20),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.08),
              blurRadius: 20,
              offset: const Offset(0, 8),
            ),
          ],
          border: Border.all(
            color: AppColors.primaryGreen.withOpacity(0.1),
            width: 1.5,
          ),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Product Image with Sale Badge
            Stack(
              children: [
                Container(
                  height: 100,
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
                    borderRadius: const BorderRadius.only(
                      topLeft: Radius.circular(20),
                      topRight: Radius.circular(20),
                    ),
                  ),
                  child: Center(
                    child: Icon(
                      _getProductIcon(product.imageUrl),
                      size: 40,
                      color: Colors.black87,
                    ),
                  ),
                ),
                Positioned(
                  top: 8,
                  left: 8,
                  child: Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 8,
                      vertical: 4,
                    ),
                    decoration: BoxDecoration(
                      color: Colors.red,
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Text(
                      '$discountPercent% OFF',
                      style: GoogleFonts.nunitoSans(
                        color: Colors.white,
                        fontWeight: FontWeight.w700,
                        fontSize: 5,
                      ),
                    ),
                  ),
                ),
                Positioned(
                  top: 8,
                  right: 8,
                  child: Container(
                    padding: const EdgeInsets.all(6),
                    decoration: BoxDecoration(
                      color: Colors.white.withOpacity(0.9),
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: Icon(
                      Icons.favorite_border,
                      color: AppColors.primaryGreen,
                      size: 16,
                    ),
                  ),
                ),
              ],
            ),

            // Product Details
            Expanded(
              child: Padding(
                padding: const EdgeInsets.all(12),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Title Section - Fixed height container
                    SizedBox(
                      height: 32, // Fixed height for 2 lines of text
                      child: Text(
                        product.name,
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                        style: GoogleFonts.nunitoSans(
                          fontWeight: FontWeight.w700,
                          fontSize: 13,
                          height: 1.2,
                        ),
                      ),
                    ),
                    const SizedBox(height: 8),

                    // Price Row - Consistent positioning
                    Row(
                      children: [
                        // Sale Price
                        Text(
                          '\u20B9${(product.price * (100 - discountPercent) / 100).toInt()}',
                          style: GoogleFonts.nunitoSans(
                            fontWeight: FontWeight.w800,
                            fontSize: 15,
                            color: AppColors.primaryGreen,
                          ),
                        ),
                        const SizedBox(width: 6),
                        // Original Price
                        Text(
                          '\u20B9${product.price}',
                          style: GoogleFonts.nunitoSans(
                            fontWeight: FontWeight.w600,
                            fontSize: 12,
                            color: Colors.grey,
                            decoration: TextDecoration.lineThrough,
                          ),
                        ),
                      ],
                    ),
                    const Spacer(), // This pushes the sold counter to the bottom
                    // Quantity Sold Display
                    Container(
                      width: double.infinity,
                      height: 34,
                      decoration: BoxDecoration(
                        color: AppColors.primaryGreen.withOpacity(0.1),
                        borderRadius: BorderRadius.circular(8),
                        border: Border.all(
                          color: AppColors.primaryGreen.withOpacity(0.3),
                          width: 1,
                        ),
                      ),
                      child: Center(
                        child: Row(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Icon(
                              Icons.local_fire_department,
                              color: AppColors.red,
                              size: 16,
                            ),
                            const SizedBox(width: 6),
                            Text(
                              '${_getQuantitySold(index)} sold',
                              style: GoogleFonts.nunitoSans(
                                color: AppColors.primaryGreen,
                                fontWeight: FontWeight.w700,
                                fontSize: 12,
                              ),
                            ),
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

  int _getQuantitySold(int index) {
    // Generate different quantities sold for demo purposes
    final quantitiesSold = [147, 89, 203, 156, 78, 245, 312, 134];
    return quantitiesSold[index % quantitiesSold.length];
  }

  // ...existing code...

  Widget _buildActionCard(
    String title,
    IconData icon,
    Color color, {
    Color textColor = Colors.black87,
  }) {
    return GestureDetector(
      onTap: () => HapticFeedback.lightImpact(),
      child: Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: color,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: color.withOpacity(0.2), width: 1),
        ),
        child: Column(
          children: [
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: color.withOpacity(0.15),
                borderRadius: BorderRadius.circular(12),
              ),
              child: Icon(icon, color: Colors.white, size: 24),
            ),
            const SizedBox(height: 8),
            Text(
              title,
              style: GoogleFonts.nunitoSans(
                color: textColor,
                fontWeight: FontWeight.w600,
                fontSize: 13,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildLoadingState() {
    return const Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          CircularProgressIndicator(),
          SizedBox(height: 16),
          Text('Loading products...'),
        ],
      ),
    );
  }

  Widget _buildErrorState() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(
            Icons.error_outline,
            size: 64,
            color: Colors.grey[400],
          ),
          const SizedBox(height: 16),
          Text(
            'Unable to load products',
            style: GoogleFonts.nunitoSans(
              fontSize: 18,
              fontWeight: FontWeight.w600,
              color: Colors.grey[600],
            ),
          ),
          const SizedBox(height: 8),
          Text(
            _errorMessage ?? 'Please check your internet connection',
            style: GoogleFonts.nunitoSans(
              fontSize: 14,
              color: Colors.grey[500],
            ),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 24),
          ElevatedButton.icon(
            onPressed: _loadProducts,
            icon: const Icon(Icons.refresh),
            label: const Text('Try Again'),
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.primaryGreen,
              foregroundColor: Colors.white,
              padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
            ),
          ),
          const SizedBox(height: 16),
          Text(
            'Note: Make sure your backend server is running',
            style: GoogleFonts.nunitoSans(
              fontSize: 12,
              color: Colors.orange[600],
              fontStyle: FontStyle.italic,
            ),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }
}
