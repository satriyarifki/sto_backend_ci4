<?php

namespace App\Controllers;

use CodeIgniter\Controller;

use \App\Models\Temp;
use \IonAuth\Libraries\IonAuth;
use Config\Menu;
use App\Models\M_curl;
use App\Models\M_dropbox;
use App\Models\M_auth;
use App\Models\M_news;

class Dashboard extends BaseController
{
    protected $ionAuth;
    protected $data = [];

    public function __construct()
    {
        $model = new IonAuth();
        $this->ionAuth = $model;
    }

    public function sup_index()
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
            'toastr/toastr.min',

        );

        $data['css']['header'] = array(
            'datatables/DataTables-2.0.1/css/dataTables.dataTables',
            'datatables/DataTables-2.0.1/css/dataTables.dateTime.min',
            'daterangepicker/daterangepicker',
            'toastr/toastr.min',
        );

        $data['current_user'] = $this->ionAuth->user()->row();
        return $this->_render_page('dashboard/supplier/index', $data);
    }

    public function cli_index()
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
            'toastr/toastr.min',
            'select2/js/select2.full.min',

        );

        $data['css']['header'] = array(
            'datatables/DataTables-2.0.1/css/dataTables.dataTables',
            'datatables/DataTables-2.0.1/css/dataTables.dateTime.min',
            'daterangepicker/daterangepicker',
            'toastr/toastr.min',
            'select2/css/select2.min',
            'select2-bootstrap4-theme/select2-bootstrap4.min',
        );

        $data['current_user'] = $this->ionAuth->user()->row();
        return $this->_render_page('dashboard/client/index', $data);
    }

    public function admin_index()
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
            'toastr/toastr.min',
            'select2/js/select2.full.min',

        );

        $data['css']['header'] = array(
            'datatables/DataTables-2.0.1/css/dataTables.dataTables',
            'datatables/DataTables-2.0.1/css/dataTables.dateTime.min',
            'daterangepicker/daterangepicker',
            'toastr/toastr.min',
            'select2/css/select2.min',
            'select2-bootstrap4-theme/select2-bootstrap4.min',
        );

        $model = new M_dropbox();
        $data['resultInv'] = $model->getAll();
        $data['resultMiro'] = $model->getAll(null, 'miro');
        $data['resultOnHold'] = $model->getAll(null, null, 'onhold');
        $data['current_user'] = $this->ionAuth->user()->row();      
        return $this->_render_page('dashboard/admin/index', $data);
    }

    public function vendor_id()
    {
        $search = $this->request->getGet('search');
        $vendorModel = new M_auth();
        $vendors['result'] = $vendorModel->getvendor($search);
        return $this->response->setJSON($vendors);
    }

    public function dash_real()
    {
        $data['current_user'] = $this->ionAuth->user()->row();
        $requestData = json_decode(file_get_contents('php://input'), true);

        if ($requestData != NULL) {
            $formatted_date_low = $requestData['startDate'] != "" ? date('Ymd', strtotime($requestData['startDate'])) : NULL;
            $formatted_date_high = $requestData['endDate'] != "" ? date('Ymd', strtotime($requestData['endDate'])) : NULL;
        } else {
            $formatted_date_low = NULL;
            $formatted_date_high = NULL;
        }

        $data['date_low'] = $formatted_date_low;
        $data['date_high'] = $formatted_date_high;

        $M_curl = new M_curl();
        $this->SAP_PARAMS['function'] = 'Z_QC';
        $this->SAP_PARAMS['params'] = [
            'RPT' => 'P_GR_ALL',
            'CNMA' => $data['current_user']->vendor_code,
            'P_DATE_LOW' => $formatted_date_low,
            'P_DATE_HIGH' => $formatted_date_high,
        ];

        $sap = $M_curl->execute("POST", $this->SAP_PARAMS);
        if ($sap['success']) {
            $data['data_sap'] = isset($sap['data']['ZGRUSER']) ? $sap['data']['ZGRUSER'] : [];
            $jumlah_GR = 0;
            $jumlah_non_GR = 0;
            $jumlah_process = 0;

            foreach ($data['data_sap'] as $item) {
                if ($item['VBELN_ST'] == '') {
                    $jumlah_non_GR++;
                }
            }

            foreach ($data['data_sap'] as $row) {
                if ($row['VBELN_ST'] == 'GR') {
                    $jumlah_process++;
                }
            }

            $data['jumlah_ALL'] = count($data['data_sap']);
            $data['jumlah_Process'] = $jumlah_process;
            $data['jumlah_GR'] = 0;
            $data['jumlah_non_GR'] = $jumlah_non_GR;
        } else {
            $data['error'] = isset($sap['message']) ? $sap['message'] : 'Terjadi kesalahan dalam permintaan SAP.';
            $data['jumlah_ALL'] = 0;
            $data['jumlah_GR'] = 0;
            $data['jumlah_non_GR'] = 0;
        }

        echo json_encode($data);
    }

    public function dash_admin()
    {
        $data['current_user'] = $this->ionAuth->user()->row();
        $requestData = json_decode(file_get_contents('php://input'), true);

        if ($requestData != NULL) {
            $formatted_date_low = $requestData['startDate'] != "" ? date('Ymd', strtotime($requestData['startDate'])) : NULL;
            $formatted_date_high = $requestData['endDate'] != "" ? date('Ymd', strtotime($requestData['endDate'])) : NULL;
        } else {
            $formatted_date_low = NULL;
            $formatted_date_high = NULL;
        }

        $data['date_low'] = $formatted_date_low;
        $data['date_high'] = $formatted_date_high;

        $M_curl = new M_curl();
        $this->SAP_PARAMS['function'] = 'Z_QC';
        $this->SAP_PARAMS['params'] = [
            'RPT' => 'P_GR_ALL',
            'CNMA' => $requestData['vendor'],
            'P_DATE_LOW' => $formatted_date_low,
            'P_DATE_HIGH' => $formatted_date_high,
        ];

        $sap = $M_curl->execute("POST", $this->SAP_PARAMS);
        if ($sap['success']) {
            $data['data_sap'] = isset($sap['data']['ZGRUSER']) ? $sap['data']['ZGRUSER'] : [];
            $jumlah_GR = 0;
            $jumlah_non_GR = 0;
            $jumlah_process = 0;

            foreach ($data['data_sap'] as $item) {
                if ($item['VBELN_ST'] == '') {
                    $jumlah_non_GR++;
                }
            }

            foreach ($data['data_sap'] as $row) {
                if ($row['VBELN_ST'] == 'GR') {
                    $jumlah_process++;
                }
            }

            $data['jumlah_ALL'] = count($data['data_sap']);
            $data['jumlah_Process'] = $jumlah_process;
            $data['jumlah_GR'] = 0;
            $data['jumlah_non_GR'] = $jumlah_non_GR;
        } else {
            $data['error'] = isset($sap['message']) ? $sap['message'] : 'Terjadi kesalahan dalam permintaan SAP.';
            $data['jumlah_ALL'] = 0;
            $data['jumlah_GR'] = 0;
            $data['jumlah_non_GR'] = 0;
        }

        echo json_encode($data);
    }

    public function card_value()
    {
        $data['current_user'] = $this->ionAuth->user()->row();
        $requestData = json_decode(file_get_contents('php://input'), true);

        $formatted_date_low = $formatted_date_high = NULL;

        if ($requestData != NULL) {
            $formatted_date_low = !empty($requestData['startDate']) ? date('Ymd', strtotime($requestData['startDate'])) : NULL;
            $formatted_date_high = !empty($requestData['endDate']) ? date('Ymd', strtotime($requestData['endDate'])) : NULL;
        }

        $data['date_low'] = $formatted_date_low;
        $data['date_high'] = $formatted_date_high;

        $M_curl = new M_curl();
        $this->SAP_PARAMS['function'] = 'Z_QC';

        $this->SAP_PARAMS['params'] = [
            'RPT' => 'P_GR_ALL',
            'CNMA' => $data['current_user']->vendor_code,
            'P_DATE_LOW' => $formatted_date_low,
            'P_DATE_HIGH' => $formatted_date_high,
        ];

        $sap = $M_curl->execute("POST", $this->SAP_PARAMS);
        if ($sap['success']) {
            $data['data_sap'] = isset($sap['data']['ZGRUSER']) ? $sap['data']['ZGRUSER'] : [];

            $data['result'] = [];

            if ($requestData['indikasi'] == 'UNVERIFIED') {
                foreach ($data['data_sap'] as $item) {
                    if ($item['VBELN_ST'] == '') {
                        $data['result'][] = $item;
                        $data['table_header'] = 'Data GR Unverified';
                    }
                }
            } else if ($requestData['indikasi'] == 'PROCESS'){
                foreach ($data['data_sap'] as $item) {
                    if ($item['VBELN_ST'] == 'GR') {
                        $data['result'][] = $item;
                        $data['table_header'] = 'Data GR Process';
                    }
                }
            } else if ($requestData['indikasi'] == 'RECEIVED'){
                $data['result'] = [];
                $data['table_header'] = 'Data GR Received';
            } else {
                $data['result'] = $data['data_sap'];
                $data['table_header'] = 'All Data Document';
            }

        } else {
            $data['error'] = isset($sap['message']) ? $sap['message'] : 'Terjadi kesalahan dalam permintaan SAP.';
        }
        echo json_encode($data); 
    }


    public function card_value_maj()
    {
        $data['current_user'] = $this->ionAuth->user()->row();
        $requestData = json_decode(file_get_contents('php://input'), true);

        $formatted_date_low = $formatted_date_high = NULL;

        if ($requestData != NULL) {
            $formatted_date_low = !empty($requestData['startDate']) ? date('Ymd', strtotime($requestData['startDate'])) : NULL;
            $formatted_date_high = !empty($requestData['endDate']) ? date('Ymd', strtotime($requestData['endDate'])) : NULL;
        }

        $data['date_low'] = $formatted_date_low;
        $data['date_high'] = $formatted_date_high;

        $M_curl = new M_curl();
        $this->SAP_PARAMS['function'] = 'Z_QC';

        $this->SAP_PARAMS['params'] = [
            'RPT' => 'P_GR_ALL',
            'CNMA' => $requestData['vendor'],
            'P_DATE_LOW' => $formatted_date_low,
            'P_DATE_HIGH' => $formatted_date_high,
        ];

        $sap = $M_curl->execute("POST", $this->SAP_PARAMS);
        if ($sap['success']) {
            $data['data_sap'] = isset($sap['data']['ZGRUSER']) ? $sap['data']['ZGRUSER'] : [];

            $data['result'] = [];

            if ($requestData['indikasi'] == 'UNVERIFIED') {
                foreach ($data['data_sap'] as $item) {
                    if ($item['VBELN_ST'] == '') {
                        $data['result'][] = $item;
                        $data['table_header'] = 'Data GR Unverified';
                    }
                }
            } else if ($requestData['indikasi'] == 'PROCESS'){
                foreach ($data['data_sap'] as $item) {
                    if ($item['VBELN_ST'] == 'GR') {
                        $data['result'][] = $item;
                        $data['table_header'] = 'Data GR Process';
                    }
                }
            } else if ($requestData['indikasi'] == 'RECEIVED'){
                $data['result'] = [];
                $data['table_header'] = 'Data GR Received';
            } else {
                $data['result'] = $data['data_sap'];
                $data['table_header'] = 'All Data Document';
            }

        } else {
            $data['error'] = isset($sap['message']) ? $sap['message'] : 'Terjadi kesalahan dalam permintaan SAP.';
        }
        echo json_encode($data); 
    }

    public function announcement()
    {
        $permissions = $this->ionAuth->user()->row()->permission;
        $permissions = unserialize($permissions);

        foreach ($permissions as $permission) {
            if ($permission === 'Module.View.DashboarAdmin') {
                $data['redirect_url'] = 'dashboard/admin';
                break;
            } elseif ($permission === 'Module.View.DashboarVendor') {
                $data['redirect_url'] = 'dashboard/vendor';
                break;
            } elseif ($permission === 'Module.View.DashboarMaj') {
                $data['redirect_url'] = 'dashboard/maj';
                break;
            }
        }
        $newsModel = new M_news();
        $news = $newsModel->orderBy('id', 'DESC')->findAll();

        $data['news'] = $news;
        echo view('dashboard/announcement', $data);
    }

    public function announcement_detail($id)
    {
        $newsModel = new M_news();
        $data['announcement'] = $newsModel->find($id);

        $permissions = $this->ionAuth->user()->row()->permission;
        $permissions = unserialize($permissions);

        foreach ($permissions as $permission) {
            if ($permission === 'Module.View.DashboarAdmin') {
                $data['redirect_url'] = 'dashboard/admin';
                break;
            } elseif ($permission === 'Module.View.DashboarVendor') {
                $data['redirect_url'] = 'dashboard/vendor';
                break;
            } elseif ($permission === 'Module.View.DashboarMaj') {
                $data['redirect_url'] = 'dashboard/maj';
                break;
            }
        }

        if (!$data['announcement']) {
            return redirect()->to('/news')->with('error', 'Announcement tidak ditemukan!');
        }
        echo view('dashboard/announcement_detail', $data);
    }

    public function upload_announcement()
    {
        echo view('dashboard/form_announcement');
    }

    public function upload_data()
    {
        $validation = \Config\Services::validation();

        $validation->setRules([
            'title' => 'required|max_length[255]',
            'content' => 'required',
            'image' => 'uploaded[image]|is_image[image]|max_size[image,2048]'
        ]);
    
        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->with('toastr_error', $validation->listErrors())->withInput();
        }
    
        $title = $this->request->getPost('title');
        $content = $this->request->getPost('content');
    
        $image = $this->request->getFile('image');
        $imagePath = null;
        if ($image && $image->isValid() && !$image->hasMoved()) {
            $imagePath = $image->getRandomName();
            $image->move(ROOTPATH . 'public/uploads/news', $imagePath);
        }
    
        $data = [
            'title' => $title,
            'content' => $content,
            'path_image' => $imagePath
        ];
    
        $newsModel = new M_news();
    
        if ($newsModel->insert($data)) {
            return redirect()->to(base_url('dashboard/news'))->with('toastr_success', 'Data berhasil diupload.');
        } else {
            return redirect()->back()->with('toastr_error', 'Terjadi kesalahan saat menyimpan data.')->withInput();
        }
    }
}
