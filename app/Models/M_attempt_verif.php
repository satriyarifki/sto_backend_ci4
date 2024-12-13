<?php

namespace App\Models;

use CodeIgniter\Model;

class M_attempt_verif extends Model
{
    protected $table = 'attempt'; 

    public function updateAttemptCount($no_invoice)
    {
        $currentDate = date('Y-m-d');
        $currentTime = date('H:i:s');
        $builder = $this->db->table($this->table);

        $existingAttempt = $builder->where('no_invoice', $no_invoice)->get()->getRow();

        if ($existingAttempt) {
            $builder->set('error_count', 'error_count + 1', false)
                    ->where('no_invoice', $no_invoice)
                    ->update();
        } else {
            $builder->insert([
                'no_invoice' => $no_invoice,
                'error_count' => 1,
                'date' => $currentDate,
                'time' => $currentTime,
            ]);
        }
    }

    public function getErrorCount($no_invoice)
    {
        $result = $this->where('no_invoice', $no_invoice)->get()->getRow();
        return $result ? $result->error_count : 0;
    }
}
