import 'package:flutter/material.dart';

class ProductUtils {
  static Color getProductColor(String imageUrl) {
    switch (imageUrl) {
      case 'headphones':
        return const Color(0xFFFFF3B8);
      case 'macbook':
        return Colors.grey[100]!;
      case 'chair':
        return Colors.blue[50]!;
      case 'smartwatch':
        return Colors.purple[50]!;
      case 'tshirt':
        return Colors.pink[50]!;
      case 'yoga':
        return Colors.green[50]!;
      default:
        return Colors.grey[100]!;
    }
  }

  static IconData getProductIcon(String imageUrl) {
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

  static List<String> getProductFeatures(String imageUrl) {
    switch (imageUrl) {
      case 'headphones':
        return [
          'Active Noise Cancellation',
          'Up to 30 hours battery life',
          'Wireless Bluetooth 5.0',
          'Premium sound quality',
          'Comfortable over-ear design'
        ];
      case 'macbook':
        return [
          'M3 Pro chip for incredible performance',
          '14-inch Liquid Retina XDR display',
          'Up to 18 hours battery life',
          '512GB SSD storage',
          'Advanced camera and audio'
        ];
      case 'chair':
        return [
          'Ergonomic lumbar support',
          'Adjustable height and armrests',
          'High-quality mesh material',
          '360-degree swivel',
          'Weight capacity up to 300lbs'
        ];
      case 'smartwatch':
        return [
          'Health and fitness tracking',
          'GPS and cellular connectivity',
          'Water resistant to 50 meters',
          'Always-on Retina display',
          'Up to 36 hours battery life'
        ];
      case 'tshirt':
        return [
          '100% organic cotton material',
          'Comfortable regular fit',
          'Machine washable',
          'Sustainable production',
          'Available in multiple colors'
        ];
      case 'yoga':
        return [
          'Non-slip textured surface',
          'Premium 6mm thickness',
          'Eco-friendly TPE material',
          'Lightweight and portable',
          'Easy to clean'
        ];
      default:
        return [
          'High-quality materials',
          'Durable construction',
          'Easy to use',
          'Great value for money'
        ];
    }
  }
}
