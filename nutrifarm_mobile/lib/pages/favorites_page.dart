import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:provider/provider.dart';
import '../widgets/product_card.dart' as custom_widgets;
import '../widgets/skeleton_loading.dart';
import '../models/product.dart';
import '../theme/app_theme.dart';
import '../pages/product_detail_page.dart';
import '../widgets/custom_bottom_nav_bar.dart';
import '../services/favorites_service_api.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:flutter_feather_icons/flutter_feather_icons.dart';
import 'package:lottie/lottie.dart';

class FavoritesPage extends StatefulWidget {
  const FavoritesPage({super.key});

  @override
  _FavoritesPageState createState() => _FavoritesPageState();
}

class _FavoritesPageState extends State<FavoritesPage> {
  bool _hasInitialized = false;

  @override
  void initState() {
    super.initState();
    // Initialize favorites service in the next frame to avoid setState during build
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _loadFavoriteProducts();
    });
  }

  bool _canPop(BuildContext context) => ModalRoute.of(context)?.canPop == true;

  Future<void> _loadFavoriteProducts() async {
    if (!mounted) return;
    
    try {
      final favoritesService = Provider.of<FavoritesServiceApi>(context, listen: false);
      await favoritesService.loadFavorites();
      
      if (mounted) {
        setState(() {
          _hasInitialized = true;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _hasInitialized = true;
        });
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Failed to load favorite products: $e'),
            backgroundColor: Colors.red,
          ),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final isDark = theme.brightness == Brightness.dark;
    final overlay = isDark ? SystemUiOverlayStyle.light : SystemUiOverlayStyle.dark;

    return Scaffold(
      backgroundColor: theme.scaffoldBackgroundColor,
      appBar: AppBar(
        backgroundColor: theme.colorScheme.surface,
        elevation: 0,
        centerTitle: true,
        systemOverlayStyle: overlay,
        leading: _canPop(context)
            ? IconButton(
                icon: Icon(FeatherIcons.arrowLeft, color: theme.colorScheme.onSurface),
                onPressed: () => Navigator.pop(context),
              )
            : null,
        title: Consumer<FavoritesServiceApi>(
          builder: (context, favoritesService, child) {
            final theme = Theme.of(context);
            final isDark = theme.brightness == Brightness.dark;
            return Column(
              children: [
                Text(
                  'Favorites',
                  style: GoogleFonts.nunitoSans(
                    fontWeight: FontWeight.w700,
                    fontSize: 18,
                    color: theme.colorScheme.onSurface,
                  ),
                ),
                Text(
                  '${favoritesService.favoriteProducts.length} items',
                  style: GoogleFonts.nunitoSans(
                    fontWeight: FontWeight.w500,
                    fontSize: 12,
                    color: isDark ? Colors.white : AppColors.primaryGreen,
                  ),
                ),
              ],
            );
          },
        ),
        actions: [
          Consumer<FavoritesServiceApi>(
            builder: (context, favoritesService, child) {
              if (favoritesService.favoriteProducts.isNotEmpty) {
                return IconButton(
                  icon: Icon(FeatherIcons.refreshCw, color: theme.colorScheme.onSurface),
                  onPressed: _loadFavoriteProducts,
                );
              }
              return const SizedBox.shrink();
            },
          ),
        ],
      ),
      body: Consumer<FavoritesServiceApi>(
        builder: (context, favoritesService, child) {
          if (!_hasInitialized || favoritesService.isLoading) {
            return _buildLoadingState();
          }
          
          final favoriteProducts = favoritesService.favoriteProducts;
          return favoriteProducts.isEmpty 
              ? _buildEmptyState(theme)
              : _buildFavoritesList(favoriteProducts);
        },
      ),
      // Show bottom nav only when this page is opened standalone (to avoid duplicate with MainNavigator)
      bottomNavigationBar: _canPop(context)
          ? CustomBottomNavBar(
              currentIndex: 1,
              onTap: (index) {
                // Only handle Home here; other indexes are handled by the nav bar fallback routing
                if (index == 0) {
                  Navigator.pushReplacementNamed(context, '/home');
                }
                HapticFeedback.lightImpact();
              },
            )
          : null,
    );
  }

  Widget _buildLoadingState() {
    return Padding(
      padding: const EdgeInsets.all(16.0),
      child: GridProductSkeleton(itemCount: 6),
    );
  }

  Widget _buildEmptyState(ThemeData theme) {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          SizedBox(
            width: 220,
            height: 220,
            child: Lottie.asset('assets/images/empty-ghost.json', repeat: true),
          ),
          const SizedBox(height: 24),
          Text(
            'No favorites yet',
            style: GoogleFonts.nunitoSans(
              fontSize: 20,
              fontWeight: FontWeight.w700,
              color: theme.colorScheme.onSurface,
            ),
          ),
          const SizedBox(height: 8),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 32),
            child: Text(
              'Start adding products to your wishlist by tapping the heart icon',
              style: GoogleFonts.nunitoSans(
                fontSize: 14,
                color: theme.colorScheme.onSurfaceVariant,
              ),
              textAlign: TextAlign.center,
            ),
          ),
          const SizedBox(height: 32),
          ElevatedButton(
            onPressed: () {
              Navigator.pop(context);
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.primaryGreen,
              foregroundColor: Colors.white,
              padding: const EdgeInsets.symmetric(horizontal: 32, vertical: 12),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(12),
              ),
            ),
            child: Text(
              'Browse Products',
              style: GoogleFonts.nunitoSans(
                fontWeight: FontWeight.w600,
                fontSize: 16,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildFavoritesList(List<Product> favoriteProducts) {
    return RefreshIndicator(
      onRefresh: _loadFavoriteProducts,
      child: GridView.builder(
        padding: const EdgeInsets.all(16),
        physics: const AlwaysScrollableScrollPhysics(),
        gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
          crossAxisCount: 2,
          childAspectRatio: 0.65,
          crossAxisSpacing: 16,
          mainAxisSpacing: 16,
        ),
        itemCount: favoriteProducts.length,
        itemBuilder: (context, index) {
          final product = favoriteProducts[index];
          return custom_widgets.ProductCard(
            product: product,
            onTap: () {
              Navigator.push(
                context,
                MaterialPageRoute(
                  builder: (_) => ProductDetailPage(product: product),
                ),
              );
            },
          );
        },
      ),
    );
  }
}
