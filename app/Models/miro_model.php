<?php

namespace App\Models;
 
use CodeIgniter\Model;

class miro_model extends Model
{
    protected $table = 'dropbox';

    public function getMiro($dropbox_id = false){
        if ($dropbox_id === false){
            return $this->findAll();
        }else{
            return $this->getWhere(['dropbox_id' => $dropbox_id]);
        }
    }
    
    public function updateStatusMiro($dropbox_id, $status)
    {

        // Memanggil metode editMiro dengan nilai status_miro yang sesuai
        if ($status == 'y') {
            return $this->editMiro('', $dropbox_id); // Mereset status_miro menjadi kosong
        } else {
            return $this->editMiro('y', $dropbox_id); // Menyetujui status_miro
        }
    }

    public function editMiro($status_miro, $dropbox_id){
        $builder = $this->db->table($this->table);
        $data = ['status_miro' => $status_miro];
        date_default_timezone_set('Asia/Jakarta');
        if ($status_miro == 'y') {
            $data['date_miro'] = date("Y-m-d");
            $data['time_miro'] = date("H:i:s");
        }else {
            $data['date_miro'] = null;
            $data['time_miro'] = null;
            $data['status_miro'] = '';
        }
        $builder->set($data);
        $builder->where('dropbox_id', $dropbox_id);
        return $builder->update();
    }
    
    public function getUnMiroDocument()
    {
        return $this->select('dropbox_id')->where('status_receive !=', '')
                ->where('dok_ok !=', '')
                ->where('status_miro', '')
                ->groupBy('dropbox_id')
                ->findAll();
    }
}