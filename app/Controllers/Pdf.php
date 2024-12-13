<?php

namespace App\Controllers;

use TCPDF;
use App\Libraries\MY_TCPDF;
use SimpleSoftwareIO\QrCode\Generator;

class Pdf extends BaseController
{
    public function index()
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

        // $surat_jalan = $this->request->getPost('BKTXT');
        // $no_po = $this->request->getPost('EBELN');
        // $startDate = $this->request->getPost('startDate');
        // $endDate = $this->request->getPost('endDate');

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
         $pdf->SetCreator(PDF_CREATOR);
         $pdf->SetAuthor('Sobatcoding.com');
         $pdf->SetTitle('PDF Sobatcoding.com');
         $pdf->SetSubject('TCPDF Tutorial');
         $pdf->SetKeywords('TCPDF, PDF, example, sobatcoding.com');
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
         $pdf->SetFont('dejavusans', '', 14, '', true);
         $pdf->AddPage();
         $html = view('invoicing/verify_transaction/invoice_print', $data);
         $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
         $this->response->setContentType('application/pdf');
         $pdf->Output('invoice-pos-sobatcoding.pdf', 'I'); 
    }
}