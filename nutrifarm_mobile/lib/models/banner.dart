class BannerModel {
  final int id;
  final String title;
  final String imageUrl;
  final String? description;
  final String? actionUrl;
  final bool isActive;
  final int sortOrder;

  BannerModel({
    required this.id,
    required this.title,
    required this.imageUrl,
    this.description,
    this.actionUrl,
    required this.isActive,
    required this.sortOrder,
  });

  factory BannerModel.fromJson(Map<String, dynamic> json) {
    return BannerModel(
      id: json['id'] is String ? int.tryParse(json['id']) ?? 0 : (json['id'] ?? 0),
      title: json['title']?.toString() ?? '',
      imageUrl: json['image_url']?.toString() ?? json['imageUrl']?.toString() ?? '',
      description: json['description']?.toString(),
      actionUrl: json['action_url']?.toString() ?? json['actionUrl']?.toString(),
      isActive: json['is_active'] == true || json['is_active'] == 1 || json['is_active'] == '1',
      sortOrder: json['sort_order'] is String ? int.tryParse(json['sort_order']) ?? 0 : (json['sort_order'] ?? 0),
    );
  }

  Map<String, dynamic> toJson() => {
        'id': id,
        'title': title,
        'image_url': imageUrl,
        'description': description,
        'action_url': actionUrl,
        'is_active': isActive,
        'sort_order': sortOrder,
      };
}