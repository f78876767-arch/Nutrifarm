import 'package:flutter/material.dart';

class SkeletonLoading extends StatefulWidget {
  final double width;
  final double height;
  final BorderRadius? borderRadius;
  final Widget? child;

  const SkeletonLoading({
    Key? key,
    required this.width,
    required this.height,
    this.borderRadius,
    this.child,
  }) : super(key: key);

  @override
  State<SkeletonLoading> createState() => _SkeletonLoadingState();
}

class _SkeletonLoadingState extends State<SkeletonLoading>
    with SingleTickerProviderStateMixin {
  late AnimationController _animationController;
  late Animation<double> _animation;

  @override
  void initState() {
    super.initState();
    _animationController = AnimationController(
      duration: const Duration(milliseconds: 1500),
      vsync: this,
    );
    
    _animation = Tween<double>(
      begin: 0.0,
      end: 1.0,
    ).animate(CurvedAnimation(
      parent: _animationController,
      curve: Curves.easeInOut,
    ));

    _animationController.repeat(reverse: true);
  }

  @override
  void dispose() {
    _animationController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return AnimatedBuilder(
      animation: _animation,
      builder: (context, child) {
        return Container(
          width: widget.width,
          height: widget.height,
          decoration: BoxDecoration(
            borderRadius: widget.borderRadius ?? BorderRadius.circular(4),
            gradient: LinearGradient(
              begin: Alignment(-1.0 + (_animation.value * 2.0), 0.0),
              end: Alignment(1.0 + (_animation.value * 2.0), 0.0),
              colors: [
                Colors.grey[300]!,
                Colors.grey[100]!,
                Colors.grey[300]!,
              ],
              stops: const [0.0, 0.5, 1.0],
            ),
          ),
          child: widget.child,
        );
      },
    );
  }
}

class ProductCardSkeleton extends StatelessWidget {
  const ProductCardSkeleton({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Card(
      margin: EdgeInsets.all(8),
      elevation: 4,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(15)),
      child: Container(
        width: 160,
        padding: EdgeInsets.all(12),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Image skeleton
            SkeletonLoading(
              width: double.infinity,
              height: 100,
              borderRadius: BorderRadius.circular(8),
            ),
            SizedBox(height: 8),
            
            // Title skeleton
            SkeletonLoading(
              width: double.infinity,
              height: 16,
              borderRadius: BorderRadius.circular(4),
            ),
            SizedBox(height: 4),
            
            // Subtitle skeleton
            SkeletonLoading(
              width: 100,
              height: 12,
              borderRadius: BorderRadius.circular(4),
            ),
            SizedBox(height: 8),
            
            // Price skeleton
            SkeletonLoading(
              width: 80,
              height: 14,
              borderRadius: BorderRadius.circular(4),
            ),
            SizedBox(height: 4),
            
            // Sales skeleton
            SkeletonLoading(
              width: 60,
              height: 12,
              borderRadius: BorderRadius.circular(4),
            ),
          ],
        ),
      ),
    );
  }
}

class GridProductSkeleton extends StatelessWidget {
  final int itemCount;

  const GridProductSkeleton({
    Key? key,
    this.itemCount = 6,
  }) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return GridView.builder(
      shrinkWrap: true,
      physics: NeverScrollableScrollPhysics(),
      gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
        crossAxisCount: 2,
        childAspectRatio: 0.75,
        crossAxisSpacing: 8,
        mainAxisSpacing: 8,
      ),
      itemCount: itemCount,
      itemBuilder: (context, index) {
        return Card(
          elevation: 4,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(15)),
          child: Container(
            padding: EdgeInsets.all(12),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Image skeleton
                SkeletonLoading(
                  width: double.infinity,
                  height: 100,
                  borderRadius: BorderRadius.circular(8),
                ),
                SizedBox(height: 8),
                
                // Title skeleton
                SkeletonLoading(
                  width: double.infinity,
                  height: 16,
                  borderRadius: BorderRadius.circular(4),
                ),
                SizedBox(height: 4),
                
                // Subtitle skeleton
                SkeletonLoading(
                  width: 100,
                  height: 12,
                  borderRadius: BorderRadius.circular(4),
                ),
                SizedBox(height: 8),
                
                // Price skeleton
                SkeletonLoading(
                  width: 80,
                  height: 14,
                  borderRadius: BorderRadius.circular(4),
                ),
                SizedBox(height: 4),
                
                // Sales skeleton
                SkeletonLoading(
                  width: 60,
                  height: 12,
                  borderRadius: BorderRadius.circular(4),
                ),
              ],
            ),
          ),
        );
      },
    );
  }
}

class CategorySkeleton extends StatelessWidget {
  const CategorySkeleton({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      scrollDirection: Axis.horizontal,
      child: Row(
        children: List.generate(5, (index) => Container(
          margin: EdgeInsets.only(left: 16),
          child: SkeletonLoading(
            width: 80,
            height: 32,
            borderRadius: BorderRadius.circular(16),
          ),
        )),
      ),
    );
  }
}

class BannerSkeleton extends StatelessWidget {
  const BannerSkeleton({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: EdgeInsets.symmetric(horizontal: 16),
      child: SkeletonLoading(
        width: double.infinity,
        height: 150,
        borderRadius: BorderRadius.circular(12),
      ),
    );
  }
}

class ListTileSkeleton extends StatelessWidget {
  const ListTileSkeleton({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return ListTile(
      leading: SkeletonLoading(
        width: 50,
        height: 50,
        borderRadius: BorderRadius.circular(8),
      ),
      title: SkeletonLoading(
        width: double.infinity,
        height: 16,
        borderRadius: BorderRadius.circular(4),
      ),
      subtitle: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(height: 4),
          SkeletonLoading(
            width: 100,
            height: 12,
            borderRadius: BorderRadius.circular(4),
          ),
          SizedBox(height: 4),
          SkeletonLoading(
            width: 80,
            height: 12,
            borderRadius: BorderRadius.circular(4),
          ),
        ],
      ),
    );
  }
}

class SearchResultSkeleton extends StatelessWidget {
  final int itemCount;

  const SearchResultSkeleton({
    Key? key,
    this.itemCount = 10,
  }) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return ListView.builder(
      itemCount: itemCount,
      itemBuilder: (context, index) {
        return Container(
          margin: EdgeInsets.symmetric(horizontal: 16, vertical: 4),
          child: ListTileSkeleton(),
        );
      },
    );
  }
}

class CartItemSkeleton extends StatelessWidget {
  const CartItemSkeleton({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: EdgeInsets.all(16),
      margin: EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        boxShadow: [
          BoxShadow(
            color: Colors.grey.withOpacity(0.1),
            spreadRadius: 1,
            blurRadius: 4,
            offset: Offset(0, 2),
          ),
        ],
      ),
      child: Row(
        children: [
          // Product image skeleton
          SkeletonLoading(
            width: 80,
            height: 80,
            borderRadius: BorderRadius.circular(8),
          ),
          SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Product name skeleton
                SkeletonLoading(
                  width: double.infinity,
                  height: 16,
                  borderRadius: BorderRadius.circular(4),
                ),
                SizedBox(height: 8),
                // Price skeleton
                SkeletonLoading(
                  width: 80,
                  height: 14,
                  borderRadius: BorderRadius.circular(4),
                ),
                SizedBox(height: 8),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    // Quantity controls skeleton
                    SkeletonLoading(
                      width: 100,
                      height: 32,
                      borderRadius: BorderRadius.circular(16),
                    ),
                    // Remove button skeleton
                    SkeletonLoading(
                      width: 32,
                      height: 32,
                      borderRadius: BorderRadius.circular(16),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class CartSkeleton extends StatelessWidget {
  final int itemCount;

  const CartSkeleton({
    Key? key,
    this.itemCount = 3,
  }) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        // Cart items skeleton
        Expanded(
          child: ListView.builder(
            itemCount: itemCount,
            itemBuilder: (context, index) => CartItemSkeleton(),
          ),
        ),
        // Summary skeleton
        Container(
          padding: EdgeInsets.all(16),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.vertical(top: Radius.circular(16)),
            boxShadow: [
              BoxShadow(
                color: Colors.grey.withOpacity(0.1),
                spreadRadius: 1,
                blurRadius: 4,
                offset: Offset(0, -2),
              ),
            ],
          ),
          child: Column(
            children: [
              // Summary lines
              ...List.generate(3, (index) => Padding(
                padding: const EdgeInsets.symmetric(vertical: 4),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    SkeletonLoading(
                      width: 80,
                      height: 14,
                      borderRadius: BorderRadius.circular(4),
                    ),
                    SkeletonLoading(
                      width: 60,
                      height: 14,
                      borderRadius: BorderRadius.circular(4),
                    ),
                  ],
                ),
              )),
              SizedBox(height: 16),
              // Checkout button skeleton
              SkeletonLoading(
                width: double.infinity,
                height: 48,
                borderRadius: BorderRadius.circular(12),
              ),
            ],
          ),
        ),
      ],
    );
  }
}

class ProfileMenuSkeleton extends StatelessWidget {
  final int itemCount;

  const ProfileMenuSkeleton({
    Key? key,
    this.itemCount = 6,
  }) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return ListView.builder(
      shrinkWrap: true,
      physics: NeverScrollableScrollPhysics(),
      itemCount: itemCount,
      itemBuilder: (context, index) {
        return Container(
          margin: EdgeInsets.symmetric(horizontal: 16, vertical: 4),
          child: ListTile(
            leading: SkeletonLoading(
              width: 24,
              height: 24,
              borderRadius: BorderRadius.circular(4),
            ),
            title: SkeletonLoading(
              width: double.infinity,
              height: 16,
              borderRadius: BorderRadius.circular(4),
            ),
            trailing: SkeletonLoading(
              width: 16,
              height: 16,
              borderRadius: BorderRadius.circular(4),
            ),
          ),
        );
      },
    );
  }
}
