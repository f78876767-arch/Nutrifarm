import 'package:flutter/foundation.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:geolocator/geolocator.dart';
import 'package:permission_handler/permission_handler.dart';
import 'dart:convert';
import '../models/address.dart';

class AddressService extends ChangeNotifier {
  static final AddressService _instance = AddressService._internal();
  factory AddressService() => _instance;
  AddressService._internal();

  List<Address> _addresses = [];
  SharedPreferences? _prefs;
  bool _isLoading = false;

  List<Address> get addresses => List.unmodifiable(_addresses);
  bool get isLoading => _isLoading;
  int get addressCount => _addresses.length;

  Address? get defaultAddress => _addresses.where((addr) => addr.isDefault).firstOrNull;

  Future<void> initialize() async {
    _prefs = await SharedPreferences.getInstance();
    await loadAddresses();
  }

  // Load addresses from local storage
  Future<void> loadAddresses() async {
    _isLoading = true;
    notifyListeners();

    try {
      final addressesJson = _prefs?.getStringList('saved_addresses') ?? [];
      _addresses = addressesJson.map((json) {
        final data = jsonDecode(json) as Map<String, dynamic>;
        return Address.fromJson(data);
      }).toList();

      // Sort by default first, then by creation date
      _addresses.sort((a, b) {
        if (a.isDefault && !b.isDefault) return -1;
        if (!a.isDefault && b.isDefault) return 1;
        return b.createdAt.compareTo(a.createdAt);
      });
    } catch (e) {
      print('Error loading addresses: $e');
      _addresses = [];
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  // Save addresses to local storage
  Future<void> _saveAddresses() async {
    try {
      final addressesJson = _addresses.map((address) => 
        jsonEncode(address.toJson())
      ).toList();
      await _prefs?.setStringList('saved_addresses', addressesJson);
    } catch (e) {
      print('Error saving addresses: $e');
    }
  }

  // Add new address
  Future<void> addAddress(Address address) async {
    _isLoading = true;
    notifyListeners();

    try {
      // If this is the first address or marked as default, make it default
      if (_addresses.isEmpty || address.isDefault) {
        // Remove default from other addresses
        _addresses = _addresses.map((addr) => 
          addr.copyWith(isDefault: false)
        ).toList();
      }

      _addresses.add(address);
      
      // Sort addresses
      _addresses.sort((a, b) {
        if (a.isDefault && !b.isDefault) return -1;
        if (!a.isDefault && b.isDefault) return 1;
        return b.createdAt.compareTo(a.createdAt);
      });

      await _saveAddresses();
    } catch (e) {
      print('Error adding address: $e');
      throw Exception('Failed to add address');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  // Update address
  Future<void> updateAddress(String addressId, Address updatedAddress) async {
    _isLoading = true;
    notifyListeners();

    try {
      final index = _addresses.indexWhere((addr) => addr.id == addressId);
      if (index == -1) throw Exception('Address not found');

      // If setting as default, remove default from others
      if (updatedAddress.isDefault) {
        _addresses = _addresses.map((addr) => 
          addr.id == addressId ? addr : addr.copyWith(isDefault: false)
        ).toList();
      }

      _addresses[index] = updatedAddress.copyWith(
        updatedAt: DateTime.now(),
      );

      // Sort addresses
      _addresses.sort((a, b) {
        if (a.isDefault && !b.isDefault) return -1;
        if (!a.isDefault && b.isDefault) return 1;
        return b.createdAt.compareTo(a.createdAt);
      });

      await _saveAddresses();
    } catch (e) {
      print('Error updating address: $e');
      throw Exception('Failed to update address');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  // Delete address
  Future<void> deleteAddress(String addressId) async {
    _isLoading = true;
    notifyListeners();

    try {
      final address = _addresses.where((addr) => addr.id == addressId).firstOrNull;
      if (address == null) throw Exception('Address not found');

      _addresses.removeWhere((addr) => addr.id == addressId);

      // If deleted address was default, make the first one default
      if (address.isDefault && _addresses.isNotEmpty) {
        _addresses[0] = _addresses[0].copyWith(isDefault: true);
      }

      await _saveAddresses();
    } catch (e) {
      print('Error deleting address: $e');
      throw Exception('Failed to delete address');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  // Clear all addresses (for testing purposes)
  Future<void> clearAllAddresses() async {
    _isLoading = true;
    notifyListeners();

    try {
      _addresses.clear();
      await _saveAddresses();
    } catch (e) {
      print('Error clearing addresses: $e');
      throw Exception('Failed to clear addresses');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  // Set default address
  Future<void> setDefaultAddress(String addressId) async {
    try {
      _addresses = _addresses.map((addr) => 
        addr.copyWith(isDefault: addr.id == addressId)
      ).toList();

      // Sort addresses
      _addresses.sort((a, b) {
        if (a.isDefault && !b.isDefault) return -1;
        if (!a.isDefault && b.isDefault) return 1;
        return b.createdAt.compareTo(a.createdAt);
      });

      await _saveAddresses();
      notifyListeners();
    } catch (e) {
      print('Error setting default address: $e');
      throw Exception('Failed to set default address');
    }
  }

  // Get current location
  Future<Position?> getCurrentLocation() async {
    try {
      // Check location permission
      final permission = await Permission.location.request();
      if (permission != PermissionStatus.granted) {
        throw Exception('Location permission not granted');
      }

      // Check if location services are enabled
      bool serviceEnabled = await Geolocator.isLocationServiceEnabled();
      if (!serviceEnabled) {
        throw Exception('Location services are disabled');
      }

      // Get current position
      Position position = await Geolocator.getCurrentPosition(
        desiredAccuracy: LocationAccuracy.high,
        timeLimit: const Duration(seconds: 10),
      );

      return position;
    } catch (e) {
      print('Error getting current location: $e');
      return null;
    }
  }

  // Calculate distance between two addresses
  double calculateDistance(Address address1, Address address2) {
    if (address1.latitude == null || address1.longitude == null ||
        address2.latitude == null || address2.longitude == null) {
      return 0.0;
    }

    return Geolocator.distanceBetween(
      address1.latitude!,
      address1.longitude!,
      address2.latitude!,
      address2.longitude!,
    ) / 1000; // Convert to kilometers
  }

  // Find nearby addresses
  List<Address> getNearbyAddresses(double latitude, double longitude, double radiusKm) {
    return _addresses.where((address) {
      if (address.latitude == null || address.longitude == null) return false;
      
      double distance = Geolocator.distanceBetween(
        latitude,
        longitude,
        address.latitude!,
        address.longitude!,
      ) / 1000;

      return distance <= radiusKm;
    }).toList();
  }

  // Search addresses by text
  List<Address> searchAddresses(String query) {
    if (query.isEmpty) return _addresses;

    final lowercaseQuery = query.toLowerCase();
    return _addresses.where((address) {
      return address.label.toLowerCase().contains(lowercaseQuery) ||
             address.fullAddress.toLowerCase().contains(lowercaseQuery) ||
             (address.detailAddress?.toLowerCase().contains(lowercaseQuery) ?? false) ||
             (address.recipientName?.toLowerCase().contains(lowercaseQuery) ?? false);
    }).toList();
  }
}
