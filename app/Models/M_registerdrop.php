<?php

namespace App\Models;

use CodeIgniter\Model;

class M_registerdrop extends Model
{
    protected $table1 = 'invoice';
    protected $table2 = 'invoice_npkp';
    protected $table3 = 'invoice_mgl';

    public function getDataUnreg($vendorCode)
    {
        $query1 = $this->db->table('invoice')
            ->select('invoicing_id, no_invoice, company_name, total_payment')
            ->where('user_create', $vendorCode)
            ->where('active', '')
            ->where('status_generate', '');

        $query2 = $this->db->table('invoice_mgl')
            ->select('invoicing_id, no_invoice, company_name, total_payment')
            ->where('user_create', $vendorCode)
            ->where('active', '')
            ->where('status_generate', '');

        $query3 = $this->db->table('invoice_npkp')
            ->select('invoicing_id, no_invoice, company_name, total_payment')
            ->where('user_create', $vendorCode)
            ->where('active', '')
            ->where('status_generate', '');

        $unionQuery = $query1->union($query2)->union($query3);
        return $unionQuery->get()->getResultArray();
    }

    public function getGRActive($noInvoice, $invoicingId)
    {
        $query1 = $this->db->table('invoice')
            ->select('no_gr, no_item, no_po')
            ->where('no_invoice', $noInvoice)
            ->where('active', '')
            ->where('invoicing_id', $invoicingId);

        $query2 = $this->db->table('invoice_mgl')
            ->select('no_gr, no_item, no_po')
            ->where('no_invoice', $noInvoice)
            ->where('active', '')
            ->where('invoicing_id', $invoicingId);

        $query3 = $this->db->table('invoice_npkp')
            ->select('no_gr, no_item, no_po')
            ->where('no_invoice', $noInvoice)
            ->where('active', '')
            ->where('invoicing_id', $invoicingId);
        $unionQuery = $query1->unionAll($query2)->unionAll($query3);
        return $unionQuery->get()->getResultArray();
    }
    
    public function updateActiveField($noGr, $noItem)
    {
        $tables = ['invoice', 'invoice_mgl', 'invoice_npkp'];

        foreach ($tables as $table) {
            $this->db->table($table)
                ->where('no_gr', $noGr)
                ->where('no_item', $noItem)
                ->update(['active' => 'X']);
        }
    }
}
