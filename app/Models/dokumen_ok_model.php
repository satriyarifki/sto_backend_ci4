<?php

namespace App\Models;
 
use CodeIgniter\Model;

class dokumen_ok_model extends Model
{
    protected $table = 'dropbox';

    public function getDokumen_Ok($dropbox_id = false){
        $builder = $this->db->table($this->table);
        $builder->select('dropbox.*, invoice.*');
        $builder->join('invoice', 'invoice.invoicing_id = dropbox.invoicing_id');
        
        if ($dropbox_id === false) {
            $builder->groupBy('invoice.invoicing_id');
            return $builder->get()->getResultArray();
        } else {
            $builder->where('dropbox.dropbox_id', $dropbox_id);
            $builder->where('dropbox.status_receive', 'y');
            return $builder->get()->getResultArray();
        }
    }

  
    public function updateStatusDokumentOk($dropbox_id, $status, $user)
    {
        $builder = $this->db->table($this->table);
        date_default_timezone_set('Asia/Jakarta');
        
        $data = [
            'dok_ok'        => $status,
            'user_dok_ok'   => $status == 'Y' ? $user : null,
            'date_dok_ok'   => $status == 'Y' ? date("Y-m-d") : null,
            'time_dok_ok'   => $status == 'Y' ? date("H:i:s") : null
        ];

        $builder->set($data);
        $builder->where('dropbox_id', $dropbox_id);
        return $builder->update();
    }
	


    public function getUnOkeDocument()
    {
        return $this->select('dropbox_id')->where('status_receive !=', '')
                ->where('dok_ok', '')
                ->groupBy('dropbox_id')
                ->findAll();
    }

    public function updatePrint_Dok_Ok($dropbox_id, $status)
    {
        if ($status == 'y') {
            return $this->editPrint_Dok_Ok('', $dropbox_id); // Mereset status_receive menjadi kosong
        } else {
            return $this->editPrint_Dok_Ok('y', $dropbox_id); // Menyetujui status_receive
        }
    }

    public function editPrint_Dok_Ok($print, $dropbox_id){
        $builder = $this->db->table($this->table);
        
        $currentPrint = $builder->select('print')->where('dropbox_id', $dropbox_id)->get()->getRow()->print;

        if ($print == 'y') {
            $newPrint = $currentPrint + 1;
        } else {
            $newPrint = $currentPrint;
        }
        $data = ['print' => $newPrint];
        $builder->set($data);
        $builder->where('dropbox_id', $dropbox_id);
        return $builder->update();
    }

    public function getDataSpecific()
    {
        $builder = $this->db->table($this->table);
        $builder->select('dropbox.dropbox_id, dropbox.dok_ok, dropbox.user_generate, dropbox.in_pud, dropbox.date_dok_ok, dropbox.time_dok_ok, dropbox.date_approve,
                COALESCE(invoice.invoicing_id, invoice_npkp.invoicing_id, invoice_mgl.invoicing_id) as invoicing_id,
                COALESCE(invoice.npwp, "") as npwp,
                COALESCE(invoice.tax_date, invoice_npkp.date_invoice, invoice_mgl.date_invoice) as tax_date,
                COALESCE(invoice.tax_number, "") as tax_number,
                COALESCE(invoice.company_name, invoice_npkp.company_name, invoice_mgl.company_name) as company_name,
                COALESCE(invoice.no_invoice, invoice_npkp.no_invoice, invoice_mgl.no_invoice) as no_invoice,
                COALESCE(invoice.total_payment, invoice_npkp.total_payment, invoice_mgl.total_payment) as total_payment');
        $builder->join('invoice', 'dropbox.invoicing_id = invoice.invoicing_id', 'left');
        $builder->join('invoice_npkp', 'dropbox.invoicing_id = invoice_npkp.invoicing_id', 'left');
        $builder->join('invoice_mgl', 'dropbox.invoicing_id = invoice_mgl.invoicing_id', 'left');
        $builder->where('dropbox.status_archieve_pud', 'Y');
        $builder->where('dropbox.active', '');
        $builder->where('dropbox.onhold', 'N');
        $query = $builder->get();
        $results = [];
        foreach ($query->getResultArray() as $row) {
            $dropboxId = $row['dropbox_id'];
            $invoicingId = $row['invoicing_id'];

            if (!isset($results[$dropboxId])) {
                $results[$dropboxId] = [
                    'dropbox_id' => $row['dropbox_id'],
                    'user_generate' => $row['user_generate'],
                    'date' => $row['date_dok_ok'],
                    'dok_ok' => $row['dok_ok'],
                    'in_pud' => $row['in_pud'],
                    'time_dok_ok' => $row['time_dok_ok'],
                    'invoices' => []
                ];
            }

            if (!isset($results[$dropboxId]['invoices'][$invoicingId])) {
                $results[$dropboxId]['invoices'][$invoicingId] = [
                    'invoicing_id' => $row['invoicing_id'],
                    'no_invoice' => $row['no_invoice'],
                    'npwp' => $row['npwp'],
                    'tax_date' => $row['tax_date'],
                    'date_approve' => $row['date_approve'],
                    'tax_number' => $row['tax_number'],
                    'company_name' => $row['company_name'],
                    'total_payment' => $row['total_payment']
                ];
            }
        }

        foreach ($results as &$result) {
            $result['invoices'] = array_values($result['invoices']);
        }
        return array_values($results);
    }

    
    public function updateOnHoldStatusV2($no_invoice, $alasan)
    {
        $builder = $this->db->table('dropbox');
        $builder->select('COALESCE(invoice.no_invoice, invoice_npkp.no_invoice, invoice_mgl.no_invoice) as no_invoice, 
                        COALESCE(invoice.invoicing_id, invoice_npkp.invoicing_id, invoice_mgl.invoicing_id) as invoicing_id,
                        COALESCE(invoice.invoicing_id, NULL, NULL) as invoice_invoicing_id,
                        COALESCE(invoice_npkp.invoicing_id, NULL, NULL) as invoice_npkp_invoicing_id,
                        COALESCE(invoice_mgl.invoicing_id, NULL, NULL) as invoice_mgl_invoicing_id');
        $builder->join('invoice', 'dropbox.invoicing_id = invoice.invoicing_id', 'left');
        $builder->join('invoice_npkp', 'dropbox.invoicing_id = invoice_npkp.invoicing_id', 'left');
        $builder->join('invoice_mgl', 'dropbox.invoicing_id = invoice_mgl.invoicing_id', 'left');
        
        $builder->where('COALESCE(invoice.no_invoice, invoice_npkp.no_invoice, invoice_mgl.no_invoice)', $no_invoice);
        $query = $builder->get();
        $result = $query->getRow();

        if ($result) {
            $statusGenerateData = ['active' => 'X'];

            if ($result->invoice_invoicing_id) {
                $this->db->table('invoice')
                        ->where('invoicing_id', $result->invoice_invoicing_id)
                        ->update($statusGenerateData);
            } elseif ($result->invoice_npkp_invoicing_id) {
                $this->db->table('invoice_npkp')
                        ->where('invoicing_id', $result->invoice_npkp_invoicing_id)
                        ->update($statusGenerateData);
            } elseif ($result->invoice_mgl_invoicing_id) {
                $this->db->table('invoice_mgl')
                        ->where('invoicing_id', $result->invoice_mgl_invoicing_id)
                        ->update($statusGenerateData);
            }

            $dropboxData = [
                'active' => 'X',
                'date_disactive' => date('Y-m-d'),
                'time_disactive' => date('H:i:s'),
                'cancel_reason'  => $alasan,
            ];

            return $this->db->table('dropbox')
                            ->where('invoicing_id', $result->invoicing_id)
                            ->update($dropboxData);
        } else {
            return false;
        }
    }

    public function getRegisterIn($no_invoice, $no_zinver, $vendor_code)
    {
        $builder = $this->db->table('dropbox');
        $builder->select('dropbox.dropbox_id, dropbox.expired, dropbox.status_register_in, dropbox.status_miro, dropbox.no_zinver, dropbox.user_generate, dropbox.date_dok_ok, dropbox.time_dok_ok, dropbox.date_approve,
                        COALESCE(invoice.invoicing_id, invoice_npkp.invoicing_id, invoice_mgl.invoicing_id) as invoicing_id,
                        COALESCE(invoice.npwp, NULL, NULL) as npwp,
                        COALESCE(invoice.total_payment, invoice_npkp.total_payment, invoice_mgl.total_payment) as total_payment,
                        COALESCE(invoice.create_date, invoice_npkp.create_date, invoice_mgl.create_date) as create_date,
                        COALESCE(invoice.tax_number, NULL, NULL) as tax_number, 
                        COALESCE(invoice.company_name, invoice_npkp.company_name, invoice_mgl.company_name) as company_name,
                        COALESCE(invoice.no_invoice, invoice_npkp.no_invoice, invoice_mgl.no_invoice) as no_invoice,
                        COALESCE(invoice.no_gr, invoice_npkp.no_gr, invoice_mgl.no_gr) as no_gr,
                        COALESCE(invoice.no_item, invoice_npkp.no_item, invoice_mgl.no_item) as no_item,
                        COALESCE(invoice.no_po, invoice_npkp.no_po, invoice_mgl.no_po) as no_po');
                        
        // Join ke tiga tabel
        $builder->join('invoice', 'dropbox.invoicing_id = invoice.invoicing_id', 'left');
        $builder->join('invoice_npkp', 'dropbox.invoicing_id = invoice_npkp.invoicing_id', 'left');
        $builder->join('invoice_mgl', 'dropbox.invoicing_id = invoice_mgl.invoicing_id', 'left');

        // Kondisi berdasarkan no_invoice dan no_zinver
        if ($no_invoice && $no_zinver == '0000000000') {
            $builder->where('dropbox.no_invoice', $no_invoice);
            $builder->where('dropbox.user_generate', $vendor_code);
        } elseif ($no_zinver && !$no_invoice) {
            $builder->where('dropbox.no_zinver', $no_zinver);
            $builder->where('dropbox.user_generate', $vendor_code);
        } elseif ($no_invoice && $no_zinver) {
            $builder->where('dropbox.no_invoice', $no_invoice);
            $builder->where('dropbox.no_zinver', $no_zinver);
            $builder->where('dropbox.user_generate', $vendor_code);
        }

        $builder->where('dropbox.out_pud', 'Y');
        $builder->where('dropbox.expired', '');
        $query = $builder->get();
        $results = [];
        
        // Proses hasil query
        foreach ($query->getResultArray() as $row) {
            $dropboxId = $row['dropbox_id'];
            $invoicingId = $row['invoicing_id'];

            if (!isset($results[$dropboxId])) {
                $results[$dropboxId] = [
                    'no_invoice' => $row['no_invoice'],
                    'tax_number' => $row['tax_number'],
                    'company_name' => $row['company_name'],
                    'invoicing_id' => $row['invoicing_id'],
                    'dropbox_id' => $row['dropbox_id'],
                    'no_zinver' => $row['no_zinver'],
                    'total_payment' => $row['total_payment'],
                    'status_register_in' => $row['status_register_in'],
                    'status_miro' => $row['status_miro'],
                    'invoices' => []
                ];
            }

            $results[$dropboxId]['invoices'][] = [
                'invoicing_id' => $row['invoicing_id'],
                'no_invoice' => $row['no_invoice'],
                'no_item' => $row['no_item'],
                'no_gr' => $row['no_gr'],
                'no_po' => $row['no_po'],
            ];
        }

        foreach ($results as &$result) {
            $result['invoices'] = array_values($result['invoices']);
        }
        
        return array_values($results);
    }


    public function getMiroSpecific($vendor_code)
    {
        $builder = $this->db->table($this->table);
        $builder->select('dropbox.dropbox_id, dropbox.status_miro, dropbox.onhold, dropbox.status_paid, dropbox.no_zinver, dropbox.user_generate, dropbox.date_dok_ok, dropbox.time_dok_ok, dropbox.date_approve,
                        COALESCE(invoice.invoicing_id, invoice_npkp.invoicing_id, invoice_mgl.invoicing_id) AS invoicing_id,
                        COALESCE(invoice.npwp, "") AS npwp, COALESCE(invoice.tax_number, "") AS tax_number, 
                        COALESCE(invoice.total_payment, invoice_npkp.total_payment, invoice_mgl.total_payment) AS total_payment,
                        COALESCE(invoice.create_date, invoice_npkp.create_date, invoice_mgl.create_date) AS create_date,
                        COALESCE(invoice.company_name, invoice_npkp.company_name, invoice_mgl.company_name) AS company_name,
                        COALESCE(invoice.no_invoice, invoice_npkp.no_invoice, invoice_mgl.no_invoice) AS no_invoice,
                        COALESCE(invoice.no_gr, invoice_npkp.no_gr, invoice_mgl.no_gr) AS no_gr,
                        COALESCE(invoice.no_item, invoice_npkp.no_item, invoice_mgl.no_item) AS no_item,
                        COALESCE(invoice.no_po, invoice_npkp.no_po, invoice_mgl.no_po) AS no_po');
        
        $builder->join('invoice', 'dropbox.invoicing_id = invoice.invoicing_id', 'left');
        $builder->join('invoice_npkp', 'dropbox.invoicing_id = invoice_npkp.invoicing_id', 'left');
        $builder->join('invoice_mgl', 'dropbox.invoicing_id = invoice_mgl.invoicing_id', 'left');

        $builder->where('dropbox.user_generate', $vendor_code);
        $builder->where('dropbox.status_archieve_finance', 'Y');
        $builder->where('dropbox.expired', '');
        $builder->where('dropbox.onhold', 'N');
        $builder->where('dropbox.active', '');
        $query = $builder->get();
        
        $results = [];
        foreach ($query->getResultArray() as $row) {
            $invoicingId = $row['invoicing_id'];

            if (!isset($results[$invoicingId])) {
                $results[$invoicingId] = [
                    'no_invoice' => $row['no_invoice'],
                    'tax_number' => $row['tax_number'],
                    'user_generate' => $row['user_generate'],
                    'company_name' => $row['company_name'],
                    'invoicing_id' => $row['invoicing_id'],
                    'dropbox_id' => $row['dropbox_id'],
                    'no_zinver' => $row['no_zinver'],
                    'total_payment' => $row['total_payment'],
                    'status_miro' => $row['status_miro'],
                    'status_paid' => $row['status_paid'],
                    'invoices' => []
                ];
            }

            $results[$invoicingId]['invoices'][] = [
                'invoicing_id' => $row['invoicing_id'],
                'no_invoice' => $row['no_invoice'],
                'no_item' => $row['no_item'],
                'no_gr' => $row['no_gr'],
                'no_po' => $row['no_po'],
            ];
        }

        foreach ($results as &$result) {
            $result['invoices'] = array_values($result['invoices']);
        }

        return array_values($results);
    }


    public function getPaidSpecific($vendor_code)
    {
        $builder = $this->db->table($this->table);
        $builder->select('dropbox.dropbox_id, dropbox.status_miro, dropbox.onhold, dropbox.status_paid, dropbox.no_zinver, dropbox.user_generate, dropbox.date_dok_ok, dropbox.time_dok_ok, dropbox.date_approve,
                        COALESCE(invoice.invoicing_id, invoice_npkp.invoicing_id, invoice_mgl.invoicing_id) AS invoicing_id,
                        COALESCE(invoice.npwp, "") AS npwp, COALESCE(invoice.tax_number, "") AS tax_number, 
                        COALESCE(invoice.total_payment, invoice_npkp.total_payment, invoice_mgl.total_payment) AS total_payment,
                        COALESCE(invoice.create_date, invoice_npkp.create_date, invoice_mgl.create_date) AS create_date,
                        COALESCE(invoice.company_name, invoice_npkp.company_name, invoice_mgl.company_name) AS company_name,
                        COALESCE(invoice.no_invoice, invoice_npkp.no_invoice, invoice_mgl.no_invoice) AS no_invoice,
                        COALESCE(invoice.no_gr, invoice_npkp.no_gr, invoice_mgl.no_gr) AS no_gr,
                        COALESCE(invoice.no_item, invoice_npkp.no_item, invoice_mgl.no_item) AS no_item,
                        COALESCE(invoice.no_po, invoice_npkp.no_po, invoice_mgl.no_po) AS no_po');
        
        $builder->join('invoice', 'dropbox.invoicing_id = invoice.invoicing_id', 'left');
        $builder->join('invoice_npkp', 'dropbox.invoicing_id = invoice_npkp.invoicing_id', 'left');
        $builder->join('invoice_mgl', 'dropbox.invoicing_id = invoice_mgl.invoicing_id', 'left');

        $builder->where('dropbox.user_generate', $vendor_code);
        $builder->where('dropbox.status_miro', 'Y');
        $builder->where('dropbox.expired', '');
        $builder->where('dropbox.onhold', 'N');
        $query = $builder->get();
        $results = [];
        foreach ($query->getResultArray() as $row) {
            $invoicingId = $row['invoicing_id'];

            if (!isset($results[$invoicingId])) {
                $results[$invoicingId] = [
                    'no_invoice' => $row['no_invoice'],
                    'tax_number' => $row['tax_number'],
                    'company_name' => $row['company_name'],
                    'invoicing_id' => $row['invoicing_id'],
                    'dropbox_id' => $row['dropbox_id'],
                    'total_payment' => $row['total_payment'],
                    'no_zinver' => $row['no_zinver'],
                    'status_paid' => $row['status_paid'],
                    'invoices' => []
                ];
            }

            $results[$invoicingId]['invoices'][] = [
                'invoicing_id' => $row['invoicing_id'],
                'no_invoice' => $row['no_invoice'],
                'no_item' => $row['no_item'],
                'no_gr' => $row['no_gr'],
                'no_po' => $row['no_po'],
            ];
        }

        foreach ($results as &$result) {
            $result['invoices'] = array_values($result['invoices']);
        }
        return array_values($results);
    }


    public function getOnHoldSpesific()
    {
        $builder = $this->db->table($this->table);
        $builder->select('dropbox.dropbox_id, dropbox.dok_ok, dropbox.onhold, dropbox.onhold_datetime, dropbox.user_generate, dropbox.date_dok_ok, dropbox.time_dok_ok, dropbox.date_approve,
                        invoice.invoicing_id, invoice.npwp, invoice.create_date, invoice.tax_number, invoice.company_name, invoice.no_invoice, invoice.total_payment');
        $builder->join('invoice', 'dropbox.invoicing_id = invoice.invoicing_id');
        $builder->where('dropbox.status_archieve_pud', 'Y');
        $builder->where('dropbox.delete_onhold', 'N');
        $builder->where('dropbox.onhold', 'Y');
        $query = $builder->get();
        $results = [];
        foreach ($query->getResultArray() as $row) {
            $dropboxId = $row['dropbox_id'];
            $invoicingId = $row['invoicing_id'];

            if (!isset($results[$dropboxId])) {
                $results[$dropboxId] = [
                    'dropbox_id' => $row['dropbox_id'],
                    'user_generate' => $row['user_generate'],
                    'onhold' => $row['onhold'],
                    'onhold_datetime' => $row['onhold_datetime'],
                    'date' => $row['date_dok_ok'],
                    'dok_ok' => $row['dok_ok'],
                    'time_dok_ok' => $row['time_dok_ok'],
                    'invoices' => []
                ];
            }

            if (!isset($results[$dropboxId]['invoices'][$invoicingId])) {
                $results[$dropboxId]['invoices'][$invoicingId] = [
                    'invoicing_id' => $row['invoicing_id'],
                    'no_invoice' => $row['no_invoice'],
                    'npwp' => $row['npwp'],
                    'create_date' => $row['create_date'],
                    'date_approve' => $row['date_approve'],
                    'tax_number' => $row['tax_number'],
                    'company_name' => $row['company_name'],
                    'total_payment' => $row['total_payment']
                ];
            }
        }

        foreach ($results as &$result) {
            $result['invoices'] = array_values($result['invoices']);
        }
        return array_values($results);
    }


    public function updateOnHoldStatus($dropbox_id)
    {
        $data = [
            'onhold' => 'Y',
            'onhold_datetime' => date('Y-m-d'),
        ];
        return $this->db->table($this->table)
                        ->where('dropbox_id', $dropbox_id)
                        ->update($data);
    }

    public function del_onhold($dropbox_id)
    {
        $data = [
            'delete_onhold' => 'Y',
        ];
        return $this->db->table($this->table)
                        ->where('dropbox_id', $dropbox_id)
                        ->update($data);
    }

    public function restoreOnHold($dropbox_id)
    {
        $data = [
            'onhold' => 'N',
        ];
        return $this->db->table($this->table)
                        ->where('dropbox_id', $dropbox_id)
                        ->update($data);
    }

}