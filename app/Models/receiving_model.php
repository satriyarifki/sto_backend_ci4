<?php

namespace App\Models;
 
use CodeIgniter\Model;

class receiving_model extends Model
{
    protected $table = 'dropbox';

    public function getReceiving($dropbox_id = false){
        if ($dropbox_id === false){
            return $this->findAll();
        }else{
            return $this->getWhere(['dropbox_id' => $dropbox_id]);
        }
    }

    public function getUnreceivedDropbox()
    {
        return $this->select('dropbox_id')->where('status_receive', '')->groupBy('dropbox_id')->findAll();
    }
    
    public function updateStatusReceive($dropbox_id, $status, $user)
    {
        $builder = $this->db->table($this->table);
        date_default_timezone_set('Asia/Jakarta');
        
        $data = [
            'status_receive'    => $status,
            'user_receive'      => $status == 'Y' ? $user : null,
            'date_approve'      => $status == 'Y' ? date("Y-m-d") : null,
            'time_approve'      => $status == 'Y' ? date("H:i:s") : null
        ];

        $builder->set($data);
        $builder->where('dropbox_id', $dropbox_id);
        return $builder->update();
    }
    
    public function getReceiveSpecific($dropbox_id)
    {
        $builder = $this->db->table($this->table);
        $builder->select('dropbox.dropbox_id, dropbox.status_receive, dropbox.dok_ok, dropbox.due_date, dropbox.user_generate,
                        COALESCE(invoice.invoicing_id, invoice_npkp.invoicing_id, invoice_mgl.invoicing_id) as invoicing_id, 
                        COALESCE(invoice.company_name, invoice_npkp.company_name, invoice_mgl.company_name) as company_name, 
                        COALESCE(invoice.no_invoice, invoice_npkp.no_invoice, invoice_mgl.no_invoice) as no_invoice');
        $builder->join('invoice', 'dropbox.invoicing_id = invoice.invoicing_id', 'left');
        $builder->join('invoice_npkp', 'dropbox.invoicing_id = invoice_npkp.invoicing_id', 'left');
        $builder->join('invoice_mgl', 'dropbox.invoicing_id = invoice_mgl.invoicing_id', 'left');
        $builder->where('dropbox.dropbox_id', $dropbox_id);
        $builder->where('dropbox.delete_onhold', 'N');
        $builder->where('dropbox.delete_pud', 'N');
        
        $query = $builder->get();
        $results = [];

        if ($query->getNumRows() > 0) {
            foreach ($query->getResultArray() as $row) {
                $dropboxId = $row['dropbox_id'];
                $invoicingId = $row['invoicing_id'];

                if (!isset($results[$dropboxId])) {
                    $results[$dropboxId] = [
                        'dropbox_id' => $row['dropbox_id'],
                        'company_name' => $row['company_name'],
                        'user_generate' => $row['user_generate'],
                        'status_receive' => $row['status_receive'],
                        'dok_ok' => $row['dok_ok'],
                        'due_date' => $row['due_date'],
                        'invoices' => []
                    ];
                }

                if (!isset($results[$dropboxId]['invoices'][$invoicingId])) {
                    $results[$dropboxId]['invoices'][$invoicingId] = [
                        'invoicing_id' => $row['invoicing_id'],
                        'no_invoice' => $row['no_invoice'],
                    ];
                }
            }

            foreach ($results as &$result) {
                $result['invoices'] = array_values($result['invoices']);
            }

            $today = date('Y-m-d');

            foreach ($results as $result) {
                $dueDate = $result['due_date'];

                $userGenerate = $result['user_generate'];

                if ($userGenerate === '0000100444') {
                    continue;
                }
                
                if ($dueDate > $today) {
                    return ['error' => "Data hanya dapat diproses pada hari due date-nya: {$dueDate}.", 'status' => false];
                }

                if ($dueDate < $today) {
                    $this->resetStatusGenerate($result['invoices']);

                    return ['error' => "Data dropbox expired. Silahkan registrasi ulang.", 'status' => false];
                }
            }

            return array_values($results);
        } else {
            return false;
        }
    }

    private function resetStatusGenerate($invoices)
    {
        foreach ($invoices as $invoice) {
            $invoicingId = $invoice['invoicing_id'];

            $this->db->table('dropbox')
                        ->where('invoicing_id', $invoicingId)
                        ->update(['expired' => 'X']);

            $invoiceExists = $this->db->table('invoice')
                                    ->where('invoicing_id', $invoicingId)
                                    ->countAllResults();
            if ($invoiceExists > 0) {
                $this->db->table('invoice')
                        ->where('invoicing_id', $invoicingId)
                        ->update(['status_generate' => '']);    
                continue;
            }

            $invoiceNpkpExists = $this->db->table('invoice_npkp')
                                        ->where('invoicing_id', $invoicingId)
                                        ->countAllResults();
            if ($invoiceNpkpExists > 0) {
                $this->db->table('invoice_npkp')
                        ->where('invoicing_id', $invoicingId)
                        ->update(['status_generate' => '']);
                continue;
            }

            $invoiceMglExists = $this->db->table('invoice_mgl')
                                        ->where('invoicing_id', $invoicingId)
                                        ->countAllResults();
            if ($invoiceMglExists > 0) {
                $this->db->table('invoice_mgl')
                        ->where('invoicing_id', $invoicingId)
                        ->update(['status_generate' => '']);
            }
        }
    }
}