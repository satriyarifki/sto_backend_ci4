<?php

namespace App\Controllers;
use App\Models\ExcelModel;
use App\Models\M_invoicing;
use App\Models\M_dropbox;
use App\Models\dokumen_ok_model;
use App\Models\M_curl;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Zxing\QrReader;
use Spatie\PdfToImage\Pdf;
use Imagick;
use \IonAuth\Libraries\IonAuth;

class Temp extends BaseController
{
    // protected $ionAuth;
    // public function __construct()
    // {
    //     $model = new IonAuth();
    //     $this->ionAuth = $model;
    // }
    public function __construct()
    {
        $model = new IonAuth();
        $this->ionAuth = $model;
    }

    public function tampilan()
    {
        return view('cobaread');
    }

    public function upload()
    {
        $uploadedFile = $_FILES['excel_file']['tmp_name'];
        $spreadsheet = IOFactory::load($uploadedFile);
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();

        $data = [];

        for ($row = 1; $row <= $highestRow; $row++) {
            $cellValue = $sheet->getCell('B' . $row)->getValue();
            $data[] = $cellValue;
        }

        $response['Faktur Pajak'] = $data[0];
        echo json_encode($response);
    }

    private function readExcelColumns($filePath, $columns)
    {
        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();
        $highestRow = $worksheet->getHighestRow();

        $dataArray = [];

        foreach ($columns as $col) {
            $columnData = [];
            for ($row = 1; $row <= $highestRow; $row++) {
                $cellValue = $worksheet->getCell($col . $row)->getValue();
                $columnData[] = $cellValue;
            }
            $dataArray[$col] = $columnData;
        }

        return $dataArray;
    }

    public function temp()
	{
        $this->ionAuth    = new \IonAuth\Libraries\IonAuth();
        $user = $this->ionAuth->user()->row();
        echo $user->email;
    }

    public function getPart()
    {
        $output['data'] = [];
        $M_curl = new M_curl();
        $this->SAP_PARAMS['function'] = 'Z_PUD';
        $this->SAP_PARAMS['params'] = [
            'RPT' => 'RPT_ZINVER',
            'ACTION' => 'OUT_PUD',
            'P_NOZINVER' => '68',
            // 'P_EBELN' => '4515003600',
            // 'P_ZXBLNR' => 'ANF-001-03',
            // 'P_ZRMWWR' => '11200000',
            // 'P_ZPUD_ACPT' => date('Ymd', strtotime('2024-06-15')),
            // 'P_ZTGLINV' => date('Ymd', strtotime('2024-06-16')),
            // 'P_ZJTMP' => date('Ymd', strtotime('2024-07-15')),
            'P_ZNIK_PUD' => 'K.8670',
            // 'P_ALASAN' => 'Agak lain',
        ];
        $sap = $M_curl->execute("POST", $this->SAP_PARAMS);

        $data['data'] = $sap;
        echo json_encode($data);
    }

    public function postDataToSap()
    {
        $M_curl = new M_curl();
        $this->SAP_PARAMS['data'] = array(
            'PTAG'        => 'PTAG',
            'PDATE'       => 'PDATE',
            'PSHIFT'      => 'PSHIFT',
            'PMATNR'      => 'PMATNR',
            'PQTY'        => 'PQTY',
            'PKET'        => 'PKET',
            'PUSER_INPUT' => 'PUSER_INPUT',
            'PDATE_INPUT' => 'PDATE_INPUT',
        );
        $this->SAP_PARAMS['function'] = 'Z_QC';
        $this->SAP_PARAMS['params'] = [
            'RPT' => 'P_MATNR_FERT',
        ];
        // $response = $M_curl->insertDataToSAP($this->SAP_PARAMS);
        print_r(json_encode($this->SAP_PARAMS));
    }

    public function uploadPDF()
    {
        $file = $this->request->getFile('png_file');
        $newName = $file->getRandomName();
        $file->move('uploads/', $newName);
        
        // Ubah file PDF menjadi gambar PNG
        $pngFilePath = $this->convertPDFtoPNG('uploads/' . $newName);
        // // Baca QR code dari gambar PNG
        // $qrCodeText = $this->readQRCodeFromPNG($pngFilePath);
        
        echo "Hasil pembacaan QR Code: ";
    }

    // private function convertPDFToImage($pdfFilePath)
    // {
    //     $imagePath = ''; // Inisialisasi path untuk menyimpan gambar hasil konversi

    //     try {
    //         $imagick = new \Imagick($pdfFilePath . '[0]');
    //         $imagick->setImageFormat('jpeg'); 
    //         $imagePath = 'writable/uploads/' . pathinfo($pdfFilePath, PATHINFO_FILENAME) . '.jpg';
    //         $imagick->writeImage($imagePath);
    //     } catch (\ImagickException $e) {
    //         log_message('error', 'Gagal mengonversi PDF ke gambar: ' . $e->getMessage());
    //     }
        
    //     return $imagePath;
    // }

    private function convertPDFtoPNG($pdfFilePath)
    {
        // Tentukan nama file sementara untuk gambar PNG
        $tempPNGFilePath = 'uploads/' . uniqid() . '.jpg';

        $imagick = new \Imagick($pdfFilePath);
        $imagick->setResolution(300, 300);
        $imagick->setImageFormat('jpg');    
        $imagick->writeImage($tempPNGFilePath); 
        $imagick->clear(); 
        
        return $tempPNGFilePath;
    }

    private function readQRCodeFromPNG($pngFilePath)
    {
        // Gunakan library ZXing untuk membaca QR code dari gambar PNG
        $qrcode = new \Zxing\QrReader($pngFilePath);
        $qrCodeText = $qrcode->text();
        
        return $qrCodeText;
    }

    public function readpng()
    {
        // $pdfFilePath = 'uploads/faktur.jpg';

        // $imagick = new \Imagick();
        
        // $imagick->setResolution(300, 300); // Atur resolusi sesuai kebutuhan
        // $imagick->readImage($pdfFilePath);
        // $imagick->setImageFormat('png');
        
        // // Simpan gambar PNG secara bertahap jika ada beberapa halaman
        // foreach ($imagick as $index => $page) {
        //     // Buat nama file PNG untuk setiap halaman
        //     $pngFilePath = 'uploads/' . ($index + 1) . '.png';
            
        //     // Simpan gambar PNG
        //     $page->writeImage($pngFilePath);
        // }
        
        // $imagick->clear();
        // $imagick->destroy();

        $qrcode = new QrReader('uploads/output/663050f2367a0.png');
        $text = $qrcode->text(); //return decoded text from QR Code

        $xmlString = file_get_contents($text);
        
        // Mengonversi data XML menjadi objek SimpleXMLElement
        $xml = simplexml_load_string($xmlString);
        
        // Ambil data tertentu dari objek SimpleXMLElement
        $nomorFaktur = (string) $xml->nomorFaktur;
        $tanggalFaktur = (string) $xml->tanggalFaktur;
        $npwpPenjual = (string) $xml->npwpPenjual;
        $namaPenjual = (string) $xml->namaPenjual;
        $alamatPenjual = (string) $xml->alamatPenjual;
        $fakturPajak = (string) $xml->detailTransaksi->hargaTotal;
        // Dan seterusnya, sesuai dengan data yang Anda butuhkan
        
        // Tampilkan data yang telah diambil
        echo "Nomor Faktur: " . $nomorFaktur . "<br>";
        echo "Tanggal Faktur: " . $tanggalFaktur . "<br>";
        echo "NPWP Penjual: " . $npwpPenjual . "<br>";
        echo "Nama Penjual: " . $namaPenjual . "<br>";
        echo "Alamat Penjual: " . $alamatPenjual . "<br>";
        echo "Faktur Pajak: " . $fakturPajak . "<br>";
        // Dan seterusnya, sesuai dengan data yang Anda butuhkan
    }

    public function pdftopng()
    {

        $pdfFilePath = 'uploads/sample1.pdf'; // Ubah sesuai dengan lokasi file PDF Anda
        $outputPath = 'uploads/output/'; // Ubah sesuai dengan lokasi tempat gambar PNG akan disimpan
        $randomName = uniqid();
        $pdf = new Pdf($pdfFilePath);
        $pdf->setCompressionQuality(100); 
        $pdf->setOutputFormat('png')
        ->width(1653)
        ->saveImage($outputPath . $randomName . '.png');
        
        $filePath = 'uploads/output/' . $randomName . '.png';
        // Mendapatkan ukuran gambar asli
        list($width, $height) = getimagesize($filePath);

        $x = 0;
        $y = 1000;
        $newWidth = 600;
        $newHeight = 450;
        $croppedImage = imagecrop(imagecreatefrompng($filePath), ['x' => $x, 'y' => $y, 'width' => $newWidth, 'height' => $newHeight]);

        // Menyimpan gambar yang telah di-crop
        imagepng($croppedImage, 'uploads/output/' . $randomName . '.png');

        // Menghapus gambar yang telah di-crop dari memori
        imagedestroy($croppedImage);

        $qrcode = new QrReader('uploads/output/'. $randomName .'.png');
        $text = $qrcode->text(); //return decoded text from QR Code

        $xmlString = file_get_contents($text);
        
        // Mengonversi data XML menjadi objek SimpleXMLElement
        $xml = simplexml_load_string($xmlString);
        
        // Ambil data tertentu dari objek SimpleXMLElement
        $nomorFaktur = (string) $xml->nomorFaktur;
        $tanggalFaktur = date('Y-m-d', strtotime($xml->tanggalFaktur));
        $npwpPenjual = (string) $xml->npwpPenjual;
        $namaPenjual = (string) $xml->namaPenjual;
        $alamatPenjual = (string) $xml->alamatPenjual;
        $fakturPajak = (string) $xml->detailTransaksi->hargaTotal;
        // Dan seterusnya, sesuai dengan data yang Anda butuhkan
        
        // Tampilkan data yang telah diambil
        echo "Nomor Faktur: " . $nomorFaktur . "<br>";
        echo "Tanggal Faktur: " . $tanggalFaktur . "<br>";
        echo "NPWP Penjual: " . $npwpPenjual . "<br>";
        echo "Nama Penjual: " . $namaPenjual . "<br>";
        echo "Alamat Penjual: " . $alamatPenjual . "<br>";
        echo "Faktur Pajak: " . $fakturPajak . "<br>";
    }

    public function info()
    {
        return view ('info');
    }

    public function addtemp()
    {
        date_default_timezone_set('Asia/Jakarta');
        $data['current_user'] = $this->ionAuth->user()->row();
        $model = new M_invoicing();
        $invoicing_id = $model->get_running_id();
        $company_name = 'PT. Mekar Armada Jaya';
        $create_date = date("Y-m-d H:i:s");

        // Data array berisi Nomor GR dan Nomor Item
        $data_array = array(
            array("nomor_gr" => "GR004", "nomor_item" => "Item004"),
            array("nomor_gr" => "GR005", "nomor_item" => "Item005"),
            // Tambahkan data lainnya sesuai kebutuhan
        );

        // Loop melalui data array dan sisipkan data ke dalam database
        foreach ($data_array as $data) {
            $data_to_insert = array(
                "invoicing_id" => $invoicing_id,
                "company_name" => $company_name,
                "create_date" => $create_date,
                "no_gr" => $data['nomor_gr'],
                "no_item" => $data['nomor_item'],
            );
            $model->insert_invoice($data_to_insert);
        }
        echo 'Data berhasil Insert';
    }

    public function notif()
    {
		session()->setFlashdata("success", "This is success message");
		session()->setFlashdata("warning", "This is warning message");
		session()->setFlashdata("info", "This is information message");
		session()->setFlashdata("error", "This is error message");
		return view("notif");
    }

    public function testindex()
    {
        return $this->_render_page('jumlah');
    }

    public function getreceive()
    {
        $model = new M_invoicing;
        $data = $model->reachstatusgr('5000328971', '00330', '4515003600');
        return $data;
    }


    public function fixheader()
    {
        $data['title'] = 'Dashboard';
        $data['ionAuth'] = $this->ionAuth;
        $data['js']['footer'] = array(
            'datatables/jquery.dataTables.min',
            'datatables-bs4/js/dataTables.bootstrap4.min',
            'datatables-responsive/js/dataTables.responsive.min',
            'datatables-responsive/js/responsive.bootstrap4.min',
            'datatables/DataTables-2.0.1/js/dataTables',
            'moment/moment.min',
            'datatables/DataTables-2.0.1/js/dataTables.dateTime.min',
            'daterangepicker/daterangepicker.min',
        );

        $data['css']['header'] = array(
            'datatables/DataTables-2.0.1/css/dataTables.dataTables',
            'datatables/DataTables-2.0.1/css/dataTables.dateTime.min',
            'daterangepicker/daterangepicker',
        );

        $data['current_user'] = $this->ionAuth->user()->row();
        return $this->_render_page('fixheader', $data);
    }

    public function queryspesific()
    {
        $model = new dokumen_ok_model();
        $data = $model->getDataSpecific();
        return $this->response->setJSON(['data' => $data]);
    }

    public function updateDokOk()
    {
        $dropbox_id = $this->request->getPost('dropbox_id');
        $dok_ok = $this->request->getPost('dok_ok');

        $model = new dokumen_ok_model();
        $model->updateDok_Ok($dropbox_id, $dok_ok);

        return redirect()->back();
    }


    public function idtest_invoice()
    {
        $model = new M_invoicing();
        $data['id'] = $model->get_running_id();
        echo json_encode($data);
    }

    public function idtest_dropbox()
    {
        $model = new M_dropbox();
        $data['id'] = $model->get_running_id();
        echo json_encode($data);
    }

    public function serbaguna()
    {
        // $email = \Config\Services::email();

        // $email->setFrom('ahmadnurfahmi.anf2@gmail.com', 'Ahmad Nur Fahmi');
        // $email->setTo('Ahmadnurdante@gmail.com');

        // $email->setSubject('Email Test');
        // $email->setMessage('Testing the email class.');

        // if($email->send()){
        //     echo 'Email berhasil terkirim';
        // } else {
        //     echo $email->printDebugger(['headers']);
        // }



        // $input = "faktur/sample2.pdf"; // Input yang ingin Anda kirimkan
        // putenv("params=$input");
        // //$command_exec = escapeshellcmd("python ../../../.././QRDetected/qrdetect.py");
        // $command_exec = escapeshellcmd("python .././QRDetected/qrdetect.py");
        // // $command_exec = escapeshellcmd("python c:\QRDetected\qrdetect.py");
        // $str_output = shell_exec($command_exec);
        // $json_output = json_decode($str_output, true);
        // if (isset($json_output['error'])) {
        //     $value = $json_output['error'];
        // } else {
        //     $value = $json_output['output'];
        // }
        // echo $value;


        //========================================================================================================//


        // $pdfPath = "faktur/sample1.pdf";
        // $command = escapeshellcmd("python ../main.py " . escapeshellarg($pdfPath));
        
        // exec($command, $output, $result);

        // $xmlString = file_get_contents($output[0]);
        // $xml = simplexml_load_string($xmlString);

        // echo 'DPP = ', (float) $xml->detailTransaksi->dpp;
        // echo "<pre>";
        // echo number_format((float) $xml->detailTransaksi->hargaTotal, 0, ',', '.');
        // echo "</pre>";
        // echo (string) $xml->nomorFaktur;
        // echo "<pre>";
        // echo (string) $xml->tanggalFaktur;
        // echo "<pre>";
        // echo (string) $xml->npwpPenjual;
        // echo "<pre>";
        // echo (float) $xml->detailTransaksi->dpp;
        // echo "<pre>";
        // echo (float) $xml->detailTransaksi->ppn;
        // echo "<pre>";

        // echo "<pre>";
        // print_r($output[0]);
        // echo "</pre>";
        // echo "Status: " . $result;


        // $data['current_user'] = $this->ionAuth->user()->row();

        // $data_permission = $data['current_user']->permission;

        $permissions_admin = [
            "Module.View.DashboarAdmin",
            "Module.View.ManageUser",
        ];

        // $permissions_maj = [
        //     "Module.View.DashboarMaj",
        //     "Module.View.GoodsReceiptMaj",
        //     "Module.View.Receivable",
        //     "Module.Update.Receivable",
        //     "Module.View.DocumentOK",
        //     "Module.Update.DocumentOK",
        //     "Module.View.Onhold",
        //     "Module.Update.Onhold",
        //     "Module.View.PrintInvoice",
        //     "Module.Create.PrintInvoice",
        //     "Module.Update.PrintInvoice",
        //     "Module.View.Miro",
        //     "Module.Update.Miro",
        //     "Module.View.ReportData",
        //     "Module.View.ArsipPdf",
        // ];

        // $permissions_vendor = [
        //     "Module.View.DashboarVendor",
        //     "Module.View.GoodsReceipt",
        //     "Module.View.Invoice",
        //     "Module.Create.Invoice",
        //     "Module.View.Qrgenerate",
        //     "Module.View.RegistrationDropbox",
        //     "Module.Create.RegistrationDropbox",
        //     "Module.View.PrintDropbox",
        // ];
        
        // // Serialize the array
        $serialized_permissions = serialize($permissions_admin);
        // $unserialized_permissions = unserialize($data_permission);
        
        // // Print the serialized string
        echo $serialized_permissions;
    }
}
