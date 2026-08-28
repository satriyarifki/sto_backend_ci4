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
        $this->Image($image_file, '', '', 22);
        $this->SetFont('helvetica', 'B', 25);
        $this->SetX(38);
        $this->Cell(0, 2, 'KARTU INVENTORY', 0, 1, '', 0, '', 0);
        $this->SetFont('helvetica', '', 20);
        $this->SetX(38);
        $this->Cell(0, 2, 'TAG STO', 0, 1, '', 0, '', 0);
        $this->SetX(38);
        $this->write2DBarcode($this->barcodeData, 'QRCODE,H', 0, 3, 31, 31, ['position' => 'R'], 'N');
        $style = array('width' => 0.25, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));

    }

    // Page footer
    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}