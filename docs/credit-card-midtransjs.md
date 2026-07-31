# Integrasi Credit Card Midtrans via Core API (Midtrans.js)

Karena *library* ini menggunakan **Midtrans Core API**, pemrosesan Kartu Kredit (termasuk Amex, Visa, Mastercard, JCB) **wajib** menggunakan tokenisasi di sisi *frontend* demi keamanan (PCI-DSS compliance). Anda tidak boleh mengirimkan nomor kartu mentah (Plain-text) langsung ke backend/server Anda.

Alur kerjanya adalah sebagai berikut:
1. Pengguna memasukkan data kartu (Nomor, Expiry, CVV) pada *form* HTML di website Anda.
2. Javascript (`midtrans.js`) mengambil data tersebut dan mengirimkannya langsung ke server Midtrans.
3. Server Midtrans merespons dengan memberikan `token_id`.
4. Website Anda mengirimkan `token_id` (beserta data order lainnya) ke backend PHP Anda.
5. Backend PHP memanggil `MidtransController->createTransaction()` dengan menyisipkan `$params['token_id']`.

## Contoh Implementasi Frontend (HTML & JS)

Berikut adalah contoh *boilerplate* sederhana yang bisa Anda pasang pada antarmuka/frontend Anda:

```html
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pembayaran Kartu Kredit</title>
    <!-- Ganti dengan URL Production jika sudah live -->
    <script type="text/javascript" src="https://api.sandbox.midtrans.com/v2/assets/js/midtrans.js" data-environment="sandbox" data-client-key="CLIENT_KEY_ANDA_DISINI"></script>
</head>
<body>

    <h2>Pembayaran Kartu Kredit</h2>
    <form id="payment-form">
        <label>Nomor Kartu:</label>
        <input type="text" id="card-number" required>
        
        <label>Bulan Kadaluarsa (MM):</label>
        <input type="text" id="card-exp-month" required>
        
        <label>Tahun Kadaluarsa (YYYY):</label>
        <input type="text" id="card-exp-year" required>
        
        <label>CVV:</label>
        <input type="text" id="card-cvv" required>

        <button type="button" id="pay-button">Bayar Sekarang</button>
    </form>

    <script>
        document.getElementById('pay-button').onclick = function() {
            // Menonaktifkan tombol agar tidak di-klik 2 kali
            this.disabled = true;
            this.innerText = "Memproses...";

            // 1. Kumpulkan data dari form
            var cardData = {
                "card_number": document.getElementById('card-number').value,
                "card_exp_month": document.getElementById('card-exp-month').value,
                "card_exp_year": document.getElementById('card-exp-year').value,
                "card_cvv": document.getElementById('card-cvv').value,
            };

            // 2. Minta Token ID dari Midtrans
            MidtransNew3ds.getCardToken(cardData, {
                onSuccess: function(response) {
                    // Token berhasil didapatkan!
                    var tokenId = response.token_id;
                    console.log("Token ID:", tokenId);
                    
                    // 3. Kirim Token ID ke backend PHP Anda via AJAX/Fetch
                    fetch('/endpoint-php-anda.php', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({
                            method: 'credit_card', // Atau 'amex'
                            token_id: tokenId,
                            order_id: 'ORDER-123',
                            amount: 100000
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        // 4. Jika butuh 3D Secure (OTP), redirect URL dari backend
                        if (data.redirect_url) {
                            window.location.href = data.redirect_url;
                        } else {
                            alert("Pembayaran Berhasil / Diproses!");
                        }
                    });
                },
                onFailure: function(response) {
                    // Gagal mendapatkan token (misal: kartu tidak valid)
                    alert("Gagal memvalidasi kartu: " + response.validation_messages.join(", "));
                    document.getElementById('pay-button').disabled = false;
                    document.getElementById('pay-button').innerText = "Bayar Sekarang";
                }
            });
        };
    </script>
</body>
</html>
```

### Catatan Penting
- **Client Key**: Pastikan Anda mengganti `CLIENT_KEY_ANDA_DISINI` dengan Client Key dari dasbor Midtrans Anda.
- **Environment**: Gunakan script dari `https://api.midtrans.com/v2/assets/js/midtrans.js` jika sudah beralih ke Production, dan ubah `data-environment="production"`.
- **3D Secure (OTP)**: Karena di dalam controller kita mengatur `'authentication' => true`, URL *redirect* yang dikembalikan oleh backend akan mengarahkan pelanggan ke halaman bank untuk memasukkan kode OTP (OTP Challenge). Setelah OTP benar, pelanggan akan otomatis dikembalikan ke *Callback/Return URL* Anda.
