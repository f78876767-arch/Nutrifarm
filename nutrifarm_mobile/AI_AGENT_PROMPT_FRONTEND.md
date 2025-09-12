# Prompt untuk Frontend Agent AI

## Tujuan
- Integrasikan cek ongkir RajaOngkir di checkout.
- Simpan alamat terstruktur user (provinsi/kota) agar mendapatkan city_id.
- Pilihkan layanan kurir dan biaya, lalu kirim shipping_method dan shipping_amount saat order.

## API Backend
- Base URL: {APP_URL}/api
- Auth: Bearer token (Sanctum)

### Alamat user
- GET /shipping/address
  - Response fields: address, phone, province_id, province_name, city_id, city_name, postal_code, subdistrict_id, subdistrict_name
- POST /shipping/address
  - Body:
    {
      address?, phone?,
      province_id (int), province_name (string),
      city_id (int), city_name (string),
      postal_code?,
      subdistrict_id?, subdistrict_name?
    }

### RajaOngkir lookups
- GET /shipping/rajaongkir/provinces
  - [{ province_id, province }]
- GET /shipping/rajaongkir/cities?province={province_id}
  - [{ city_id, province_id, type, city_name, postal_code }]
- (Opsional Pro) GET /shipping/rajaongkir/subdistricts?city={city_id}

### Hitung ongkir
- POST /shipping/rajaongkir/cost
  - Body: { origin, destination, weight, courier, originType?: 'city', destinationType?: 'city' }
  - Response normalisasi: [{ courier, service, description, cost, etd, note }]
- (Rekomendasi) POST /shipping/rajaongkir/cost/from-profile
  - Body: { weight: int, couriers: ['jne','tiki','pos','sicepat','jnt'] }
  - Otomatis pakai origin dari server dan destination = city_id dari profil user.

## Alur UI
1) Profil alamat pengiriman
   - Pada first load checkout:
     - GET /shipping/address
     - Jika province_id/city_id kosong:
       - GET /shipping/rajaongkir/provinces → dropdown
       - GET /shipping/rajaongkir/cities?province={id} → dropdown
       - Setelah dipilih, POST /shipping/address untuk menyimpan.
2) Cek ongkir
   - Hitung total berat (gram) dari keranjang (gunakan default jika belum ada per-item weight).
   - Kurir default: ['jne','tiki','pos','sicepat','jnt']
   - Panggil POST /shipping/rajaongkir/cost/from-profile dengan { weight, couriers }.
   - Tampilkan opsi (radio):
     - Label: {COURIER}-{SERVICE} — {description} • Rp{cost} • ETD {etd} hari
3) Saat user memilih layanan
   - Simpan ke state checkout:
     - shippingMethod = "{courier}-{service}"
     - shippingAmount = cost
     - shippingETD = etd
4) Saat submit order
   - Sertakan shipping_method dan shipping_amount di payload.
5) UX
   - Cache provinsi/kota selama sesi.
   - Tampilkan loading & error states.
   - Format IDR dengan Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).

## Validasi & Edge Cases
- Pastikan city_id user tersedia sebelum cek ongkir.
- Berat > 0 gram.
- Jika plan Starter, jangan gunakan subdistricts.
- Tampilkan pesan jika tidak ada layanan tersedia.

## Konfigurasi ops
- .env (backend):
  - RAJAONGKIR_KEY=...
  - RAJAONGKIR_BASE_URL=https://api.rajaongkir.com/starter
  - SHIPPING_ORIGIN_CITY_ID=... (city_id gudang/toko)
- Jalankan migrasi untuk fields alamat user.

---

## Catatan Integrasi pada Repo Ini (kondisi saat ini)
- ApiService sudah memiliki endpoints: provinces, cities, cost; dan createOrder mendukung shipping_amount.
- CheckoutService sudah menghitung ongkir multi-kurir dan auto J&T (autoCheckJntTo), menyimpan selected option (shippingMethod, shippingAmount).
- CartPage memiliki UI cek ongkir; auto-use J&T bila default address memiliki roCityId.
- AddressService saat ini menyimpan alamat lokal (SharedPreferences) dan field RO mapping (roProvinceId, roCityId). Belum terhubung ke endpoint /shipping/address.

## Tindakan Lanjutan yang Disarankan
1) Tambahkan di ApiService:
   - getUserShippingAddress(), saveUserShippingAddress() untuk /shipping/address (GET/POST).
   - costFromProfile(weight, couriers) untuk endpoint rekomendasi.
2) CheckoutService:
   - Jika address dari backend tersedia (mengandung city_id), gunakan cost/from-profile sebagai jalur utama.
3) UI Address (Tambah/Edit):
   - Dropdown Provinsi → Kota (ambil dari endpoints). Saat simpan, POST ke /shipping/address.
4) Fallback:
   - Jika /provinces 500, tetap izinkan auto J&T bila roCityId default tersedia; atau tampilkan error state yang jelas.
