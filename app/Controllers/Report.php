<?php

namespace App\Controllers;

use App\Models\M_report;
use App\Models\M_dropbox;
use \IonAuth\Libraries\IonAuth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Report extends BaseController
{
    protected $ionAuth;
    public function __construct()
    {
        $model = new IonAuth();
        $this->ionAuth = $model;
    }

    public function index()
    {
        $this->check_permission('Module.View.ReportData');
        $data['title'] = 'Report';
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
        );
        $data['permissions'] = $data['current_user']->permission;
        return $this->_render_page('report/finalReport', $data);
    }

    public function report_json()
    {
        $input = $this->request->getJSON(true);

        $dropbox = isset($input['dropbox']) ? $input['dropbox'] : null;
        $invoice = isset($input['invoice']) ? $input['invoice'] : null;
        $startDateDbp = isset($input['startDateDbp']) ? $input['startDateDbp'] : null;
        $endDateDbp = isset($input['endDateDbp']) ? $input['endDateDbp'] : null;
        $miro = isset($input['miro']) ? $input['miro'] : null;
        $paid = isset($input['paid']) ? $input['paid'] : null;
        $out_pud = isset($input['out_pud']) ? $input['out_pud'] : null;

        $model = new M_report();

        $results = $model->getReportData($dropbox, $invoice, $startDateDbp, $endDateDbp, $miro, $paid, $out_pud);

        if ($results) {
            return $this->response->setJSON(['result' => $results]);
        } else {
            return $this->response->setJSON(['error' => 'Data tidak ditemukan']);
        }
    }

    public function arsip_pdf()
    {
        $this->check_permission('Module.View.ArsipPdf');
        $data['title'] = 'Report';
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

        return $this->_render_page('report/arsip_pdf', $data);
    }

    public function arsip_pdf_json()
    {
        date_default_timezone_set('Asia/Jakarta');
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
        
        $data['Tanggal Awal'] = $formatted_date_low;
        $data['Tanggal Akhir'] = $formatted_date_high;
        $data['Invoice'] = $invoice_number;
        $data['Vendor'] = $requestData['vendor'];
        $data['Category'] = $requestData['category'];

        $model = new M_report();
        if ($requestData['category'] == "1") {
            $query = $model->getArsipData($formatted_date_low, $formatted_date_high, $invoice_number, $requestData['vendor']);
        } elseif ($requestData['category'] == "2") {
            $query = $model->getArsipData2($formatted_date_low, $formatted_date_high, $invoice_number, $requestData['vendor']);
        } elseif ($requestData['category'] == "3"){
            $query = $model->getArsipData3($formatted_date_low, $formatted_date_high, $invoice_number, $requestData['vendor']);
        }

        if (!empty($query)) {
            $data['result'] = $query;
        } else {
            $data['error'] = 'Data tidak tersedia';
            unset($data['result']);
        }
        echo json_encode($data);
    }

    public function viewPDF()
    {
        $file = $this->request->getGet('file');
        $filePath = 'uploads/pajak/' . $file;

        if (file_exists($filePath)) {
            return $this->response
                        ->setHeader('Content-Type', 'application/pdf')
                        ->setBody(file_get_contents($filePath));
        } else {
            return $this->response->setStatusCode(404, 'File tidak ditemukan');
        }
    }
    
    public function viewPDFInvoice()
    {
        $file = $this->request->getGet('file');
        $filePath = 'uploads/invoices/' . $file;

        if (file_exists($filePath)) {
            return $this->response
                        ->setHeader('Content-Type', 'application/pdf')
                        ->setBody(file_get_contents($filePath));
        } else {
            return $this->response->setStatusCode(404, 'File tidak ditemukan');
        }
    }

    public function viewPDFInvoiceNpkp()
    {
        $file = $this->request->getGet('file');
        $filePath = 'uploads/invoice_npkp/' . $file;

        if (file_exists($filePath)) {
            return $this->response
                        ->setHeader('Content-Type', 'application/pdf')
                        ->setBody(file_get_contents($filePath));
        } else {
            return $this->response->setStatusCode(404, 'File tidak ditemukan');
        }
    }

    public function viewPDFInvoiceMgl()
    {
        $file = $this->request->getGet('file');
        $filePath = 'uploads/invoice_mgl/' . $file;

        if (file_exists($filePath)) {
            return $this->response
                        ->setHeader('Content-Type', 'application/pdf')
                        ->setBody(file_get_contents($filePath));
        } else {
            return $this->response->setStatusCode(404, 'File tidak ditemukan');
        }
    }

    public function viewPDFakturpengganti()
    {
        $file = $this->request->getGet('file');
        $filePath = 'uploads/pajak_rev/' . $file;

        if (file_exists($filePath)) {
            return $this->response
                        ->setHeader('Content-Type', 'application/pdf')
                        ->setBody(file_get_contents($filePath));
        } else {
            return $this->response->setStatusCode(404, 'File tidak ditemukan');
        }
    }

    
    public function export_inprocess()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $style_col = [
            'font' => ['bold' => true, 'color' => ['rgb' => '000000']],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'top' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                'right' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                'bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                'left' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]
            ],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F27F0C']]
        ];

        $style_row = [
            'alignment' => [
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'top' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                'right' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                'bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                'left' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]
            ]
        ];

        $sheet->setCellValue('A1', "DATA IN PROCESS");
        $sheet->mergeCells('A1:J1');
        $sheet->getStyle('A1')->getFont()->setBold(true);

        $sheet->setCellValue('A3', "DROPBOX ID");
        $sheet->setCellValue('B3', "NOMOR INVOICE");
        $sheet->setCellValue('C3', "NOMOR VERIFIKASI");
        $sheet->setCellValue('D3', "VENDOR");
        $sheet->setCellValue('E3', "CODE VENDOR SAP");
        $sheet->setCellValue('F3', "POS SATPAM");
        $sheet->setCellValue('G3', "VERIFIKASI PUD");
        $sheet->setCellValue('H3', "ZINVER IN");
        $sheet->setCellValue('I3', "ZINVER OUT");
        $sheet->setCellValue('J3', "MIRO");

        // Apply style header
        $sheet->getStyle('A3:J3')->applyFromArray($style_col);

        $model = new M_dropbox();
        $inProcessData = $model->getAll();

        $numrow = 4;
        $currentDropboxId = null;
        $currentCompanyName = null;
        $currentUserGenerate = null;
        $startRow = $numrow;

        foreach($inProcessData as $data) {
            // Jika ada perubahan di salah satu kolom yang di-merge
            if ($currentDropboxId != $data->dropbox_id || $currentCompanyName != $data->company_name || $currentUserGenerate != $data->user_generate) {
                // Merge untuk data sebelumnya
                if ($currentDropboxId !== null) {
                    // Merge cells untuk dropbox_id, company_name, dan user_generate
                    if ($startRow !== $numrow - 1) {
                        $sheet->mergeCells('A'.$startRow.':A'.($numrow - 1));
                        $sheet->mergeCells('D'.$startRow.':D'.($numrow - 1));
                        $sheet->mergeCells('E'.$startRow.':E'.($numrow - 1));
                        
                        // Apply border untuk cells yang di-merge
                        $sheet->getStyle('A'.$startRow.':A'.($numrow - 1))->applyFromArray($style_row);
                        $sheet->getStyle('D'.$startRow.':D'.($numrow - 1))->applyFromArray($style_row);
                        $sheet->getStyle('E'.$startRow.':E'.($numrow - 1))->applyFromArray($style_row);
                    }
                }
                // Update data saat ini
                $currentDropboxId = $data->dropbox_id;
                $currentCompanyName = $data->company_name;
                $currentUserGenerate = $data->user_generate;
                $startRow = $numrow; // Awal merge
            }

            // Isi data selain yang di-merge
            $sheet->setCellValue('A'.$numrow, $data->dropbox_id);
            $sheet->setCellValue('B'.$numrow, $data->no_invoice);
            $sheet->setCellValue('C'.$numrow, $data->invoicing_id);
            $sheet->setCellValue('D'.$numrow, $data->company_name);
            $sheet->setCellValue('E'.$numrow, (int) $data->user_generate);
            $sheet->setCellValue('F'.$numrow, $data->date_approve);
            $sheet->setCellValue('G'.$numrow, $data->date_dok_ok);
            $sheet->setCellValue('H'.$numrow, $data->date_in_pud);
            $sheet->setCellValue('I'.$numrow, $data->date_out_pud);
            $sheet->setCellValue('J'.$numrow, $data->date_miro);

            // Apply style pada baris data
            $sheet->getStyle('A'.$numrow)->applyFromArray($style_row);
            $sheet->getStyle('B'.$numrow)->applyFromArray($style_row);
            $sheet->getStyle('C'.$numrow)->applyFromArray($style_row);
            $sheet->getStyle('D'.$numrow)->applyFromArray($style_row);
            $sheet->getStyle('E'.$numrow)->applyFromArray($style_row);
            $sheet->getStyle('F'.$numrow)->applyFromArray($style_row);
            $sheet->getStyle('G'.$numrow)->applyFromArray($style_row);
            $sheet->getStyle('H'.$numrow)->applyFromArray($style_row);
            $sheet->getStyle('I'.$numrow)->applyFromArray($style_row);
            $sheet->getStyle('J'.$numrow)->applyFromArray($style_row);

            $numrow++;
        }

        // Merge cells terakhir
        if ($startRow !== $numrow - 1) {
            $sheet->mergeCells('A'.$startRow.':A'.($numrow - 1));
            $sheet->mergeCells('D'.$startRow.':D'.($numrow - 1));
            $sheet->mergeCells('E'.$startRow.':E'.($numrow - 1));

            // Apply border untuk cells yang di-merge
            $sheet->getStyle('A'.$startRow.':A'.($numrow - 1))->applyFromArray($style_row);
            $sheet->getStyle('D'.$startRow.':D'.($numrow - 1))->applyFromArray($style_row);
            $sheet->getStyle('E'.$startRow.':E'.($numrow - 1))->applyFromArray($style_row);
        }

        // Set lebar kolom
        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(40);
        $sheet->getColumnDimension('E')->setWidth(20);
        $sheet->getColumnDimension('F')->setWidth(20);
        $sheet->getColumnDimension('G')->setWidth(20);
        $sheet->getColumnDimension('H')->setWidth(20);
        $sheet->getColumnDimension('I')->setWidth(20);
        $sheet->getColumnDimension('J')->setWidth(20);

        $sheet->getDefaultRowDimension()->setRowHeight(-1);
        $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->setTitle("Data In Process");

        // Set header untuk download file
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="Data_InProcess.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
    }


    public function export_infinance()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $style_col = [
            'font' => ['bold' => true, 'color' => ['rgb' => '000000']],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'top' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                'right' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                'bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                'left' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]
            ],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F27F0C']]
        ];

        $style_row = [
            'alignment' => [
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'top' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                'right' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                'bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                'left' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]
            ]
        ];

        $sheet->setCellValue('A1', "DATA IN FINANCE");
        $sheet->mergeCells('A1:F1');
        $sheet->getStyle('A1')->getFont()->setBold(true);

        $sheet->setCellValue('A3', "DROPBOX ID");
        $sheet->setCellValue('B3', "NOMOR INVOICE");
        $sheet->setCellValue('C3', "NOMOR ZINVER");
        $sheet->setCellValue('D3', "VENDOR");
        $sheet->setCellValue('E3', "CODE VENDOR SAP");
        $sheet->setCellValue('F3', "DEADLINE");

        // Apply style header
        $sheet->getStyle('A3:F3')->applyFromArray($style_col);

        $model = new M_report();
        $infinance = $model->getReportData(null, null, null, null, null, null, 'Y');

        $numrow = 4;
        $currentDropboxId = null;
        $currentCompanyName = null;
        $currentUserGenerate = null;
        $startRow = $numrow;

        foreach($infinance as $data) {
            // Jika ada perubahan di salah satu kolom yang di-merge
            if ($currentDropboxId != $data->dropbox_id || $currentCompanyName != $data->company_name || $currentUserGenerate != $data->user_generate) {
                // Merge untuk data sebelumnya
                if ($currentDropboxId !== null) {
                    // Merge cells untuk dropbox_id, company_name, dan user_generate
                    if ($startRow !== $numrow - 1) {
                        $sheet->mergeCells('A'.$startRow.':A'.($numrow - 1));
                        $sheet->mergeCells('D'.$startRow.':D'.($numrow - 1));
                        $sheet->mergeCells('E'.$startRow.':E'.($numrow - 1));
                        
                        // Apply border untuk cells yang di-merge
                        $sheet->getStyle('A'.$startRow.':A'.($numrow - 1))->applyFromArray($style_row);
                        $sheet->getStyle('D'.$startRow.':D'.($numrow - 1))->applyFromArray($style_row);
                        $sheet->getStyle('E'.$startRow.':E'.($numrow - 1))->applyFromArray($style_row);
                    }
                }
                // Update data saat ini
                $currentDropboxId = $data->dropbox_id;
                $currentCompanyName = $data->company_name;
                $currentUserGenerate = $data->user_generate;
                $startRow = $numrow; // Awal merge
            }

            // Isi data selain yang di-merge
            $sheet->setCellValue('A'.$numrow, $data->dropbox_id);
            $sheet->setCellValue('B'.$numrow, $data->no_invoice);
            $sheet->setCellValue('C'.$numrow, (int) $data->no_zinver);
            $sheet->setCellValue('D'.$numrow, $data->company_name);
            $sheet->setCellValue('E'.$numrow, (int) $data->user_generate);
            $sheet->setCellValue('F'.$numrow, $data->deadline);

            // Apply style pada baris data
            $sheet->getStyle('A'.$numrow)->applyFromArray($style_row);
            $sheet->getStyle('B'.$numrow)->applyFromArray($style_row);
            $sheet->getStyle('C'.$numrow)->applyFromArray($style_row);
            $sheet->getStyle('D'.$numrow)->applyFromArray($style_row);
            $sheet->getStyle('E'.$numrow)->applyFromArray($style_row);
            $sheet->getStyle('F'.$numrow)->applyFromArray($style_row);

            $numrow++;
        }

        // Merge cells terakhir
        if ($startRow !== $numrow - 1) {
            $sheet->mergeCells('A'.$startRow.':A'.($numrow - 1));
            $sheet->mergeCells('D'.$startRow.':D'.($numrow - 1));
            $sheet->mergeCells('E'.$startRow.':E'.($numrow - 1));

            // Apply border untuk cells yang di-merge
            $sheet->getStyle('A'.$startRow.':A'.($numrow - 1))->applyFromArray($style_row);
            $sheet->getStyle('D'.$startRow.':D'.($numrow - 1))->applyFromArray($style_row);
            $sheet->getStyle('E'.$startRow.':E'.($numrow - 1))->applyFromArray($style_row);
        }

        // Set lebar kolom
        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(40);
        $sheet->getColumnDimension('E')->setWidth(20);
        $sheet->getColumnDimension('F')->setWidth(20);

        $sheet->getDefaultRowDimension()->setRowHeight(-1);
        $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->setTitle("Data In Process");

        // Set header untuk download file
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="Data_InFinance.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
    }

}
