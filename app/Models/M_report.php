<?php

namespace App\Models;
use CodeIgniter\Model;

class M_report extends Model
{
    protected $table = 'dropbox'; 
    protected $table1 = 'invoice';
    protected $table2 = 'invoice_npkp';
    protected $table3 = 'invoice_mgl';

    public function getReportData($dropbox = null, $invoice = null, $startDateDbp = null, $endDateDbp = null, $miro = null,  $paid = null, $in_finance = null)
    {
        $builder = $this->builder();
        $builder->select('dropbox.*, total_payments.total_payment');

        if (!empty($dropbox)) {
            $builder->like('dropbox_id', $dropbox);
        }

        if (!empty($invoice)) {
            $builder->like('no_invoice', $invoice);
        }

        if (!empty($startDateDbp) && !empty($endDateDbp)) {
            $builder->where('generated_date >=', $startDateDbp);
            $builder->where('generated_date <=', $endDateDbp);
        } elseif (!empty($startDateDbp)) {
            $builder->where('generated_date', $startDateDbp);
        } elseif (!empty($endDateDbp)) {
            $builder->where('generated_date', $endDateDbp);
        }

        if (!empty($miro)) {
            $builder->where('status_miro', 'Y');
        }

        if (!empty($paid)) {
            $builder->where('status_paid', 'Y');
        }

        if (!empty($in_finance)) {
            $builder->where('out_pud', 'Y');
            $builder->where('status_miro', 'N');
        }

        $subquery = "(SELECT invoicing_id, total_payment 
                        FROM {$this->table1} 
                    UNION 
                    SELECT invoicing_id, total_payment 
                        FROM {$this->table2} 
                    UNION 
                    SELECT invoicing_id, total_payment 
                        FROM {$this->table3}
                    ) as total_payments";

        $builder->join($subquery, 'dropbox.invoicing_id = total_payments.invoicing_id', 'left');

        $builder->groupBy('dropbox.invoicing_id');

        $query = $builder->get();
        return $query->getResult();
    }


    public function getArsipData($startDate = null, $endDate = null, $no_invoice = null, $vendor_code = null)
    {
        $builder = $this->db->table($this->table1)
                            ->select('invoice.invoicing_id, MAX(invoice.no_invoice) as no_invoice, MAX(invoice.create_date) as create_date, MAX(invoice.tax_date) as tax_date, MAX(arsip_pdf.file_path) as file_path, MAX(arsip_pdf.file_rev_path) as file_rev_path, MAX(arsip_pdf.file_path_invoice) as file_path_invoice')
                            ->join('arsip_pdf', 'invoice.invoicing_id = arsip_pdf.invoicing_id')
                            ->groupBy('invoice.invoicing_id');

        if ($startDate && $endDate) {
            $builder->where('DATE(invoice.create_date) >', $startDate)
                    ->where('DATE(invoice.create_date) <=', $endDate);
        } elseif ($startDate) {
            $builder->where('DATE(invoice.create_date)', $startDate);
        } elseif ($endDate) {
            $builder->where('DATE(invoice.create_date)', $endDate);
        }
    
        if ($no_invoice) {
            $builder->where('invoice.no_invoice', $no_invoice);
        }

        $builder->where('invoice.user_create', $vendor_code);
        $query = $builder->get();
        return $query->getResult();
    }


    public function getArsipData2($startDate = null, $endDate = null, $no_invoice = null, $vendor_code = null)
    {
        $builder = $this->db->table($this->table2)
                            ->select('invoice_npkp.invoicing_id, invoice_npkp.no_invoice, invoice_npkp.user_create, invoice_npkp.create_date, invoice_npkp.date_invoice as tax_date, arsip_pdf_npkp.file_path_faktur as file_path, arsip_pdf_npkp.file_path_invoice_npkp as file_path_invoice')
                            ->join('arsip_pdf_npkp', 'invoice_npkp.invoicing_id = arsip_pdf_npkp.invoicing_id')
                            ->groupBy('invoice_npkp.invoicing_id');

        if ($startDate && $endDate) {
            $builder->where('DATE(invoice_npkp.create_date) >', $startDate)
                    ->where('DATE(invoice_npkp.create_date) <=', $endDate);
        } elseif ($startDate) {
            $builder->where('DATE(invoice_npkp.create_date)', $startDate);
        } elseif ($endDate) {
            $builder->where('DATE(invoice_npkp.create_date)', $endDate);
        }
    
        if ($no_invoice) {
            $builder->where('invoice_npkp.no_invoice', $no_invoice);
        }

        $builder->where('invoice_npkp.user_create', $vendor_code);
        $query = $builder->get();
        return $query->getResult();
    }


    public function getArsipData3($startDate = null, $endDate = null, $no_invoice = null, $vendor_code = null)
    {
        $builder = $this->db->table($this->table3)
                            ->select('invoice_mgl.invoicing_id, invoice_mgl.no_invoice, invoice_mgl.user_create, invoice_mgl.create_date, invoice_mgl.date_invoice as tax_date, arsip_pdf_mgl.file_path_invoice_mgl as file_path_invoice')
                            ->join('arsip_pdf_mgl', 'invoice_mgl.invoicing_id = arsip_pdf_mgl.invoicing_id')
                            ->groupBy('invoice_mgl.invoicing_id');

        if ($startDate && $endDate) {
            $builder->where('DATE(invoice_mgl.create_date) >', $startDate)
                    ->where('DATE(invoice_mgl.create_date) <=', $endDate);
        } elseif ($startDate) {
            $builder->where('DATE(invoice_mgl.create_date)', $startDate);
        } elseif ($endDate) {
            $builder->where('DATE(invoice_mgl.create_date)', $endDate);
        }
    
        if ($no_invoice) {
            $builder->where('invoice_mgl.no_invoice', $no_invoice);
        }

        $builder->where('invoice_mgl.user_create', $vendor_code);
        $query = $builder->get();
        return $query->getResult();
    }
}