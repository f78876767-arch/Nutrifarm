class Address {
  final String id;
  final String label;
  final String fullAddress;
  final String? detailAddress;
  final double? latitude;
  final double? longitude;
  final String? recipientName;
  final String? phoneNumber;
  // RajaOngkir mapping (optional)
  final int? roProvinceId;
  final int? roCityId;
  final String? roProvince;
  final String? roCity;
  final bool isDefault;
  final DateTime createdAt;
  final DateTime updatedAt;
  final String? jntCityCode;

  Address({
    required this.id,
    required this.label,
    required this.fullAddress,
    this.detailAddress,
    this.latitude,
    this.longitude,
    this.recipientName,
    this.phoneNumber,
    this.roProvinceId,
    this.roCityId,
    this.roProvince,
    this.roCity,
    this.isDefault = false,
    required this.createdAt,
    required this.updatedAt,
    this.jntCityCode,
  });

  Map<String, dynamic> toJson() => {
    'id': id,
    'label': label,
    'full_address': fullAddress,
    'detail_address': detailAddress,
    'latitude': latitude,
    'longitude': longitude,
    'recipient_name': recipientName,
    'phone_number': phoneNumber,
    'ro_province_id': roProvinceId,
    'ro_city_id': roCityId,
    'ro_province': roProvince,
    'ro_city': roCity,
    'is_default': isDefault,
    'created_at': createdAt.toIso8601String(),
    'updated_at': updatedAt.toIso8601String(),
    'jnt_city_code': jntCityCode,
  };

  static Address fromJson(Map<String, dynamic> json) => Address(
    id: json['id'].toString(),
    label: json['label'] ?? '',
    fullAddress: json['full_address'] ?? '',
    detailAddress: json['detail_address'],
    latitude: json['latitude']?.toDouble(),
    longitude: json['longitude']?.toDouble(),
    recipientName: json['recipient_name'],
    phoneNumber: json['phone_number'],
    roProvinceId: json['ro_province_id'] is String ? int.tryParse(json['ro_province_id']) : json['ro_province_id'],
    roCityId: json['ro_city_id'] is String ? int.tryParse(json['ro_city_id']) : json['ro_city_id'],
    roProvince: json['ro_province'],
    roCity: json['ro_city'],
    isDefault: json['is_default'] ?? false,
    createdAt: DateTime.parse(json['created_at'] ?? DateTime.now().toIso8601String()),
    updatedAt: DateTime.parse(json['updated_at'] ?? DateTime.now().toIso8601String()),
    jntCityCode: json['jnt_city_code']?.toString(),
  );

  Address copyWith({
    String? id,
    String? label,
    String? fullAddress,
    String? detailAddress,
    double? latitude,
    double? longitude,
    String? recipientName,
    String? phoneNumber,
    int? roProvinceId,
    int? roCityId,
    String? roProvince,
    String? roCity,
    bool? isDefault,
    DateTime? createdAt,
    DateTime? updatedAt,
    String? jntCityCode,
  }) {
    return Address(
      id: id ?? this.id,
      label: label ?? this.label,
      fullAddress: fullAddress ?? this.fullAddress,
      detailAddress: detailAddress ?? this.detailAddress,
      latitude: latitude ?? this.latitude,
      longitude: longitude ?? this.longitude,
      recipientName: recipientName ?? this.recipientName,
      phoneNumber: phoneNumber ?? this.phoneNumber,
      roProvinceId: roProvinceId ?? this.roProvinceId,
      roCityId: roCityId ?? this.roCityId,
      roProvince: roProvince ?? this.roProvince,
      roCity: roCity ?? this.roCity,
      isDefault: isDefault ?? this.isDefault,
      createdAt: createdAt ?? this.createdAt,
      updatedAt: updatedAt ?? this.updatedAt,
      jntCityCode: jntCityCode ?? this.jntCityCode,
    );
  }
}
