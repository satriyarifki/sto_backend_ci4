<?php

namespace App\Controllers;

use CodeIgniter\Controller;

use \App\Models\Temp;
use \IonAuth\Libraries\IonAuth;
use Config\Menu;
use App\Libraries\MY_TCPDF AS TCPDF;
use App\Models\M_curl;
use App\Models\M_dropbox;
use App\Models\M_exception;
use App\Models\M_invoicing;
use App\Models\M_auth;
use App\Models\M_attempt_verif;
use App\Models\M_invoicing_npkp;

class Exception extends BaseController
{
    protected $ionAuth;
    protected $data = [];

    public function __construct()
    {
        $model = new IonAuth();
        $this->ionAuth = $model;
    }

    public function excep_index()
    {
        $data['title'] = 'Exception Gap';
        $data['ionAuth'] = $this->ionAuth;
        $data['current_user'] = $this->ionAuth->user()->row();
        $checkDataVerif = $this->request->getGet('checkDataVerif');
        if ($checkDataVerif) {
            $checkDataVerif = json_decode(base64_decode($checkDataVerif), true);
        }

        $data['checkDataVerif'] = $checkDataVerif;

        $data['css']['header'] = array(
            'datatables/DataTables-2.0.1/css/dataTables.dataTables',
            'datatables/DataTables-2.0.1/css/dataTables.dateTime.min',
            'daterangepicker/daterangepicker',
            'toastr/toastr.min',
            'select2/css/select2.min',
            'select2-bootstrap4-theme/select2-bootstrap4.min',
        );
        $data['js']['footer'] = array(
            'datatables/DataTables-2.0.1/js/dataTables',
            'moment/moment.min',
            'datatables/DataTables-2.0.1/js/dataTables.dateTime.min',
            'daterangepicker/daterangepicker.min',
            'toastr/toastr.min',
            'select2/js/select2.full.min',
            'xlsx/xlsx.full.min',
        );

        return $this->_render_page('exception/exception_vendor', $data);
    }



    public function makexception()
    {
        $data['current_user'] = $this->ionAuth->user()->row();
        date_default_timezone_set('Asia/Jakarta');
        $checkedData = json_decode($this->request->getPost('checkedData'), true);
        $checkDataVerif = json_decode($this->request->getPost('checkDataVerif'), true);
        $totalAmount = floatval($this->request->getPost('totalAmount'));
        $amountFormat = number_format((float) $totalAmount, 0, ',', '.');
        $model = new M_invoicing();
        $modelMakexception = new M_exception();
        $invoicing_id = $model->get_running_id();

        $matchingResults = [];

        function formatRupiahToFloat($str) {
            $strippedString = preg_replace("/[^\d]/", "", $str);
            return floatval($strippedString) / 100;
        }

        function formatInt($str) {
            $strippedString = preg_replace("/[^\d]/", "", $str);
            return floatval($strippedString);
        }

        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['pdfFile']) && isset($_FILES['invoiceFile'])) {
            $targetDir = 'uploads/pajak/';
            $targetDirInvoices = 'uploads/invoices/';
            $targetDirRev = 'uploads/pajak_rev/';

            $targetFile = $targetDir . $data['current_user']->username . '_' . $invoicing_id . '_' . 'FAKTUR_PAJAK' . '.pdf';
            $targetFileInvoice = $targetDirInvoices . $data['current_user']->username . '_' . $invoicing_id . '_' . 'INVOICE' . '.pdf';

            $uploadOk = 1;
            $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
            $fileTypeInvoice = strtolower(pathinfo($targetFileInvoice, PATHINFO_EXTENSION));

            if (move_uploaded_file($_FILES['pdfFile']['tmp_name'], $targetFile) && move_uploaded_file($_FILES['invoiceFile']['tmp_name'], $targetFileInvoice)) {

                if (isset($_FILES['pdfFileRev']) && $_FILES['pdfFileRev']['error'] == UPLOAD_ERR_OK) {
                    $targetFileRev = $targetDirRev . $data['current_user']->username . '_' . $invoicing_id . '_' . 'FAKTUR_REV' . '.pdf';
                    move_uploaded_file($_FILES['pdfFileRev']['tmp_name'], $targetFileRev);
                }

                $command = escapeshellcmd("python3 ../main.py " . escapeshellarg($targetFile));
                exec($command, $output, $result);

                $command2 = escapeshellcmd("python3 ../main.py " . escapeshellarg($targetFileInvoice));
                exec($command2, $output2, $result2);

                $json_output = json_decode($output[0], true);
                $json_output2 = json_decode($output2[0], true);
                
                $decoded_response = json_decode($json_output2['output'], true);
                $data['id_qr'] = $decoded_response['id_qr'];
                $data['total_invoice_payment'] = number_format($decoded_response['total_payment'], 0, ',', '.');
                $data['no_invoice'] = $decoded_response['no_invoice'];
                $data['invoice_date'] = $decoded_response['invoice_date'];
                $data['no_faktur_invoice'] = $decoded_response['no_faktur'];

                $xmlString = file_get_contents($json_output['output']);
                $xml = simplexml_load_string($xmlString);

                $detailTransaksiData = [];
                foreach ($xml->detailTransaksi as $detailTransaksi) {
                    $detailTransaksiArray = (array) $detailTransaksi;
                    $detailTransaksiData[] = $detailTransaksiArray;
                }

                function normalizewords($word) {
                    $word = strtolower(trim(preg_replace('/\s+/', ' ', $word)));
                    return preg_replace('/[.,\/]/', '', $word);
                }

                $fakturFormat = number_format((float) $xml->jumlahDpp, 0, ',', '.');
                $data['nomorFaktur'] = (string) $xml->kdJenisTransaksi . $xml->fgPengganti . $xml->nomorFaktur;
                $data['tanggalFaktur'] = (string) $xml->tanggalFaktur;
                $data['alamatLawanTransaksi'] = (string) $xml->alamatLawanTransaksi;
                $data['npwpLawanTransaksi'] = (string) $xml->npwpLawanTransaksi;
                $data['dpp'] = (float) $xml->jumlahDpp;
                $data['ppn'] = (float) $xml->jumlahPpn;
                $total = $data['dpp'] + $data['ppn'];
                $data['total_payment'] = number_format($total, 0, ',', '.');
                $data['totalAmount'] = $totalAmount;
                $data['totalFaktur'] = $fakturFormat;
                $data['amountFormat'] = $amountFormat;
                $data['checkedData'] = $checkedData;
                $data['checkDataVerif'] = $checkDataVerif;
                $data['NomorInvoice'] = $data['no_invoice'];
                $data['DetailTransaksi'] = $detailTransaksiData;
                $data['company_name'] = $data['current_user']->company_name;
                $data['user_create'] = $data['current_user']->vendor_code;
                $data['invoicing_id'] = $invoicing_id;

                $existingInvoice = $model->where('no_invoice', $data['no_invoice'])->first();
                $existingFaktur = $model->where('tax_number', $data['nomorFaktur'])->first();

                if ($existingInvoice || $existingFaktur) {
                    $data['response'] = "Nomor invoice atau Nomor Faktur Sudah digunakan";
                    $data['message'] = false;
                    unlink($targetFile);
                    unlink($targetFileInvoice);
                    if (isset($targetFileRev)) {
                        unlink($targetFileRev);
                    }
                    echo json_encode($data);
                    return;
                }

                $M_curl = new M_curl();
                $this->SAP_PARAMS['function'] = 'Z_QC';
                $dataToSAP = [];
        
                foreach ($checkDataVerif as $rowData) {
                    $sapParams = [
                        'RPT' => 'P_GR_UPDATE_GR',
                        'P_BELNR' => $rowData[2], 
                        'P_EBELP' => $rowData[3],
                    ];
        
                    $this->SAP_PARAMS['params'] = $sapParams;
                    $sapResult = $M_curl->execute("POST", $this->SAP_PARAMS);
        
                    if ($sapResult['success']) {
                        $dataToSAP[] = isset($sapResult['data']['ZGRUSER']) ? $sapResult['data']['ZGRUSER'] : [];
                    } else {
                        $dataToSAP[] = isset($sapResult['message']) ? $sapResult['message'] : 'Terjadi kesalahan dalam permintaan SAP.';
                    }
                }
        
                $company_name = $data['current_user']->company_name;
                $user_create = $data['current_user']->vendor_code;
                $npwp = (string) $xml->npwpPenjual;
                $taxdate = (string) $xml->tanggalFaktur;
                $create_date = date("Y-m-d H:i:s");
        
                $data_arsip_pdf = array(
                    "invoicing_id" => $invoicing_id,
                    "no_invoice" => $data['no_invoice'],
                    "file_path" => $data['current_user']->username . '_' . $invoicing_id . '_' . 'FAKTUR_PAJAK' . '.pdf',
                    "file_path_invoice" => $data['current_user']->username . '_' . $invoicing_id . '_' . 'INVOICE' . '.pdf',
                );

                if (isset($_FILES['pdfFileRev']) && $_FILES['pdfFileRev']['error'] == UPLOAD_ERR_OK) {
                    $data_arsip_pdf['file_rev_path'] = $data['current_user']->username . '_' . $invoicing_id . '_' . 'FAKTUR_REV' . '.pdf';
                }

                $model->insert_arsip_pdf($data_arsip_pdf);
        
                foreach ($checkDataVerif as $iterate) {
                    $data_to_insert = array(
                        "invoicing_id" => $invoicing_id,
                        "no_invoice" => $data['no_invoice'], 
                        "user_create" => $user_create,
                        "company_name" => $company_name,
                        "create_date" => $create_date,
                        "total_payment" => $data['dpp'] + $data['ppn'],
                        "npwp" => $npwp,
                        "tax_number" => $data['nomorFaktur'],
                        "tax_date" => $taxdate,
                        "no_gr" => $iterate[2],
                        "no_item" => $iterate[3],
                        "no_po" => $iterate[1],
                        "exception" => 'Y',
                        'approve' => 'X',
                    );
                    $model->insert_invoice($data_to_insert);
                }

                $currentDate = date('Y-m-d');
                $currentTime = date('H:i:s');

                $data_pending_insert = array(
                    'no_invoice' => $data['no_invoice'],
                    'generated_date' => $currentDate,
                    'generated_time' => $currentTime,
                    'user_generate' => $user_create,
                    'company_name' => $company_name,
                    'invoicing_id' => $invoicing_id,
                );
                $modelMakexception->insertDbpPendingPkp($data_pending_insert);

                session()->setFlashdata("success", "This is success message");
                $data['response'] = "Data berhasil terverifikasi";
                $data['message'] = true;
            } else {
                session()->setFlashdata("error", "Maaf, terjadi kesalahan saat mengunggah file");
                $data['response'] = "Maaf, terjadi kesalahan saat mengunggah file";
                $data['message'] = false;
            }

        } else {
            session()->setFlashdata("error", "Permintaan tidak valid atau tidak ada file yang dikirim");
            $data['response'] = "Permintaan tidak valid atau tidak ada file yang dikirim";
            $data['message'] = false;
        }
        echo json_encode($data);
    }


    public function makeinvoicenpkp()
    {
        $data['current_user'] = $this->ionAuth->user()->row();
        date_default_timezone_set('Asia/Jakarta');
        $checkDataVerif = json_decode($this->request->getPost('checkDataVerif'), true);
        $totalAmount = floatval($this->request->getPost('totalAmount'));
        $amountFormat = number_format((float) $totalAmount, 0, ',', '.');
        $model = new M_invoicing();
        $modelnpkp = new M_invoicing_npkp();
        $attemptModel = new M_attempt_verif();
        $modelMakexception = new M_exception();
        $invoicing_id = $modelnpkp->get_running_id_npkp();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['invoiceFileNonPkp'])) {
            $targetDirInvoices = 'uploads/invoice_npkp/';
            $targetFileInvoice = $targetDirInvoices . $data['current_user']->username . '_' . $invoicing_id . '_' . 'INVOICE_NPKP' . '.pdf';

            if (move_uploaded_file($_FILES['invoiceFileNonPkp']['tmp_name'], $targetFileInvoice)) {
                $command2 = escapeshellcmd("python3 ../main.py " . escapeshellarg($targetFileInvoice));
                exec($command2, $output2, $result2);

                if (!empty($output2)) {
                    $json_output2 = json_decode($output2[0], true);
                    if (isset($json_output2['error'])) {
                        $data['response'] = $json_output2['error'] . ' Pada Invoice';
                        $data['message'] = false;
                        unlink($targetFileInvoice);
                    } else {
                        if ($data['current_user']->combined == 'X') {
                            $targetDirFaktur = 'uploads/pajak/';
                            $targetFileFaktur = $targetDirFaktur . $data['current_user']->username . '_' . $invoicing_id . '_FAKTUR_PAJAK.pdf';
                            move_uploaded_file($_FILES['fakturPajakFile']['tmp_name'], $targetFileFaktur);
                        }

                        $decoded_response = json_decode($json_output2['output'], true);
                        $data['id_qr'] = $decoded_response['id_qr'];
                        $data['total_invoice_payment'] = number_format($decoded_response['total_payment'], 0, ',', '.');
                        $data['no_invoice'] = $decoded_response['no_invoice'];
                        $data['invoice_date'] = $decoded_response['invoice_date'];
                        $data['amountFormat'] = $amountFormat;

                        $existingInvoice = $modelnpkp->where('no_invoice', $data['no_invoice'])->first();

                        if ($existingInvoice) {
                            $data['response'] = "Nomor invoice Sudah digunakan";
                            $data['message'] = false;
                            unlink($targetFileInvoice);
                            echo json_encode($data);
                            return;
                        }

                        $M_curl = new M_curl();
                        $this->SAP_PARAMS['function'] = 'Z_QC';
                        $dataToSAP = [];

                        foreach ($checkDataVerif as $rowData) {
                            $sapParams = [
                                'RPT' => 'P_GR_UPDATE_GR',
                                'P_BELNR' => $rowData[2], 
                                'P_EBELP' => $rowData[3],
                            ];

                            $this->SAP_PARAMS['params'] = $sapParams;
                            $sapResult = $M_curl->execute("POST", $this->SAP_PARAMS);

                            if ($sapResult['success']) {
                                $dataToSAP[] = isset($sapResult['data']['ZGRUSER']) ? $sapResult['data']['ZGRUSER'] : [];
                            } else {
                                $dataToSAP[] = isset($sapResult['message']) ? $sapResult['message'] : 'Terjadi kesalahan dalam permintaan SAP.';
                            }
                        }

                        $company_name = $data['current_user']->company_name;
                        $user_create = $data['current_user']->vendor_code;
                        $create_date = date("Y-m-d H:i:s");

                        $data_arsip_pdf = array(
                            "invoicing_id" => $invoicing_id,
                            "no_invoice" => $data['no_invoice'],
                            "file_path_invoice_npkp" => $data['current_user']->username . '_' . $invoicing_id . '_' . 'INVOICE_NPKP' . '.pdf',
                        );

                        if ($data['current_user']->combined == 'X') {
                            $data_arsip_pdf["file_path_faktur"] = $data['current_user']->username . '_' . $invoicing_id . '_' . 'FAKTUR_PAJAK' . '.pdf';
                        }

                        $modelnpkp->insert_arsip_pdf_npkp($data_arsip_pdf);
                        foreach ($checkDataVerif as $iterate) {
                            $data_to_insert = array(
                                "invoicing_id" => $invoicing_id,
                                "no_invoice" => $data['no_invoice'], 
                                "user_create" => $user_create,
                                "company_name" => $company_name,
                                "create_date" => $create_date,
                                "total_payment" => $totalAmount,
                                "date_invoice" =>  $data['invoice_date'],
                                "no_gr" => $iterate[2],
                                "no_item" => $iterate[3],
                                "no_po" => $iterate[1],
                                "exception" => 'Y',
                                'approve' => 'X',
                            );
                            $modelnpkp->insert_invoice_npkp($data_to_insert);
                        }

                        $currentDate = date('Y-m-d');
                        $currentTime = date('H:i:s');

                        $data_pending_insert = array(
                            'no_invoice' => $data['no_invoice'],
                            'generated_date' => $currentDate,
                            'generated_time' => $currentTime,
                            'user_generate' => $user_create,
                            'company_name' => $company_name,
                            'invoicing_id' => $invoicing_id,
                        );
                        $modelMakexception->insertDbpPendingPkp($data_pending_insert);

                        session()->setFlashdata("success", "This is success message");
                        $data['response'] = "Data berhasil terverifikasi";
                        $data['message'] = true;
                    }
                } else {
                    $data['response'] = "Sistem tidak mendeteksi adanya QR Code";
                    $data['message'] = false;
                    unlink($targetFileInvoice);
                }

            } else {
                session()->setFlashdata("error", "Maaf, terjadi kesalahan saat mengunggah file");
                $data['response'] = "Maaf, terjadi kesalahan saat mengunggah file";
                $data['message'] = false;
            }

        } else {
            session()->setFlashdata("error", "tidak ada file yang dikirim");
            $data['response'] = "Tidak ada file yang dikirim";
            $data['message'] = false;
        }

        echo json_encode($data);
    }



    public function makeinvoicemgl()
    {
        $data['current_user'] = $this->ionAuth->user()->row();
        date_default_timezone_set('Asia/Jakarta');
        $checkDataVerif = json_decode($this->request->getPost('checkDataVerif'), true);
        $totalAmount = floatval($this->request->getPost('totalAmount'));
        $amountFormat = number_format((float) $totalAmount, 0, ',', '.');
        $model = new M_invoicing();
        $modelmgl = new M_invoicing_mgl();
        $attemptModel = new M_attempt_verif();
        $modelMakexception = new M_exception();
        $invoicing_id = $modelmgl->get_running_id_mgl();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['invoiceFileNonPkp'])) {
            $targetDirInvoices = 'uploads/invoice_mgl/';
            $targetFileInvoice = $targetDirInvoices . $data['current_user']->username . '_' . $invoicing_id . '_' . 'INVOICE_MGL' . '.pdf';

            if (move_uploaded_file($_FILES['invoiceFileNonPkp']['tmp_name'], $targetFileInvoice)) {
                $command2 = escapeshellcmd("python3 ../main_inv.py " . escapeshellarg($targetFileInvoice));
                exec($command2, $output2, $result2);

                if (!empty($output2)) {
                    $json_output2 = json_decode($output2[0], true);
                    if (isset($json_output2['error'])) {
                        $data['response'] = $json_output2['error'] . ' Pada Invoice';
                        $data['message'] = false;
                        unlink($targetFileInvoice);
                    } else {
                        $decoded_response = $json_output2['output'];
                        $data['id_qr'] = $decoded_response['id_qr'];
                        $data['total_invoice_payment'] = number_format($decoded_response['total_payment'], 0, ',', '.');
                        $data['no_invoice'] = $decoded_response['no_invoice'];
                        $data['invoice_date'] = $decoded_response['invoice_date'];
                        $data['amountFormat'] = $amountFormat;

                        $existingInvoice = $modelmgl->where('no_invoice', $data['no_invoice'])
                                                    ->where('active', '')
                                                    ->first();

                        if ($existingInvoice) {
                            $data['response'] = "Nomor invoice Sudah digunakan";
                            $data['message'] = false;
                            unlink($targetFileInvoice);
                            echo json_encode($data);
                            return;
                        }

                        foreach ($checkDataVerif as $rowData) {
                            $sapParams = [
                                'RPT' => 'P_GR_UPDATE_GR',
                                'P_BELNR' => $rowData[2], 
                                'P_EBELP' => $rowData[3],
                            ];

                            $this->SAP_PARAMS['params'] = $sapParams;
                            $sapResult = $M_curl->execute("POST", $this->SAP_PARAMS);

                            if ($sapResult['success']) {
                                $dataToSAP[] = isset($sapResult['data']['ZGRUSER']) ? $sapResult['data']['ZGRUSER'] : [];
                            } else {
                                $dataToSAP[] = isset($sapResult['message']) ? $sapResult['message'] : 'Terjadi kesalahan dalam permintaan SAP.';
                            }
                        }

                        $company_name = $data['current_user']->company_name;
                        $user_create = $data['current_user']->vendor_code;
                        $create_date = date("Y-m-d H:i:s");

                        $data_arsip_pdf = array(
                            "invoicing_id" => $invoicing_id,
                            "no_invoice" => $data['no_invoice'],
                            "file_path_invoice_mgl" => $data['current_user']->username . '_' . $invoicing_id . '_' . 'INVOICE_MGL' . '.pdf',
                        );


                        $modelmgl->insert_arsip_pdf_mgl($data_arsip_pdf);
                        foreach ($checkDataVerif as $iterate) {
                            $data_to_insert = array(
                                "invoicing_id" => $invoicing_id,
                                "no_invoice" => $data['no_invoice'], 
                                "user_create" => $user_create,
                                "company_name" => $company_name,
                                "create_date" => $create_date,
                                "total_payment" => $totalAmount,
                                "date_invoice" =>  $data['invoice_date'],
                                "no_gr" => $iterate[2],
                                "no_item" => $iterate[3],
                                "no_po" => $iterate[1],
                                "exception" => 'Y',
                                "approve" => 'X',
                            );
                            $modelmgl->insert_invoice_mgl($data_to_insert);
                        }

                        $currentDate = date('Y-m-d');
                        $currentTime = date('H:i:s');

                        $data_pending_insert = array(
                            'no_invoice' => $data['no_invoice'],
                            'generated_date' => $currentDate,
                            'generated_time' => $currentTime,
                            'user_generate' => $user_create,
                            'company_name' => $company_name,
                            'invoicing_id' => $invoicing_id,
                        );
                        $modelMakexception->insertDbpPendingPkp($data_pending_insert);
                        
                        session()->setFlashdata("success", "This is success message");
                        $data['response'] = "Data berhasil terverifikasi";
                        $data['message'] = true;
                    }
                } else {
                    $data['response'] = "Sistem tidak mendeteksi adanya QR Code";
                    $data['message'] = false;
                    unlink($targetFileInvoice);
                }

            } else {
                session()->setFlashdata("error", "Maaf, terjadi kesalahan saat mengunggah file");
                $data['response'] = "Maaf, terjadi kesalahan saat mengunggah file";
                $data['message'] = false;
            }

        } else {
            session()->setFlashdata("error", "tidak ada file yang dikirim");
            $data['response'] = "Tidak ada file yang dikirim";
            $data['message'] = false;
        }

        echo json_encode($data);
    }

    public function pdf()
    {
        $data['title'] = 'Print Dropbox Pending';

        $data['ionAuth'] = $this->ionAuth;
        $data['current_user'] = $this->ionAuth->user()->row();
        $data['css']['header'] = array(
            'datatables/DataTables-2.0.1/css/dataTables.dataTables',
            'datatables/DataTables-2.0.1/css/dataTables.dateTime.min',
            'daterangepicker/daterangepicker',
        );
        $data['js']['footer'] = array(
            'datatables/DataTables-2.0.1/js/dataTables',
            'moment/moment.min',
            'datatables/DataTables-2.0.1/js/dataTables.dateTime.min',
            'daterangepicker/daterangepicker.min',
        );

        $model = new M_exception;
        $no_invoice = $this->request->getGet('no_invoice');
        $data['result'] = $model->getDataPending($no_invoice);
        $data['current_user'] = $this->ionAuth->user()->row();

        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $barcodeData = $no_invoice;
        $pdf->setBarcodeData($barcodeData);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('fahmi');
        $pdf->SetTitle('Dropbox Print');
        $pdf->SetSubject('Dropbox');
        $pdf->SetKeywords('TCPDF, PDF, dropbox, mekararmadajaya');

        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 001', PDF_HEADER_STRING, array(0,64,255), array(0,64,128));
        $pdf->setFooterData(array(0,64,0), array(0,64,128));
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        $pdf->setFontSubsetting(true);
        $pdf->SetFont('dejavusans', '', 11, '', true);
        $pdf->AddPage();

        $html = view('dropbox/dropbox_pending_print', $data);

        $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
        $this->response->setContentType('application/pdf');
        $pdf->Output('Dropbox.pdf', 'I');
    }

    public function get_exception_document()
    {
        $model = new M_exception();
        $data = $model->getDataException();
        return $this->response->setJSON(['data' => $data]);
    }

    public function get_exception_document_vendor()
    {
        $model = new M_exception();
        $data = $model->getDataExceptionVendor($this->ionAuth->user()->row()->vendor_code);
        return $this->response->setJSON(['data' => $data]);
    }

    public function update_exception_dropbox()
    {
        $no_invoice = $this->request->getPost('no_invoice');
        $approve = $this->request->getPost('approve');

        $model = new M_exception();
        $result = $model->getDataSpescException($no_invoice);
        $model->updateStatusDropboxPending($no_invoice, $approve);
    }
}
