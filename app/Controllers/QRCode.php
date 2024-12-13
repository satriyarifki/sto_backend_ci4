<?php

namespace App\Controllers;
use SimpleSoftwareIO\QrCode\Generator;

class QRCode extends BaseController
{
    public function generateQRCode()
    {
        $qrcode = new Generator;
        $qrCodes = [];
        $qrCodes['styleRound'] = $qrcode->size(120)->color(0, 0, 0)->backgroundColor(255, 255, 255)->style('round')->generate('https://www.binaryboxtuts.com/');
        return view('QRCode', $qrCodes);
    }
}