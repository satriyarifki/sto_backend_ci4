<?php

namespace App\Models;

use CodeIgniter\Model;

class M_recap extends Model
{
    protected $table = 'new_table_sto';
    protected $primaryKey = 'id_tag';
    protected $allowedFields = ['id_tag', 'area', 'address', 'job_number', 'part_number', 'material_description', 'type', 'created_at'];
}
