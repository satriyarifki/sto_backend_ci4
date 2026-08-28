<?php

namespace App\Models;

use CodeIgniter\Model;
use Config\Database;

class M_buffer_manifest extends Model
{
    protected $table      = 'buffer';
    protected $dbGroup    = 'dbManifest';
    public $running_id    = "SKD";

    public function __construct()
    {
        parent::__construct();
        $this->db = Database::connect('dbManifest'); 
        $this->table = 'emanifest_order';
    }

    public function getSkidId($no_manifest)
    {
        $existing_record = $this->db->table('buffer')
                                    ->select('manifest_number, count')
                                    ->where('manifest_number', $no_manifest)
                                    ->get()
                                    ->getRowArray();

        if ($existing_record) {
            $next_count = $existing_record['count'] + 1;
            $this->db->table('buffer')
                    ->where('manifest_number', $no_manifest)
                    ->update(['count' => $next_count]);

            $next_id = sprintf("%03d", $next_count); 
            $next_skd_id = "SKD" . $no_manifest . $next_id;

            return $next_skd_id;
        } else {
            $this->db->table('buffer')
                    ->insert([
                        'manifest_number' => $no_manifest,
                        'count' => 1,
                    ]);

            $next_skd_id = "SKD" . $no_manifest . "001"; 

            return $next_skd_id;
        }
    }
}
