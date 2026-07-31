<?php

if (!function_exists('gdp_payment_get_active_channels')) {
    /**
     * Mengambil daftar metode pembayaran yang aktif berdasarkan konfigurasi yang diberikan.
     * Mengelompokkan berdasarkan kategori (Virtual Account, E-Wallet, dll).
     *
     * @param array $config Konfigurasi metode pembayaran yang aktif
     * @param string $baseImgUrl URL dasar untuk gambar icon pembayaran
     * @return object
     */
    function gdp_payment_get_active_channels(array $config, string $baseImgUrl = '/assets/images/payment/'): object
    {
        $masterChannels = [
            //Virtual Account
            'bca' => ['name' => 'BCA Virtual Account', 'icon' => $baseImgUrl . 'bca.png', 'category' => 'virtual_account'],
            'bni' => ['name' => 'BNI Virtual Account', 'icon' => $baseImgUrl . 'bni.png', 'category' => 'virtual_account'],
            'bri' => ['name' => 'BRI Virtual Account', 'icon' => $baseImgUrl . 'bri.png', 'category' => 'virtual_account'],
            'bsi' => ['name' => 'BSI Virtual Account', 'icon' => $baseImgUrl . 'bsi.png', 'category' => 'virtual_account'],
            'echannel' => ['name' => 'Mandiri Virtual Account', 'icon' => $baseImgUrl . 'mandiri.png', 'category' => 'virtual_account'],
            'mandiri' => ['name' => 'Mandiri Virtual Account', 'icon' => $baseImgUrl . 'mandiri.png', 'category' => 'virtual_account'],
            'permata' => ['name' => 'Permata Virtual Account', 'icon' => $baseImgUrl . 'permata.png', 'category' => 'virtual_account'],
            'cimb' => ['name' => 'CIMB Niaga Virtual Account', 'icon' => $baseImgUrl . 'cimb.png', 'category' => 'virtual_account'],
            'cimb_niaga' => ['name' => 'CIMB Niaga Virtual Account', 'icon' => $baseImgUrl . 'cimb.png', 'category' => 'virtual_account'],
            'danamon' => ['name' => 'Danamon Virtual Account', 'icon' => $baseImgUrl . 'danamon.png', 'category' => 'virtual_account'],
            'seabank' => ['name' => 'SeaBank Virtual Account', 'icon' => $baseImgUrl . 'seabank.png', 'category' => 'virtual_account'],
            'bank_mega' => ['name' => 'Bank Mega Virtual Account', 'icon' => $baseImgUrl . 'bank-mega.png', 'category' => 'virtual_account'],
            'bjb' => ['name' => 'BJB Virtual Account', 'icon' => $baseImgUrl . 'bjb.png', 'category' => 'virtual_account'],
            'bnc' => ['name' => 'Neo Commerce (BNC)', 'icon' => $baseImgUrl . 'bnc.png', 'category' => 'virtual_account'],
            'bss' => ['name' => 'BSS Virtual Account', 'icon' => $baseImgUrl . 'bss.png', 'category' => 'virtual_account'],
            'hana' => ['name' => 'Hana Bank', 'icon' => $baseImgUrl . 'hana.png', 'category' => 'virtual_account'],
            'jenius' => ['name' => 'Jenius', 'icon' => $baseImgUrl . 'jenius.png', 'category' => 'virtual_account'],
            'maybank' => ['name' => 'Maybank Virtual Account', 'icon' => $baseImgUrl . 'maybank.png', 'category' => 'virtual_account'],
            'muamalat' => ['name' => 'Muamalat Virtual Account', 'icon' => $baseImgUrl . 'muamalat.png', 'category' => 'virtual_account'],
            'nobu' => ['name' => 'Nobu Virtual Account', 'icon' => $baseImgUrl . 'nobu.png', 'category' => 'virtual_account'],
            'ocbc' => ['name' => 'OCBC NISP Virtual Account', 'icon' => $baseImgUrl . 'ocbc.png', 'category' => 'virtual_account'],
            
            //E-Wallet
            'gopay' => ['name' => 'GoPay', 'icon' => $baseImgUrl . 'gopay.png', 'category' => 'ewallet'],
            'gopay_dynamic_qris' => ['name' => 'GoPay', 'icon' => $baseImgUrl . 'gopay.png', 'category' => 'ewallet'],
            'gopay_static_qris' => ['name' => 'GoPay', 'icon' => $baseImgUrl . 'gopay.png', 'category' => 'ewallet'],
            'shopeepay' => ['name' => 'ShopeePay', 'icon' => $baseImgUrl . 'shopeePay.png', 'category' => 'ewallet'],
            'dana' => ['name' => 'DANA', 'icon' => $baseImgUrl . 'dana.png', 'category' => 'ewallet'],
            'ovo' => ['name' => 'OVO', 'icon' => $baseImgUrl . 'ovo.png', 'category' => 'ewallet'],
            'linkaja' => ['name' => 'LinkAja', 'icon' => $baseImgUrl . 'linkaja.png', 'category' => 'ewallet'],
            'astrapay' => ['name' => 'AstraPay', 'icon' => $baseImgUrl . 'astrapay.png', 'category' => 'ewallet'],
            'nusapay' => ['name' => 'NusaPay', 'icon' => $baseImgUrl . 'nusapay.png', 'category' => 'ewallet'],
            
            'qris' => ['name' => 'QRIS', 'icon' => $baseImgUrl . 'qris.png', 'category' => 'qris'],
            
            //Retail
            'alfamart' => ['name' => 'Alfamart', 'icon' => $baseImgUrl . 'alfamart.svg', 'category' => 'retail'],
            'indomaret' => ['name' => 'Indomaret', 'icon' => $baseImgUrl . 'indomaret.png', 'category' => 'retail'],
            'alfamidi' => ['name' => 'Alfamidi', 'icon' => $baseImgUrl . 'alfamidi.png', 'category' => 'retail'],
            
            //Credit Card
            'amex' => ['name' => 'Amex Card', 'icon' => $baseImgUrl . 'amex.png', 'category' => 'credit_card'],
            'credit_card' => ['name' => 'Credit/Debit Card', 'icon' => $baseImgUrl . 'mastercard.png', 'category' => 'credit_card'],
            'visa' => ['name' => 'Visa', 'icon' => $baseImgUrl . 'visa.png', 'category' => 'credit_card'],
            'mastercard' => ['name' => 'Mastercard', 'icon' => $baseImgUrl . 'mastercard.png', 'category' => 'credit_card'],
            
            //Paylater
            'kredivo' => ['name' => 'Kredivo', 'icon' => $baseImgUrl . 'kredivo.png', 'category' => 'paylater'],
            'akulaku' => ['name' => 'Akulaku', 'icon' => $baseImgUrl . 'akulaku.svg', 'category' => 'paylater'],
            'indodana' => ['name' => 'Indodana', 'icon' => $baseImgUrl . 'indodana.png', 'category' => 'paylater'],
            'uangme' => ['name' => 'UangMe', 'icon' => $baseImgUrl . 'uangme.png', 'category' => 'paylater'],
        ];

        $categoryLabels = [
            'virtual_account' => 'Virtual Account',
            'ewallet' => 'E-Wallet',
            'qris' => 'QR Code (QRIS)',
            'retail' => 'Minimarket / Retail',
            'credit_card' => 'Kartu Kredit / Debit',
            'paylater' => 'PayLater',
            'manual_transfer' => 'Transfer Manual',
        ];

        $activeChannelsRaw = [];

        // 1. Cek Midtrans
        if (!empty($config['midtrans_enabled'])) {
            $midtransChannels = $config['midtrans_enabled_channels'] ?? [];
            if (is_array($midtransChannels)) {
                foreach ($midtransChannels as $code => $isEnabled) {
                    if ($isEnabled && isset($masterChannels[strtolower($code)])) {
                        $key = 'midtrans-' . strtolower($code);
                        $activeChannelsRaw[$key] = $masterChannels[strtolower($code)];
                    }
                }
            }
        }

        // 2. Cek Xendit
        if (!empty($config['xendit_enabled'])) {
            $xenditChannels = $config['xendit_enabled_channels'] ?? [];
            if (is_array($xenditChannels)) {
                foreach ($xenditChannels as $code => $isEnabled) {
                    if ($isEnabled && isset($masterChannels[strtolower($code)])) {
                        $key = 'xendit-' . strtolower($code);
                        $activeChannelsRaw[$key] = $masterChannels[strtolower($code)];
                    }
                }
            }
        }

        // 3. Cek Tripay
        if (!empty($config['tripay_enabled'])) {
            $tripayChannels = $config['tripay_enabled_channels'] ?? [];
            if (is_array($tripayChannels)) {
                foreach ($tripayChannels as $code => $isEnabled) {
                    if ($isEnabled && isset($masterChannels[strtolower($code)])) {
                        $key = 'tripay-' . strtolower($code);
                        $activeChannelsRaw[$key] = $masterChannels[strtolower($code)];
                    }
                }
            }
        }

        // 4. Cek Duitku
        if (!empty($config['duitku_enabled'])) {
            $duitkuChannels = $config['duitku_enabled_channels'] ?? [];
            if (is_array($duitkuChannels)) {
                foreach ($duitkuChannels as $code => $isEnabled) {
                    if ($isEnabled && isset($masterChannels[strtolower($code)])) {
                        $key = 'duitku-' . strtolower($code);
                        $activeChannelsRaw[$key] = $masterChannels[strtolower($code)];
                    }
                }
            }
        }

        // 5. Cek PayPal
        if (!empty($config['paypal_enabled'])) {
            $activeChannelsRaw['paypal-paypal'] = [
                'name' => 'PayPal',
                'icon' => $baseImgUrl . 'paypal.png', 
                'category' => 'ewallet'
            ];
        }

        $groupedChannels = [];
        foreach ($activeChannelsRaw as $code => $data) {
            $cat = $data['category'];
            if (!isset($groupedChannels[$cat])) {
                $groupedChannels[$cat] = [
                    'label' => $categoryLabels[$cat] ?? 'Other',
                    'methods' => []
                ];
            }
            $groupedChannels[$cat]['methods'][$code] = [
                'name' => $data['name'],
                'icon' => $data['icon']
            ];
        }

        $sortedGroupedChannels = [];
        foreach (array_keys($categoryLabels) as $catKey) {
            if (isset($groupedChannels[$catKey])) {
                $sortedGroupedChannels[$catKey] = $groupedChannels[$catKey];
            }
        }

        return (object) [
            'status' => 'success',
            'data' => $sortedGroupedChannels,
            'message' => 'Daftar channel aktif berhasil diambil.'
        ];
    }
}
