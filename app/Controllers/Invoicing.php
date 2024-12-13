<?php

namespace App\Controllers;
use \IonAuth\Libraries\IonAuth;
use CodeIgniter\Controller;
use App\Models\M_invoicing;
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
use App\Models\InvoiceAfterDokOk_model;
use App\Models\M_miro;
use App\Models\M_generate_qr;
use App\Models\M_attempt_verif;
use CodeIgniter\Email\Email;
use App\Libraries\QrCodeGenerator;

class Invoicing extends BaseController
{
    protected $ionAuth;
    public function __construct()
    {
        $model = new IonAuth();
        $this->ionAuth = $model;
    }

    public function gr_index()
    {
        $this->check_permission('Module.View.GoodsReceipt');
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
            'toastr/toastr.min',
            'xlsx/xlsx.full.min',
        );

        return $this->_render_page('invoicing/GR/index', $data);
    }

    public function gr_maj()
    {
        $data['title'] = 'Transaction';
        $data['ionAuth'] = $this->ionAuth;
        $data['current_user'] = $this->ionAuth->user()->row();
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

        return $this->_render_page('invoicing/GR/indexmaj', $data);
    }

    public function datatrack()
    {
        $this->check_permission('Module.View.TrackData');
        $data['title'] = 'Transaction';
        $data['ionAuth'] = $this->ionAuth;
        $data['current_user'] = $this->ionAuth->user()->row();
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
        );

        return $this->_render_page('invoicing/GR/trackdata', $data);
    }


    public function majdatatrack()
    {
        $this->check_permission('Module.View.MajTrackData');
        $data['title'] = 'Transaction';
        $data['ionAuth'] = $this->ionAuth;
        $data['current_user'] = $this->ionAuth->user()->row();
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
        );

        return $this->_render_page('invoicing/GR/trackdatamaj', $data);
    }


    public function track_real()
    {
        $data['current_user'] = $this->ionAuth->user()->row();
        $requestData = json_decode(file_get_contents('php://input'), true);

        if ($requestData != NULL) {
            $formatted_date_low = $requestData['startDate'] != "" ? date('Ymd', strtotime($requestData['startDate'])) : NULL;
            $formatted_date_high = $requestData['endDate'] != "" ? date('Ymd', strtotime($requestData['endDate'])) : NULL;
            $po_number = $requestData['poNumber'] != "" ? $requestData['poNumber'] : NULL;
        } else {
            $formatted_date_low = NULL;
            $formatted_date_high = NULL;
            $po_number = NULL;
        }        

        $data['date_low'] = $formatted_date_low;
        $data['date_high'] = $formatted_date_high;
        $data['po_number'] = $po_number;

        $model = new M_invoicing();
        $result = $model->getDataUnderProcess($formatted_date_low, $formatted_date_high, $po_number, $data['current_user']->vendor_code);

        if (!empty($result)) {
            $data['result'] = $result;
        } else {
            $data['error'] = 'Data tidak tersedia';
        }

        return $this->response->setJSON($data);
    }

    public function track_maj_real()
    {
        $data['current_user'] = $this->ionAuth->user()->row();
        $requestData = json_decode(file_get_contents('php://input'), true);

        if ($requestData != NULL) {
            $formatted_date_low = $requestData['startDate'] != "" ? date('Ymd', strtotime($requestData['startDate'])) : NULL;
            $formatted_date_high = $requestData['endDate'] != "" ? date('Ymd', strtotime($requestData['endDate'])) : NULL;
            $po_number = $requestData['poNumber'] != "" ? $requestData['poNumber'] : NULL;
            $gr_number = $requestData['noGR'] != "" ? $requestData['noGR'] : NULL;
        } else {
            $formatted_date_low = NULL;
            $formatted_date_high = NULL;
            $po_number = NULL;
            $gr_number = NULL;
        }        

        $data['date_low'] = $formatted_date_low;
        $data['date_high'] = $formatted_date_high;
        $data['po_number'] = $po_number;
        $data['gr_number'] = $gr_number;

        $model = new M_invoicing();
        $data['result'] = $model->getDataUnderProcess($formatted_date_low, $formatted_date_high, $po_number, $gr_number, $requestData['vendor']);

        if (!empty($result)) {
            $data['result'] = $result;
        } else {
            $data['error'] = 'Data tidak tersedia';
        }

        return $this->response->setJSON($data);
    }

    public function gr_real()
    {
        $data['current_user'] = $this->ionAuth->user()->row();
        $requestData = json_decode(file_get_contents('php://input'), true);

        if ($requestData != NULL) {
            $formatted_date_low = $requestData['startDate'] != "" ? date('Ymd', strtotime($requestData['startDate'])) : NULL;
            $formatted_date_high = $requestData['endDate'] != "" ? date('Ymd', strtotime($requestData['endDate'])) : NULL;
            $po_number = $requestData['poNumber'] != "" ? $requestData['poNumber'] : NULL;
        } else {
            $formatted_date_low = NULL;
            $formatted_date_high = NULL;
            $po_number = NULL;
        }        

        $data['date_low'] = $formatted_date_low;
        $data['date_high'] = $formatted_date_high;
        $data['po_number'] = $po_number;

        $M_curl = new M_curl();
        $this->SAP_PARAMS['function'] = 'Z_QC';
        // Request pertama
        $this->SAP_PARAMS['params'] = [
            'RPT' => 'P_GR_SELECT',
            'CNMA' => $data['current_user']->vendor_code,
            'P_DATE_LOW' => $formatted_date_low,
            'P_DATE_HIGH' => $formatted_date_high,
            'P_EBELN' => $po_number,
        ];

        $sap = $M_curl->execute("POST", $this->SAP_PARAMS);
        if ($sap['success']) {
            $data['data_sap'] = $sap['data']['ZGRUSER'];
        } else {
            $data['error'] = isset($sap['message']) ? $sap['message'] : 'Terjadi kesalahan dalam permintaan SAP.';
        }
        echo json_encode($data);
    }

    public function dropboxtrackvendor()
    {
        $this->check_permission('Module.View.TrackDropbox');
        $model = new receiving_model;
        $data['title'] = 'Transaction';
        $data['ionAuth'] = $this->ionAuth;
        $data['current_user'] = $this->ionAuth->user()->row();
        $data['js']['footer'] = array(
            'datatables/jquery.dataTables.min',
            'datatables-bs4/js/dataTables.bootstrap4.min',
            'datatables-responsive/js/dataTables.responsive.min',
            'datatables-responsive/js/responsive.bootstrap4.min',
            'datatables/DataTables-2.0.1/js/dataTables',
            'moment/moment.min',
            'datatables/DataTables-2.0.1/js/dataTables.dateTime.min',
            'sweetalert2/sweetalert2.min',
            'toastr/toastr.min'
        );

        $data['css']['header'] = array(
            'datatables/DataTables-2.0.1/css/dataTables.dataTables',
            'datatables/DataTables-2.0.1/css/dataTables.dateTime.min',
            'daterangepicker/daterangepicker',
            'toastr/toastr.min',
            'sweetalert2-theme-bootstrap-4/bootstrap-4.min'
        );

        return $this->_render_page('invoicing/GR/dropboxvendor', $data);
    }


    public function dropboxtrackvendor_json(){
        $data['current_user'] = $this->ionAuth->user()->row();
        $data['dropbox_id'] = $this->request->getPost('dropbox_id') ?: null;
        $data['startDate'] = $this->request->getPost('startDate') ?: null; 
        $data['endDate'] = $this->request->getPost('endDate') ?: null;
        $model = new M_dropbox();
        $result = $model->getDropboxTrackVendor($data['dropbox_id'], $data['current_user']->vendor_code, $data['startDate'], $data['endDate']);
        
        if (!empty($result)) {
            $response = $result;
        } else {
            $response = ['error' => 'Data tidak tersedia', 'status' => false];
        }
        return $this->response->setJSON(['data' => $response, 'status' => true]);
    }


    public function dropboxtrackmaj()
    {
        $this->check_permission('Module.View.MajTrackDropbox');
        $model = new receiving_model;
        $data['title'] = 'Transaction';
        $data['ionAuth'] = $this->ionAuth;
        $data['current_user'] = $this->ionAuth->user()->row();
        $data['js']['footer'] = array(
            'datatables/jquery.dataTables.min',
            'datatables-bs4/js/dataTables.bootstrap4.min',
            'datatables-responsive/js/dataTables.responsive.min',
            'datatables-responsive/js/responsive.bootstrap4.min',
            'datatables/DataTables-2.0.1/js/dataTables',
            'moment/moment.min',
            'datatables/DataTables-2.0.1/js/dataTables.dateTime.min',
            'sweetalert2/sweetalert2.min',
            'toastr/toastr.min',
            'select2/js/select2.full.min'
        );

        $data['css']['header'] = array(
            'datatables/DataTables-2.0.1/css/dataTables.dataTables',
            'datatables/DataTables-2.0.1/css/dataTables.dateTime.min',
            'daterangepicker/daterangepicker',
            'toastr/toastr.min',
            'sweetalert2-theme-bootstrap-4/bootstrap-4.min',
            'select2/css/select2.min',
            'select2-bootstrap4-theme/select2-bootstrap4.min',
        );

        return $this->_render_page('invoicing/GR/dropboxmaj', $data);
    }

    public function dropboxtrackmaj_json(){
        $data['dropbox_id'] = $this->request->getPost('dropbox_id') ?: null;
        $data['startDate'] = $this->request->getPost('startDate') ?: null; 
        $data['endDate'] = $this->request->getPost('endDate') ?: null;
        $data['vendorSelect'] = $this->request->getPost('vendorSelect') ?: null;
        $model = new M_dropbox();
        $result = $model->getDropboxTrackVendor($data['dropbox_id'], $data['vendorSelect'], $data['startDate'], $data['endDate']);
        
        if (!empty($result)) {
            $response = $result;
        } else {
            $response = ['error' => 'Data tidak tersedia', 'status' => false];
        }
        return $this->response->setJSON(['data' => $response, 'status' => true]);
    }

    public function gr_real_maj()
    {
        $data['current_user'] = $this->ionAuth->user()->row();
        $requestData = json_decode(file_get_contents('php://input'), true);

        if ($requestData != NULL) {
            $formatted_date_low = $requestData['startDate'] != "" ? date('Ymd', strtotime($requestData['startDate'])) : NULL;
            $formatted_date_high = $requestData['endDate'] != "" ? date('Ymd', strtotime($requestData['endDate'])) : NULL;
            $po_number = $requestData['poNumber'] != "" ? $requestData['poNumber'] : NULL;
        } else {
            $formatted_date_low = NULL;
            $formatted_date_high = NULL;
            $po_number = NULL;
        }        

        $data['date_low'] = $formatted_date_low;
        $data['date_high'] = $formatted_date_high;
        $data['po_number'] = $po_number;

        $M_curl = new M_curl();
        $this->SAP_PARAMS['function'] = 'Z_QC';
        // Request pertama
        $this->SAP_PARAMS['params'] = [
            'RPT' => 'P_GR_SELECT',
            'CNMA' => $requestData['vendor'],//$data['current_user']->id_vendor,
            'P_DATE_LOW' => $formatted_date_low,
            'P_DATE_HIGH' => $formatted_date_high,
            'P_EBELN' => $po_number,
        ];

        $sap = $M_curl->execute("POST", $this->SAP_PARAMS);
        if ($sap['success']) {
            $data['data_sap'] = $sap['data']['ZGRUSER'];
        } else {
            $data['error'] = isset($sap['message']) ? $sap['message'] : 'Terjadi kesalahan dalam permintaan SAP.';
        }
        echo json_encode($data);
    }

    public function gr_approve($gr, $item)
    {
        $data['data_sap'] = [];
        $data['ionAuth'] = $this->ionAuth;
        $M_curl = new M_curl();
        $this->SAP_PARAMS['function'] = 'Z_QC';
        $this->SAP_PARAMS['params'] = [
            'RPT' => 'P_GR_UPDATE_GR',
            'P_BELNR' => $gr,
            'P_EBELP' => $item
        ];
        $sap = $M_curl->execute("POST", $this->SAP_PARAMS);
        return redirect()->to('invoicing/gr')->with('success', $sap['message']);
    }

    public function reistration_dropbox()
    {
        $data['title'] = 'Registration Dropbox';
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

        return $this->_render_page('invoicing/verify_transaction/index', $data);
    }


    public function status_update($id)
    {
        $data['title'] = 'Invoicing';
        $data['ionAuth'] = $this->ionAuth;
        $M_invoicing = new M_invoicing();
        $data['js']['footer'] = array(
            'bs-custom-file-input/bs-custom-file-input.min',
        );
        $data['gr_data'] = $M_invoicing->getGRbyID($id);
        $data['current_user'] = $this->ionAuth->user()->row();
        return $this->_render_page('invoicing/GR/form', $data);
    }


    public function statusDropbox()
    {
        $dropbox_id = $this->request->getPost('dropbox_id');
        $model = new M_invoicing;
        $response = $model->reachstatusdropbox($dropbox_id);
        $data['result'] = $response;
        echo json_encode($data);
    }

    public function print_gr()
    {
        $data['title'] = 'Invoicing';
        $data['ionAuth'] = $this->ionAuth;
        $qrcode = new Generator;
        $data['styleRound'] = $qrcode->size(150)->color(0, 0, 0)->backgroundColor(255, 255, 255)->style('round')->generate('https://www.binaryboxtuts.com/');
        $data['current_user'] = $this->ionAuth->user()->row();
        return $this->_render_page('invoicing/GR/printgr', $data);
    }


    public function table_res()
    {
        $M_invoicing = new M_invoicing();
        $data['ionAuth'] = $this->ionAuth;
        $data['data_po'] =  $M_invoicing->getData();
        return view('invoicing/verify_transaction/table',$data);
    }

    public function verify_transaction()
    {
        $data['title'] = 'Verify Transaction';
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

        return $this->_render_page('invoicing/verify_transaction/index', $data);
    }

    public function jsonverify()
    {
        $data['current_user'] = $this->ionAuth->user()->row();
        $requestData = json_decode(file_get_contents('php://input'), true);

        $formatted_month = NULL;

        if ($requestData != NULL) {
            $formatted_month = !empty($requestData['monthDate']) ? date('Ym01', strtotime($requestData['monthDate'])) : NULL;
        }

        $data['bulan json'] = $formatted_month;

        $M_curl = new M_curl();
        $this->SAP_PARAMS['function'] = 'Z_QC';
        // Request pertama
        $this->SAP_PARAMS['params'] = [
            'RPT' => 'P_INVOICE',
            'CNMA' => '0000100136',
            'P_DATE' => $formatted_month,
        ];

        $timestamp = strtotime($formatted_month);
        $nama_bulan = date('F Y', $timestamp);

        $sap = $M_curl->execute("POST", $this->SAP_PARAMS);
        if ($sap['success']) {
            $invoice = isset($sap['data']['ZGRUSER']) ? $sap['data']['ZGRUSER'] : [];
            $total_amount = 0;
            foreach ($invoice as $value) {
                if (isset($value['WRBTR']) && is_numeric($value['WRBTR'])) {
                    $total_amount += floatval($value['WRBTR']);
                }
            }

            $data['data_sap'] = array(
                'total_transaction' => count($invoice),
                'total_verified' => count($invoice),
                'total_unverified' => '0',
                'total_amount' => number_format($total_amount * 1000, 0, ',', '.'),
                'currency' => 'IDR',
                'period' => $nama_bulan,
                'view' => '---id_unverified_transaction---',
            );
        } else {
            $data['error'] = isset($sap['message']) ? $sap['message'] : 'Terjadi kesalahan dalam permintaan SAP.';
        }
        echo json_encode($data); // Keluarkan respons JSON tunggal dari server
    }


    public function process()
    {
        $data['title'] = 'View Verify';
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

        return $this->_render_page('invoicing/verify_transaction/unverify_view', $data);
    }

    public function vendor_verify()
    {
        $this->check_permission('Module.View.Invoice');
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
        return $this->_render_page('invoicing/verify_transaction/vendor_verify_new', $data);
    }

    public function non_reguler()
    {
        $this->check_permission('Module.View.NonReguler');
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

        return $this->_render_page('invoicing/verify_transaction/vendor_verify_new', $data);
    }

    public function generate_invoice_json()
    {
        $data['idqr'] = $this->request->getGet('idqr');
        $data['totalpayment'] = preg_replace('/[^\d]/', '', $this->request->getGet('totalpayment')) / 100;

        $dataqr = [
            'id_qr' => $data['idqr'],
            'total_payment' => $data['totalpayment'],
        ];

        $json_data = json_encode($dataqr);

        $qrCodeGenerator = new QrCodeGenerator();
        $qrCodePath = $qrCodeGenerator->generate($json_data);
        return $this->response->download($qrCodePath, null)->setFileName('qrcode.png');
        // echo json_encode($data);
    }

    public function vendor_verify_json()
    {
        $data['current_user'] = $this->ionAuth->user()->row();
        $requestData = json_decode(file_get_contents('php://input'), true);
        $po_number = $requestData['poNumber'];

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
        echo json_encode($data);
    }


    public function invoice()
    {
        $data['title'] = 'Invoice';
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
        return $this->_render_page('invoicing/verify_transaction/invoice', $data);
    }

    public function invoice_json()
    {
        $data['current_user'] = $this->ionAuth->user()->row();
        $requestData = json_decode(file_get_contents('php://input'), true);

        if ($requestData != NULL) {
            $formatted_date_low = $requestData['startDate'] != "" ? date('Ymd', strtotime($requestData['startDate'])) : NULL;
            $formatted_date_high = $requestData['endDate'] != "" ? date('Ymd', strtotime($requestData['endDate'])) : NULL;
            $po_number = $requestData['poNumber'] != "" ? $requestData['poNumber'] : NULL;
        } else {
            $formatted_date_low = NULL;
            $formatted_date_high = NULL;
            $po_number = NULL;
        }        

        $data['date_low'] = $formatted_date_low;
        $data['date_high'] = $formatted_date_high;
        $data['po_number'] = $po_number;

        $M_curl = new M_curl();
        $this->SAP_PARAMS['function'] = 'Z_QC';
        // Request pertama
        $this->SAP_PARAMS['params'] = [
            'RPT' => 'P_GR_SELECT',
            'CNMA' => $data['current_user']->vendor_code,//$data['current_user']->id_vendor,
            'P_DATE_LOW' => $formatted_date_low,
            'P_DATE_HIGH' => $formatted_date_high,
            'P_EBELN' => $po_number,
        ];

        $sap = $M_curl->execute("POST", $this->SAP_PARAMS);
        if ($sap['success']) {
            $grouped_data = []; // Data yang akan dikelompokkan

            foreach ($sap['data']['ZGRUSER'] as $item) {
                if ($item['VBELN_ST'] == 'GR') {
                    $key = $item['EBELN'] . '_' . $item['BKTXT'];

                    if (!isset($grouped_data[$key])) {
                        // Buat entri baru jika belum ada untuk kunci ini
                        $grouped_data[$key] = [
                            'EBELN' => $item['EBELN'],
                            'BKTXT' => $item['BKTXT'],
                            'items' => []
                        ];
                    }

                    // Tambahkan item ke dalam kelompok yang sesuai
                    $grouped_data[$key]['items'][] = [
                        'EBELN' => $item['EBELN'], // Ganti 'field1' dengan nama field yang benar
                        'BELNR' => $item['BELNR'],
                        'MENGE' => $item['MENGE'],
                    ];
                }
            }

            // Ubah kembali ke array indeks untuk mendapatkan satu baris per kelompok
            $data['data_sap'] = array_values($grouped_data);
        } else {
            $data['error'] = isset($sap['message']) ? $sap['message'] : 'Terjadi kesalahan dalam permintaan SAP.';
        }

        echo json_encode($data);
    }


    public function invoice_print()
    {
        $data['title'] = 'Print Invoice';

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

        $qrcode = new Generator;
        $data['styleRound'] = $qrcode->size(120)->color(0, 0, 0)->backgroundColor(255, 255, 255)->style('round')->generate('12345678');

        $no_po = $this->request->getGet('EBELN');
        $surat_jalan = $this->request->getGet('BKTXT');
        $startDate = $this->request->getGet('startDate');
        $endDate = $this->request->getGet('endDate');

        $data['Surat Jalan'] = $surat_jalan;
        $data['Nomor PO'] = $no_po;
        $data['start date'] = $startDate;
        $data['end date'] = $endDate;

        $data['current_user'] = $this->ionAuth->user()->row();

        $formatted_date_low = $startDate != "" ? date('Ymd', strtotime($startDate)) : NULL;
        $formatted_date_high = $endDate != "" ? date('Ymd', strtotime($endDate)) : NULL;
        $po_number = $no_po != "" ? $no_po : NULL;
      
        $data['date_low'] = $formatted_date_low;
        $data['date_high'] = $formatted_date_high;
        $data['po_number'] = $po_number;

        $M_curl = new M_curl();
        $this->SAP_PARAMS['function'] = 'Z_QC';
        $this->SAP_PARAMS['params'] = [
            'RPT' => 'P_GR_SELECT',
            'CNMA' => $data['current_user']->vendor_code,//$data['current_user']->id_vendor,
            'P_DATE_LOW' => $formatted_date_low,
            'P_DATE_HIGH' => $formatted_date_high,
            'P_EBELN' => $po_number,
        ];

        $sap = $M_curl->execute("POST", $this->SAP_PARAMS);
        if ($sap['success']) {
            $filtered_data = [];

            foreach ($sap['data']['ZGRUSER'] as $item) {
                if ($item['VBELN_ST'] == 'GR' && 
                    $item['EBELN'] == $po_number && 
                    $item['BKTXT'] == $surat_jalan) {
                    $filtered_data[] = $item;
                }
            }

            $data['data_sap'] = array_values($filtered_data);
        } else {
            $data['error'] = isset($sap['message']) ? $sap['message'] : 'Terjadi kesalahan dalam permintaan SAP.';
        }

        return $this->_render_page('invoicing/verify_transaction/invoice_print', $data);
    }

    public function pdf()
    {
        $data['title'] = 'Print Invoice';

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

        $no_po = $this->request->getGet('EBELN');
        $surat_jalan = $this->request->getGet('BKTXT');
        $startDate = $this->request->getGet('startDate');
        $endDate = $this->request->getGet('endDate');

        $data['Surat Jalan'] = $surat_jalan;
        $data['Nomor PO'] = $no_po;
        $data['start date'] = $startDate;
        $data['end date'] = $endDate;

        $data['current_user'] = $this->ionAuth->user()->row();

        $formatted_date_low = $startDate != "" ? date('Ymd', strtotime($startDate)) : NULL;
        $formatted_date_high = $endDate != "" ? date('Ymd', strtotime($endDate)) : NULL;
        $po_number = $no_po != "" ? $no_po : NULL;
      
        $data['date_low'] = $formatted_date_low;
        $data['date_high'] = $formatted_date_high;
        $data['po_number'] = $po_number;

        $M_curl = new M_curl();
        $this->SAP_PARAMS['function'] = 'Z_QC';
        // Request pertama
        $this->SAP_PARAMS['params'] = [
            'RPT' => 'P_GR_SELECT',
            'CNMA' => $data['current_user']->vendor_code,//$data['current_user']->id_vendor,
            'P_DATE_LOW' => $formatted_date_low,
            'P_DATE_HIGH' => $formatted_date_high,
            'P_EBELN' => $po_number,
        ];

        $sap = $M_curl->execute("POST", $this->SAP_PARAMS);
        if ($sap['success']) {
            $filtered_data = [];

            foreach ($sap['data']['ZGRUSER'] as $item) {
                if ($item['VBELN_ST'] == 'GR' && 
                    $item['EBELN'] == $po_number && 
                    $item['BKTXT'] == $surat_jalan) {
                    $filtered_data[] = $item;
                }
            }
            // Ubah kembali ke array indeks untuk mendapatkan satu baris per kelompok
            $data['data_sap'] = array_values($filtered_data);
        } else {
            $data['error'] = isset($sap['message']) ? $sap['message'] : 'Terjadi kesalahan dalam permintaan SAP.';
        }

        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $barcodeData = '123456789';
        $pdf->setBarcodeData($barcodeData);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('fahmi');
        $pdf->SetTitle('Invoice Print');
        $pdf->SetSubject('invoice vendor');
        $pdf->SetKeywords('TCPDF, PDF, invoice, mekararmadajaya');

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

        $html = view('invoicing/verify_transaction/invoice_print', $data);

        $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
        $this->response->setContentType('application/pdf');
        $pdf->Output('invoice.pdf', 'I');
    }
    
    
    public function cancel_process()
    {
        date_default_timezone_set('Asia/Jakarta');
        $data['no_invoice'] = $this->request->getPost('no_invoice');
        $data['uservendor'] = $this->request->getPost('uservendor');
        $data['invoice_id'] = $this->request->getPost('invoice_id');
        $data['pesan'] = $this->request->getPost('pesan');
        $data['alasan'] = $this->request->getPost('alasan');
        $data['nomor_zinver'] = $this->request->getPost('input_zinver');
        $model = new M_dropbox();
        $model2 = new dokumen_ok_model();

        $emailData = $model->getEmailData($data['uservendor']);
        if ($emailData) {
            $email = \Config\Services::email();

            $email->setTo($emailData->cp_email1);
            $email->setFrom('pud.tbn@newarmada.co.id', 'PT. Mekar Armada Jaya');
            $email->setBCC('pud.tbn@newarmada.co.id');

            $email->setSubject('Invoice Cancel');
            $formattedMessage = nl2br($data['pesan']);

            $no_invoice = $data['no_invoice'];
            $invoiceNumber = $data['invoice_id'];
            $companyName = $emailData->company_name;
            $picName = $emailData->cp_name;
            $picMaj = $this->ionAuth->user()->row()->cp_name;

            $message = "
                <html>
                    <head>
                        <style>
                            body {
                                font-family: Arial, sans-serif;
                                line-height: 1.6;
                            }
                            .header {
                                font-size: 20px;
                                font-weight: bold;
                            }
                            .content {
                                margin-top: 20px;
                            }
                            .footer {
                                margin-top: 40px;
                            }
                        </style>
                        <title>Invoice Cancel</title>
                    </head>
                    <body>
                        <div class='header'>
                            <p>Yth. Bapak/Ibu {$picName}</p>
                            <p>dari {$companyName},</p>
                        </div>
                        <div class='content'>
                            <p>Dengan ini kami informasikan bahwa Invoice: {$no_invoice} dengan nomor verifikasi: {$invoiceNumber} tidak dapat kami tindak lanjuti karena alasan berikut :</p>
                            <p>{$formattedMessage}</p>
                            <p>Demikian informasi ini kami sampaikan. Atas perhatian dan kerja samanya, kami ucapkan terima kasih.</p>
                        </div>
                        <div class='footer'>
                            <p>Salam Hormat,</p>
                            <p>{$picMaj}</p>
                            <p>PT. Mekar Armada Jaya</p>
                        </div>
                    </body>
                </html>
            ";

            $email->setMessage($message);

            if ($email->send()) {
                $data['email_status'] = 'Email berhasil dikirim';
                $notificationModel = new \App\Models\NotificationModel();
                $notificationData = [
                    'user_id'       => $emailData->id,
                    'header'        => 'Berkas Cancel',
                    'message'       => 'Invoice ' . $no_invoice,
                    'created_at'    => date('Y-m-d H:i:s')
                ];
                $notificationModel->insert($notificationData);
                $model2->updateOnHoldStatusV2($data['no_invoice'], $data['alasan']);

                $M_curl = new M_curl();

                $this->SAP_PARAMS['function'] = 'Z_PUD';
                $this->SAP_PARAMS['params'] = [
                    'RPT' => 'RPT_ZINVER',
                    'ACTION' => 'DELETE_FIN',
                    'P_NOZINVER' => $data['nomor_zinver'],
                    'P_ALASAN' => $data['alasan'],
                    'P_ZNIK_PUD' => $this->ionAuth->user()->row()->nik_user,
                ];

                $sap = $M_curl->execute("POST", $this->SAP_PARAMS);

                $gr = $model->getGR($data['invoice_id']);

                if ($sap['success']) {
                    $errors = [];
                
                    foreach ($gr as $row) {
                        $this->SAP_PARAMS['function'] = 'Z_QC';
                        $this->SAP_PARAMS['params'] = [
                            'RPT' => 'P_GR_RESTORE',
                            'P_BELNR' => $row['no_gr'],
                            'P_EBELP' => $row['no_item'],
                        ];
                
                        $sap = $M_curl->execute("POST", $this->SAP_PARAMS);
                
                        if (!$sap['success']) {
                            $errors[] = "GR_RESTORE gagal pada GR {$row['no_gr']}: " . $sap['message'];
                        }
                    }
                    
                    if (!empty($errors)) {
                        $data['response'] = "DELETE_PUD berhasil, tetapi beberapa GR_RESTORE gagal: " . implode(', ', $errors);
                    } else {
                        $data['response'] = "Proses DELETE_PUD dan semua GR_RESTORE berhasil.";
                    }
                } else {
                    $data['response'] = "DELETE_PUD gagal: " . $sap['message'];
                }
                
            } else {
                $data['email_status'] = 'Email gagal dikirim: ' . $email->printDebugger(['headers']);
            }
        } else {
            $data['email_status'] = 'Email tidak ditemukan';
        }

        echo json_encode($data);
    }

    public function unity()
    {
        $this->check_permission('Module.Create.Invoice');
        $postData = json_decode(file_get_contents('php://input'), true);
        $checkedData = $postData['checkedData'];

        $result = [];

        function cleanPrice($price) {
            $strippedString = preg_replace('/[^\d]/', '', $price);
            return floatval($strippedString) / 100;
        }

        foreach ($checkedData as $item) {
            $key = $item[3] . '-' . $item[5] . '-' . $item[6];

            if (!isset($result[$key])) {
                $item[7] = strval(floatval($item[7]));
                $result[$key] = $item;
            } else {
                $result[$key][7] = strval(floatval($result[$key][7]) + floatval($item[7])); // Jumlahkan lalu ubah kembali ke string
                $result[$key][8] = 'Rp ' . number_format((cleanPrice($result[$key][8]) + cleanPrice($item[8])), 2, ',', '.');
            }
        }
    
        $result = array_values($result);
        header('Content-Type: application/json');
        return $this->response->setJSON(['result' => $result]);
    }


    public function cancel_process_from_pud()
    {
        date_default_timezone_set('Asia/Jakarta');
        $data['no_invoice'] = $this->request->getPost('no_invoice');
        $data['uservendor'] = $this->request->getPost('uservendor');
        $data['invoice_id'] = $this->request->getPost('invoice_id');
        $data['pesan'] = $this->request->getPost('pesan');
        $data['alasan'] = $this->request->getPost('alasan');
        $model = new M_dropbox();
        $model2 = new dokumen_ok_model();

        $emailData = $model->getEmailData($data['uservendor']);
        if ($emailData) {
            $email = \Config\Services::email();

            $email->setTo($emailData->cp_email1);
            $email->setFrom('pud.tbn@newarmada.co.id', 'PT. Mekar Armada Jaya');
            $email->setBCC('pud.tbn@newarmada.co.id');

            $email->setSubject('Invoice Cancel');
            $formattedMessage = nl2br($data['pesan']);

            $no_invoice = $data['no_invoice'];
            $invoiceNumber = $data['invoice_id'];
            $companyName = $emailData->company_name;
            $picName = $emailData->cp_name;
            $picMaj = $this->ionAuth->user()->row()->cp_name;

            $message = "
                <html>
                    <head>
                        <style>
                            body {
                                font-family: Arial, sans-serif;
                                line-height: 1.6;
                            }
                            .header {
                                font-size: 20px;
                                font-weight: bold;
                            }
                            .content {
                                margin-top: 20px;
                            }
                            .footer {
                                margin-top: 40px;
                            }
                        </style>
                        <title>Invoice Cancel</title>
                    </head>
                    <body>
                        <div class='header'>
                            <p>Yth. Bapak/Ibu {$picName}</p>
                            <p>dari {$companyName},</p>
                        </div>
                        <div class='content'>
                            <p>Dengan ini kami informasikan bahwa Invoice: {$no_invoice} dengan nomor verifikasi: {$invoiceNumber} tidak dapat kami tindak lanjuti karena alasan berikut :</p>
                            <p>{$formattedMessage}</p>
                            <p>Demikian informasi ini kami sampaikan. Atas perhatian dan kerja samanya, kami ucapkan terima kasih.</p>
                        </div>
                        <div class='footer'>
                            <p>Salam Hormat,</p>
                            <p>{$picMaj}</p>
                            <p>PT. Mekar Armada Jaya</p>
                        </div>
                    </body>
                </html>
            ";

            $email->setMessage($message);

            if ($email->send()) {
                $data['email_status'] = 'Email berhasil dikirim';
                $notificationModel = new \App\Models\NotificationModel();
                $notificationData = [
                    'user_id'       => $emailData->id,
                    'header'        => 'Berkas Cancel',
                    'message'       => 'Invoice ' . $no_invoice,
                    'created_at'    => date('Y-m-d H:i:s')
                ];
                $notificationModel->insert($notificationData);
                $model2->updateOnHoldStatusV2($no_invoice, $data['alasan']);
                $M_curl = new M_curl();
                $gr = $model->getGR($data['invoice_id']);

                foreach ($gr as $row) {
                    $this->SAP_PARAMS['function'] = 'Z_QC';
                    $this->SAP_PARAMS['params'] = [
                        'RPT' => 'P_GR_RESTORE',
                        'P_BELNR' => $row['no_gr'],
                        'P_EBELP' => $row['no_item'],
                    ];
                    $sap = $M_curl->execute("POST", $this->SAP_PARAMS);
                }
                
                if (!empty($errors)) {
                    $data['response'] = "DELETE_PUD berhasil, tetapi beberapa GR_RESTORE gagal: " . implode(', ', $errors);
                } else {
                    $data['response'] = "Proses DELETE_PUD dan semua GR_RESTORE berhasil.";
                }
                
            } else {
                $data['email_status'] = 'Email gagal dikirim: ' . $email->printDebugger(['headers']);
            }
        } else {
            $data['email_status'] = 'Email tidak ditemukan';
        }

        echo json_encode($data);
    }

    public function unity_mgl()
    {
        $this->check_permission('Module.Create.InvoiceMgl');
        $postData = json_decode(file_get_contents('php://input'), true);
        $checkedData = $postData['checkedData'];

        $result = [];

        function cleanPrice($price) {
            $strippedString = preg_replace('/[^\d]/', '', $price);
            return floatval($strippedString) / 100;
        }

        foreach ($checkedData as $item) {
            $key = $item[5];

            if (!isset($result[$key])) {
                $item[7] = strval(floatval($item[7]));
                $result[$key] = $item;
            } else {
                $result[$key][7] = strval(floatval($result[$key][7]) + floatval($item[7]));
                $result[$key][8] = 'Rp ' . number_format((cleanPrice($result[$key][8]) + cleanPrice($item[8])), 2, ',', '.');
            }
        }
    
        $result = array_values($result);
        header('Content-Type: application/json');
        return $this->response->setJSON(['result' => $result]);
    }


    public function makeinvoice()
    {
        $data['current_user'] = $this->ionAuth->user()->row();
        date_default_timezone_set('Asia/Jakarta');
        $checkedData = json_decode($this->request->getPost('checkedData'), true);
        $checkDataVerif = json_decode($this->request->getPost('checkDataVerif'), true);
        $data['dppLain'] = floatval($this->request->getPost('dppLain'));
        $invoiceNumber = $this->request->getPost('invoiceNumber');
        $fakturNumber = $this->request->getPost('fakturNumber');
        $invoiceDate = date_create($this->request->getPost('invoiceDate'));
        $invoiceDateFormat = date_format($invoiceDate, 'd/m/Y');
        $totalAmount = floatval($this->request->getPost('totalAmount'));
        // $invoice_number = $this->request->getPost('invoiceNumber');
        $amountFormat = number_format((float) $totalAmount, 0, ',', '.');
        $model = new M_invoicing();
        $attemptModel = new M_attempt_verif();
        $invoicing_id = $model->get_running_id();

        $checkedDataDum = array(
            [
                "",
                "4515003600",
                "5000328990",
                "00040",
                "GR Part Mgl Deficit JAN",
                "58313-BZ010-00",
                "SHGA270D-O 45 2.60x145.0x1205.0",
                "200",
                "Rp 13.331.600,00",
                "01-01-2016",
            ],
            
        );

        $checkedDataReal = array(
            [
                "diskon" => "0",
                "dpp" => "2110000",
                "hargaSatuan" => "52750",
                "hargaTotal" => "2110000",
                "jumlahBarang" => "40",
                "nama" => "PLATE SUB-ASSY, FR SIDE MEMBER, RH",
                "ppn" => "232100",
                "ppnbm" => "0",
                "tarifPpnbm" => "0",
            ],
            [
                "diskon" => "0",
                "dpp" => "2110000",
                "hargaSatuan" => "52750",
                "hargaTotal" => "2110000",
                "jumlahBarang" => "40",
                "nama" => "PLATE SUB-ASSY, FR SIDE MEMBER, RH",
                "ppn" => "232100",
                "ppnbm" => "0",
                "tarifPpnbm" => "0",
            ],
        );

        $matchingResults = [];

        function formatRupiahToFloat($str) {
            $strippedString = preg_replace("/[^\d]/", "", $str);
            $floatValue = floatval($strippedString) / 100;
            $formattedValue = rtrim(rtrim(number_format($floatValue, 2, ',', '.'), '0'), ',');
            $finalValue = str_replace(['.', ','], '', $formattedValue);
            return $finalValue; 
        }

        function formatInt($str) {
            $strippedString = preg_replace("/[^\d]/", "", $str);
            $floatValue = floatval($strippedString);
            return strval($floatValue); 
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['pdfFile']) && isset($_FILES['invoiceFile'])) {
            $targetDir = 'uploads/pajak/';
            $targetDirInvoices = 'uploads/invoices/';
            $targetDirRev = 'uploads/pajak_rev/';

            $pic_faktur_1 = $data['current_user']->pic_faktur_1 ?? '';
            $pic_faktur_2 = $data['current_user']->pic_faktur_2 ?? '';
            $pic_faktur_3 = $data['current_user']->pic_faktur_3 ?? '';

            $pic_faktur_names = array_filter([$pic_faktur_1, $pic_faktur_2, $pic_faktur_3], function($name) {
                return !empty($name);
            });

            $names_argument = implode(' ', array_map('escapeshellarg', $pic_faktur_names));

            $targetFile = $targetDir . $data['current_user']->username . '_' . $invoicing_id . '_' . 'FAKTUR_PAJAK' . '.pdf';
            $targetFileInvoice = $targetDirInvoices . $data['current_user']->username . '_' . $invoicing_id . '_' . 'INVOICE' . '.pdf';

            $uploadOk = 1;
            $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
            $fileTypeInvoice = strtolower(pathinfo($targetFileInvoice, PATHINFO_EXTENSION));

            if (move_uploaded_file($_FILES['pdfFile']['tmp_name'], $targetFile) && move_uploaded_file($_FILES['invoiceFile']['tmp_name'], $targetFileInvoice)) {

                $xmlString = null;
                if (isset($_FILES['pdfFileRev']) && $_FILES['pdfFileRev']['error'] == UPLOAD_ERR_OK) {
                    $targetFileRev = $targetDirRev . $data['current_user']->username . '_' . $invoicing_id . '_' . 'FAKTUR_REV' . '.pdf';
                    move_uploaded_file($_FILES['pdfFileRev']['tmp_name'], $targetFileRev);

                    $commandRev = escapeshellcmd("python3 ../main.py " . escapeshellarg($targetFileRev));
                    exec($commandRev, $outputRev, $resultRev);

                    if (!empty($outputRev)) {
                        $json_outputRev = json_decode($outputRev[0], true);
                        if (isset($json_outputRev['output'])) {
                            $xmlString = file_get_contents($json_outputRev['output']);
                        }
                    }
                }

                if (is_null($xmlString)) {
                    $command = escapeshellcmd("python3 ../main.py " . escapeshellarg($targetFile));
                    exec($command, $output, $result);

                    if (!empty($output)) {
                        $json_output = json_decode($output[0], true);
                        if (isset($json_output['output'])) {
                            $xmlString = file_get_contents($json_output['output']);
                        }
                    }
                }

                $command2 = escapeshellcmd("python3 ../main.py " . escapeshellarg($targetFileInvoice));
                exec($command2, $output2, $result2);

                if (!empty($names_argument)) {
                    $command3 = escapeshellcmd("python3 ../main_name.py " . escapeshellarg($targetFile) . " " . $names_argument);
                    exec($command3, $output3, $result3);
                }

                if (!empty($xmlString) && !empty($output2) && !empty($output3)) {
                    $json_output2 = json_decode($output2[0], true);
                    $json_output3 = json_decode($output3[0], true);
                    if (isset($json_output['error']) && isset($json_output2['error'])) {
                        $data['response'] = "QR Code tidak ditemukan pada Faktur Pajak dan Invoice";
                        $data['message'] = false;
                        unlink($targetFile);
                        unlink($targetFileInvoice);
                        if (isset($targetFileRev)) {
                            unlink($targetFileRev);
                        }
                    } elseif (isset($json_output['error'])) {
                        $data['response'] = $json_output['error'] . ' Pada Faktur Pajak';
                        $data['message'] = false;
                        unlink($targetFile);
                        unlink($targetFileInvoice);
                        if (isset($targetFileRev)) {
                            unlink($targetFileRev);
                        }
                    } elseif (isset($json_output2['error'])) {
                        $data['response'] = $json_output2['error'] . ' Pada Invoice';
                        $data['message'] = false;
                        unlink($targetFile);
                        unlink($targetFileInvoice);
                        if (isset($targetFileRev)) {
                            unlink($targetFileRev);
                        }
                    } else {
                        $decoded_response = json_decode($json_output2['output'], true);
                        $data['id_qr'] = $decoded_response['id_qr'];
                        $data['total_invoice_payment'] = number_format($decoded_response['total_payment'], 0, ',', '.');
                        $data['no_invoice'] = $invoiceNumber;
                        $data['invoice_date'] = $invoiceDateFormat;
                        $data['no_faktur_invoice'] = $fakturNumber;

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

                        if ($data['dppLain'] == 1) {
                            $totalHarga = array_reduce($detailTransaksiData, function ($carry, $item) {
                                return $carry + formatInt($item['hargaTotal']);
                            }, 0);
                            $fakturFormat = number_format($totalHarga, 0, ',', '.');
                        } else {
                            $fakturFormat = number_format((float) $xml->jumlahDpp, 0, ',', '.');
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
                        $data['checkedData'] = $checkedData;
                        $data['checkDataVerif'] = $checkDataVerif;
                        $data['NomorInvoice'] = $data['no_invoice'];
                        $data['DetailTransaksi'] = $detailTransaksiData;
                        $data['NamaFaktur'] = $json_output3['output'];

                        $existingInvoice = $model->where('no_invoice', $data['no_invoice'])
                                                ->where('active', '')                         
                                                ->first();
                        $existingFaktur = $model->where('tax_number', $data['nomorFaktur'])
                                                ->where('active', '')                         
                                                ->first();

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

                        if (count($checkedData) !== count($detailTransaksiData)) {
                            $data['response'] = 'Jumlah data GR tidak sama';
                            $data['message'] = false;
                            echo json_encode($data);
                            unlink($targetFile);
                            unlink($targetFileInvoice);
                            if (isset($targetFileRev)) {
                                unlink($targetFileRev);
                            }
                            return;
                            
                        } else {
                            $allMatched = true;
                            foreach ($checkedData as $index => $dumItem) {
                                if (isset($detailTransaksiData[$index])) {
                                    $realItem = $detailTransaksiData[$index];
                    
                                    $valueReal = formatInt($realItem['hargaTotal']);
                                    $valueDum = formatRupiahToFloat($dumItem[8]);

                                    $alamat_faktur = normalizewords($data['alamatLawanTransaksi']);
                                    $alamat_maj = normalizewords('Jl. Mayjend Bambang Soegeng No.7 Mertoyudan, Mertoyudan Magelang Jawa Tengah');

                                    // if ($alamat_faktur !== $alamat_maj) {
                                    //     $data['response'] = 'Alamat lawan transaksi tidak cocok';
                                    //     $data['message'] = false;
                                    //     $allMatched = false;
                                    //     break;
                                    // }
                                    
                                    if ($data['npwpLawanTransaksi'] !== '011075934524000') {
                                        $data['response'] = 'Nomor NPWP lawan transaksi tidak cocok';
                                        $data['message'] = false;
                                        $allMatched = false;
                                        break;
                                    } elseif ($json_output3['output'] !== true) {
                                        $data['response'] = 'Nama yang Bertanda Tangan di Faktur Pajak Tidak Sesuai';
                                        $data['message'] = false;
                                        $allMatched = false;
                                        break;
                                    } elseif ($xml->fgPengganti != "1" && $data['invoice_date'] !== $data['tanggalFaktur']) {
                                        $data['response'] = 'Tanggal invoice tidak sama dengan tanggal faktur';
                                        $data['message'] = false;
                                        $allMatched = false;
                                        break;
                                    } elseif ($data['nomorFaktur'] !== $data['no_faktur_invoice']) {
                                        $data['response'] = 'Nomor faktur pajak yang terlampir di Invoice tidak sesuai dengan yang ada di Faktur pajak';
                                        $data['message'] = false;
                                        $allMatched = false;
                                        break;
                                    } elseif ($fakturFormat !== $amountFormat || $amountFormat !== $data['total_invoice_payment']) {
                                        // $attemptModel->updateAttemptCount($data['no_invoice']);
                                        // $errorCount = $attemptModel->getErrorCount($data['no_invoice']);
                                        $data['response'] = 'DPP faktur tidak sama dengan Total harga GR SAP';
                                        $data['message'] = false;
                                        // $data['error_count'] = $errorCount;
                                        $allMatched = false;
                                        break;
                                    } elseif ($dumItem[7] !== $realItem['jumlahBarang']) {
                                        // $attemptModel->updateAttemptCount($data['no_invoice']);
                                        // $errorCount = $attemptModel->getErrorCount($data['no_invoice']);
                                        $data['response'] = "Jumlah Barang/Jasa urutan ke - $index tidak sesuai";
                                        $data['message'] = false;
                                        // $data['error_count'] = $errorCount;
                                        $allMatched = false;
                                        break;
                                    } elseif ($valueDum !== $valueReal) {
                                        $data['response'] = "Harga total Barang/Jasa urutan ke - $index tidak sesuai";
                                        $data['message'] = false;
                                        $allMatched = false;
                                        break;
                                    } else {
                                        $matchingResults[] = [
                                            'index' => $index,
                                            'value' => $valueDum,
                                            'result' => 'Cocok'
                                        ];
                                    }
                                }
                            }
                        }
                
                        $data['result'] = $matchingResults;
                        
                        if ($allMatched) {
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
                    
                            // $model = new M_invoicing();
                            // $invoicing_id = $model->get_running_id();
                            $company_name = $data['current_user']->company_name;
                            $user_create = $data['current_user']->vendor_code;
                            $npwp = (string) $xml->npwpPenjual;
                            // $taxnumber = (string) $xml->nomorFaktur;
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
                                    "tax_number" => $data['nomorFaktur'], //$taxnumber,
                                    "tax_date" => $taxdate,
                                    "no_gr" => $iterate[2],
                                    "no_item" => $iterate[3],
                                    "no_po" => $iterate[1],
                                );
                                $model->insert_invoice($data_to_insert);
                            }
                            session()->setFlashdata("success", "This is success message");
                            $data['response'] = "Data berhasil terverifikasi";
                            $data['message'] = true;
                        } else {
                            unlink($targetFile);
                            unlink($targetFileInvoice);
                            if (isset($targetFileRev)) {
                                unlink($targetFileRev);
                            }
                        }
                    }
                } else {
                    $data['response'] = "Sistem tidak mendeteksi adanya QR Code";
                    $data['message'] = false;
                    unlink($targetFile);
                    unlink($targetFileInvoice);
                    if (isset($targetFileRev)) {
                        unlink($targetFileRev);
                    }
                }

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

    public function getreceive()
    {
        $belnr = $this->request->getPost('belnr');
        $ebelp = $this->request->getPost('ebelp');
        $ebeln = $this->request->getPost('ebeln');
        $model = new M_invoicing;
        $response = $model->reachstatusgr($belnr, $ebelp, $ebeln);
        $data['result'] = $response;
        echo json_encode($data);
    }

    public function receivingindex()
    {
        $this->check_permission('Module.View.Receivable');
        $model = new receiving_model;
        $data['title'] = 'Transaction';
        $data['ionAuth'] = $this->ionAuth;
        $data['current_user'] = $this->ionAuth->user()->row();
        $data['js']['footer'] = array(
            'datatables/jquery.dataTables.min',
            'datatables-bs4/js/dataTables.bootstrap4.min',
            'datatables-responsive/js/dataTables.responsive.min',
            'datatables-responsive/js/responsive.bootstrap4.min',
            'datatables/DataTables-2.0.1/js/dataTables',
            'moment/moment.min',
            'datatables/DataTables-2.0.1/js/dataTables.dateTime.min',
            'sweetalert2/sweetalert2.min',
            'toastr/toastr.min'
        );

        $data['css']['header'] = array(
            'datatables/DataTables-2.0.1/css/dataTables.dataTables',
            'datatables/DataTables-2.0.1/css/dataTables.dateTime.min',
            'daterangepicker/daterangepicker',
            'toastr/toastr.min',
            'sweetalert2-theme-bootstrap-4/bootstrap-4.min'
        );

        return $this->_render_page('receiving/received', $data);
    }

    public function receiving_json(){
        $data['dropbox_id'] = $this->request->getPost('dropbox_id');
        $model = new receiving_model();
        $data = $model->getReceiveSpecific($data['dropbox_id']);

        if (!empty($data)) {
            $response = $data;
        } else {
            $response = ['error' => 'Data tidak tersedia', 'status' => false];
        }
        return $this->response->setJSON(['data' => $response, 'status' => true]);
    }
    
    public function approvereceiving()
    {
        $dropbox_id = $this->request->getPost('dropbox_id');
        $status_receive = $this->request->getPost('status_receive');
        $user_receive = $this->ionAuth->user()->row()->nik_user;

        // return $this->response->setJSON(['dropbox_id' => $dropbox_id, 'status_receive' => $status_receive]);

        $model = new receiving_model(); 
        $model->updateStatusReceive($dropbox_id, $status_receive, $user_receive);
    }

    public function dokumen_okindex()
    {
        $model = new dokumen_ok_model;
        $data['title'] = 'Dokumen Ok';
        $data['getDokumen_Ok'] = $model->getdokumen_Ok();
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

        return $this->_render_page('Dokumen_Ok/Vdokumen_ok', $data);
    }

    public function updateDokOk()
    {
        $dropbox_id = $this->request->getPost('dropbox_id');
        $dok_ok_status = $this->request->getPost('dok_ok');
        $user_dok_ok = $this->ionAuth->user()->row()->nik_user;

        $model = new dokumen_ok_model();
        $model->updateStatusDokumentOk($dropbox_id, $dok_ok_status, $user_dok_ok);
    }

    public function dokumenok_index()
    {
        $this->check_permission('Module.View.DocumentOK');
        $data['title'] = 'Transaction';
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
            'sweetalert2/sweetalert2.min',
            'toastr/toastr.min'
        );

        $data['css']['header'] = array(
            'datatables/DataTables-2.0.1/css/dataTables.dataTables',
            'datatables/DataTables-2.0.1/css/dataTables.dateTime.min',
            'daterangepicker/daterangepicker',
            'toastr/toastr.min',
            'sweetalert2-theme-bootstrap-4/bootstrap-4.min'
        );

        $data['current_user'] = $this->ionAuth->user()->row();
        return $this->_render_page('Dokumen_Ok/dokumen_ok', $data);
    }

    public function queryspesific()
    {
        $model = new dokumen_ok_model();
        $data = $model->getDataSpecific();
        return $this->response->setJSON(['data' => $data]);
    }


    public function on_hold()
    {
        $this->check_permission('Module.View.Onhold');
        $data['title'] = 'Transaction';
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
            'sweetalert2/sweetalert2.min',
            'toastr/toastr.min'
        );

        $data['css']['header'] = array(
            'datatables/DataTables-2.0.1/css/dataTables.dataTables',
            'datatables/DataTables-2.0.1/css/dataTables.dateTime.min',
            'daterangepicker/daterangepicker',
            'toastr/toastr.min',
            'sweetalert2-theme-bootstrap-4/bootstrap-4.min'
        );

        $data['current_user'] = $this->ionAuth->user()->row();
        return $this->_render_page('Dokumen_Ok/on_hold', $data);
    }

    public function onhold_json()
    {
        $model = new dokumen_ok_model();
        $data = $model->getOnHoldSpesific();
        return $this->response->setJSON(['data' => $data]);
    }

    public function restore_onhold()
    {
        $data['dropbox_id'] = $this->request->getPost('dropbox_id');
        $model = new dokumen_ok_model();
        $model->restoreOnHold($data['dropbox_id']);

        echo json_encode($data);
    }


    public function onhold_email()
    {
        $data['dropbox_id'] = $this->request->getPost('dropbox_id');
        $data['uservendor'] = $this->request->getPost('uservendor');
        $data['invoice_id'] = $this->request->getPost('invoice_id');
        $data['pesan'] = $this->request->getPost('pesan');
        $model = new M_dropbox();
        $model2 = new dokumen_ok_model();
        $model2->updateOnHoldStatus($data['dropbox_id']);

        $emailData = $model->getEmailData($data['uservendor']);
        if ($emailData) {
            $email = \Config\Services::email();

            $email->setTo($emailData->cp_email1);
            $email->setFrom('pud.tbn@newarmada.co.id', 'PT. Mekar Armada Jaya');
            $email->setBCC('pud.tbn@newarmada.co.id');

            $email->setSubject('Berkas Tertahan');
            $formattedMessage = nl2br($data['pesan']);

            $dropboxId = $data['dropbox_id'];
            $invoiceNumber = $data['invoice_id'];
            $companyName = $emailData->company;
            $picName = $emailData->cp_name;

            // Template email
            $message = "
                <html>
                    <head>
                        <style>
                            body {
                                font-family: Arial, sans-serif;
                                line-height: 1.6;
                            }
                            .header {
                                font-size: 20px;
                                font-weight: bold;
                            }
                            .content {
                                margin-top: 20px;
                            }
                            .footer {
                                margin-top: 40px;
                            }
                        </style>
                        <title>Berkas Tertahan</title>
                    </head>
                    <body>
                        <div class='header'>
                            <p>Yth. Bapak/Ibu {$picName}</p>
                            <p>dari {$companyName},</p>
                        </div>
                        <div class='content'>
                            <p>Kami mohon penyerahan dokumen revisi nomor Dropbox: {$dropboxId} dengan nomor Invoice: {$invoiceNumber} rincian kesalahan sebagai berikut:</p>
                            <p>{$formattedMessage}</p>
                            <p>Apabila dokumen revisi tidak segera dikirimkan 3 hari setelah email ini dikirimkan, proses invoice akan dibatalkan dan harap melakukan proses ulang pada laman web. Terima kasih atas perhatian Anda yang cepat terhadap masalah ini.</p>
                        </div>
                        <div class='footer'>
                            <p>Salam Hormat,</p>
                            <p>PIC MAJ Vendor</p>
                            <p>PUD MAJ</p>
                        </div>
                    </body>
                </html>
            ";

            $email->setMessage($message);

            if ($email->send()) {
                $data['email_status'] = 'Email berhasil dikirim';
                $notificationModel = new \App\Models\NotificationModel();
                $notificationData = [
                    'user_id'       => $emailData->id,
                    'header'        => 'Berkas Tertahan',
                    'message'       => 'Dropbox ' . $dropboxId,
                    'created_at'    => date('Y-m-d H:i:s')
                ];
                $notificationModel->insert($notificationData);

            } else {
                $data['email_status'] = 'Email gagal dikirim: ' . $email->printDebugger(['headers']);
            }
        } else {
            $data['email_status'] = 'Email tidak ditemukan';
        }

        echo json_encode($data);
    }

    ////////////////////////////// onhold process //////////////////////////////

    public function onhold_email_v2()
    {
        $data['no_invoice'] = $this->request->getPost('no_invoice');
        $data['uservendor'] = $this->request->getPost('uservendor');
        $data['invoice_id'] = $this->request->getPost('invoice_id');
        $data['pesan'] = $this->request->getPost('pesan');
        $data['nomor_zinver'] = $this->request->getPost('input_zinver');
        $model = new M_dropbox();
        $model2 = new dokumen_ok_model();

        $emailData = $model->getEmailData($data['uservendor']);
        if ($emailData) {
            $email = \Config\Services::email();

            $email->setTo($emailData->cp_email1);
            $email->setFrom('pud.tbn@newarmada.co.id', 'PT. Mekar Armada Jaya');

            $email->setSubject('Berkas Tertahan');
            $formattedMessage = nl2br($data['pesan']);

            $no_invoice = $data['no_invoice'];
            $invoiceNumber = $data['invoice_id'];
            $companyName = $emailData->company_name;
            $picName = $emailData->cp_name;
            $picMaj = $this->ionAuth->user()->row()->cp_name;

            $message = "
                <html>
                    <head>
                        <style>
                            body {
                                font-family: Arial, sans-serif;
                                line-height: 1.6;
                            }
                            .header {
                                font-size: 20px;
                                font-weight: bold;
                            }
                            .content {
                                margin-top: 20px;
                            }
                            .footer {
                                margin-top: 40px;
                            }
                        </style>
                        <title>Berkas Tertahan</title>
                    </head>
                    <body>
                        <div class='header'>
                            <p>Yth. Bapak/Ibu {$picName}</p>
                            <p>dari {$companyName},</p>
                        </div>
                        <div class='content'>
                            <p>Kami mohon merevisi Invoice : {$no_invoice} dengan nomor verifikasi: {$invoiceNumber} dikarenakan :</p>
                            <p>{$formattedMessage}</p>
                            <p>Dimohon untuk dapat segera mengirimkan berkas revisi invoice selambat-lambatnya 2 hari setelah diterimanya email ini. Apabila berkas revisi tidak diterima dalam batas waktu tersebut, maka dengan sangat menyesal kami akan membatalkan proses penagihan atas nomor invoice {$no_invoice}.</p>
                            <p>Demikian informasi ini kami sampaikan. Atas perhatian dan kerja samanya, kami ucapkan terima kasih.</p>
                        </div>
                        <div class='footer'>
                            <p>Salam Hormat,</p>
                            <p>{$picMaj}</p>
                            <p>PT. Mekar Armada Jaya</p>
                        </div>
                    </body>
                </html>
            ";

            $email->setMessage($message);

            if ($email->send()) {
                $data['email_status'] = 'Email berhasil dikirim';
                $notificationModel = new \App\Models\NotificationModel();
                $notificationData = [
                    'user_id'       => $emailData->id,
                    'header'        => 'Berkas Tertahan',
                    'message'       => 'Invoice ' . $no_invoice,
                    'created_at'    => date('Y-m-d H:i:s')
                ];
                $notificationModel->insert($notificationData);
                $model2->updateOnHoldStatus($data['no_invoice']);
            } else {
                $data['email_status'] = 'Email gagal dikirim: ' . $email->printDebugger(['headers']);
            }
        } else {
            $data['email_status'] = 'Email tidak ditemukan';
        }
        echo json_encode($data);
    }

    ////////////////////////////// onhold process //////////////////////////////

    public function del_onhold()
    {
        $data['dropbox_id'] = $this->request->getPost('dropbox_id');
        $data['uservendor'] = $this->request->getPost('uservendor');
        $data['no_invoice'] = $this->request->getPost('no_invoice');
        $model = new M_dropbox();
        $model2 = new dokumen_ok_model();
        $model2->del_onhold($data['dropbox_id']);

        $emailData = $model->getEmailData($data['uservendor']);
        if ($emailData) {
            $email = \Config\Services::email();

            $email->setTo($emailData->cp_email1);
            $email->setFrom('ahmadnurfahmi.anf2@gmail.com', 'PT. Mekar Armada Jaya');
            $email->setSubject('Berkas Dibatalkan');
            
            $dropboxId = $data['dropbox_id'];
            $invoiceNumber = $data['no_invoice'];
            $companyName = $emailData->company;
            $picName = $emailData->cp_name;

            // Template email
            $message = "
                <html>
                    <head>
                        <style>
                            body {
                                font-family: Arial, sans-serif;
                                line-height: 1.6;
                            }
                            .header {
                                font-size: 20px;
                                font-weight: bold;
                            }
                            .content {
                                margin-top: 20px;
                            }
                            .footer {
                                margin-top: 40px;
                            }
                        </style>
                        <title>Berkas Dibatalkan</title>
                    </head>
                    <body>
                        <div class='header'>
                            <p>Yth. Bapak/Ibu {$picName}</p>
                            <p>dari {$companyName},</p>
                        </div>
                        <div class='content'>
                            <p>Kami mohon maaf sebesar-besarnya karena data dengan nomor Dropbox {$dropboxId} dan nomor Invoice {$invoiceNumber} harus kami batalkan dan hapus. Hal ini disebabkan oleh berkas tersebut tidak ditindaklanjuti dalam jangka waktu yang telah ditentukan.</p>
                            <p>Kami sangat menghargai perhatian dan pengertian Anda dalam hal ini. Apabila Anda masih memerlukan bantuan lebih lanjut, jangan ragu untuk menghubungi kami kembali.</p>
                        </div>
                        <div class='footer'>
                            <p>Salam Hormat,</p>
                            <p>PIC MAJ Vendor</p>
                            <p>PUD MAJ</p>
                        </div>
                    </body>
                </html>
            ";

            $email->setMessage($message);

            if ($email->send()) {
                $data['email_status'] = 'Email berhasil dikirim';
            } else {
                $data['email_status'] = 'Email gagal dikirim: ' . $email->printDebugger(['headers']);
            }
        } else {
            $data['email_status'] = 'Email tidak ditemukan';
        }

        echo json_encode($data);
    }

    public function printdok_ok_count()
    {
        date_default_timezone_set('Asia/Jakarta');
        $no_invoice = $this->request->getGet('no_invoice');
        $no_dropbox = $this->request->getGet('no_dropbox');
        $model = new InvoiceAfterDokOk_model();
        $model->updatePrint_Dok_Ok($no_invoice, $no_dropbox, 'y');
        $data['title'] = 'Print Zinver';
        $zinver = new M_dropbox();
        $data['current_user'] = $this->ionAuth->user()->row();
        $data['dropbox_id'] = $no_invoice;
        $data['result'] = $zinver->getDataforZinver($no_invoice, $no_dropbox);
        $data['current_user'] = $this->ionAuth->user()->row();

        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('fahmi');
        $pdf->SetTitle('Zinver Print');
        $pdf->SetSubject('Zinver');
        $pdf->SetKeywords('TCPDF, PDF, zinver, mekararmadajaya');

        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 001', PDF_HEADER_STRING, array(0,64,255), array(0,64,128));
        $pdf->setFooterData(array(0,64,0), array(0,64,128));
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf->SetMargins(5, 5, 5,true);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        $pdf->setFontSubsetting(true);
        $pdf->SetFont('dejavusans', '', 12, '', true);

        foreach ($data['result'] as $entry) {
            $pdf->AddPage();
  
            $style = array(
                'border' => 0,
                'padding' => 1,
                'fgcolor' => array(0,0,0),
                'bgcolor' => false
            );

            $html = view('Dokumen_Ok/zinver_print', ['entry' => $entry]);
            $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
            $pdf->write2DBarcode($entry['no_invoice'], 'QRCODE,H', 180, 5, 30, 30, $style, 'N'); 
            $styleLine = array('width' => 0.25, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
            $pdf->Line(0, $pdf->GetY() + 75, 250, $pdf->GetY() + 75, $styleLine);
        }

        $this->response->setContentType('application/pdf');
        $pdf->Output('Zinver.pdf', 'I');
    }


    public function printdok_ok()
    {
        date_default_timezone_set('Asia/Jakarta');
        $no_invoice = $this->request->getPost('no_invoice');
        $no_dropbox = $this->request->getPost('no_dropbox');
        $tanggal_jatuh_tempo = $this->request->getPost('tanggal_jatuh_tempo');
        $progress = $this->request->getPost('progress');
        $qcd = $this->request->getPost('qcd');
        $nik= $this->ionAuth->user()->row()->nik_user;
        
        $deadline = date('Ymd', strtotime($tanggal_jatuh_tempo));
        $zinver = new M_dropbox();

        $data['result'] = $zinver->getDataforZinver($no_invoice, $no_dropbox);
        $M_curl = new M_curl();
        $this->SAP_PARAMS['function'] = 'Z_PUD';

        foreach ($data['result'] as $entry) {
            $tax_date_parts = explode('/', $entry['tax_date']);
            $formatted_tax_date = $tax_date_parts[2] . $tax_date_parts[1] . $tax_date_parts[0];
            $params = [
                'RPT'           => 'RPT_ZINVER',
                'ACTION'        => 'IN_PUD',
                'P_EBELN'       => $entry['no_po'],
                'P_ZXBLNR'      => $entry['no_invoice'],
                'P_ZRMWWR'      => $entry['total_payment'],
                'P_ZPUD_ACPT'   => date('Ymd', strtotime($entry['date_approve'])),
                'P_ZTGLINV'     => $formatted_tax_date,
                'P_ZJTMP'       => $deadline,
                'P_PROGRESS'    => $progress . '%',
                'P_QCD'         => $qcd,
                'P_ZNIK_PUD'    => $nik,
            ];
    
            $this->SAP_PARAMS['params'] = $params;
            $sap = $M_curl->execute("POST", $this->SAP_PARAMS);
            if ($sap['success']) {
                $data['data'] = isset($sap['data']['NO_ZINVER']) ? $sap['data']['NO_ZINVER'] : [];
                $data['insert'] = $zinver->updateDataZinver($entry['invoicing_id'], $data['data'], $deadline, $progress, $qcd, $nik);
            } else {
                $data['error'] = isset($sap['MESSAGE']) ? $sap['MESSAGE'] : 'Terjadi kesalahan dalam permintaan SAP.';
            }
        }

        echo json_encode($data);

        // $data['current_user'] = $this->ionAuth->user()->row();

        // // echo json_encode($data);

        // $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        // // $barcodeData = $data['result'][0]['dropbox_id'];
        // // $pdf->setBarcodeData($barcodeData);
        // $pdf->SetCreator(PDF_CREATOR);
        // $pdf->SetAuthor('fahmi');
        // $pdf->SetTitle('Zinver Print');
        // $pdf->SetSubject('Zinver');
        // $pdf->SetKeywords('TCPDF, PDF, zinver, mekararmadajaya');

        // $pdf->setPrintHeader(false);
        // $pdf->setPrintFooter(false);

        // $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 001', PDF_HEADER_STRING, array(0,64,255), array(0,64,128));
        // $pdf->setFooterData(array(0,64,0), array(0,64,128));
        // $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        // $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
        // $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        // $pdf->SetMargins(1, 1, 1,true);
        // $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        // $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        
        // $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        // $pdf->setFontSubsetting(true);
        // $pdf->SetFont('dejavusans', '', 11, '', true);

        // // Loop through each entry in the result array
        // foreach ($data['result'] as $entry) {
        //     $pdf->AddPage(); // Add a new page for each entry
        //     $html = view('Dokumen_Ok/zinver_print', ['entry' => $entry]); // Pass the entry data to the view
        //     $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
        // }

        // $this->response->setContentType('application/pdf');
        // $pdf->Output('Zinver.pdf', 'I');
        //................................//

        // $notification = 'Data berhasil diperbarui';
        // session()->setFlashdata('notification', $notification);
        // return redirect()->back();
    }

    public function register_in()
    {
        $this->check_permission('Module.View.RegisterIn');
        $data['title'] = 'Transaction';
        $data['ionAuth'] = $this->ionAuth;
        $data['js']['footer'] = array(
            'datatables/jquery.dataTables.min',
            'datatables-bs4/js/dataTables.bootstrap4.min',
            'datatables-responsive/js/dataTables.responsive.min',
            'datatables-responsive/js/responsive.bootstrap4.min',
            'datatables/DataTables-2.0.1/js/dataTables',
            'moment/moment.min',
            'datatables/DataTables-2.0.1/js/dataTables.dateTime.min',
            'sweetalert2/sweetalert2.min',
            'toastr/toastr.min',
            'select2/js/select2.full.min',
        );

        $data['css']['header'] = array(
            'datatables/DataTables-2.0.1/css/dataTables.dataTables',
            'datatables/DataTables-2.0.1/css/dataTables.dateTime.min',
            'daterangepicker/daterangepicker',
            'toastr/toastr.min',
            'sweetalert2-theme-bootstrap-4/bootstrap-4.min',
            'select2/css/select2.min',
            'select2-bootstrap4-theme/select2-bootstrap4.min',
        );

        $data['current_user'] = $this->ionAuth->user()->row();
        return $this->_render_page('Miro/register_in', $data);
    }

    public function registerin_json()
    {
        $data['no_invoice'] = $this->request->getPost('no_invoice');
        $data['zinver'] = $this->request->getPost('input_zinver');
        $data['vendor'] = $this->request->getPost('vendor');
        $model = new dokumen_ok_model();
        $result = $model->getRegisterIn($data['no_invoice'], $data['zinver'], $data['vendor']);

        if (!empty($result)) {
            $response = $result;
        } else {
            $response = ['error' => 'Data tidak tersedia', 'status' => false];
        }
        
        return $this->response->setJSON(['data' => $response, 'status' => true]);
    }

    public function registerin_approve()
    {
        $no_invoice = $this->request->getPost('no_invoice');
        $status_register_in = $this->request->getPost('status_register_in');
        $data['nomor_zinver'] = $this->request->getPost('no_zinver');
        $data['nik'] = $this->ionAuth->user()->row()->nik_user;;

        $model = new M_miro();
        $model->updateRegisterIn($no_invoice, $status_register_in, $data['nik']);
    }

    public function miroindex()
    {
        $this->check_permission('Module.View.Miro');
        $data['title'] = 'Transaction';
        $data['ionAuth'] = $this->ionAuth;
        $data['js']['footer'] = array(
            'datatables/jquery.dataTables.min',
            'datatables-bs4/js/dataTables.bootstrap4.min',
            'datatables-responsive/js/dataTables.responsive.min',
            'datatables-responsive/js/responsive.bootstrap4.min',
            'datatables/DataTables-2.0.1/js/dataTables',
            'moment/moment.min',
            'datatables/DataTables-2.0.1/js/dataTables.dateTime.min',
            'sweetalert2/sweetalert2.min',
            'toastr/toastr.min',
            'select2/js/select2.full.min',
        );

        $data['css']['header'] = array(
            'datatables/DataTables-2.0.1/css/dataTables.dataTables',
            'datatables/DataTables-2.0.1/css/dataTables.dateTime.min',
            'daterangepicker/daterangepicker',
            'toastr/toastr.min',
            'sweetalert2-theme-bootstrap-4/bootstrap-4.min',
            'select2/css/select2.min',
            'select2-bootstrap4-theme/select2-bootstrap4.min',
        );

        $data['current_user'] = $this->ionAuth->user()->row();
        return $this->_render_page('Miro/miro', $data);
    }

    public function miro_approve()
    {
        $no_invoice = $this->request->getPost('no_invoice');
        $invoicing_id = $this->request->getPost('invoicing_id');
        $status_miro = $this->request->getPost('status_miro');
        $nik = $this->ionAuth->user()->row()->nik_user;

        $model = new M_miro();
        $model->updateMiro($no_invoice, $invoicing_id, $status_miro, $nik);
    }
    
    public function miro_json()
    {
        $data['vendor'] = $this->request->getPost('vendor');
        $model = new dokumen_ok_model();
        $result = $model->getMiroSpecific($data['vendor']);

        if (!empty($result)) {
            $response = $result;
        } else {
            $response = ['error' => 'Data tidak tersedia', 'status' => false];
        }
        
        return $this->response->setJSON(['data' => $response, 'status' => true]);
    }

    public function paid()
    {
        $this->check_permission('Module.View.Paid');
        $data['title'] = 'Transaction';
        $data['ionAuth'] = $this->ionAuth;
        $data['js']['footer'] = array(
            'datatables/jquery.dataTables.min',
            'datatables-bs4/js/dataTables.bootstrap4.min',
            'datatables-responsive/js/dataTables.responsive.min',
            'datatables-responsive/js/responsive.bootstrap4.min',
            'datatables/DataTables-2.0.1/js/dataTables',
            'moment/moment.min',
            'datatables/DataTables-2.0.1/js/dataTables.dateTime.min',
            'sweetalert2/sweetalert2.min',
            'toastr/toastr.min',
            'select2/js/select2.full.min',
        );

        $data['css']['header'] = array(
            'datatables/DataTables-2.0.1/css/dataTables.dataTables',
            'datatables/DataTables-2.0.1/css/dataTables.dateTime.min',
            'daterangepicker/daterangepicker',
            'toastr/toastr.min',
            'sweetalert2-theme-bootstrap-4/bootstrap-4.min',
            'select2/css/select2.min',
            'select2-bootstrap4-theme/select2-bootstrap4.min',
        );

        $data['current_user'] = $this->ionAuth->user()->row();
        return $this->_render_page('Miro/paid', $data);
    }


    public function paid_approve()
    {
        $no_invoice = $this->request->getPost('no_invoice');
        $invoicing_id = $this->request->getPost('invoicing_id');
        $status_paid = $this->request->getPost('status_paid');
        $paidDate = $this->request->getPost('paidDate');
        $nik = $this->ionAuth->user()->row()->nik_user;;

        $model = new M_miro();
        $model->updatePaid($no_invoice, $invoicing_id, $status_paid, $paidDate, $nik);
    }
    
    public function paid_json()
    {
        $data['vendor'] = $this->request->getPost('vendor');
        $model = new dokumen_ok_model();
        $result = $model->getPaidSpecific($data['vendor']);

        if (!empty($result)) {
            $response = $result;
        } else {
            $response = ['error' => 'Data tidak tersedia', 'status' => false];
        }
        
        return $this->response->setJSON(['data' => $response, 'status' => true]);
    }

    public function InvoiceAfterDokOkindex()
    {
        $this->check_permission('Module.View.PrintInvoice');
        $data['title'] = 'Transaction';
        $data['ionAuth'] = $this->ionAuth;
        $data['current_user'] = $this->ionAuth->user()->row();
        $data['css']['header'] = array(
            'datatables/DataTables-2.0.1/css/dataTables.dataTables',
            'datatables/DataTables-2.0.1/css/dataTables.dateTime.min',
            'daterangepicker/daterangepicker',
            'toastr/toastr.min',
            'sweetalert2-theme-bootstrap-4/bootstrap-4.min',
        );
        $data['js']['footer'] = array(
            'datatables/DataTables-2.0.1/js/dataTables',
            'moment/moment.min',
            'datatables/DataTables-2.0.1/js/dataTables.dateTime.min',
            'daterangepicker/daterangepicker.min',
            'toastr/toastr.min',
            'sweetalert2/sweetalert2.min',
        );
        return $this->_render_page('Dokumen_Ok/print_invoice_verification', $data);
    }

    public function invoiceAfterDokOk_json()
    {
        $model = new InvoiceAfterDokOk_model;
        $data['getInvoiceAfterDokOk'] = $model->getInvoiceAfterDokOk();
        echo json_encode($data);
    }

    public function delete_pud()
    {
        $data['invoicing_id'] = $this->request->getPost('invoicing_id');
        $data['nomor_zinver'] = $this->request->getPost('nozinver');
        $data['alasan'] = $this->request->getPost('formAlasan');
        $nik = $this->ionAuth->user()->row()->nik_user;
        $zinver = new M_dropbox();
        $M_curl = new M_curl();
        $this->SAP_PARAMS['function'] = 'Z_PUD';
        $this->SAP_PARAMS['params'] = [
            'RPT' => 'RPT_ZINVER',
            'ACTION' => 'DELETE_PUD',
            'P_NOZINVER' => $data['nomor_zinver'],
            'P_ALASAN' => $data['alasan'],
            'P_ZNIK_PUD' => $nik,
        ];

        $sap = $M_curl->execute("POST", $this->SAP_PARAMS);
        if ($sap['success']) {
            $data['insert'] = $zinver->deleteDataZinver($data['invoicing_id']);
        } else {
            $data['error'] = isset($sap['MESSAGE']) ? $sap['MESSAGE'] : 'Terjadi kesalahan dalam permintaan SAP.';
        }

        echo json_encode($data);
    }


    public function out_pud()
    {
        $data['invoicing_id'] = $this->request->getPost('invoicing_id');
        $data['nomor_zinver'] = $this->request->getPost('nozinver');
        // $data['nik'] = $this->request->getPost('formNik');
        $nik = $this->ionAuth->user()->row()->nik_user;

        $zinver = new M_dropbox();
        $M_curl = new M_curl();
        $this->SAP_PARAMS['function'] = 'Z_PUD';
        $this->SAP_PARAMS['params'] = [
            'RPT' => 'RPT_ZINVER',
            'ACTION' => 'OUT_PUD',
            'P_NOZINVER' => $data['nomor_zinver'],
            'P_ZNIK_PUD' => $nik,
        ];

        $sap = $M_curl->execute("POST", $this->SAP_PARAMS);
        if ($sap['success']) {
            $data['insert'] = $zinver->OutPudDataZinver($data['invoicing_id'],  $nik);
        } else {
            $data['error'] = isset($sap['MESSAGE']) ? $sap['MESSAGE'] : 'Terjadi kesalahan dalam permintaan SAP.';
        }
        
        echo json_encode($data);
    }

    public function zinver()
    {
        $dropbox_id = 'DB2405081967';
    }
}
