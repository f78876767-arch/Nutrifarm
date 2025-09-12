# Cart Debouncing Implementation Guide

## Fitur yang Ditambahkan

### 1. Debouncing Mechanism
- **Delay**: 500 milidetik (bisa diatur di `_debounceDelay`)
- **Cara Kerja**: API call hanya dikirim setelah user berhenti menekan tombol +/- selama 500ms
- **Benefit**: Mencegah spam API calls saat user menekan tombol berulang kali dengan cepat

### 2. Visual Indicator
- **Dot Indicator**: Titik oranye kecil muncul di samping quantity saat ada pending update
- **Real-time**: UI langsung update quantity, server sync di background

### 3. Metode Kontrol Tambahan

```dart
// Check apakah ada pending update untuk produk tertentu
bool hasPendingUpdate(int productId) 

// Check apakah ada pending update secara keseluruhan
bool get hasPendingUpdates

// Force sync semua pending updates (berguna untuk checkout)
Future<void> forceSyncPendingUpdates()
```

## Implementasi Detail

### CartService Changes
1. **Import Timer**: `dart:async` untuk Timer functionality
2. **Timer Map**: `Map<int, Timer> _updateTimers` untuk tracking per-product timers
3. **Debounce Logic**: Cancel existing timer, create new timer with 500ms delay
4. **Cleanup**: Auto-cancel timers on dispose

### UI Changes  
1. **Visual Feedback**: Orange dot indicator untuk pending updates
2. **Immediate Response**: Quantity berubah langsung di UI
3. **Background Sync**: API call tidak memblokir UI interaction

## Contoh Penggunaan

### Force Sync Before Checkout
```dart
// Di checkout page, sebelum submit order
await CartService().forceSyncPendingUpdates();
await proceedToCheckout();
```

### Check Pending Status
```dart
// Di UI, untuk menampilkan status
if (cart.hasPendingUpdates) {
  // Show "Syncing..." indicator
}
```

## Performance Benefits

1. **Reduced API Calls**: Dari N calls menjadi 1 call per 500ms window
2. **Better UX**: UI responsive tanpa loading delay
3. **Server Protection**: Mencegah server overload dari rapid requests
4. **Battery Saving**: Fewer network requests = less battery drain

## Configuration

Untuk mengubah delay timing, edit konstanta di `CartService`:

```dart
static const Duration _debounceDelay = Duration(milliseconds: 500); // Ubah nilai ini
```

Nilai yang disarankan:
- **300ms**: Responsive tapi masih bisa spam jika user sangat cepat
- **500ms**: Balance terbaik antara responsiveness dan spam prevention
- **800ms**: Sangat aman dari spam tapi user mungkin merasa lambat
