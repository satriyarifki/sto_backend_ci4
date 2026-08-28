<?php

namespace App\Controllers;

use CodeIgniter\Controller;

use \IonAuth\Libraries\IonAuth;
use \App\Models\M_sto;
use \App\Models\M_recap;
use \App\Models\M_news;
use App\Libraries\MY_TCPDF AS TCPDF;
use OpenSpout\Reader\Common\Creator\ReaderFactory;
use OpenSpout\Reader\XLSX\Options;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Sto extends BaseController
{
    protected $ionAuth;
    protected $data = [];

    public function __construct()
    {
        $model = new IonAuth();
        $this->ionAuth = $model;
    }

    public function index()
    {
        $data['title'] = 'STO DOCUMENTATION';
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
        return view('sto/index', $data);
    }

    public function prep()
    {
        $data['title'] = 'STO Preparation';
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
        );        
        return view('sto/prep', $data);
    }


    public function master_area()
    {
        $search = $this->request->getGet('search');
        $model = new M_sto();
        $data['result'] = $model->getDataArea($search);
        return $this->response->setJSON($data);
    }

    public function master_address()
    {
        $search = $this->request->getGet('search');
        $part = $this->request->getGet('part');
        $model = new M_sto();
        $data['result'] = $model->getDataAddress($search, $part);
        return $this->response->setJSON($data);
    }

    public function master_job_number()
    {
        $search = $this->request->getGet('search');
        $part = $this->request->getGet('part');
        $model = new M_sto();
        $data['result'] = $model->getDataJobNumber($search, $part);
        return $this->response->setJSON($data);
    }

    public function master_part_number()
    {
        $search = $this->request->getGet('search');
        $area = $this->request->getGet('area');
        $model = new M_sto();
        $data['result'] = $model->getDataPartNumberTrial($search, $area);
        return $this->response->setJSON($data);
    }
    public function master_part_job_number()
    {
        $search = $this->request->getGet('search');
        $area = $this->request->getGet('area');
        $model = new M_sto();
        $data['result'] = $model->getDataPartJobNumber($search, $area);
        return $this->response->setJSON($data);
    }

    public function master_part_desc()
    {
        $search = $this->request->getGet('search');
        $part = $this->request->getGet('part');
        $model = new M_sto();
        $data['result'] = $model->getDataPartDesc($search, $part);
        return $this->response->setJSON($data);
    }

    public function master_type()
    {
        $search = $this->request->getGet('search');
        $address = $this->request->getGet('address');
        $model = new M_sto();
        $data['result'] = $model->getDataType($search, $address);
        return $this->response->setJSON($data);
    }

    public function part_number_flutter()
    {
        $model = new M_sto();
        $raw = $model->partNumberFlutter();

        $partNumbers = array_column($raw, 'part_number');

        return $this->response->setJSON($partNumbers);
    }

    public function getPartDetailByNumber()
    {
        $part_number    = $this->request->getPost('part_number');
        $area           = $this->request->getPost('area');
        $model          = new M_sto();
        $data           = $model->getDetailByPartNumber($part_number, $area);
        return $this->response->setJSON($data);
    }

    public function store_sto()
    {
        date_default_timezone_set('Asia/Jakarta');
        $request = $this->request;

        $area   = $request->getPost('area');
        $nik    = $request->getPost('nik');
        $no_tag = $request->getPost('no_tag');

        $file = $request->getFile('evidence');

        if ($file->isValid() && !$file->hasMoved()) {
            $allowedExtensions = ['jpg', 'jpeg'];
            $extension = $file->getExtension();

            $newFileName = str_replace(' ', '', $nik) . "_" . $no_tag . '.' . $extension;
            $file->move('uploads/evidence/', $newFileName);
        } else {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Gagal mengupload evidence.'
            ]);
        }

        $stoModel = new M_sto();
        $stoModel->store_doc([
            'area'     => $area,
            'nik'      => $nik,
            'no_tag'   => $no_tag,
            'path_file' => $newFileName,
            'create_date' => date('Y-m-d H:i:s')
        ]);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Data berhasil disimpan.'
        ]);
    }

    public function report()
    {
        $data['title'] = 'STO REPORT';
        $data['ionAuth'] = $this->ionAuth;
        
        return view('sto/report', $data);
    }

    public function report_process()
    {
        $data['noTag'] = $this->request->getPost('no_tag');

        $model = new M_sto();

        $result = $model->get_all($data['noTag']);

        if (!empty($result)) {
            $response = $result;
        } else {
            $response = ['error' => 'Data tidak tersedia', 'status' => false];
        }
        
        return $this->response->setJSON(['data' => $response, 'status' => true]);
    }

    public function store_data_sto()
    {
        date_default_timezone_set('Asia/Jakarta');
        $area = $this->request->getPost('area');
        $customer = $this->request->getPost('customer');
        $job_number = $this->request->getPost('job_number');
        $part_number = $this->request->getPost('part_number');
        $part_desc = $this->request->getPost('part_desc');
        $type = $this->request->getPost('type');
        $total_tag = (int)$this->request->getPost('total_tag');
        if (empty($customer) || empty($area) || empty($job_number) || empty($part_number) || empty($part_desc) || empty($type) || $total_tag <= 0) {
            return $this->response->setJSON([
                'error' => true,
                'status' => 'error',
                'message' => 'Data harus lengkap dan Total TAG minimal 1'
            ])->setStatusCode(400);
        }

        $model = new M_recap();
        $bufferModel = new M_sto();
        $id_tags = [];

        for ($i = 0; $i < $total_tag; $i++) {
            $id_tag = $bufferModel->generatedID();

            $data = [
                'id_tag'      => $id_tag,
                'area'        => $area,
                'customer'     => $customer,
                'job_number'  => $job_number,
                'part_number' => $part_number,
                'material_description' => $part_desc,
                'type'        => $type,
                'created_at'  => date('Y-m-d H:i:s')
            ];

            $model->insert($data);
            $id_tags[] = $id_tag;
        }

        return $this->response->setJSON([
            'message' => 'Data berhasil disimpan',
            'status'  => 'success',
            'id_tags' => $id_tags
        ]);
    }


    public function store_data_sto_mobile()
    {
        date_default_timezone_set('Asia/Jakarta');
        $area = $this->request->getPost('area');
        $customer = $this->request->getPost('customer');
        $job_number = $this->request->getPost('jobNumber');
        $part_number = $this->request->getPost('partNumber');
        $part_desc = $this->request->getPost('partDesc');
        $type = $this->request->getPost('type');

        $model = new M_recap();
        $bufferModel = new M_sto();

        $id_tag = $bufferModel->generatedID();

        $data = [
            'id_tag'      => $id_tag,
            'area'        => $area,
            'customer'     => $customer,
            'job_number'  => $job_number,
            'part_number' => $part_number,
            'material_description' => $part_desc,
            'type'        => $type,
            'created_at'  => date('Y-m-d H:i:s')
        ];

        $model->insert($data);

        return $this->response->setJSON([
            'message' => 'Data berhasil disimpan',
            'status'  => 'success',
            'id_tags' => $id_tag
        ]);
    }


    public function get_data_sto()
    {
        date_default_timezone_set('Asia/Jakarta');
        $barcode = $this->request->getGet('barcode');

        $model = new M_sto();
        $model_kanban = new M_news();

        if (substr($barcode, 0, 1) == 'A') {
            $data = $model->getDataSto($barcode);
        } else {
            $data = $model_kanban->getDataKanban($barcode);
        }

        if ($data) {
            return $this->response->setJSON($data);
        } else {
            return $this->response->setJSON([
                'error'   => true,
                'message' => 'Data tidak ditemukan'
            ])->setStatusCode(404);
        }
    }

    public function get_data_nik()
    {
        date_default_timezone_set('Asia/Jakarta');
        $nik = $this->request->getPost('nik');
        $group = $this->request->getPost('group');

        $model = new M_sto();
        $data = $model->getDataNik($nik, $group);

        if ($data) {
            return $this->response->setJSON($data);
        } else {
            return $this->response->setJSON([
                'error'   => true,
                'message' => 'Data tidak ditemukan'
            ])->setStatusCode(404);
        }
    }

    public function update_data_sto()
    {
        date_default_timezone_set('Asia/Jakarta');
        $tag_sto = $this->request->getPost('tag_sto');
        $qty = $this->request->getPost('qty');
        $group = $this->request->getPost('group');
        $nik = $this->request->getPost('nik');
        $area = $this->request->getPost('area');

        $model_insert = new M_recap();
        $model = new M_sto();
        $model_kanban = new M_news();

        if (substr($tag_sto, 0, 1) == 'A') {
            $update = $model->updateQtyByTagSto($tag_sto, $qty, $group, $nik);

            if ($update === 'filled') {
                return $this->response->setJSON([
                    'status'  => false,
                    'message' => 'Data sudah terisi, tidak dapat diperbarui!',
                ], 400);
            } elseif ($update === false) {
                return $this->response->setJSON([
                    'status'  => false,
                    'message' => 'Data gagal diperbarui, mungkin tag_sto tidak ditemukan.',
                ], 404);
            } else {
                return $this->response->setJSON([
                    'status'  => true,
                    'message' => 'Data berhasil diperbarui!',
                ], 200);
            }
        } else {
            $data_kanban = $model_kanban->getDataKanban($tag_sto);
            if (!$data_kanban) {
                return $this->response->setJSON([
                    'status'  => false,
                    'message' => 'ID tidak terdaftar pada kanban.'
                ], 404);
            }

            $data_sto = $model->getDataSto($tag_sto);

            if (!$data_sto) {
                $insertData = [
                    'id_tag'               => $data_kanban['id_tag'],
                    'job_number'           => $data_kanban['job_number'],
                    'part_number'          => $data_kanban['part_number'],
                    'area'                 => $area,
                    'material_description' => $data_kanban['material_description'],
                    'qty_a'                => $group == 'A' ? $qty : 0,
                    'qty_b'                => $group == 'B' ? $qty : 0,
                    'nik_a'                => $group == 'A' ? $nik : null,
                    'nik_b'                => $group == 'B' ? $nik : null,
                    'updated_a'            => $group == 'A' ? date('Y-m-d H:i:s') : null,
                    'updated_b'            => $group == 'B' ? date('Y-m-d H:i:s') : null,
                    'created_at'           => date('Y-m-d H:i:s')
                ];

                $model->insertDataSto($insertData);
                return $this->response->setJSON([
                    'status'  => true,
                    'message' => 'Data berhasil ditambahkan ke STO.',
                ], 201);
            } else {
                $update = $model->updateQtyByTagSto($tag_sto, $qty, $group, $nik);

                if ($update === 'filled') {
                    return $this->response->setJSON([
                        'status'  => false,
                        'message' => 'Data sudah terisi, tidak dapat diperbarui!',
                    ], 400);
                } elseif ($update === false) {
                    return $this->response->setJSON([
                        'status'  => false,
                        'message' => 'Data gagal diperbarui, mungkin tag_sto tidak ditemukan.',
                    ], 404);
                } else {
                    return $this->response->setJSON([
                        'status'  => true,
                        'message' => 'Data berhasil diperbarui!',
                    ], 200);
                }
            }
        }
    }


    public function rev_qty()
    {
        $id_tag = $this->request->getPost('id_tag');
        $qty    = $this->request->getPost('qty');
        $nik    = $this->request->getPost('nik'); 
        $group  = $this->request->getPost('group');
        $model  = new M_sto();
        $update = $model->revQtyByTagSto($id_tag, $qty, $group, $nik);

        if ($update === false) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Data gagal diperbarui, mungkin tag_sto tidak ditemukan.',
            ], 404);
        } else {
            return $this->response->setJSON([
                'status'  => true,
                'message' => 'Data berhasil diperbarui!',
            ], 200);
        }
    }

    public function pdf_tag_sto()
    {
        date_default_timezone_set('Asia/Jakarta');
        $data['area'] = $this->request->getGet('area');
        $data['customer'] = $this->request->getGet('customer');
        $data['job_number'] = $this->request->getGet('job_number');
        $data['part_number'] = $this->request->getGet('part_number');
        $data['part_desc'] = $this->request->getGet('part_desc');
        $data['type'] = $this->request->getGet('type');
        $id_tags = $this->request->getGet('id_tags');

        if (empty($id_tags)) {
            throw new \Exception("ID Tag tidak ditemukan.");
        }
    
        $id_tags_array = explode(',', $id_tags);

        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('fahmi');
        $pdf->SetTitle($data['part_number'] . ' | ' . $data['type']. ' | ' .$data['customer']);
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
        $pdf->setPrintFooter(false);

        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        $pdf->setFontSubsetting(true);
        $pdf->SetFont('dejavusans', '', 11, '', true);

        foreach ($id_tags_array as $id_tag) {
            $data['id_tag'] = $id_tag;
            $pdf->setBarcodeData($id_tag);
            $pdf->AddPage();
            $html = view('sto/tag_sto', $data);
    
            $pdf->writeHTML($html, true, false, true, false, '');
        }

        $this->response->setContentType('application/pdf');
        $pdf->Output($data['part_number'] . ' | ' . $data['type']. ' | ' .$data['customer'].'.pdf', 'I');
    }

    public function InsertData()
    {
        ini_set('memory_limit', '512M');
        $filePath = 'public/assets/file/Master_NonGPART_STO_12_Apr.xlsx';

        if (!file_exists($filePath)) {
            echo json_encode(['status' => 'error', 'message' => 'File tidak ditemukan.']);
            return;
        }

        try {
            $options = new Options();
            $reader = ReaderFactory::createFromFile($filePath, $options);

            $reader->open($filePath);

            $result = [];
            $rowIndex = 1;

            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $row) {
                    if ($rowIndex > 1) { // Melewati header
                        $cells = $row->toArray();

                        $result[] = [
                            'area' => $cells[5] ?? null,
                            'job_number' => $cells[1] ?? null,
                            'part_number' => $cells[0] ?? null,
                            'material_description' => $cells[2] ?? null,
                            'type' => $cells[4] ?? null,
                            'status_part' => $cells[3] ?? null,
                            'customer' => $cells[6] ?? null,
                        ];
                    }
                    $rowIndex++;
                }
            }

            $reader->close();

            if (!empty($result)) {
                $model = new M_sto();
                $insertResult = $model->insertDataPartNumber($result);

                if ($insertResult) {
                    echo json_encode(['status' => 'success', 'message' => 'Data berhasil dimasukkan.', 'data' => $result]);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan data ke database.', 'data' => $result]);
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Tidak ada data yang dapat disimpan.']);
            }

        } catch (\Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    // NEW EXCEL STORE DATA STO
    // public function store_data_sto_from_excel()
    // {
    //     date_default_timezone_set('Asia/Jakarta');

    //     $file = $this->request->getFile('file');

    //     if (!$file || !$file->isValid()) {
    //         return $this->response->setJSON([
    //             'status' => 'error',
    //             'message' => 'File Excel tidak valid'
    //         ])->setStatusCode(400);
    //     }

    //     $ext = $file->getClientExtension();
    //     if (!in_array($ext, ['xls', 'xlsx'])) {
    //         return $this->response->setJSON([
    //             'status' => 'error',
    //             'message' => 'Format file harus Excel (.xls / .xlsx)'
    //         ])->setStatusCode(400);
    //     }

    //     $spreadsheet = IOFactory::load($file->getTempName());
    //     $sheet = $spreadsheet->getActiveSheet();
    //     $rows = $sheet->toArray();

    //     // remove header
    //     unset($rows[0]);

    //     $model       = new M_recap();
    //     $bufferModel = new M_sto();

    //     $inserted = 0;
    //     $errors   = [];
    //     $id_tags  = [];

    //     foreach ($rows as $index => $row) {
    //         [
    //             $area,
    //             $job_number,
    //             $part_number,
    //             $total_tag
    //         ] = $row;

    //         if (
    //             empty($area) || empty($job_number) ||
    //             empty($part_number) ||
    //             (int)$total_tag <= 0
    //         ) {
    //             $errors[] = "Row " . ($index + 1) . " data tidak lengkap";
    //             continue;
    //         }

    //         for ($i = 0; $i < (int)$total_tag; $i++) {
    //             $id_tag = $bufferModel->generatedID();

    //             $model->insert([
    //                 'id_tag' => $id_tag,
    //                 'area' => $area,
    //                 'job_number' => $job_number,
    //                 'part_number' => $part_number,
    //                 'created_at' => date('Y-m-d H:i:s')
    //             ]);

    //             $id_tags[] = $id_tag;
    //             $inserted++;
    //         }
    //     }

    //     return $this->response->setJSON([
    //         'status' => 'success',
    //         'message' => 'Import Excel selesai',
    //         'total_inserted' => $inserted,
    //         'failed_rows' => $errors,
    //         'id_tags' => $id_tags
    //     ]);
    // }

    public function store_data_sto_from_excel()
    {
        date_default_timezone_set('Asia/Jakarta');

        
        $file = $this->request->getFile('file');
        if (!$file || !$file->isValid()) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Invalid file'
            ])->setStatusCode(400);
        }

        $spreadsheet = IOFactory::load($file->getTempName());
        $rows = $spreadsheet->getActiveSheet()->toArray();

        unset($rows[0]); // remove header

        $stoModel   = new M_recap();
        $tagModel   = new M_sto();

        $inserted = 0;
        $errors   = [];
        $generatedIdTags = [];
        // var_dump($rows);
        foreach ($rows as $index => $row) {
            [$area, $part_number, $job_number, $total_tag] = $row;
            
            if ( empty($area) || (empty($part_number) && empty($job_number)) || (int)$total_tag <= 0
            ) {
                $errors[] = "Row ".($index+1)." incomplete";
                continue;
            }
            
            // var_dump($master);
            // 🔍 Get master part
            $master = $tagModel->findByAreaPartOrJob(
                trim($area),
                trim($part_number),
                trim($job_number)
            );
            
            if (!$master) {
                $errors[] = "Row ".($index+1)." master data not found";
                continue;
            }
                
                // 🔁 Generate TAG(s)
            for ($i = 0; $i < (int)$total_tag; $i++) {
                
                $id_tag = $tagModel->generatedID();
                
                $stoModel->insert([
                    'id_tag' => $id_tag,
                    'area' => $master['area'],
                    'job_number' => $master['job_number'],
                    'part_number' => $master['part_number'],
                    'material_description' => $master['material_description'],
                    'type' => $master['type'],
                    'customer' => $master['customer'].' '.$master['plant'],
                    'created_at' => date('Y-m-d H:i:s')
                ]);
                $generatedIdTags[] = $id_tag;
                $inserted++;
            }
        }
        
        
        return $this->response->setJSON([
            'status' => 'success',
            'total_inserted' => $inserted,
            'id_tags' => $generatedIdTags,
            'failed_rows' => $errors
        ]);
    }

    public function download_sto_template()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header
        $headers = [
            'area',
            // 'customer',
            'part_number',
            'job_number',
            // 'part_desc',
            // 'type',
            'total_tag'
        ];

        $sheet->fromArray($headers, null, 'A1');

        // Example row
        $example = [
            'IFPD',
            // 'ADM',
            '51311-BZ020-00',
            'NZ-076',
            // 'Bolt 10mm',
            // 'RAW',
            3
        ];
        $sheet->fromArray($example, null, 'A2');

        // Styling header
        $sheet->getStyle('A1:G1')->getFont()->setBold(true);
        $sheet->getColumnDimension('A')->setWidth(15);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(18);
        $sheet->getColumnDimension('E')->setWidth(30);
        $sheet->getColumnDimension('F')->setWidth(15);
        $sheet->getColumnDimension('G')->setWidth(12);

        $filename = 'STO_Import_Template.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'.$filename.'"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }


    public function pdf_tag_sto_batch()
    {
        ob_end_clean(); // VERY IMPORTANT

        $id_tags = $this->request->getGet('id_tags');
        if (empty($id_tags)) {
            throw new \Exception('ID tags not found');
        }

        $id_tags_array = explode(',', $id_tags);

        $stoModel = new M_sto();

        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('fahmi');
        // $pdf->SetTitle($data['part_number'] . ' | ' . $data['type']. ' | ' .$data['customer']);
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
        $pdf->setPrintFooter(false);

        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        $pdf->setFontSubsetting(true);
        $pdf->SetFont('dejavusans', '', 11, '', true);

        foreach ($id_tags_array as $id_tag) {

            $data = $stoModel->getDataSto($id_tag);
            if (!$data) continue;

            // 🔥 set barcode BEFORE AddPage
            $pdf->setBarcodeData($id_tag);

            $pdf->AddPage();

            $html = view('sto/tag_sto_batch', $data);
            $pdf->writeHTML($html, true, false, true, false, '');
        }

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setBody($pdf->Output('STO_TAG.pdf', 'S'));
    }

}
