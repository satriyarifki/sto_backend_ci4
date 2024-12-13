<?php

namespace App\Models;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ExcelModel extends \CodeIgniter\Model
{
    public function hashAndWritePasswords($inputFilePath, $outputFilePath)
    {
        // Membaca file Excel
        $spreadsheet = IOFactory::load($inputFilePath);
        $sheet = $spreadsheet->getActiveSheet();

        // Mendapatkan jumlah baris
        $lastRow = $sheet->getHighestRow();

        // Mendapatkan data kolom password dan menulis hash kembali ke kolom baru
        for ($row = 2; $row <= $lastRow; $row++) {
            $password = $sheet->getCell('B' . $row)->getValue(); // Ganti 'A' dengan kolom password Anda

            // Mengonversi password menjadi hash (gunakan algoritma hash yang sesuai)
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Menulis hash password ke kolom baru (misalnya, kolom B)
            $sheet->setCellValue('D' . $row, $hashedPassword);
        }

        // Menyimpan hasil perubahan ke file Excel baru
        $writer = new Xlsx($spreadsheet);
        $writer->save($outputFilePath);
    }
}