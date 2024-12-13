<?php

namespace App\Models;
 
use CodeIgniter\Model;

class InvoiceAfterDokOk_model extends Model
{
    protected $table = 'dropbox';

    public function getInvoiceAfterDokOk()
    {
        $builder = $this->db->table($this->table);
        $builder->where('dok_ok', 'Y');
        $builder->where('delete_pud', 'N');
        $builder->where('out_pud', 'N'); // Adjust field name if necessary
        $builder->where('active', '');
        return $builder->get()->getResultArray(); // Convert the result to an array
    }

    public function updatePrint_Dok_Ok($no_invoice, $dropbox_id, $print){
        $builder = $this->db->table($this->table);
        $currentPrint = $builder->select('print')->where('no_invoice', $no_invoice)->where('dropbox_id', $dropbox_id)->get()->getRow()->print;
        if ($print == 'y') {
            $newPrint = $currentPrint + 1;
        }
        $data = ['print' => $newPrint];
        $builder->set($data);
        $builder->where('no_invoice', $no_invoice);
        $builder->where('dropbox_id', $dropbox_id);
        return $builder->update();
    }

    
}