# Laporan Verifikasi Struktur URL & Multi-Environment Unimart.group

Dokumen ini mendokumentasikan hasil audit, perbaikan, dan pengujian struktur URL pada sistem **Unimart.group** agar berjalan konsisten dan aman baik di lingkungan **pengembangan lokal** (`unimart.group.localhost`) maupun **lingkungan publik/produksi** (`unimart.group`).

---

## 1. Ringkasan Hasil Audit & Tindakan Perbaikan

| Komponen / File | Masalah Sebelumnya | Status & Perbaikan |
|---|---|---|
| [`IdentifyTenant.php`](file:///home/dev360_03/locomo.id/unimart.group/app/Http/Middleware/IdentifyTenant.php) | Parsing subdomain masih rentan terhadap port dan domain hardcoded lokal saja. | **SELESAI**: Ditingkatkan dengan `substr()` dinamis berbasis `APP_LOCAL_DOMAIN` (`unimart.group.localhost`) dan `APP_BASE_DOMAIN` (`unimart.group`), pembersihan port, serta isolasi session/container saat beralih konteks. |
| [`routes/web.php`](file:///home/dev360_03/locomo.id/unimart.group/routes/web.php) | Root `/` belum otomatis mendeteksi apakah request berasal dari subdomain toko atau portal marketplace. | **SELESAI**: Rute root `/` secara otonom mendeteksi flag `is_marketplace` (menampilkan modul Marketplace) atau subdomain toko aktif (`GrosirLandingController` atau `CaffeLandingController`). |
| [`GatewayService.php`](file:///home/dev360_03/locomo.id/unimart.group/app/Services/GatewayService.php) | URL WebSocket Gateway hardcoded ke `http://gateway.locomo.id.localhost` di beberapa Blade view. | **SELESAI**: Dibuat `GatewayService::getClientSocketUrl()` yang secara otomatis memilih `gateway.locomo.id.localhost` di lokal atau `https://gateway.unimart.group` di publik (atau via `GATEWAY_CLIENT_URL`). |
| [`Tenant.php`](file:///home/dev360_03/locomo.id/unimart.group/app/Models/Tenant.php) | Tidak ada accessor dinamis untuk domain penuh tenant. | **SELESAI**: Ditambahkan accessor `$tenant->full_domain` dan `$tenant->store_url` yang otomatis beradaptasi dengan hostname aktif (menghasilkan `*.unimart.group.localhost` di lokal dan `*.unimart.group` di publik). |
| [`TableController.php`](file:///home/dev360_03/locomo.id/unimart.group/app/Http/Controllers/Owner/TableController.php) | QR Code generator sempat menggunakan URL statis localhost. | **SELESAI**: Diganti menggunakan `url('/caffe/' . $tenant->domain . '?table=' . $i)` yang dinamis mengikuti domain aktif. |
| [`MerchantRegistrationController.php`](file:///home/dev360_03/locomo.id/unimart.group/app/Http/Controllers/Api/MerchantRegistrationController.php) | Pembuatan tenant baru sempat menempelkan suffix `.unimart.group` ke kolom domain slug. | **SELESAI**: Slug disimpan murni (`Str::slug($name)`), dan webhook PitiCoin menggunakan fallback dinamis (`config('services.piticoin.webhook_url')`). |
| [`caffe-qr.blade.php`](file:///home/dev360_03/locomo.id/unimart.group/resources/views/caffe-qr.blade.php) & View Lainnya | Socket.io client hardcoded ke `.localhost`. | **SELESAI**: Menggunakan `GatewayService::getClientSocketUrl()`. |
| [`izone-hotspot.blade.php`](file:///home/dev360_03/locomo.id/unimart.group/resources/views/izone-hotspot.blade.php) | Tautan PitiCoin hardcoded ke `http://piticoin.id.localhost`. | **SELESAI**: Menggunakan variabel dinamis dengan fallback aman ke `https://piticoin.id` saat publik. |

---

## 2. Matriks Resolusi URL

| Konteks URL | Development (`.localhost`) | Production (`.unimart.group`) | Handler / Controller |
|---|---|---|---|
| **Platform Landing SaaS** | `http://unimart.group.localhost/` | `https://unimart.group/` | `resources/views/welcome.blade.php` |
| **Marketplace Portal** | `http://marketplace.unimart.group.localhost/` atau `/marketplace` | `https://marketplace.unimart.group/` atau `/marketplace` | `App\Modules\Marketplace\Controllers\MarketplaceHomeController` |
| **Subdomain Kafe** | `http://[slug].unimart.group.localhost/` atau `/caffe/[slug]` | `https://[slug].unimart.group/` atau `/caffe/[slug]` | `App\Modules\Caffe\Controllers\CaffeLandingController` |
| **Subdomain Grosir** | `http://[slug].unimart.group.localhost/` atau `/grosir/[slug]` | `https://[slug].unimart.group/` atau `/grosir/[slug]` | `App\Modules\Grosir\Controllers\GrosirLandingController` |
| **QR Table Order** | `.../caffe/[slug]?table=X` | `.../caffe/[slug]?table=X` | `caffe.qr` view |
| **WebSocket Gateway** | `http://gateway.locomo.id.localhost` | `https://gateway.unimart.group` (atau env `GATEWAY_CLIENT_URL`) | Node.js Gateway via Socket.io |

---

## 3. Hasil Validasi Otomatis (Feature Test)

Seluruh skenario telah diuji dan divalidasi langsung di dalam kontainer Docker `php-fpm` melalui file pengujian [`UrlStructureAndRoutingTest.php`](file:///home/dev360_03/locomo.id/unimart.group/tests/Feature/UrlStructureAndRoutingTest.php):

```bash
docker compose exec -w /var/www/html/unimart.group php-fpm php artisan test
```

**Hasil Eksekusi:**
```
PASS  Tests\Unit\ExampleTest
PASS  Tests\Feature\ExampleTest
PASS  Tests\Feature\TenantIsolationAndAuthTest
PASS  Tests\Feature\UrlStructureAndRoutingTest
  ✓ public and local main landing page
  ✓ caffe subdomain routing local and prod
  ✓ grosir subdomain routing local and prod
  ✓ marketplace subdomain routing local and prod
  ✓ tenant full domain attribute

Tests:    13 passed (37 assertions)
Duration: 0.81s
```
Semua 13 pengujian (37 assertions) lulus 100% tanpa kesalahan.
