<?php

namespace App\Models;
 
use CodeIgniter\Model;

class M_news extends Model
{
    public function getDataKanban($barcode)
    {
        $dbKanban = \Config\Database::connect('kanbanInventory');

        $builder = $dbKanban->table('ms_data_kanban');
        $builder->select('id_kanban, job_number, part_number, part_name');
        $builder->where('id_kanban', $barcode);
        $query = $builder->get();
        $result = $query->getRowArray();
    
        if ($result) {
            return [
                'id_tag' => $result['id_kanban'],
                'area' => '',
                'job_number' => $result['job_number'],
                'material_description' => $result['part_name'],
                'part_number' => $result['part_number']
            ];
        }
    
        return null;
    }
}