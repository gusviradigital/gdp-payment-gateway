# Implementasi GDP Payment Gateway

Library ini bersifat **framework-agnostic**, artinya dapat digunakan pada berbagai framework PHP (seperti Laravel, CodeIgniter, Symfony, atau Native PHP).

## 1. Instalasi

Karena ini adalah package composer, pastikan Anda telah menjalankan `composer install` pada direktori root, dan autoloader Composer telah dimuat di aplikasi Anda.

```php
require_once __DIR__ . '/vendor/autoload.php';
```

## 2. Konfigurasi
Library ini dirancang agar konfigurasi diberikan langsung saat inisiasi kelas atau dipassing melalui parameter, sehingga tidak ada ketergantungan pada *singleton state*.

```php
// Siapkan array konfigurasi Anda (bisa didapat dari database atau .env)
$config = [
    'midtrans_enabled' => true,
    'midtrans_server_key' => 'SB-Mid-server-xxxx',
    'midtrans_client_key' => 'SB-Mid-client-xxxx',
    'midtrans_is_production' => false,
    
    // ... konfigurasi gateway lainnya
];
```

## 3. Menampilkan Metode Pembayaran Aktif

Anda dapat menggunakan helper yang disediakan untuk mengambil daftar metode pembayaran yang aktif. Metode pembayaran dikelompokkan berdasarkan kategori.

```php
// Jika Anda tidak menggunakan Composer autoload files, pastikan menginclude helper:
require_once __DIR__ . '/src/Helpers/helpers.php';

// Pastikan Anda sudah menyiapkan $config seperti pada langkah 2

// Ambil daftar metode
$channelsResponse = gdp_payment_get_active_channels($config, 'https://domain-anda.com/assets/images/payment/');

if ($channelsResponse->status === 'success') {
    $activeCategories = $channelsResponse->data;
    foreach ($activeCategories as $categoryKey => $categoryData) {
        echo "Kategori: " . $categoryData['label'] . "\n";
        foreach ($categoryData['methods'] as $code => $method) {
            echo "- " . $method['name'] . " (" . $code . ")\n";
        }
    }
}
```

## 4. Membuat Transaksi Pembayaran

Library ini memisahkan Controller berdasarkan *payment gateway*-nya. Anda dapat menggunakan `MidtransController`, `XenditController`, `TripayController`, `DuitkuController`, `PaypalController`, atau `ManualController`.

```php
use GusviraDigital\GdpPaymentGateway\Controllers\MidtransController;

// Pastikan Anda meneruskan array $config
// Anda bisa mengganti ini dengan XenditController($config), TripayController($config), dll sesuai kebutuhan.
$controller = new MidtransController($config);

$params = [
    'order_id' => 'ORDER-12345',
    'amount' => 150000,
    'method' => 'bca', // atau 'snap' untuk mode Pop-up
    'description' => 'Pembayaran Tagihan',
    'customer_details' => [
        'first_name' => 'Budi',
        'email' => 'budi@example.com',
        'phone' => '08123456789'
    ],
    // URL opsional jika Anda ingin overide dari konfigurasi Option
    'return_url' => 'https://domain-anda.com/payment-finish'
];

try {
    $response = $controller->createTransaction($params);
    
    echo "Status: " . $response->status . "\n";
    echo "URL Pembayaran: " . $response->paymentUrl . "\n";
    echo "Referensi: " . $response->reference . "\n";
    
    // Simpan ke database Anda
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
```

## 5. Menangani Webhook (Callback)

Gateway pembayaran akan mengirimkan request (webhook) ketika status pembayaran berubah. Anda harus membuat endpoint di aplikasi Anda (misal: `POST /api/payment/callback/midtrans`).

```php
use GusviraDigital\GdpPaymentGateway\Controllers\CallbackController;

// 1. Ambil payload mentah dari request
$payload = file_get_contents('php://input');
$provider = 'midtrans'; // Ambil dari URL route aplikasi Anda

// 2. Siapkan $config Anda (sesuai langkah 2)
// $config = [...]

// 3. Dispatch ke CallbackController
$callback = new CallbackController();
$result = $callback->handle($provider, $payload, $config);

if ($result['status'] === 'success') {
    $orderId = $result['order_id'];
    $transactionStatus = $result['transaction_status'];
    
    // 3. Update database Anda berdasarkan $orderId dan $transactionStatus
    if ($transactionStatus === 'success') {
        // Tandai pesanan sebagai Lunas
    } elseif ($transactionStatus === 'failed') {
        // Batalkan pesanan
    }
    
    // 4. Balas response 200 OK ke Gateway
    http_response_code(200);
    echo json_encode(['status' => 'ok']);
} else {
    // Verifikasi gagal (misal: Signature tidak valid)
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $result['message']]);
}
```

## Polling vs SSE

Seperti yang disarankan, untuk kompatibilitas framework yang maksimal (terutama di PHP tradisional dengan Apache/Nginx + PHP-FPM), gunakan **Polling** (AJAX Requests setiap beberapa detik) untuk mengecek status transaksi dari frontend alih-alih menggunakan SSE atau WebSocket yang dapat menyebabkan *worker exhaustion*.
