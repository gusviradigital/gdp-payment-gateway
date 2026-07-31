<?php

namespace GusviraDigital\GdpPaymentGateway\Support;

class PaymentChannelRegistry
{
    private array $config;
    private string $baseImgUrl;
    
    private array $masterChannels;
    private array $categoryLabels;
    
    /**
     * Map Xendit's unique keys to generic keys.
     */
    private array $xenditAliases = [
        'BCA_VIRTUAL_ACCOUNT' => 'bca',
        'BNI_VIRTUAL_ACCOUNT' => 'bni',
        'BRI_VIRTUAL_ACCOUNT' => 'bri',
        'MANDIRI_VIRTUAL_ACCOUNT' => 'mandiri',
        'PERMATA_VIRTUAL_ACCOUNT' => 'permata',
        'BSI_VIRTUAL_ACCOUNT' => 'bsi',
        'BJB_VIRTUAL_ACCOUNT' => 'bjb',
        'CIMB_VIRTUAL_ACCOUNT' => 'cimb',
        'BNC_VIRTUAL_ACCOUNT' => 'bnc',
        'HANA_VIRTUAL_ACCOUNT' => 'hana',
        'MUAMALAT_VIRTUAL_ACCOUNT' => 'muamalat',
        'BSS_VIRTUAL_ACCOUNT' => 'bss',
        'CARDS' => 'credit_card',
        'BRI_DIRECT_DEBIT' => 'bri',
        'MANDIRIVA' => 'mandiri',
        'BNIVA' => 'bni',
        'BRIVA' => 'bri',
        'PERMATAVA' => 'permata',
        'BCAVA' => 'bca',
        'CIMBVA' => 'cimb',
        'BSIVA' => 'bsi',
        'OVO' => 'ovo',
        'DANA' => 'dana',
        'LINKAJA' => 'linkaja',
        'SHOPEEPAY' => 'shopeepay',
        'ASTRAPAY' => 'astrapay',
        'QRIS' => 'qris',
        'ALFAMART' => 'alfamart',
        'INDOMARET' => 'indomaret',
        'KREDIVO' => 'kredivo',
        'AKULAKU' => 'akulaku',
        'UANGME' => 'uangme',
        'INDODANA' => 'indodana',
    ];

    private array $duitkuAliases = [ 
        // Credit Card
        'VC' => 'credit_card',
        
        // Virtual Account
        'BC' => 'bca',
        'M2' => 'mandiri',
        'VA' => 'maybank',
        'I1' => 'bni',
        'B1' => 'cimb',
        'BT' => 'permata',
        'A1' => 'atm_bersama',
        'AG' => 'artha_graha',
        'NC' => 'bnc',
        'BR' => 'briva',
        'S1' => 'bss', // Bank Sahabat Sampoerna
        'DM' => 'danamon',
        'BV' => 'bsi',
        
        // Retail
        'FT' => 'alfamart', // Pegadaian/ALFA/Pos
        'IR' => 'indomaret',
        
        // E-Wallet
        'OV' => 'ovo',
        'SA' => 'shopeepay',
        'LF' => 'linkaja',
        'LA' => 'linkaja',
        'DA' => 'dana',
        'SL' => 'shopeepay', // Shopee Pay Account Link
        'OL' => 'ovo', // OVO Account Link
        
        // QRIS
        'SP' => 'qris', // Shopee Pay QRIS
        'NQ' => 'qris', // Nobu QRIS
        'GQ' => 'gudang_voucher', // Gudang Voucher
        'SQ' => 'nusapay',
        
        // Kredit / Paylater
        'DN' => 'indodana',
        'AT' => 'atome',
        
        // E-Banking
        'JP' => 'jeniuspay',
        
        // E-Commerce
        'T1' => 'tokopedia',
        'T2' => 'tokopedia',
        'T3' => 'tokopedia',
    ];

    public function __construct(array $config, string $baseImgUrl = '/assets/images/payment/')
    {
        $this->config = $config;
        $this->baseImgUrl = rtrim($baseImgUrl, '/') . '/';
        $this->initMasterChannels();
        $this->initCategoryLabels();
    }

    private function initMasterChannels(): void
    {
        $this->masterChannels = [
            // Virtual Account
            'artha_graha' => ['name' => 'Arta Graha Virtual Account', 'icon' => $this->baseImgUrl . 'ag.png', 'category' => 'virtual_account'],
            'atm_bersama' => ['name' => 'ATM Bersama Virtual Account', 'icon' => $this->baseImgUrl . 'a1.png', 'category' => 'virtual_account'],
            'bca' => ['name' => 'BCA Virtual Account', 'icon' => $this->baseImgUrl . 'bca.png', 'category' => 'virtual_account'],
            'bni' => ['name' => 'BNI Virtual Account', 'icon' => $this->baseImgUrl . 'bni.png', 'category' => 'virtual_account'],
            'bri' => ['name' => 'BRI Virtual Account', 'icon' => $this->baseImgUrl . 'bri.png', 'category' => 'virtual_account'],
            'briva' => ['name' => 'BRI Virtual Account', 'icon' => $this->baseImgUrl . 'br.png', 'category' => 'virtual_account'],
            'bsi' => ['name' => 'BSI Virtual Account', 'icon' => $this->baseImgUrl . 'bsi.png', 'category' => 'virtual_account'],
            'echannel' => ['name' => 'Mandiri Virtual Account', 'icon' => $this->baseImgUrl . 'mandiri.png', 'category' => 'virtual_account'],
            'mandiri' => ['name' => 'Mandiri Virtual Account', 'icon' => $this->baseImgUrl . 'mandiri.png', 'category' => 'virtual_account'],
            'permata' => ['name' => 'Permata Virtual Account', 'icon' => $this->baseImgUrl . 'permata.png', 'category' => 'virtual_account'],
            'cimb' => ['name' => 'CIMB Niaga Virtual Account', 'icon' => $this->baseImgUrl . 'cimb.png', 'category' => 'virtual_account'],
            'cimb_niaga' => ['name' => 'CIMB Niaga Virtual Account', 'icon' => $this->baseImgUrl . 'cimb.png', 'category' => 'virtual_account'],
            'danamon' => ['name' => 'Danamon Virtual Account', 'icon' => $this->baseImgUrl . 'danamon.png', 'category' => 'virtual_account'],
            'seabank' => ['name' => 'SeaBank Virtual Account', 'icon' => $this->baseImgUrl . 'seabank.png', 'category' => 'virtual_account'],
            'bank_mega' => ['name' => 'Bank Mega Virtual Account', 'icon' => $this->baseImgUrl . 'bank-mega.png', 'category' => 'virtual_account'],
            'bjb' => ['name' => 'BJB Virtual Account', 'icon' => $this->baseImgUrl . 'bjb.png', 'category' => 'virtual_account'],
            'bnc' => ['name' => 'Neo Commerce (BNC)', 'icon' => $this->baseImgUrl . 'bnc.png', 'category' => 'virtual_account'],
            'bss' => ['name' => 'BSS Virtual Account', 'icon' => $this->baseImgUrl . 'bss.png', 'category' => 'virtual_account'],
            'hana' => ['name' => 'Hana Bank', 'icon' => $this->baseImgUrl . 'hana.png', 'category' => 'virtual_account'],
            'jenius' => ['name' => 'Jenius', 'icon' => $this->baseImgUrl . 'jenius.png', 'category' => 'virtual_account'],
            'maybank' => ['name' => 'Maybank Virtual Account', 'icon' => $this->baseImgUrl . 'maybank.png', 'category' => 'virtual_account'],
            'muamalat' => ['name' => 'Muamalat Virtual Account', 'icon' => $this->baseImgUrl . 'muamalat.png', 'category' => 'virtual_account'],
            'nobu' => ['name' => 'Nobu Virtual Account', 'icon' => $this->baseImgUrl . 'nobu.png', 'category' => 'virtual_account'],
            'ocbc' => ['name' => 'OCBC NISP Virtual Account', 'icon' => $this->baseImgUrl . 'ocbc.png', 'category' => 'virtual_account'],
            
            // E-Wallet
            'gopay' => ['name' => 'GoPay', 'icon' => $this->baseImgUrl . 'gopay.png', 'category' => 'ewallet'],
            'gopay_recurring' => ['name' => 'GoPay Recurring', 'icon' => $this->baseImgUrl . 'gopay.png', 'category' => 'ewallet'],
            'gopay_dynamic_qris' => ['name' => 'GoPay', 'icon' => $this->baseImgUrl . 'gopay.png', 'category' => 'ewallet'],
            'gopay_static_qris' => ['name' => 'GoPay', 'icon' => $this->baseImgUrl . 'gopay.png', 'category' => 'ewallet'],
            'shopeepay' => ['name' => 'ShopeePay', 'icon' => $this->baseImgUrl . 'shopeePay.png', 'category' => 'ewallet'],
            'dana' => ['name' => 'DANA', 'icon' => $this->baseImgUrl . 'dana.png', 'category' => 'ewallet'],
            'ovo' => ['name' => 'OVO', 'icon' => $this->baseImgUrl . 'ovo.png', 'category' => 'ewallet'],
            'linkaja' => ['name' => 'LinkAja', 'icon' => $this->baseImgUrl . 'linkaja.png', 'category' => 'ewallet'],
            'astrapay' => ['name' => 'AstraPay', 'icon' => $this->baseImgUrl . 'astrapay.png', 'category' => 'ewallet'],
            'nusapay' => ['name' => 'NusaPay', 'icon' => $this->baseImgUrl . 'nusapay.png', 'category' => 'ewallet'],
            'jeniuspay' => ['name' => 'Jenius Pay', 'icon' => $this->baseImgUrl . 'jenius.png', 'category' => 'ewallet'],
            'gudang_voucher' => ['name' => 'Gudang Voucher', 'icon' => $this->baseImgUrl . 'gv.png', 'category' => 'ewallet'],
            'tokopedia' => ['name' => 'Tokopedia', 'icon' => $this->baseImgUrl . 'tokopedia.webp', 'category' => 'ewallet'],
            
            // QRIS
            'qris' => ['name' => 'QRIS', 'icon' => $this->baseImgUrl . 'qris.png', 'category' => 'qris'],
            
            // Retail
            'alfamart' => ['name' => 'Alfamart', 'icon' => $this->baseImgUrl . 'alfamart.svg', 'category' => 'retail'],
            'indomaret' => ['name' => 'Indomaret', 'icon' => $this->baseImgUrl . 'indomaret.png', 'category' => 'retail'],
            'alfamidi' => ['name' => 'Alfamidi', 'icon' => $this->baseImgUrl . 'alfamidi.png', 'category' => 'retail'],
            
            // Credit Card
            'amex' => ['name' => 'Amex Card', 'icon' => $this->baseImgUrl . 'amex.png', 'category' => 'credit_card'],
            'credit_card' => ['name' => 'Credit/Debit Card', 'icon' => $this->baseImgUrl . 'mastercard.png', 'category' => 'credit_card'],
            'visa' => ['name' => 'Visa', 'icon' => $this->baseImgUrl . 'visa.png', 'category' => 'credit_card'],
            'mastercard' => ['name' => 'Mastercard', 'icon' => $this->baseImgUrl . 'mastercard.png', 'category' => 'credit_card'],
            
            // Paylater
            'kredivo' => ['name' => 'Kredivo', 'icon' => $this->baseImgUrl . 'kredivo.png', 'category' => 'paylater'],
            'akulaku' => ['name' => 'Akulaku', 'icon' => $this->baseImgUrl . 'akulaku.svg', 'category' => 'paylater'],
            'indodana' => ['name' => 'Indodana', 'icon' => $this->baseImgUrl . 'indodana.png', 'category' => 'paylater'],
            'uangme' => ['name' => 'UangMe', 'icon' => $this->baseImgUrl . 'uangme.png', 'category' => 'paylater'],
            'atome' => ['name' => 'Atome', 'icon' => $this->baseImgUrl . 'atom.webp', 'category' => 'paylater'],
            
            // Direct Debit
            'bri_direct_debit' => ['name' => 'BRI Direct Debit', 'icon' => $this->baseImgUrl . 'bri.png', 'category' => 'direct_debit'],
            'dd_bri' => ['name' => 'BRI Direct Debit', 'icon' => $this->baseImgUrl . 'bri.png', 'category' => 'direct_debit'],
            'dd_mandiri' => ['name' => 'Mandiri Direct Debit', 'icon' => $this->baseImgUrl . 'mandiri.png', 'category' => 'direct_debit'],
            'bri_epay' => ['name' => 'BRI e-Pay', 'icon' => $this->baseImgUrl . 'bri.png', 'category' => 'direct_debit'],
        ];
    }

    private function initCategoryLabels(): void
    {
        $this->categoryLabels = [
            'virtual_account' => 'Virtual Account',
            'ewallet' => 'E-Wallet',
            'qris' => 'QR Code (QRIS)',
            'retail' => 'Minimarket / Retail',
            'credit_card' => 'Kartu Kredit / Debit',
            'paylater' => 'PayLater',
            'direct_debit' => 'Direct Debit',
            'manual_transfer' => 'Transfer Manual',
        ];
    }

    public function getActiveChannels(): object
    {
        $activeChannelsRaw = [];

        // 1. Midtrans
        if (!empty($this->config['midtrans_enabled'])) {
            $midtransChannels = $this->config['midtrans_enabled_channels'] ?? [];
            if (is_array($midtransChannels)) {
                foreach ($midtransChannels as $code => $isEnabled) {
                    $cleanCode = strtolower($code);
                    if ($isEnabled && isset($this->masterChannels[$cleanCode])) {
                        $key = 'midtrans-' . $cleanCode;
                        $activeChannelsRaw[$key] = $this->masterChannels[$cleanCode];
                    }
                }
            }
        }

        // 2. Xendit
        if (!empty($this->config['xendit_enabled'])) {
            $xenditChannels = $this->config['xendit_enabled_channels'] ?? [];
            if (is_array($xenditChannels)) {
                foreach ($xenditChannels as $code => $isEnabled) {
                    if ($isEnabled) {
                        $mappedCode = $this->xenditAliases[strtoupper($code)] ?? strtolower($code);
                        if (isset($this->masterChannels[$mappedCode])) {
                            // Xendit often requires the exact original code for API requests,
                            // We use the original $code as the value, mapped to the generic masterChannel icon.
                            $key = 'xendit-' . $code; 
                            $activeChannelsRaw[$key] = $this->masterChannels[$mappedCode];
                        }
                    }
                }
            }
        }

        // 3. Tripay
        if (!empty($this->config['tripay_enabled'])) {
            $tripayChannels = $this->config['tripay_enabled_channels'] ?? [];
            if (is_array($tripayChannels)) {
                foreach ($tripayChannels as $code => $isEnabled) {
                    $cleanCode = strtolower($code);
                    if ($isEnabled && isset($this->masterChannels[$cleanCode])) {
                        $key = 'tripay-' . $cleanCode;
                        $activeChannelsRaw[$key] = $this->masterChannels[$cleanCode];
                    }
                }
            }
        }

        // 4. Duitku
        if (!empty($this->config['duitku_enabled'])) {
            $duitkuChannels = $this->config['duitku_enabled_channels'] ?? [];
            if (is_array($duitkuChannels)) {
                foreach ($duitkuChannels as $code => $isEnabled) {
                    $cleanCode = strtolower($code);
                    if ($isEnabled && isset($this->masterChannels[$cleanCode])) {
                        $key = 'duitku-' . $cleanCode;
                        $activeChannelsRaw[$key] = $this->masterChannels[$cleanCode];
                    }
                }
            }
        }

        // 5. PayPal
        if (!empty($this->config['paypal_enabled'])) {
            $activeChannelsRaw['paypal-paypal'] = [
                'name' => 'PayPal',
                'icon' => $this->baseImgUrl . 'paypal.png', 
                'category' => 'ewallet'
            ];
        }

        $groupedChannels = [];
        foreach ($activeChannelsRaw as $code => $data) {
            $cat = $data['category'];
            if (!isset($groupedChannels[$cat])) {
                $groupedChannels[$cat] = [
                    'label' => $this->categoryLabels[$cat] ?? 'Other',
                    'methods' => []
                ];
            }
            $groupedChannels[$cat]['methods'][$code] = [
                'name' => $data['name'],
                'icon' => $data['icon']
            ];
        }

        $sortedGroupedChannels = [];
        foreach (array_keys($this->categoryLabels) as $catKey) {
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
