<?php

namespace App\Models;

use CodeIgniter\Model;

class M_invoicing_mgl extends Model
{
    protected $table = 'invoice_mgl';
    protected $primaryKey = 'invoicing_id'; 
    public $running_id_mgl = "MGL";

    public function get_running_id_mgl()
    {
        $uniqid_record = $this->db->table('uniq_id_buffer')
                        ->select('Uniqid, Date')
                        ->where('Name', 'invoice_mgl')
                        ->get()
                        ->getRowArray();

        $today_formatted = date('Ymd');

        if ($uniqid_record) {
            $record_date = date('Ymd', strtotime($uniqid_record['Date']));

            if ($record_date !== $today_formatted) {
                $next_id_number = 0;
                $this->db->table('uniq_id_buffer')
                    ->where('Name', 'invoice_mgl')
                    ->update(['Uniqid' => $next_id_number, 'Date' => date('Y-m-d')]);
            } else {
                $next_id_number = intval($uniqid_record['Uniqid']) + 1;
                $this->db->table('uniq_id_buffer')
                    ->where('Name', 'invoice_mgl')
                    ->update(['Uniqid' => $next_id_number]);
            }

            $next_id = sprintf("%04d", $next_id_number);
            $next_invoice_id = $this->running_id_mgl . $today_formatted . $next_id;

            return $next_invoice_id;
        } else {
            throw new \Exception('Record dengan Name = "invoice" tidak ditemukan di tabel uniq_id_buffer.');
        }
    }

    public function insert_invoice_mgl($data)
    {
        return $this->db->table('invoice_mgl')->insert($data);
    }

    public function insert_arsip_pdf_mgl($data)
    {
        return $this->db->table('arsip_pdf_mgl')->insert($data);
    }

    public function getDataBasedDate($dateLow, $dateHigh, $invoiceNumber, $vendor_code)
    {
        $builder = $this->db->table('invoice_mgl')
			->select('invoicing_id,
                MAX(no_invoice) as no_invoice,
				MAX(user_create) as user_create,
				MAX(company_name) as company_name,
				MAX(create_date) as create_date,
				MAX(total_payment) as total_payment');
        
        if ($dateLow !== null && $dateHigh !== null) {
            $builder->where("DATE(create_date) BETWEEN '$dateLow' AND '$dateHigh'");
        } elseif ($dateLow !== null) {
            $builder->where("DATE(create_date)", $dateLow);
        } elseif ($dateHigh !== null) {
            $builder->where("DATE(create_date)", $dateHigh);
        }

        if ($invoiceNumber !== null) {
            $builder->where('no_invoice', $invoiceNumber);
        }

        $builder->where('status_generate !=', 'X');
        $builder->where('user_create', $vendor_code);
        $builder->groupBy('invoicing_id');
        return $builder->get()->getResult();
    }

}