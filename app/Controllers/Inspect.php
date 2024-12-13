<?php

namespace App\Controllers;
use \IonAuth\Libraries\IonAuth;
use CodeIgniter\Controller;
use App\Models\M_invoicing;
use App\Models\receiving_model;
use SimpleSoftwareIO\QrCode\Generator;
use App\Models\M_curl;
use App\Models\ExcelModel;
use Zxing\QrReader;
use Spatie\PdfToImage\Pdf;
use Imagick;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Libraries\MY_TCPDF AS TCPDF;

class Inspect extends BaseController 
{
    protected $ionAuth;
    public function __construct()
    {
        $model = new IonAuth();
        $this->ionAuth = $model;
    }

    public function receivingindex(){

        $model = new receiving_model;
        $data['title'] = 'Receiving';
        $data['getReceiving'] = $model->getReceiving();
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
        // return $this->_render_page('inspect/receiving', $data);
        var_dump($data);
    }

    public function approvereceiving($id, $status = null)
    {
        if ($status === null) {
            $status = 'y'; 
        } else {
            $status = '';
        }

        $model = new receiving_model();
        $model->updateStatusReceive($id, $status);

        $notification = 'Data berhasil diperbarui';
        session()->setFlashdata('notification', $notification);
        return redirect()->back();
    }
}