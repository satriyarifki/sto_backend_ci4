<?php

namespace App\Controllers;
use \IonAuth\Libraries\IonAuth;
use CodeIgniter\Controller;
use App\Models\M_invoicing;
use App\Models\M_invoicing_mgl;
use SimpleSoftwareIO\QrCode\Generator;
use App\Models\M_curl;
use App\Models\ExcelModel;
use Zxing\QrReader;
use Spatie\PdfToImage\Pdf;
use Imagick;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Libraries\MY_TCPDF AS TCPDF;
use App\Models\receiving_model;
use App\Models\dokumen_ok_model;
use App\Models\miro_model;
use App\Models\M_dropbox;
use App\Models\M_attempt_verif;
use App\Models\InvoiceAfterDokOk_model;
use App\Models\M_miro;
use App\Models\M_generate_qr;
use CodeIgniter\Email\Email;
use App\Libraries\QrCodeGenerator;

class InvoicingMgl extends BaseController
{
    protected $ionAuth;
    public function __construct()
    {
        $model = new IonAuth();
        $this->ionAuth = $model;
    }

    public function invoice_mgl()
    {
        $this->check_permission('Module.View.InvoiceMgl');
        $data['title'] = 'Transaction';
        $data['ionAuth'] = $this->ionAuth;
        $data['current_user'] = $this->ionAuth->user()->row();
        $data['css']['header'] = array(
            'datatables/DataTables-2.0.1/css/dataTables.dataTables',
            'datatables/DataTables-2.0.1/css/dataTables.dateTime.min',
            'daterangepicker/daterangepicker',
            'toastr/toastr.min',
        );
        $data['js']['footer'] = array(
            'datatables/DataTables-2.0.1/js/dataTables',
            'moment/moment.min',
            'datatables/DataTables-2.0.1/js/dataTables.dateTime.min',
            'daterangepicker/daterangepicker.min',
            'bs-custom-file-input/bs-custom-file-input.min',
            'toastr/toastr.min',
        );
        
        $model = new M_generate_qr();
        $data['qrid'] = $model->get_running_id();
        return $this->_render_page('invoicing/verify_transaction/vendor_mgl_verify', $data);
    }

    public function generate_mgl_qr()
    {
        $data['idqr'] = $this->request->getGet('idqr');
        $data['totalpayment'] = preg_replace('/[^\d]/', '', $this->request->getGet('totalpayment'));

        $dataqr = [
            'id_qr' => $data['idqr'],
            'total_payment' => $data['totalpayment'],
        ];

        $json_data = json_encode($dataqr);

        $qrCodeGenerator = new QrCodeGenerator();
        $qrCodePath = $qrCodeGenerator->generate($json_data);
        return $this->response->download($qrCodePath, null)->setFileName('qrcode.png');
    }

    public function makeinvoice()
    {
        $data['current_user'] = $this->ionAuth->user()->row();
        date_default_timezone_set('Asia/Jakarta');
        $checkDataVerif = json_decode($this->request->getPost('checkDataVerif'), true);
        $noInvoice = $this->request->getPost('noInvoice');
        $dateInvoice = date_create($this->request->getPost('dateInvoice'));
        $invoiceDateFormat = date_format($dateInvoice, 'd/m/Y');
        $totalAmount = floatval($this->request->getPost('totalAmount'));
        $amountFormat = number_format((float) $totalAmount, 0, ',', '.');
        $model = new M_invoicing();
        $modelmgl = new M_invoicing_mgl();
        $attemptModel = new M_attempt_verif();
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
                        $data['no_invoice'] = $noInvoice;
                        $data['invoice_date'] = $invoiceDateFormat;
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

                        if ($data['total_invoice_payment'] != $amountFormat) {
                            // $attemptModel->updateAttemptCount($data['no_invoice']);
                            // $errorCount = $attemptModel->getErrorCount($data['no_invoice']);
                            $data['response'] = 'Jumlah DPP tidak sama';
                            $data['message'] = false;
                            // $data['error_count'] = $errorCount;
                            unlink($targetFileInvoice);
                            echo json_encode($data);
                            return;
                        } else {
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
                                );
                                $modelmgl->insert_invoice_mgl($data_to_insert);
                            }
                            session()->setFlashdata("success", "This is success message");
                            $data['response'] = "Data berhasil terverifikasi";
                            $data['message'] = true;
                        }
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

    public function vendor_verify_json()
    {
        $data['current_user'] = $this->ionAuth->user()->row();
        $requestData = json_decode(file_get_contents('php://input'), true);
        $po_number = $requestData['poNumber'];

        // if ($requestData != NULL) {
        //     $formatted_date_low = $requestData['startDate'] != "" ? date('Ymd', strtotime($requestData['startDate'])) : NULL;
        //     $formatted_date_high = $requestData['endDate'] != "" ? date('Ymd', strtotime($requestData['endDate'])) : NULL;
        //     $po_number = $requestData['poNumber'] != "" ? $requestData['poNumber'] : NULL;
        // } else {
        //     $formatted_date_low = NULL;
        //     $formatted_date_high = NULL;
        //     $po_number = NULL;
        // }        

        // $data['date_low'] = $formatted_date_low;
        // $data['date_high'] = $formatted_date_high;
        $data['po_number'] = $po_number;

        $M_curl = new M_curl();
        $this->SAP_PARAMS['function'] = 'Z_QC';
        // Request pertama
        $this->SAP_PARAMS['params'] = [
            'RPT' => 'P_GR_SELECT',
            'CNMA' => $data['current_user']->vendor_code,
            // 'P_DATE_LOW' => $formatted_date_low,
            // 'P_DATE_HIGH' => $formatted_date_high,
            'P_EBELN' => $po_number,
        ];

        $sap = $M_curl->execute("POST", $this->SAP_PARAMS);
        if ($sap['success']) {
            $filtered_data = [];
            foreach ($sap['data']['ZGRUSER'] as $item) {
                if ($item['VBELN_ST'] != 'GR') {
                    $filtered_data[] = $item;
                }
            }
            $data['data_sap'] = $filtered_data;
        } else {
            $data['error'] = isset($sap['message']) ? $sap['message'] : 'Terjadi kesalahan dalam permintaan SAP.';
        }
        echo json_encode($data); // Keluarkan respons JSON tunggal dari server
    }
}