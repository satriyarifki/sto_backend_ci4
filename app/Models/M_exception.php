<?php

namespace App\Models;
 
use CodeIgniter\Model;

class M_exception extends Model
{
    protected $table = 'pending_dropbox';
    protected $table1 = 'invoice';
    protected $table2 = 'invoice_npkp';
    protected $table3 = 'invoice_mgl';
    protected $table_user = 'users';
    public $running_id = "DBP";

    public function insertDbpPendingPkp($data)
    {
        return $this->db->table($this->table)->insert($data);
    }

    public function getDataPending($no_invoice)
    {
        $query = $this->db->table($this->table)
                ->select('*') 
                ->where('no_invoice', $no_invoice)
                ->get();
        return $query->getResultArray();
    }

    public function getDataException()
    {
        $builder = $this->db->table($this->table);
        $builder->select("pending_dropbox.*, 
            CASE
                WHEN pending_dropbox.invoicing_id LIKE 'INV%' THEN arsip_pdf.file_path
                WHEN pending_dropbox.invoicing_id LIKE 'NPKP%' THEN arsip_pdf_npkp.file_path_faktur
                ELSE NULL
            END AS file_path,
            CASE
                WHEN pending_dropbox.invoicing_id LIKE 'INV%' THEN arsip_pdf.file_rev_path
                ELSE NULL
            END AS file_rev_path,
            CASE
                WHEN pending_dropbox.invoicing_id LIKE 'INV%' THEN arsip_pdf.file_path_invoice
                WHEN pending_dropbox.invoicing_id LIKE 'NPKP%' THEN arsip_pdf_npkp.file_path_invoice_npkp
                WHEN pending_dropbox.invoicing_id LIKE 'MGL%' THEN arsip_pdf_mgl.file_path_invoice_mgl
                ELSE NULL
            END AS file_path_invoice");

        $builder->join('arsip_pdf', "arsip_pdf.invoicing_id = pending_dropbox.invoicing_id AND pending_dropbox.invoicing_id LIKE 'INV%'", 'left');
        $builder->join('arsip_pdf_npkp', "arsip_pdf_npkp.invoicing_id = pending_dropbox.invoicing_id AND pending_dropbox.invoicing_id LIKE 'NPKP%'", 'left');
        $builder->join('arsip_pdf_mgl', "arsip_pdf_mgl.invoicing_id = pending_dropbox.invoicing_id AND pending_dropbox.invoicing_id LIKE 'MGL%'", 'left');

        return $builder->get()->getResultArray();
    }


    public function getDataExceptionVendor($vendor_code)
    {
        $builder = $this->db->table($this->table);
        $builder->select("pending_dropbox.*, 
            CASE
                WHEN pending_dropbox.invoicing_id LIKE 'INV%' THEN arsip_pdf.file_path
                WHEN pending_dropbox.invoicing_id LIKE 'NPKP%' THEN arsip_pdf_npkp.file_path_faktur
                ELSE NULL
            END AS file_path,
            CASE
                WHEN pending_dropbox.invoicing_id LIKE 'INV%' THEN arsip_pdf.file_rev_path
                ELSE NULL
            END AS file_rev_path,
            CASE
                WHEN pending_dropbox.invoicing_id LIKE 'INV%' THEN arsip_pdf.file_path_invoice
                WHEN pending_dropbox.invoicing_id LIKE 'NPKP%' THEN arsip_pdf_npkp.file_path_invoice_npkp
                WHEN pending_dropbox.invoicing_id LIKE 'MGL%' THEN arsip_pdf_mgl.file_path_invoice_mgl
                ELSE NULL
            END AS file_path_invoice");

        $builder->join('arsip_pdf', "arsip_pdf.invoicing_id = pending_dropbox.invoicing_id AND pending_dropbox.invoicing_id LIKE 'INV%'", 'left');
        $builder->join('arsip_pdf_npkp', "arsip_pdf_npkp.invoicing_id = pending_dropbox.invoicing_id AND pending_dropbox.invoicing_id LIKE 'NPKP%'", 'left');
        $builder->join('arsip_pdf_mgl', "arsip_pdf_mgl.invoicing_id = pending_dropbox.invoicing_id AND pending_dropbox.invoicing_id LIKE 'MGL%'", 'left');

        $builder->where('user_generate', $vendor_code);

        return $builder->get()->getResultArray();
    }

    public function getDataSpescException($no_invoice)
    {
        $builder = $this->db->table($this->table);
        $builder->select('*');
        $builder->where('no_invoice', $no_invoice);
        return $builder->get()->getResultArray();
    }

    public function updateStatusDropboxPending($no_invoice, $status)
    {
        $builderPendingDropbox = $this->db->table($this->table);
        date_default_timezone_set('Asia/Jakarta');

        $data_dropbox = [
            'approve' => $status,
        ];

        $builderPendingDropbox->set($data_dropbox);
        $builderPendingDropbox->where('no_invoice', $no_invoice);
        $updatePendingDropbox = $builderPendingDropbox->update();

        $tables = [$this->table1, $this->table2, $this->table3];
        $foundTable = null;

        foreach ($tables as $table) {
            $builder = $this->db->table($table);
            $builder->select('no_invoice');
            $builder->where('no_invoice', $no_invoice);
            $query = $builder->get();

            if ($query->getRow()) {
                $foundTable = $table;
                break;
            }
        }

        if ($foundTable) {
            $builderInvoice = $this->db->table($foundTable);

            $data_invoice = [
                'approve' => '',
            ];

            $builderInvoice->set($data_invoice);
            $builderInvoice->where('no_invoice', $no_invoice);
            $updateInvoice = $builderInvoice->update();
        } else {
            $updateInvoice = false;
        }

        if ($updatePendingDropbox && $updateInvoice) {
            return true;
        } else {
            return false;
        }
    }

    public function insertDropboxData($result)
    {
        $builder = $this->db->table('dropbox');
        
        foreach ($result as $row) {
            $data = [
                'dropbox_id'        => $row['dropbox_id'],
                'generated_date'    => date('Ymd'),
                'generated_time'    => date('H:i:s'),
                'user_generate'     => $row['user_generate'],
                'company_name'      => $row['company_name'],
                'invoicing_id'      => $row['invoicing_id'],
                'no_invoice'        => $row['no_invoice'],
                'status_receive'    => 'Y',
                'date_approve'      => date('Ymd') ,
                'time_approve'      => date('H:i:s')
            ];
            $builder->insert($data);
        }
    }
}