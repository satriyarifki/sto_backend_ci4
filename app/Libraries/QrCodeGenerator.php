<?php

namespace App\Libraries;

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class QrCodeGenerator
{
    public function generate($data)
    {
        $qrCode = new QrCode($data);
        $writer = new PngWriter();

        $qrCode->setSize(400);
        $qrCode->setMargin(2);

        $qrCodePath = WRITEPATH . 'qrcodes/qrcode_' . time() . '.png';
        $result = $writer->write($qrCode);
        $result->saveToFile($qrCodePath);

        return $qrCodePath;
    }
}
