<?php

namespace App\Controllers\Andon;
use App\Controllers\BaseController;
use App\Models\M_dropbox;

class Rundown extends BaseController
{
    public function index()
    {
        $data['title'] = 'Rundown';
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

        return view('andon/rundown', $data);
    }

    public function indexMiro()
    {
        $data['title'] = 'Rundown Miro';
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

        return view('andon/rundownMiro', $data);
    }

    public function getInvoices()
    {
        $model = new M_dropbox();
        $result = $model->getDropboxNoMiro();
        return $this->response->setJSON($result);
    }
    public function getDataMiro()
    {
        $model = new M_dropbox();
        $result = $model->getDropboxMiro();
        return $this->response->setJSON($result);
    }
}