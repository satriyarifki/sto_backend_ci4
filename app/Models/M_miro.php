<?php

namespace App\Models;
 
use CodeIgniter\Model;

class M_miro extends Model
{

    protected $table = 'dropbox';

    ////////////////////////////////////////// Filter miro data Invoice based on Vendor Name //////////////////////////////////////////

    public function updateMiro($no_invoice, $invoicing_id, $status, $nik)
    {
        $builder = $this->db->table($this->table);
        date_default_timezone_set('Asia/Jakarta');
        
        $data = [
            'status_miro' => $status,
            'date_miro' => $status == 'Y' ? date("Y-m-d") : null,
            'time_miro' => $status == 'Y' ? date("H:i:s") : null,
            'user_create_miro' => $status == 'Y' ? $nik : null,
        ];

        $builder->set($data);
        $builder->where('active', '');
        $builder->where('onhold', 'N');
        $builder->where('expired', '');
        $builder->where('no_invoice', $no_invoice);
        $builder->where('invoicing_id', $invoicing_id);
        return $builder->update();
    }

    ////////////////////////////////////////// Filter miro data Invoice based on Vendor Name //////////////////////////////////////////

    public function updateRegisterIn($no_invoice, $status, $nik)
    {
        $builder = $this->db->table($this->table);
        date_default_timezone_set('Asia/Jakarta');
        
        $data = [
            'status_register_in' => $status,
            'date_register_in' => $status == 'Y' ? date("Y-m-d") : null,
            'time_register_in' => $status == 'Y' ? date("H:i:s") : null,
            'user_create_register_in' => $nik
        ];

        $builder->set($data);
        $builder->where('no_invoice', $no_invoice);
        return $builder->update();
    }

    public function updatePaid($no_invoice, $invoicing_id, $status, $paidDate, $nik)
    {
        $builder = $this->db->table($this->table);
        date_default_timezone_set('Asia/Jakarta');
        
        $data = [
            'status_paid' => $status,
            'date_paid' => $status == 'Y' ? ($paidDate != '' ? date('Ymd', strtotime($paidDate)) : date("Y-m-d")) : null,
            'date_time' => $status == 'Y' ? date("H:i:s") : null,
            'user_create_paid' => $status == 'Y' ? $nik : null,
        ];

        $builder->set($data);
        $builder->where('active', '');
        $builder->where('onhold', 'N');
        $builder->where('expired', '');
        $builder->where('no_invoice', $no_invoice);
        $builder->where('invoicing_id', $invoicing_id);
        return $builder->update();
    }
}