import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import '../widgets/product_card.dart' hide Container;
import '../data/product_data.dart';
import '../theme/app_theme.dart';
import '../pages/product_detail_page.dart';
import '../utils/page_transitions.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:flutter_feather_icons/flutter_feather_icons.dart';

class CategoryItem {
  final String name;
  final IconData icon;
  final Color color;
  final int productCount;

  CategoryItem({
    required this.name,
    required this.icon,
    required this.color,
    required this.productCount,
  });
}

class CategoriesPage extends StatefulWidget {
  const CategoriesPage({super.key});

  @override
  _CategoriesPageState createState() => _CategoriesPageState();
}

class _CategoriesPageState extends State<CategoriesPage> {
  String selectedCategory = 'All';

  final List<CategoryItem> _categories = [
    CategoryItem(
      name: 'All',
      icon: FeatherIcons.grid,
      color: AppColors.primaryGreen,
      productCount: ProductData.products.length,
    ),
    CategoryItem(
      name: 'Oils',
      icon: Icons.water_drop,
      color: Colors.orange,
      productCount: ProductData.products.where((p) => p.category == 'Oils').length,
    ),
    CategoryItem(
      name: 'Honey',
      icon: Icons.local_florist,
      color: Colors.amber,
      productCount: ProductData.products.where((p) => p.category == 'Honey').length,
    ),
    CategoryItem(
      name: 'Vinegar',
      icon: Icons.local_drink,
      color: Colors.red,
      productCount: ProductData.products.where((p) => p.category == 'Vinegar').length,
    ),
    CategoryItem(
      name: 'Spices',
      icon: Icons.eco,
      color: Colors.green,
      productCount: ProductData.products.where((p) => p.category == 'Spices').length,
    ),
    CategoryItem(
      name: 'Herbs',
      icon: Icons.grass,
      color: Colors.teal,
      productCount: ProductData.products.where((p) => p.category == 'Herbs').length,
    ),
    CategoryItem(
      name: 'Supplements',
      icon: Icons.medical_services,
      color: Colors.purple,
      productCount: ProductData.products.where((p) => p.category == 'Supplements').length,
    ),
    CategoryItem(
      name: 'Tea',
      icon: Icons.local_cafe,
      color: Colors.brown,
      productCount: ProductData.products.where((p) => p.category == 'Tea').length,
    ),
  ];

  @override
  Widget build(BuildContext context) {
    final filteredProducts = selectedCategory == 'All'
        ? ProductData.products
        : ProductData.products.where((product) => product.category == selectedCategory).toList();

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0,
        centerTitle: true,
        leading: IconButton(
          icon: const Icon(FeatherIcons.arrowLeft, color: Colors.black),
          onPressed: () => Navigator.pop(context),
        ),
        title: Text(
          'Categories',
          style: GoogleFonts.nunitoSans(
            fontWeight: FontWeight.w700,
            fontSize: 18,
            color: Colors.black,
          ),
        ),
      ),
      body: Column(
        children: [
          // Category Grid
          Container(
            padding: const EdgeInsets.all(16),
            color: Colors.white,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Browse by Category',
                  style: GoogleFonts.nunitoSans(
                    fontWeight: FontWeight.w700,
                    fontSize: 16,
                    color: Colors.black,
                  ),
                ),
                const SizedBox(height: 16),
                SizedBox(
                  height: 120,
                  child: ListView.separated(
                    scrollDirection: Axis.horizontal,
                    itemCount: _categories.length,
                    separatorBuilder: (_, __) => const SizedBox(width: 12),
                    itemBuilder: (context, index) {
                      final category = _categories[index];
                      final isSelected = selectedCategory == category.name;
                      
                      return GestureDetector(
                        onTap: () {
                          setState(() {
                            selectedCategory = category.name;
                          });
                          HapticFeedback.lightImpact();
                        },
                        child: Container(
                          width: 90,
                          padding: const EdgeInsets.all(12),
                          decoration: BoxDecoration(
                            color: isSelected ? category.color : Colors.grey.shade100,
                            borderRadius: BorderRadius.circular(16),
                            border: isSelected 
                                ? Border.all(color: category.color, width: 2)
                                : null,
                          ),
                          child: Column(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Container(
                                width: 40,
                                height: 40,
                                decoration: BoxDecoration(
                                  color: isSelected 
                                      ? Colors.white.withOpacity(0.2)
                                      : category.color.withOpacity(0.1),
                                  borderRadius: BorderRadius.circular(12),
                                ),
                                child: Icon(
                                  category.icon,
                                  size: 20,
                                  color: isSelected ? Colors.white : category.color,
                                ),
                              ),
                              const SizedBox(height: 8),
                              Text(
                                category.name,
                                style: GoogleFonts.nunitoSans(
                                  fontWeight: FontWeight.w600,
                                  fontSize: 12,
                                  color: isSelected ? Colors.white : Colors.black87,
                                ),
                                textAlign: TextAlign.center,
                              ),
                              Text(
                                '${category.productCount}',
                                style: GoogleFonts.nunitoSans(
                                  fontWeight: FontWeight.w500,
                                  fontSize: 10,
                                  color: isSelected ? Colors.white.withOpacity(0.8) : Colors.grey,
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
          
          // Products Section
          Expanded(
            child: Container(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            selectedCategory == 'All' ? 'All Products' : selectedCategory,
                            style: GoogleFonts.nunitoSans(
                              fontWeight: FontWeight.w700,
                              fontSize: 18,
                              color: Colors.black,
                            ),
                          ),
                          Text(
                            '${filteredProducts.length} items available',
                            style: GoogleFonts.nunitoSans(
                              fontWeight: FontWeight.w500,
                              fontSize: 14,
                              color: AppColors.outline,
                            ),
                          ),
                        ],
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                        decoration: BoxDecoration(
                          color: AppColors.primaryGreen.withOpacity(0.1),
                          borderRadius: BorderRadius.circular(20),
                        ),
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Icon(
                              FeatherIcons.filter,
                              size: 14,
                              color: AppColors.primaryGreen,
                            ),
                            const SizedBox(width: 4),
                            Text(
                              'Filter',
                              style: GoogleFonts.nunitoSans(
                                color: AppColors.primaryGreen,
                                fontWeight: FontWeight.w600,
                                fontSize: 12,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 16),
                  
                  // Products Grid
                  Expanded(
                    child: filteredProducts.isEmpty
                        ? _buildEmptyState()
                        : GridView.builder(
                            gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
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
                                    PageTransitions.flipTransition(ProductDetailPage(product: product)),
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
        ],
      ),
    );
  }

  Widget _buildEmptyState() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Container(
            width: 80,
            height: 80,
            decoration: BoxDecoration(
              color: Colors.grey.shade100,
              shape: BoxShape.circle,
            ),
            child: Icon(
              FeatherIcons.package,
              size: 40,
              color: Colors.grey.shade400,
            ),
          ),
          const SizedBox(height: 16),
          Text(
            'No products found',
            style: GoogleFonts.nunitoSans(
              fontSize: 18,
              fontWeight: FontWeight.w600,
              color: Colors.grey.shade600,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            'Try selecting a different category',
            style: GoogleFonts.nunitoSans(
              fontSize: 14,
              color: Colors.grey.shade500,
            ),
          ),
        ],
      ),
    );
  }
}
