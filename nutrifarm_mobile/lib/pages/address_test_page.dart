import 'package:flutter/material.dart';
import 'package:geolocator/geolocator.dart';
import '../models/address.dart';
import '../services/address_service.dart';

class AddressTestPage extends StatefulWidget {
  const AddressTestPage({super.key});

  @override
  State<AddressTestPage> createState() => _AddressTestPageState();
}

class _AddressTestPageState extends State<AddressTestPage> {
  final AddressService _addressService = AddressService();
  List<Address> addresses = [];
  bool isLoading = false;
  String statusMessage = '';

  @override
  void initState() {
    super.initState();
    _runTests();
  }

  Future<void> _runTests() async {
    setState(() {
      isLoading = true;
      statusMessage = 'Running address management tests...';
    });

    try {
      // Test 1: Add a test address
      await _testAddAddress();
      
      // Test 2: Load addresses
      await _testLoadAddresses();
      
      // Test 3: Location permission test
      await _testLocationPermission();

      setState(() {
        statusMessage = 'All tests completed successfully!';
      });
    } catch (e) {
      setState(() {
        statusMessage = 'Test failed: $e';
      });
    } finally {
      setState(() {
        isLoading = false;
      });
    }
  }

  Future<void> _testAddAddress() async {
    final testAddress = Address(
      id: DateTime.now().millisecondsSinceEpoch.toString(),
      recipientName: 'Test User',
      phoneNumber: '081234567890',
      fullAddress: 'Jl. Test No. 123, Jakarta',
      city: 'Jakarta',
      province: 'DKI Jakarta',
      postalCode: '12345',
      latitude: -6.2088,
      longitude: 106.8456,
      label: 'Rumah',
      isDefault: true,
    );

    await _addressService.addAddress(testAddress);
    setState(() {
      statusMessage += '\n✅ Address added successfully';
    });
  }

  Future<void> _testLoadAddresses() async {
    final loadedAddresses = await _addressService.getAddresses();
    setState(() {
      addresses = loadedAddresses;
      statusMessage += '\n✅ Loaded ${loadedAddresses.length} addresses';
    });
  }

  Future<void> _testLocationPermission() async {
    try {
      final hasPermission = await _addressService.hasLocationPermission();
      setState(() {
        statusMessage += '\n${hasPermission ? '✅' : '⚠️'} Location permission: ${hasPermission ? 'Granted' : 'Not granted'}';
      });

      if (hasPermission) {
        try {
          final position = await _addressService.getCurrentLocation();
          setState(() {
            statusMessage += '\n✅ Current location: ${position.latitude}, ${position.longitude}';
          });
        } catch (e) {
          setState(() {
            statusMessage += '\n⚠️ Could not get current location: $e';
          });
        }
      }
    } catch (e) {
      setState(() {
        statusMessage += '\n❌ Location permission test failed: $e';
      });
    }
  }

  Future<void> _clearTestData() async {
    await _addressService.clearAllAddresses();
    setState(() {
      addresses = [];
      statusMessage += '\n🗑️ Test data cleared';
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Address System Test'),
        backgroundColor: Colors.green,
        foregroundColor: Colors.white,
      ),
      body: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            if (isLoading)
              const Center(
                child: CircularProgressIndicator(),
              )
            else ...[
              Card(
                child: Padding(
                  padding: const EdgeInsets.all(16.0),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        'Test Results:',
                        style: TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      const SizedBox(height: 8),
                      Text(
                        statusMessage,
                        style: const TextStyle(fontFamily: 'monospace'),
                      ),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 16),
              if (addresses.isNotEmpty) ...[
                const Text(
                  'Test Addresses:',
                  style: TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                const SizedBox(height: 8),
                Expanded(
                  child: ListView.builder(
                    itemCount: addresses.length,
                    itemBuilder: (context, index) {
                      final address = addresses[index];
                      return Card(
                        child: ListTile(
                          title: Text(address.recipientName),
                          subtitle: Text(address.fullAddress),
                          trailing: Text(address.label),
                        ),
                      );
                    },
                  ),
                ),
              ],
            ],
            const SizedBox(height: 16),
            Row(
              children: [
                Expanded(
                  child: ElevatedButton(
                    onPressed: _runTests,
                    child: const Text('Run Tests Again'),
                  ),
                ),
                const SizedBox(width: 8),
                Expanded(
                  child: ElevatedButton(
                    onPressed: _clearTestData,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Colors.red,
                      foregroundColor: Colors.white,
                    ),
                    child: const Text('Clear Test Data'),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}
