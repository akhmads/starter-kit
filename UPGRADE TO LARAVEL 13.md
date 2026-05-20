
## Langkah Upgrade Laravel 12 → 13

### 1. Update composer.json

Edit composer.json, ubah versi berikut:

```json
"require": {
    "laravel/framework": "^13.0",
    "laravel/tinker": "^3.0"
},
"require-dev": {
    "pestphp/pest": "^4.0"
}
```

> `pestphp/pest` sudah `^4.3` di project ini, jadi sudah kompatibel. `laravel/tinker` perlu diubah dari `^2.10.1` ke `^3.0`.

---

### 2. Jalankan Composer Update

```bash
composer update laravel/framework laravel/tinker --with-all-dependencies
```

---

### 3. [HIGH IMPACT] Update Referensi CSRF Middleware

`VerifyCsrfToken` diganti menjadi `PreventRequestForgery`. Cek di test files atau route jika ada penggunaan `withoutMiddleware([VerifyCsrfToken::class])`:

```php
// Sebelum
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
->withoutMiddleware([VerifyCsrfToken::class]);

// Sesudah
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
->withoutMiddleware([PreventRequestForgery::class]);
```

Project ini tidak menggunakan `VerifyCsrfToken` secara eksplisit, jadi kemungkinan aman.

---

### 4. [MEDIUM IMPACT] Tambah `serializable_classes` di cache.php

Tambahkan opsi ini untuk mencegah PHP deserialization attacks:

```php
'serializable_classes' => false,
```

---

### 5. [MEDIUM IMPACT] Validasi `upsert` dengan MySQL

Pastikan semua pemanggilan `Model::upsert()` menyertakan argument `uniqueBy` yang tidak kosong:

```php
// SALAH - akan throw InvalidArgumentException di Laravel 13
Product::upsert($data, []);

// BENAR
Product::upsert($data, ['id']);
```

---

### 6. [LOW IMPACT] Cache Prefix & Session Cookie

Jika project **tidak** mendefinisikan `CACHE_PREFIX`, `REDIS_PREFIX`, atau `SESSION_COOKIE` di .env, tambahkan nilai eksplisit untuk menghindari perubahan nama prefix (underscore → hyphen):

```env
CACHE_PREFIX=livewire4_cache_
SESSION_COOKIE=livewire4_session
```

---

### 7. [LOW IMPACT] Cek Queue Event Listeners

Jika ada listener untuk `QueueBusy` atau `JobAttempted`, update propertinya:

```php
// QueueBusy: $event->connection → $event->connectionName
// JobAttempted: $event->exceptionOccurred → $event->exception (nullable)
```

Project ini punya custom queue module — cek Queue dan `QueueListenerServiceProvider.php`.

---

### 8. [LOW IMPACT] Polyfill PHP 8.5 — Hindari Konflik Fungsi Global

Laravel 13 menambahkan `symfony/polyfill-php85` yang mendefinisikan `array_first()` dan `array_last()`. Ganti penggunaan global helper tersebut dengan:

```php
use Illuminate\Support\Arr;

Arr::first($array, fn($v) => $condition);
```

---

### 9. Jalankan Test

```bash
composer test
```

---

### Ringkasan Dampak untuk Project Ini

| Perubahan | Dampak | Perlu Aksi |
|---|---|---|
| `laravel/framework ^13.0` + `laravel/tinker ^3.0` | Tinggi | ✅ Ya |
| `PreventRequestForgery` | Tinggi | Cek test saja |
| `serializable_classes` di cache config | Medium | ✅ Ya |
| `upsert` validation | Medium | Cek penggunaan |
| Cache prefix/session cookie | Rendah | Jika tidak ada di .env |
| `QueueBusy`/`JobAttempted` events | Rendah | Cek Queue module |

