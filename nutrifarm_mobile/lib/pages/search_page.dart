import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../widgets/product_card.dart';
import '../widgets/skeleton_loading.dart';
import '../data/product_data.dart';
import '../theme/app_theme.dart';
import '../pages/product_detail_page.dart';
import '../services/search_service.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:flutter_feather_icons/flutter_feather_icons.dart';

class SearchPage extends StatefulWidget {
  const SearchPage({super.key});

  @override
  _SearchPageState createState() => _SearchPageState();
}

class _SearchPageState extends State<SearchPage> {
  final TextEditingController _searchController = TextEditingController();
  List<dynamic> _searchResults = [];
  List<String> _suggestions = [];
  bool _showSuggestions = false;
  bool _isSearching = false;

  @override
  void initState() {
    super.initState();
    _searchController.addListener(() {
      _onSearchChanged(_searchController.text);
    });
  }

  @override
  void dispose() {
    _searchController.removeListener(() {
      _onSearchChanged(_searchController.text);
    });
    _searchController.dispose();
    super.dispose();
  }

  void _onSearchChanged(String query) async {
    final searchService = Provider.of<SearchService>(context, listen: false);

    setState(() {
      _isSearching = query.isNotEmpty;
    });

    if (query.isNotEmpty) {
      final suggestions = await searchService.getSuggestions(query);
      setState(() {
        _suggestions = suggestions;
        _showSuggestions =
            query.isNotEmpty &&
            query.length < 3; // Show suggestions for 1-2 chars
      });

      final searchResults = await searchService.search(query);
      setState(() {
        _searchResults = searchResults;
        _isSearching = false;
      });
    } else {
      setState(() {
        _suggestions.clear();
        _showSuggestions = false;
        _searchResults.clear();
        _isSearching = false;
      });
      searchService.clearSearch();
    }
  }

  void _performSearch(String query) async {
    setState(() {
      _isSearching = true;
    });

    final searchService = Provider.of<SearchService>(context, listen: false);
    _searchController.text = query;
    // Use the method that adds to search history
    final searchResults = await searchService.performSearchAndAddToHistory(
      query,
    );
    setState(() {
      _searchResults = searchResults;
      _showSuggestions = false;
      _isSearching = false;
    });
    // Dismiss keyboard
    FocusScope.of(context).unfocus();
  }

  Future<List<String>> _getPopularSearches() async {
    try {
      // Get popular searches based on actual product names and categories from API
      final products = await ProductData.getProducts();
      final popularProducts = products
          .where(
            (p) =>
                p.discountPrice != null ||
                p.variants.isNotEmpty ||
                p.rating > 4.5,
          )
          .toList();

      popularProducts.sort((a, b) => b.rating.compareTo(a.rating));

      final popularSearches = <String>[];

      // Add top-rated product names (shortened)
      for (final product in popularProducts.take(3)) {
        final words = product.name.split(' ');
        if (words.length > 2) {
          popularSearches.add('${words[0]} ${words[1]}');
        } else {
          popularSearches.add(product.name);
        }
      }

      // Add categories with high-value products
      final categoryValues = <String, double>{};
      for (final product in products) {
        for (final category in product.categories) {
          categoryValues[category.name] =
              (categoryValues[category.name] ?? 0) + product.price;
        }
      }

      final topCategories = categoryValues.entries.toList()
        ..sort((a, b) => b.value.compareTo(a.value));

      for (final entry in topCategories.take(3)) {
        if (!popularSearches.contains(entry.key)) {
          popularSearches.add(entry.key);
        }
      }

      return popularSearches.take(6).toList();
    } catch (e) {
      // Return fallback popular searches if API fails
      return [
        'Honey',
        'Organic',
        'Natural',
        'Health',
        'Beverages',
        'Supplements',
      ];
    }
  }

  @override
  Widget build(BuildContext context) {
    return Consumer<SearchService>(
      builder: (context, searchService, child) {
        return Scaffold(
          backgroundColor: AppColors.background,
          appBar: AppBar(
            backgroundColor: AppColors.surface,
            elevation: 0,
            scrolledUnderElevation: 1,
            leading: IconButton(
              icon: const Icon(
                FeatherIcons.arrowLeft,
                color: AppColors.onSurface,
              ),
              onPressed: () => Navigator.pop(context),
            ),
            title: Container(
              height: 44,
              decoration: BoxDecoration(
                color: AppColors.surfaceVariant,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: AppColors.outline.withOpacity(0.2)),
              ),
              child: TextField(
                controller: _searchController,
                autofocus: true,
                decoration: InputDecoration(
                  hintText: 'Search products, categories...',
                  hintStyle: GoogleFonts.nunitoSans(
                    color: AppColors.onSurfaceVariant,
                    fontSize: 14,
                  ),
                  border: InputBorder.none,
                  contentPadding: const EdgeInsets.symmetric(
                    horizontal: 16,
                    vertical: 12,
                  ),
                  prefixIcon: Icon(
                    FeatherIcons.search,
                    size: 18,
                    color: AppColors.onSurfaceVariant,
                  ),
                  suffixIcon: _searchController.text.isNotEmpty
                      ? IconButton(
                          icon: Icon(
                            Icons.clear,
                            size: 18,
                            color: AppColors.onSurfaceVariant,
                          ),
                          onPressed: () {
                            _searchController.clear();
                            searchService.clearSearch();
                            setState(() {
                              _searchResults.clear();
                              _suggestions.clear();
                              _showSuggestions = false;
                            });
                          },
                        )
                      : null,
                ),
                style: GoogleFonts.nunitoSans(
                  fontSize: 14,
                  color: AppColors.onSurface,
                ),
                onSubmitted: (query) {
                  if (query.trim().isNotEmpty) {
                    _performSearch(query.trim());
                  }
                },
              ),
            ),
          ),
          body: _buildBody(searchService),
        );
      },
    );
  }

  Widget _buildBody(SearchService searchService) {
    if (_isSearching) {
      return _buildSearchLoading();
    }

    if (_showSuggestions && _suggestions.isNotEmpty) {
      return _buildSuggestionsList();
    }

    if (_searchController.text.isNotEmpty && _searchResults.isNotEmpty) {
      return _buildSearchResults();
    }

    if (_searchController.text.isNotEmpty && _searchResults.isEmpty) {
      return _buildNoResults();
    }

    return _buildSearchSuggestions(searchService);
  }

  Widget _buildSearchLoading() {
    return Padding(
      padding: const EdgeInsets.all(16.0),
      child: Column(
        children: [
          Row(
            children: [
              Text(
                'Searching...',
                style: GoogleFonts.nunitoSans(
                  fontSize: 16,
                  fontWeight: FontWeight.w600,
                  color: AppColors.onSurface,
                ),
              ),
              const SizedBox(width: 12),
              SizedBox(
                width: 16,
                height: 16,
                child: CircularProgressIndicator(
                  strokeWidth: 2,
                  color: AppColors.primaryGreen,
                ),
              ),
            ],
          ),
          const SizedBox(height: 20),
          Expanded(child: SearchResultSkeleton(itemCount: 8)),
        ],
      ),
    );
  }

  Widget _buildSearchSuggestions(SearchService searchService) {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Recent Searches
          if (searchService.searchHistory.isNotEmpty) ...[
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  'Recent Searches',
                  style: GoogleFonts.nunitoSans(
                    fontSize: 18,
                    fontWeight: FontWeight.w700,
                    color: AppColors.onSurface,
                  ),
                ),
                TextButton(
                  onPressed: () {
                    searchService.clearSearchHistory();
                    setState(() {}); // Refresh UI
                  },
                  child: Text(
                    'Clear All',
                    style: GoogleFonts.nunitoSans(
                      fontSize: 14,
                      color: AppColors.primaryGreen,
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),
            Wrap(
              spacing: 8,
              runSpacing: 8,
              children: searchService.searchHistory.take(6).map((search) {
                return GestureDetector(
                  onTap: () {
                    _searchController.text = search;
                    _performSearch(search);
                  },
                  child: Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 16,
                      vertical: 10,
                    ),
                    decoration: BoxDecoration(
                      color: AppColors.surfaceVariant,
                      borderRadius: BorderRadius.circular(20),
                      border: Border.all(
                        color: AppColors.outline.withOpacity(0.2),
                      ),
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(
                          FeatherIcons.clock,
                          size: 14,
                          color: AppColors.onSurfaceVariant,
                        ),
                        const SizedBox(width: 6),
                        Text(
                          search,
                          style: GoogleFonts.nunitoSans(
                            fontSize: 13,
                            color: AppColors.onSurface,
                            fontWeight: FontWeight.w500,
                          ),
                        ),
                      ],
                    ),
                  ),
                );
              }).toList(),
            ),
            const SizedBox(height: 28),
          ],

          // Popular Searches
          Text(
            'Popular Searches',
            style: GoogleFonts.nunitoSans(
              fontSize: 18,
              fontWeight: FontWeight.w700,
              color: AppColors.onSurface,
            ),
          ),
          const SizedBox(height: 12),
          FutureBuilder<List<String>>(
            future: Future.value(_getPopularSearches()),
            builder: (context, snapshot) {
              if (snapshot.connectionState == ConnectionState.waiting) {
                return Center(
                  child: CircularProgressIndicator(
                    color: AppColors.primaryGreen,
                    strokeWidth: 2,
                  ),
                );
              }

              final popularSearches = snapshot.data ?? [];
              return Wrap(
                spacing: 8,
                runSpacing: 8,
                children: popularSearches.map((search) {
                  return GestureDetector(
                    onTap: () {
                      _searchController.text = search;
                      _performSearch(search);
                    },
                    child: Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 16,
                        vertical: 10,
                      ),
                      decoration: BoxDecoration(
                        color: AppColors.primaryGreen.withOpacity(0.1),
                        borderRadius: BorderRadius.circular(20),
                        border: Border.all(
                          color: AppColors.primaryGreen.withOpacity(0.3),
                        ),
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Icon(
                            FeatherIcons.trendingUp,
                            size: 14,
                            color: AppColors.primaryGreen,
                          ),
                          const SizedBox(width: 6),
                          Text(
                            search,
                            style: GoogleFonts.nunitoSans(
                              fontSize: 13,
                              color: AppColors.primaryGreen,
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                        ],
                      ),
                    ),
                  );
                }).toList(),
              );
            },
          ),
          const SizedBox(height: 28),

          // Categories
          Text(
            'Browse Categories',
            style: GoogleFonts.nunitoSans(
              fontSize: 18,
              fontWeight: FontWeight.w700,
              color: AppColors.onSurface,
            ),
          ),
          const SizedBox(height: 16),
          GridView.count(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            crossAxisCount: 2,
            mainAxisSpacing: 12,
            crossAxisSpacing: 12,
            childAspectRatio: 3,
            children: [
              _buildCategoryCard('Honey Products', FeatherIcons.droplet, () {
                _searchController.text = 'honey';
                _performSearch('honey');
              }),
              _buildCategoryCard('Health Products', FeatherIcons.heart, () {
                _searchController.text = 'health';
                _performSearch('health');
              }),
              _buildCategoryCard('Essential Oils', FeatherIcons.sun, () {
                _searchController.text = 'oil';
                _performSearch('oil');
              }),
              _buildCategoryCard('Natural Foods', FeatherIcons.coffee, () {
                _searchController.text = 'natural';
                _performSearch('natural');
              }),
            ],
          ),
          const SizedBox(height: 100), // Bottom padding for navigation bar
        ],
      ),
    );
  }

  Widget _buildCategoryCard(String title, IconData icon, VoidCallback onTap) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
        decoration: BoxDecoration(
          color: AppColors.surface,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: AppColors.outline.withOpacity(0.2)),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.04),
              offset: const Offset(0, 2),
              blurRadius: 8,
            ),
          ],
        ),
        child: Row(
          children: [
            Icon(icon, size: 18, color: AppColors.primaryGreen),
            const SizedBox(width: 10),
            Expanded(
              child: Text(
                title,
                style: GoogleFonts.nunitoSans(
                  fontSize: 13,
                  fontWeight: FontWeight.w600,
                  color: AppColors.onSurface,
                ),
                overflow: TextOverflow.ellipsis,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildSuggestionsList() {
    return Container(
      decoration: BoxDecoration(
        color: AppColors.surface,
        border: Border(
          top: BorderSide(color: AppColors.outline.withOpacity(0.2)),
        ),
      ),
      child: ListView.separated(
        padding: EdgeInsets.zero,
        itemCount: _suggestions.length,
        separatorBuilder: (context, index) =>
            Divider(height: 1, color: AppColors.outline.withOpacity(0.1)),
        itemBuilder: (context, index) {
          final suggestion = _suggestions[index];
          return ListTile(
            dense: true,
            contentPadding: const EdgeInsets.symmetric(
              horizontal: 20,
              vertical: 4,
            ),
            leading: Icon(
              FeatherIcons.search,
              size: 16,
              color: AppColors.onSurfaceVariant,
            ),
            title: Text(
              suggestion,
              style: GoogleFonts.nunitoSans(
                fontSize: 14,
                color: AppColors.onSurface,
                fontWeight: FontWeight.w500,
              ),
            ),
            trailing: Icon(
              FeatherIcons.arrowUpLeft,
              size: 14,
              color: AppColors.onSurfaceVariant,
            ),
            onTap: () {
              _searchController.text = suggestion;
              _performSearch(suggestion);
            },
          );
        },
      ),
    );
  }

  Widget _buildNoResults() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(40),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              padding: const EdgeInsets.all(24),
              decoration: BoxDecoration(
                color: AppColors.surfaceVariant,
                borderRadius: BorderRadius.circular(50),
              ),
              child: Icon(
                FeatherIcons.search,
                size: 48,
                color: AppColors.onSurfaceVariant,
              ),
            ),
            const SizedBox(height: 24),
            Text(
              'No Results Found',
              style: GoogleFonts.nunitoSans(
                fontSize: 20,
                fontWeight: FontWeight.w700,
                color: AppColors.onSurface,
              ),
            ),
            const SizedBox(height: 12),
            Text(
              'We couldn\'t find any products matching "${_searchController.text}". Try different keywords or browse our categories.',
              textAlign: TextAlign.center,
              style: GoogleFonts.nunitoSans(
                fontSize: 14,
                color: AppColors.onSurfaceVariant,
                height: 1.5,
              ),
            ),
            const SizedBox(height: 24),
            ElevatedButton(
              onPressed: () {
                _searchController.clear();
                setState(() {
                  _searchResults.clear();
                  _suggestions.clear();
                  _showSuggestions = false;
                });
              },
              style: ElevatedButton.styleFrom(
                backgroundColor: AppColors.primaryGreen,
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(
                  horizontal: 24,
                  vertical: 12,
                ),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
              ),
              child: Text(
                'Clear Search',
                style: GoogleFonts.nunitoSans(
                  fontSize: 14,
                  fontWeight: FontWeight.w600,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildSearchResults() {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 20),
      child: Column(
        children: [
          // Results header
          Container(
            padding: const EdgeInsets.symmetric(vertical: 16),
            child: Row(
              children: [
                Text(
                  '${_searchResults.length} results found',
                  style: GoogleFonts.nunitoSans(
                    fontSize: 16,
                    fontWeight: FontWeight.w600,
                    color: AppColors.onSurface,
                  ),
                ),
                const Spacer(),
                // Sort button (future enhancement)
                TextButton.icon(
                  onPressed: () {
                    // TODO: Implement sort functionality
                    ScaffoldMessenger.of(context).showSnackBar(
                      SnackBar(
                        content: Text(
                          'Sort options coming soon!',
                          style: GoogleFonts.nunitoSans(color: Colors.white),
                        ),
                        backgroundColor: AppColors.primaryGreen,
                      ),
                    );
                  },
                  icon: Icon(
                    FeatherIcons.sliders,
                    size: 16,
                    color: AppColors.primaryGreen,
                  ),
                  label: Text(
                    'Sort',
                    style: GoogleFonts.nunitoSans(
                      fontSize: 14,
                      color: AppColors.primaryGreen,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                ),
              ],
            ),
          ),
          // Results list
          Expanded(
            child: GridView.builder(
              padding: const EdgeInsets.only(bottom: 100),
              gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                crossAxisCount: 2,
                childAspectRatio: 0.8,
                mainAxisSpacing: 16,
                crossAxisSpacing: 16,
              ),
              itemCount: _searchResults.length,
              itemBuilder: (context, index) {
                final product = _searchResults[index];
                return ProductCard(
                  product: product,
                  onTap: () {
                    Navigator.push(
                      context,
                      MaterialPageRoute(
                        builder: (context) => ProductDetailPage(product: product),
                      ),
                    );
                  },
                );
              },
            ),
          ),
        ],
      ),
    );
  }
}
