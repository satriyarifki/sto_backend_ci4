<?php

namespace App\Libraries;

use TCPDF;

class MY_TCPDF extends TCPDF {

    private $barcodeData; // Menyimpan data barcode

    public function setBarcodeData($data) {
        $this->barcodeData = $data;
    }

    //Page header
    public function Header() {
        $image_file = ROOTPATH.'public/img/ico.png';
        $this->Image($image_file, '', '', 18);
        $this->SetFont('helvetica', 'B', 11);
        $this->SetX(38);
        $this->Cell(0, 2, 'PT. Mekar Armada Jaya', 0, 1, '', 0, '', 0);
        $this->SetFont('helvetica', '', 9);
        $this->SetX(38);
        $this->Cell(0, 2, 'Jl. Raya Diponegoro KM.38 No.107, Setiamekar, Jatimulya', 0, 1, '', 0, '', 0);
        $this->SetX(38);
        $this->Cell(0, 2, 'Kec. Tambun Sel., Kabupaten Bekasi, Jawa Barat 17510', 0, 1, '', 0, '', 0);
        $this->SetX(38);
        $this->Cell(0, 2, 'Telp. (021) 88346911', 0, 1, '', 0, '', 0);
        $this->SetX(38);
        $this->Cell(0, 2, 'https://newarmada.biz/', 0, 1, '', 0, '', 0);
        $this->write2DBarcode($this->barcodeData, 'QRCODE,H', 0, 3, 20, 20, ['position' => 'R'], 'N');
        $style = array('width' => 0.25, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
        $this->Line(15, 27, 195, 27, $style);

    }

    // Page footer
    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}