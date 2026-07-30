# GDP Payment Gateway

> Pustaka integrasi multi Payment Gateway yang fleksibel, seragam, dan siap pakai untuk aplikasi PHP. Dikembangkan oleh **Gusvira Digital**.

---

## Daftar Isi
- [Tentang Proyek](#tentang-proyek)
- [Fitur Utama](#fitur-utama)
- [Penyedia Pembayaran yang Didukung](#penyedia-pembayaran-yang-didukung)
- [Persyaratan](#persyaratan)
- [Pemasangan](#pemasangan)
- [Penggunaan Dasar](#penggunaan-dasar)
- [Struktur Direktori](#struktur-direktori)
- [Keamanan](#keamanan)
- [Dukungan](#dukungan)
- [Lisensi](#lisensi)

---

## Tentang Proyek
Paket ini menyediakan cara yang sederhana dan konsisten untuk mengintegrasikan berbagai layanan pembayaran ke dalam aplikasi PHP Anda. Didesain agar **bersifat umum**, sehingga dapat digunakan untuk segala kebutuhan transaksi: toko daring, layanan berlangganan, sistem donasi, pembayaran jasa, dan lain-lain — tidak terbatas pada satu jenis penggunaan saja.

Menggunakan pola desain **Factory Pattern** serta mengikuti standar penulisan kode **PSR-4**, sehingga mudah dipelajari, dikembangkan, dan disesuaikan dengan berbagai kerangka kerja aplikasi.

**Informasi Resmi:**
- Pengembang: [Gusvira Digital](https://gusviradigital.co.id/)
- Situs Resmi: https://gusviradigital.co.id/
- Repositori: https://github.com/gusviradigital/gdp-payment-gateway
- Lisensi: MIT

---

## Fitur Utama
- ✅ Satu antarmuka untuk mengelola banyak penyedia pembayaran
- ✅ Penanganan notifikasi balik (callback/webhook) yang aman
- ✅ Verifikasi tanda tangan untuk mencegah manipulasi data
- ✅ Pencatatan riwayat transaksi untuk kebutuhan audit
- ✅ Penyimpanan sementara konfigurasi guna meningkatkan kinerja
- ✅ Dukungan peristiwa (event) saat status pembayaran berubah
- ✅ Penanganan kesalahan khusus yang mudah dilacak
- ✅ Sepenuhnya dapat disesuaikan dan diperluas

---

## Penyedia Pembayaran yang Didukung
- Midtrans
- Xendit
- Tripay
- Duitku
- PayPal

---

## Persyaratan
- PHP versi **7.4 | 8.0 | 8.1 | 8.2 | 8.3**
- Ekstensi PHP: `json`, `curl`

---

## Pemasangan
Gunakan Composer untuk memasang paket ini:

```bash
composer require gusviradigital/gdp-payment-gateway
```

---

## Penggunaan Dasar

Untuk dokumentasi penggunaan yang lebih lengkap dan detail, silakan baca **[Panduan Implementasi Lengkap](docs/implementasi.md)**.

### Contoh Membuat Transaksi Pembayaran
```php
<?php

require __DIR__ . '/vendor/autoload.php';

use GusviraDigital\GdpPaymentGateway\Controllers\MidtransController;

// 1. Siapkan array konfigurasi (dari database atau environment)
$config = [
    'server_key' => 'SB-Mid-server-xxxx',
    'client_key' => 'SB-Mid-client-xxxx',
    'is_production' => false,
];

// 2. Inisialisasi controller
$controller = new MidtransController($config);

// 3. Buat transaksi
$params = [
    'order_id' => 'ORDER-12345',
    'amount' => 100000,
    'method' => 'bca', // atau 'snap', dll.
];

$response = $controller->createTransaction($params);

echo "URL Pembayaran: " . $response->paymentUrl;
```

### Penanganan Notifikasi Balik (Webhook)
Gunakan `CallbackController` untuk memproses pembaruan status pembayaran yang dikirimkan secara otomatis oleh penyedia layanan pembayaran. Controller ini secara otomatis akan memverifikasi tanda tangan kriptografi (Signature) notifikasi sebelum memberikan respon.

---

## Struktur Direktori
```
gdp-payment-gateway/
├── src/
│   ├── Contracts/          # Definisi antarmuka standar
│   ├── Controllers/        # Pengendali pembayaran & notifikasi balik
│   ├── Gateways/           # Implementasi masing-masing penyedia
│   ├── Libraries/          # Kelas inti seperti Pabrik Pembayaran
│   ├── Utilities/          # Alat bantu verifikasi, pencatatan log
│   ├── Helpers/            # Fungsi pembantu umum
│   ├── Support/            # Kelas pendukung (Cache sementara)
│   ├── Events/             # Peristiwa perubahan status pembayaran
│   └── Exceptions/         # Kelas penanganan kesalahan khusus
├── composer.json
├── README.md
└── LICENSE
```

---

## Keamanan
Harap segera melaporkan segala celah keamanan yang ditemukan ke alamat email: **security@gusviradigital.co.id**. Jangan melaporkan masalah keamanan melalui pelacakan isu publik.

---

## Dukungan
Untuk bantuan, pertanyaan, atau saran:
- 📧 Email: support@gusviradigital.co.id
- 📝 Laporan Masalah: [GitHub Issues](https://github.com/gusviradigital/gdp-payment-gateway/issues)
- 🌐 Informasi Lain: https://gusviradigital.co.id/

---

## Lisensi
Paket ini dirilis di bawah lisensi MIT. Silakan lihat berkas [LICENSE](LICENSE) untuk rincian lebih lanjut.

---