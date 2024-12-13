<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\M_curl;
use App\Models\M_dropbox;
use \IonAuth\Libraries\IonAuth;
use App\Models\M_registerdrop;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Libraries\MY_TCPDF AS TCPDF;
use App\Models\M_invoicing;
use App\Models\M_invoicing_npkp;
use App\Models\M_invoicing_mgl;

class Dropbox extends BaseController
{

    protected $ionAuth;

    public function __construct()
    {
        $model = new IonAuth();
        $this->ionAuth = $model;
    }

    public function regris()
    {
        $this->check_permission('Module.View.RegistrationDropbox');
        $data['title'] = 'Registration Dropbox';
        $data['ionAuth'] = $this->ionAuth;
        $data['current_user'] = $this->ionAuth->user()->row();
        $model = new M_invoicing();
        $data['js']['footer'] = array(
            'datatables/jquery.dataTables.min',
            'datatables-bs4/js/dataTables.bootstrap4.min',
            'datatables-responsive/js/dataTables.responsive.min',
            'datatables-responsive/js/responsive.bootstrap4.min',
            'datatables/DataTables-2.0.1/js/dataTables',
            'toastr/toastr.min',
        );

        $data['css']['header'] = array(
            'datatables/DataTables-2.0.1/css/dataTables.dataTables',
            'datatables/DataTables-2.0.1/css/dataTables.dateTime.min',
            'toastr/toastr.min',
        );

        return $this->_render_page('dropbox/regristration_dropbox', $data);
    }


    public function regis_json()
    {
        $model = new M_invoicing();
        $data['current_user'] = $this->ionAuth->user()->row();
        $requestData = json_decode(file_get_contents('php://input'), true);

        if ($requestData != NULL) {
            $formatted_date_low = $requestData['startDate'] != "" ? date('Ymd', strtotime($requestData['startDate'])) : NULL;
            $formatted_date_high = $requestData['endDate'] != "" ? date('Ymd', strtotime($requestData['endDate'])) : NULL;
            $invoice_number = $requestData['invoiceNumber'] != "" ? $requestData['invoiceNumber'] : NULL;
        } else {
            $formatted_date_low = NULL;
            $formatted_date_high = NULL;
            $invoice_number = NULL;
        }
        $data['startDate'] = $formatted_date_low;
        $data['endDate'] = $formatted_date_high;
        $data['invoicenumber'] = $invoice_number;

        $result = $model->getDataBasedDate($formatted_date_low, $formatted_date_high, $invoice_number, $data['current_user']->vendor_code);

        if (!empty($result)) {
            $data['result'] = $result;
        } else {
            $data['error'] = 'Data tidak tersedia';
            unset($data['result']);
        }

        echo json_encode($data);
    }

    public function regris_npkp()
    {
        $this->check_permission('Module.View.RegistrationDropboxNpkp');
        $data['title'] = 'Registration Dropbox NPKP';
        $data['ionAuth'] = $this->ionAuth;
        $data['current_user'] = $this->ionAuth->user()->row();
        $model = new M_invoicing();
        $data['js']['footer'] = array(
            'datatables/jquery.dataTables.min',
            'datatables-bs4/js/dataTables.bootstrap4.min',
            'datatables-responsive/js/dataTables.responsive.min',
            'datatables-responsive/js/responsive.bootstrap4.min',
            'datatables/DataTables-2.0.1/js/dataTables',
            'toastr/toastr.min',
        );

        $data['css']['header'] = array(
            'datatables/DataTables-2.0.1/css/dataTables.dataTables',
            'datatables/DataTables-2.0.1/css/dataTables.dateTime.min',
            'toastr/toastr.min',
        );

        $data['permissions'] = $data['current_user']->permission; 

        return $this->_render_page('dropbox/regristration_dropbox_npkp', $data);
    }

    public function regis_npkp_json()
    {
        $model = new M_invoicing_npkp();
        $data['current_user'] = $this->ionAuth->user()->row();
        $requestData = json_decode(file_get_contents('php://input'), true);

        if ($requestData != NULL) {
            $formatted_date_low = $requestData['startDate'] != "" ? date('Ymd', strtotime($requestData['startDate'])) : NULL;
            $formatted_date_high = $requestData['endDate'] != "" ? date('Ymd', strtotime($requestData['endDate'])) : NULL;
            $invoice_number = $requestData['invoiceNumber'] != "" ? $requestData['invoiceNumber'] : NULL;
        } else {
            $formatted_date_low = NULL;
            $formatted_date_high = NULL;
            $invoice_number = NULL;
        }
        $data['startDate'] = $formatted_date_low;
        $data['endDate'] = $formatted_date_high;
        $data['invoicenumber'] = $invoice_number;

        $result = $model->getDataBasedDate($formatted_date_low, $formatted_date_high, $invoice_number, $data['current_user']->vendor_code);

        if (!empty($result)) {
            $data['result'] = $result;
        } else {
            $data['error'] = 'Data tidak tersedia';
            unset($data['result']);
        }

        echo json_encode($data);
    }

    public function regris_mgl()
    {
        $this->check_permission('Module.View.RegistrationDropboxMgl');
        $data['title'] = 'Registration Dropbox MGL';
        $data['ionAuth'] = $this->ionAuth;
        $data['current_user'] = $this->ionAuth->user()->row();
        $model = new M_invoicing();
        $data['js']['footer'] = array(
            'datatables/jquery.dataTables.min',
            'datatables-bs4/js/dataTables.bootstrap4.min',
            'datatables-responsive/js/dataTables.responsive.min',
            'datatables-responsive/js/responsive.bootstrap4.min',
            'datatables/DataTables-2.0.1/js/dataTables',
            'toastr/toastr.min',
        );

        $data['css']['header'] = array(
            'datatables/DataTables-2.0.1/css/dataTables.dataTables',
            'datatables/DataTables-2.0.1/css/dataTables.dateTime.min',
            'toastr/toastr.min',
        );

        $data['permissions'] = $data['current_user']->permission; 

        return $this->_render_page('dropbox/regristration_dropbox_mgl', $data);
    }

    public function regis_mgl_json()
    {
        $model = new M_invoicing_mgl();
        $data['current_user'] = $this->ionAuth->user()->row();
        $requestData = json_decode(file_get_contents('php://input'), true);

        if ($requestData != NULL) {
            $formatted_date_low = $requestData['startDate'] != "" ? date('Ymd', strtotime($requestData['startDate'])) : NULL;
            $formatted_date_high = $requestData['endDate'] != "" ? date('Ymd', strtotime($requestData['endDate'])) : NULL;
            $invoice_number = $requestData['invoiceNumber'] != "" ? $requestData['invoiceNumber'] : NULL;
        } else {
            $formatted_date_low = NULL;
            $formatted_date_high = NULL;
            $invoice_number = NULL;
        }
        $data['startDate'] = $formatted_date_low;
        $data['endDate'] = $formatted_date_high;
        $data['invoicenumber'] = $invoice_number;

        $result = $model->getDataBasedDate($formatted_date_low, $formatted_date_high, $invoice_number, $data['current_user']->vendor_code);

        if (!empty($result)) {
            $data['result'] = $result;
        } else {
            $data['error'] = 'Data tidak tersedia';
            unset($data['result']);
        }

        echo json_encode($data);
    }


    public function datapushdropbox()
    {
        // $model = new M_dropbox;
        // date_default_timezone_set('Asia/Jakarta');
        // $postData = json_decode(file_get_contents('php://input'), true);
        // $checkedData = $postData['checkedData'];
        // $due_date = $postData['date'];
        // $data['result'] = $checkedData;

        // $dropbox_id = $model->get_running_id();
        // $date = date_create();
        // $generate_date = Date('Ymd');
        // $generate_time = date_format($date, 'H:i:s');

        // foreach ($checkedData as $iterate){
        //     $data_to_insert = array(
        //         'dropbox_id' => $dropbox_id,
        //         'due_date' => $due_date,
        //         'generated_date' => $generate_date,
        //         'generated_time' => $generate_time,
        //         'user_generate' => $iterate[3],
        //         'company_name' => $iterate[4],
        //         'invoicing_id' => $iterate[2],
        //         'no_invoice' => $iterate[1],
        //     );
        //     $model->insertDropbox($data_to_insert);
        // }
        // session()->setFlashdata("success", "This is success message");
        // $data['message'] = true;
        // echo json_encode($data);

        $model = new M_dropbox;
        date_default_timezone_set('Asia/Jakarta');

        try {
            $postData = json_decode(file_get_contents('php://input'), true);
            $checkedData = $postData['checkedData'];
            $due_date = $postData['date'];
            $data['result'] = $checkedData;

            $dropbox_id = $model->get_running_id();
            $date = date_create();
            $generate_date = Date('Ymd');
            $generate_time = date_format($date, 'H:i:s');

            $successCount = 0;
            $errorMessages = []; 

            foreach ($checkedData as $iterate) {
                $no_invoice = $iterate[1];
                if (!$model->isDuplicateActiveData($dropbox_id, $no_invoice)) {
                    $data_to_insert = array(
                        'dropbox_id' => $dropbox_id,
                        'due_date' => $due_date,
                        'generated_date' => $generate_date,
                        'generated_time' => $generate_time,
                        'user_generate' => $iterate[3],
                        'company_name' => $iterate[4],
                        'invoicing_id' => $iterate[2],
                        'no_invoice' => $no_invoice,
                    );
                    $model->insertDropbox($data_to_insert);
                    $successCount++;
                } else {
                    $errorMessages[] = "No. Invoice $no_invoice sudah ada dalam data aktif.";
                }
            }

            if ($successCount > 0) {
                $data['message'] = true;
                $data['response'] = "$successCount data berhasil diproses.";
            } else {
                $data['message'] = false;
                $data['response'] = "Tidak ada data yang berhasil diproses. " . implode(' ', $errorMessages);
            }

            return $this->response->setJSON($data);

        } catch (\Exception $e) {
            $data['message'] = false;
            $data['response'] = "Terjadi kesalahan: " . $e->getMessage();
            return $this->response->setStatusCode(500)->setJSON($data);
        }
    }

    public function datapushdropboxnpkp()
    {
        $model = new M_dropbox;
        date_default_timezone_set('Asia/Jakarta');

        try {
            $postData = json_decode(file_get_contents('php://input'), true);
            $checkedData = $postData['checkedData'];
            $due_date = $postData['date'];
            $data['result'] = $checkedData;

            $dropbox_id = $model->get_running_id();
            $date = date_create();
            $generate_date = Date('Ymd');
            $generate_time = date_format($date, 'H:i:s');

            $successCount = 0;
            $errorMessages = []; 

            foreach ($checkedData as $iterate) {
                $no_invoice = $iterate[1];
                if (!$model->isDuplicateActiveData($dropbox_id, $no_invoice)) {
                    $data_to_insert = array(
                        'dropbox_id' => $dropbox_id,
                        'due_date' => $due_date,
                        'generated_date' => $generate_date,
                        'generated_time' => $generate_time,
                        'user_generate' => $iterate[3],
                        'company_name' => $iterate[4],
                        'invoicing_id' => $iterate[2],
                        'no_invoice' => $no_invoice,
                    );
                    $model->insertDropboxnpkp($data_to_insert);
                    $successCount++;
                } else {
                    $errorMessages[] = "No. Invoice $no_invoice sudah ada dalam data aktif.";
                }
            }

            if ($successCount > 0) {
                $data['message'] = true;
                $data['response'] = "$successCount data berhasil diproses.";
            } else {
                $data['message'] = false;
                $data['response'] = "Tidak ada data yang berhasil diproses. " . implode(' ', $errorMessages);
            }

            return $this->response->setJSON($data);

        } catch (\Exception $e) {
            $data['message'] = false;
            $data['response'] = "Terjadi kesalahan: " . $e->getMessage();
            return $this->response->setStatusCode(500)->setJSON($data);
        }
    }

    public function datapushdropboxmgl()
    {

    //     $model = new M_dropbox;
    //     date_default_timezone_set('Asia/Jakarta');
    //     $postData = json_decode(file_get_contents('php://input'), true);
    //     $checkedData = $postData['checkedData'];
    //     $due_date = $postData['date'];
    //     $data['result'] = $checkedData;

    //     $dropbox_id = $model->get_running_id();
    //     $date = date_create();
    //     $generate_date = Date('Ymd');
    //     $generate_time = date_format($date, 'H:i:s');

    //     foreach ($checkedData as $iterate){
    //         if (!$model->isDuplicateActiveData($dropbox_id, $iterate[1])) {
    //             $data_to_insert = array(
    //                 'dropbox_id' => $dropbox_id,
    //                 'due_date' => $due_date,
    //                 'generated_date' => $generate_date,
    //                 'generated_time' => $generate_time,
    //                 'user_generate' => $iterate[3],
    //                 'company_name' => $iterate[4],
    //                 'invoicing_id' => $iterate[2],
    //                 'no_invoice' => $iterate[1],
    //             );
    //             $model->insertDropboxmgl($data_to_insert);
    //         } else {
    //             session()->setFlashdata("warning", "Data aktif dengan No. Invoice $no_invoice sudah ada.");
    //         }
    //     }
    //     session()->setFlashdata("success", "This is success message");
    //     $data['message'] = true;
    //     echo json_encode($data);

        $model = new M_dropbox;
        date_default_timezone_set('Asia/Jakarta');

        try {
            $postData = json_decode(file_get_contents('php://input'), true);
            $checkedData = $postData['checkedData'];
            $due_date = $postData['date'];
            $data['result'] = $checkedData;

            $dropbox_id = $model->get_running_id();
            $date = date_create();
            $generate_date = Date('Ymd');
            $generate_time = date_format($date, 'H:i:s');

            $successCount = 0;
            $errorMessages = []; 

            foreach ($checkedData as $iterate) {
                $no_invoice = $iterate[1];
                if (!$model->isDuplicateActiveData($dropbox_id, $no_invoice)) {
                    $data_to_insert = array(
                        'dropbox_id' => $dropbox_id,
                        'due_date' => $due_date,
                        'generated_date' => $generate_date,
                        'generated_time' => $generate_time,
                        'user_generate' => $iterate[3],
                        'company_name' => $iterate[4],
                        'invoicing_id' => $iterate[2],
                        'no_invoice' => $no_invoice,
                    );
                    $model->insertDropboxmgl($data_to_insert);
                    $successCount++;
                } else {
                    $errorMessages[] = "No. Invoice $no_invoice sudah ada dalam data aktif.";
                }
            }

            if ($successCount > 0) {
                $data['message'] = true;
                $data['response'] = "$successCount data berhasil diproses.";
            } else {
                $data['message'] = false;
                $data['response'] = "Tidak ada data yang berhasil diproses. " . implode(' ', $errorMessages);
            }

            return $this->response->setJSON($data);

        } catch (\Exception $e) {
            $data['message'] = false;
            $data['response'] = "Terjadi kesalahan: " . $e->getMessage();
            return $this->response->setStatusCode(500)->setJSON($data);
        }
    }


    public function updateStatusGenerate()
    {
        $builder = new M_invoicing();
        $invoicingId = $this->request->getPost('invoicingId');
        $data = ['status_generate' => 'X'];
        $builder->set($data);
        $builder->whereIn('invoicing_id', $invoicingId);
        return $builder->update();
    }

    public function temp()
    {
        $checkedItems = json_decode($this->request->getPost('checkedItems'), true);
        $model = new M_dropbox();
        $dropbox_id = 'DB' . date('ymd') . str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
        $date = date_create();
        $generate_date = date_format($date, 'd/m/Y');
        $generate_time = date_format($date, 'H:i:s');
        $data['checkData'] = $checkedItems;

        // foreach ($checkedItems as $iterate) {
        //     $data_to_insert = array(
        //         'dropbox_id' => $dropbox_id,
        //         'generated_date' => $generate_date,
        //         'generated_time' => $generate_time,
        //         'invoicing_id' => $iterate[2],
        //         'no_invoice' => $iterate[3],
        //     );
        //     $model->sendData($data_to_insert);
        // }
        // return session()->setFlashdata('success', 'DropBox added successfully.');

        echo json_encode($data);
    }

    public function gr_real()
    {
        $data['current_user'] = $this->ionAuth->user()->row();
        $requestData = json_decode(file_get_contents('php://input'), true);

        if ($requestData != NULL) {
            $po_number = $requestData['poNumber'] != "" ? $requestData['poNumber'] : NULL;
        } else {
            $po_number = NULL;
        }

        $data['po_number'] = $po_number;

        $M_curl = new M_curl();
        $this->SAP_PARAMS['function'] = 'Z_QC';
        $this->SAP_PARAMS['params'] = [
            'RPT' => 'P_GR_SELECT',
            'CNMA' => $data['current_user']->vendor_code,
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

    public function reprint()
    {
        $this->check_permission('Module.View.PrintDropbox');
        $data['title'] = 'Reprint Dropbox';
        $data['ionAuth'] = $this->ionAuth;
        $model = new M_dropbox();
        $data['js']['footer'] = array(
            'datatables/jquery.dataTables.min',
            'datatables-bs4/js/dataTables.bootstrap4.min',
            'datatables-responsive/js/dataTables.responsive.min',
            'datatables-responsive/js/responsive.bootstrap4.min',
            'datatables/DataTables-2.0.1/js/dataTables',
            'moment/moment.min',
            'datatables/DataTables-2.0.1/js/dataTables.dateTime.min',
            'daterangepicker/daterangepicker.min',
            'toastr/toastr.min',
        );

        $data['css']['header'] = array(
            'datatables/DataTables-2.0.1/css/dataTables.dataTables',
            'datatables/DataTables-2.0.1/css/dataTables.dateTime.min',
            'daterangepicker/daterangepicker',
            'toastr/toastr.min',
        );

        // $data['getDropBox'] = $model->getDropBox();
        $data['current_user'] = $this->ionAuth->user()->row();
        return $this->_render_page('dropbox/reprint', $data);
    }

    public function reprint_json()
    {
        $model = new M_dropbox();
        $data['current_user'] = $this->ionAuth->user()->row();
        $requestData = json_decode(file_get_contents('php://input'), true);

        if ($requestData != NULL) {
            $formatted_date_low = $requestData['startDate'] != "" ? date('Ymd', strtotime($requestData['startDate'])) : NULL;
            $formatted_date_high = $requestData['endDate'] != "" ? date('Ymd', strtotime($requestData['endDate'])) : NULL;
        } else {
            $formatted_date_low = NULL;
            $formatted_date_high = NULL;
        }

        $result = $model->getDataBasedDate($formatted_date_low, $formatted_date_high, $data['current_user']->vendor_code);
        if (!empty($result)) {
            $data['result'] = $result;
        } else {
            $data['error'] = 'Data tidak tersedia';
            unset($data['result']);
        }

        echo json_encode($data);
    }

    public function reprint_npkp()
    {
        $this->check_permission('Module.View.PrintDropboxNpkp');
        $data['title'] = 'Reprint Dropbox';
        $data['ionAuth'] = $this->ionAuth;
        $model = new M_dropbox();
        $data['js']['footer'] = array(
            'datatables/jquery.dataTables.min',
            'datatables-bs4/js/dataTables.bootstrap4.min',
            'datatables-responsive/js/dataTables.responsive.min',
            'datatables-responsive/js/responsive.bootstrap4.min',
            'datatables/DataTables-2.0.1/js/dataTables',
            'moment/moment.min',
            'datatables/DataTables-2.0.1/js/dataTables.dateTime.min',
            'daterangepicker/daterangepicker.min',
            'toastr/toastr.min',
        );

        $data['css']['header'] = array(
            'datatables/DataTables-2.0.1/css/dataTables.dataTables',
            'datatables/DataTables-2.0.1/css/dataTables.dateTime.min',
            'daterangepicker/daterangepicker',
            'toastr/toastr.min',
        );

        $data['current_user'] = $this->ionAuth->user()->row();
        return $this->_render_page('dropbox/reprint_npkp', $data);
    }

    public function reprint_json_npkp()
    {
        $model = new M_dropbox();
        $data['current_user'] = $this->ionAuth->user()->row();
        $requestData = json_decode(file_get_contents('php://input'), true);

        if ($requestData != NULL) {
            $formatted_date_low = $requestData['startDate'] != "" ? date('Ymd', strtotime($requestData['startDate'])) : NULL;
            $formatted_date_high = $requestData['endDate'] != "" ? date('Ymd', strtotime($requestData['endDate'])) : NULL;
        } else {
            $formatted_date_low = NULL;
            $formatted_date_high = NULL;
        }

        $result = $model->getDataBasedDate($formatted_date_low, $formatted_date_high, $data['current_user']->vendor_code);
        if (!empty($result)) {
            $data['result'] = $result;
        } else {
            $data['error'] = 'Data tidak tersedia';
            unset($data['result']);
        }
        echo json_encode($data);
    }

    public function reprint_mgl()
    {
        $this->check_permission('Module.View.PrintDropboxMgl');
        $data['title'] = 'Reprint Dropbox';
        $data['ionAuth'] = $this->ionAuth;
        $model = new M_dropbox();
        $data['js']['footer'] = array(
            'datatables/jquery.dataTables.min',
            'datatables-bs4/js/dataTables.bootstrap4.min',
            'datatables-responsive/js/dataTables.responsive.min',
            'datatables-responsive/js/responsive.bootstrap4.min',
            'datatables/DataTables-2.0.1/js/dataTables',
            'moment/moment.min',
            'datatables/DataTables-2.0.1/js/dataTables.dateTime.min',
            'daterangepicker/daterangepicker.min',
            'toastr/toastr.min',
        );

        $data['css']['header'] = array(
            'datatables/DataTables-2.0.1/css/dataTables.dataTables',
            'datatables/DataTables-2.0.1/css/dataTables.dateTime.min',
            'daterangepicker/daterangepicker',
            'toastr/toastr.min',
        );

        $data['current_user'] = $this->ionAuth->user()->row();
        return $this->_render_page('dropbox/reprint_mgl', $data);
    }

    public function reprint_json_mgl()
    {
        $model = new M_dropbox();
        $data['current_user'] = $this->ionAuth->user()->row();
        $requestData = json_decode(file_get_contents('php://input'), true);

        if ($requestData != NULL) {
            $formatted_date_low = $requestData['startDate'] != "" ? date('Ymd', strtotime($requestData['startDate'])) : NULL;
            $formatted_date_high = $requestData['endDate'] != "" ? date('Ymd', strtotime($requestData['endDate'])) : NULL;
        } else {
            $formatted_date_low = NULL;
            $formatted_date_high = NULL;
        }

        $result = $model->getDataBasedDate($formatted_date_low, $formatted_date_high, $data['current_user']->vendor_code);
        if (!empty($result)) {
            $data['result'] = $result;
        } else {
            $data['error'] = 'Data tidak tersedia';
            unset($data['result']);
        }
        echo json_encode($data);
    }

    public function pdf()
    {
        $data['title'] = 'Print Dropbox';

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
        $model = new M_dropbox;

        $dropbox_id = $this->request->getGet('dropbox_id');

        $data['result'] = $model->getDataBasedID($dropbox_id);


        $data['current_user'] = $this->ionAuth->user()->row();

        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $barcodeData = $data['result'][0]['dropbox_id'];
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

        $html = view('dropbox/dropbox_print', $data);

        $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
        $this->response->setContentType('application/pdf');
        $pdf->Output('Dropbox.pdf', 'I');
    }

    public function pdfnpkp()
    {
        $data['title'] = 'Print Dropbox';
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
        $model = new M_dropbox;

        $dropbox_id = $this->request->getGet('dropbox_id');

        $data['result'] = $model->getDataBasedIDNpkp($dropbox_id);

        $data['current_user'] = $this->ionAuth->user()->row();

        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $barcodeData = $data['result'][0]['dropbox_id'];
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

        $html = view('dropbox/dropbox_print', $data);

        $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
        $this->response->setContentType('application/pdf');
        $pdf->Output('Dropbox.pdf', 'I');
    }

    public function pdfmgl()
    {
        $data['title'] = 'Print Dropbox';
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
        $model = new M_dropbox;

        $dropbox_id = $this->request->getGet('dropbox_id');

        $data['result'] = $model->getDataBasedIDMgl($dropbox_id);

        $data['current_user'] = $this->ionAuth->user()->row();

        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $barcodeData = $data['result'][0]['dropbox_id'];
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

        $html = view('dropbox/dropbox_print', $data);

        $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
        $this->response->setContentType('application/pdf');
        $pdf->Output('Dropbox.pdf', 'I');
    }


    public function dropbox_detail()
    {
        $dropbox_id = $this->request->getGet('dropbox_id');
        $model = new M_dropbox;
        $result = $model->getDataBasedID($dropbox_id);
        $data['detail'] = $result;
        echo json_encode($data);
    }

    public function dropbox_detail_npkp()
    {
        $dropbox_id = $this->request->getGet('dropbox_id');
        $model = new M_dropbox;
        $result = $model->getDataBasedIDNpkp($dropbox_id);
        $data['detail'] = $result;
        echo json_encode($data);
    }

    public function dropbox_detail_mgl()
    {
        $dropbox_id = $this->request->getGet('dropbox_id');
        $model = new M_dropbox;
        $result = $model->getDataBasedIDMgl($dropbox_id);
        $data['detail'] = $result;
        echo json_encode($data);
    }
}
