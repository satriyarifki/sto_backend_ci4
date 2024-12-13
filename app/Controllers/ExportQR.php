<?php

namespace App\Controllers;
use CodeIgniter\Controller;
use App\Libraries\QrCodeGenerator;

class ExportQR extends BaseController
{
    public function generateQRCode()
    {
        $contoh_data = [
            'nomor_faktur' => '12345678901234567890',
            'tanggal_faktur' => '2024-05-31',
            'pembeli' => [
                'nama' => 'PT Contoh Jaya',
                'npwp' => '01.234.567.8-901.234'
            ],
            'penjual' => [
                'nama' => 'CV Contoh Sejahtera',
                'npwp' => '09.876.543.2-109.876'
            ],
            'barang' => [
                [
                    'nama' => 'Laptop ASUS ROG',
                    'harga_satuan' => 15000000,
                    'jumlah' => 2,
                    'total' => 30000000
                ],
                [
                    'nama' => 'Monitor LG Ultrawide',
                    'harga_satuan' => 5000000,
                    'jumlah' => 1,
                    'total' => 5000000
                ]
            ],
            'subtotal' => 35000000,
            'pajak' => [
                'ppn' => [
                    'persentase' => 10,
                    'nilai' => 3500000
                ],
                'ppnbm' => [
                    'persentase' => 0,
                    'nilai' => 0
                ]
            ],
            'total' => 38500000
        ];

        $json_data = json_encode($contoh_data);

        $qrCodeGenerator = new QrCodeGenerator();
        $qrCodePath = $qrCodeGenerator->generate($json_data);

        return $this->response->download($qrCodePath, null)->setFileName('qrcode.png');
    }
}