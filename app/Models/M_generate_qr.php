<?php

namespace App\Models;

use CodeIgniter\Model;

class M_generate_qr extends Model
{
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    public $running_id = "MAJ";

    public function get_running_id()
    {
        $uniqid_record = $this->db->table('uniq_id_buffer')
                        ->select('Uniqid, Date')
                        ->where('Name', 'generate_qr')
                        ->get()
                        ->getRowArray();

        $today_formatted = date('Ymd');

        if ($uniqid_record) {
            $record_date = date('Ymd', strtotime($uniqid_record['Date']));

            if ($record_date !== $today_formatted) {
                $next_id_number = 0;
                $this->db->table('uniq_id_buffer')
                    ->where('Name', 'generate_qr')
                    ->update(['Uniqid' => $next_id_number, 'Date' => date('Y-m-d')]);
            } else {
                $next_id_number = intval($uniqid_record['Uniqid']) + 1;
                $this->db->table('uniq_id_buffer')
                    ->where('Name', 'generate_qr')
                    ->update(['Uniqid' => $next_id_number]);
            }

            $next_id = sprintf("%04d", $next_id_number);
            $next_invoice_id = $this->running_id . $today_formatted . $next_id;

            return $next_invoice_id;
        } else {
            throw new \Exception('Record dengan Name = "invoice" tidak ditemukan di tabel uniq_id_buffer.');
        }
    }
}