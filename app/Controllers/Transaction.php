<?php

namespace App\Controllers;

use \IonAuth\Libraries\IonAuth;
use CodeIgniter\Controller;
use App\Models\M_invoicing;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Libraries\MY_TCPDF AS TCPDF;
use App\Models\M_dropbox;
use App\Models\M_registerdrop;
use App\Models\M_curl;


class Transaction extends BaseController
{
    protected $ionAuth;
    public function __construct()
    {
        $model = new IonAuth();
        $this->ionAuth = $model;
    }

    //////////////////////////////////////////// Process PUD Scan Achievement ////////////////////////////////////////////

    public function scan_pud()
    {
        $this->check_permission('Module.View.ScanPud');
        $data['title'] = 'Scan PUD';
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

        return $this->_render_page('scan/pud', $data);
    }

    public function process_scan_pud()
    {
        $barcode = $this->request->getJSON()->barcode;
        date_default_timezone_set('Asia/Jakarta');
        $model = new M_dropbox();

        $result = $model->DropboxGet($barcode);

        if ($result) {
            $dropboxId = $result[0]->dropbox_id;
            $statusArchieve = $result[0]->status_archieve_pud; 

            if ($statusArchieve == 'N') {
                $model->archieve_update_pud($dropboxId);
                return $this->response->setJSON([
                    'message' => true,
                    'response' => 'Registrasi ' . $dropboxId . ' berhasil dilakukan!',
                    'data' => $result
                ]);
            } else {
                return $this->response->setJSON([
                    'message' => false,
                    'response' => 'Registrasi gagal: ' . $dropboxId . ' sudah terdaftar sebelumnya.',
                ]);
            }
        } else {
            return $this->response->setJSON([
                'message' => false,
                'response' => 'Data tidak ditemukan'
            ]);
        }
    }

    //////////////////////////////////////////// Process PUD Scan Achievement ////////////////////////////////////////////

    public function scan_finance()
    {
        $this->check_permission('Module.View.ScanFinance');
        $data['title'] = 'Scan Finance';
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

        return $this->_render_page('scan/finance', $data);
    }

    public function process_scan_finance()
    {
        $barcode = $this->request->getJSON()->barcode;
        date_default_timezone_set('Asia/Jakarta');
        $model = new M_dropbox();

        $result = $model->getInvoice($barcode);

        if ($result) {
            $noInvoice = $result[0]->no_invoice;
            $statusArchieve = $result[0]->status_archieve_finance; 

            if ($statusArchieve == 'N') {
                $model->archieve_update_finance($noInvoice);
                return $this->response->setJSON([
                    'message' => true,
                    'response' => 'Registrasi ' . $noInvoice . ' berhasil dilakukan!',
                ]);
            } else {
                return $this->response->setJSON([
                    'message' => false,
                    'response' => 'Registrasi gagal: ' . $noInvoice . ' sudah terdaftar sebelumnya.',
                ]);
            }
        } else {
            return $this->response->setJSON([
                'message' => false,
                'response' => 'Data tidak ditemukan'
            ]);
        }
    }

    public function pending_dropbox_index()
    {
        $this->check_permission('Module.View.PendingDropbox');
        $data['title'] = 'Approve Pending Dropbox';
        $data['ionAuth'] = $this->ionAuth;
        $data['current_user'] = $this->ionAuth->user()->row();
        $data['css']['header'] = array(
            'datatables/DataTables-2.0.1/css/dataTables.dataTables',
            'datatables/DataTables-2.0.1/css/dataTables.dateTime.min',
            'daterangepicker/daterangepicker',
            'toastr/toastr.min',
            'sweetalert2-theme-bootstrap-4/bootstrap-4.min'
        );
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

        return $this->_render_page('dropbox/pending_approve', $data);
    }

    public function waiting_dropbox_index()
    {
        $this->check_permission('Module.View.WaitingDropbox');
        $data['title'] = 'Waiting Dropbox';
        $data['ionAuth'] = $this->ionAuth;
        $data['current_user'] = $this->ionAuth->user()->row();
        $data['css']['header'] = array(
            'datatables/DataTables-2.0.1/css/dataTables.dataTables',
            'datatables/DataTables-2.0.1/css/dataTables.dateTime.min',
            'daterangepicker/daterangepicker',
            'toastr/toastr.min',
            'sweetalert2-theme-bootstrap-4/bootstrap-4.min'
        );
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

        return $this->_render_page('dropbox/waiting_approve', $data);
    }

    public function cancel_verification()
    {
        $this->check_permission('Module.View.CancelInvoice');
        $data['title'] = 'Cancel Verification';
        $data['ionAuth'] = $this->ionAuth;
        $data['current_user'] = $this->ionAuth->user()->row();
        $data['css']['header'] = array(
            'datatables/DataTables-2.0.1/css/dataTables.dataTables',
            'datatables/DataTables-2.0.1/css/dataTables.dateTime.min',
            'daterangepicker/daterangepicker',
            'toastr/toastr.min',
            'sweetalert2-theme-bootstrap-4/bootstrap-4.min'
        );
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

        return $this->_render_page('invoicing/verify_transaction/cancel_invoice', $data);
    }

    public function get_invoice_unreg()
    {
        $model = new M_registerdrop();
        $userVendor = $this->ionAuth->user()->row()->vendor_code;
        $data = $model->getDataUnreg($userVendor);
        return $this->response->setJSON(['data' => $data]);
    }

    public function cancel_verify_json()
    {
        $data['no_invoice'] = $this->request->getPost('no_invoice');
        $data['invoicing_id'] = $this->request->getPost('invoicing_id');

        $model = new M_registerdrop();
        $data['result'] = $model->getGRActive($data['no_invoice'], $data['invoicing_id']);

        $M_curl = new M_curl();

        foreach ($data['result'] as $row) {
            $this->SAP_PARAMS['function'] = 'Z_QC';
            $this->SAP_PARAMS['params'] = [
                'RPT' => 'P_GR_RESTORE',
                'P_BELNR' => $row['no_gr'],
                'P_EBELP' => $row['no_item'],
            ];

            $sap = $M_curl->execute("POST", $this->SAP_PARAMS);
            $model->updateActiveField($row['no_gr'], $row['no_item']);
        }

        $response = [
            'status' => 'completed',
            'message' => 'Proses telah selesai diproses.',
        ];

        echo json_encode($response);


    }
}