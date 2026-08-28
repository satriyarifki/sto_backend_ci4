<?php

namespace App\Models;

use CodeIgniter\Model;

class M_sto extends Model
{
    protected $table_sto = 'sto_table';
    protected $table_sto_new = 'new_table_sto';
    protected $table_master = 'master_data';
    protected $table_master_trial = 'master_data_trial';
    protected $table_buffer = 'buffer';
    protected $table_material_number = 'part_number';
    protected $table_master_kanban = 'master_data_kanban';
    protected $DBGroup = 'default';


    public function generatedID()
    {
        $builder = $this->db->table($this->table_buffer);
        $buffer = $builder->where('tag', 'STO')->get()->getRowArray();

        if (!$buffer) {
            $builder->insert([
                'tag' => 'STO',
                'number' => 1
            ]);
            $newNumber = 1;
        } else {
            $newNumber = (int)$buffer['number'] + 1;
            $builder->where('tag', 'STO')->update(['number' => $newNumber]);
        }
        return 'A' . str_pad($newNumber, 5, '0', STR_PAD_LEFT);
    }

    // public function getDataKanban($barcode)
    // {
    //     $dbKanban = \Config\Database::connect('kanbanInventory');

    //     $builder = $dbKanban->table('ms_data_kanban');
    //     $builder->select('id_kanban, job_number, part_number, part_name');
    //     $builder->where('id_kanban', $barcode);
    //     $query = $builder->get();
    //     $result = $query->getRowArray();
    
    //     if ($result) {
    //         return [
    //             'id_tag' => $result['id_kanban'],
    //             'area' => '',
    //             'job_number' => $result['job_number'],
    //             'material_description' => $result['part_name'],
    //             'part_number' => $result['part_number']
    //         ];
    //     }
    
    //     return null;
    // }

    public function store_doc($data)
    {
        return $this->db->table($this->table_sto)->insert($data);
    }

    public function insertDataSto($data)
    {
        return $this->db->table($this->table_sto_new)->insert($data);
    }

    public function getDataSto($data)
    {
        $builder = $this->db->table($this->table_sto_new . ' sto');
        $builder->select('*');
            // $builder->select('
            //     sto.id_tag, 
            //     sto.part_number, 
            //     sto.job_number, 
            //     sto.area, 
            //     sto.material_description, 
            //     p.plant, 
            //     p.process
            // ');
            $builder->join('master_data_trial p', 'p.part_number = sto.part_number AND p.area = sto.area', 'inner');
        $query = $builder->where('sto.id_tag', $data);
        $query = $builder->get();
        return $query->getRowArray()?$query->getRowArray():$query;
    }

    public function getDataNik($nik, $group) 
    {
        $builder = $this->db->table($this->table_sto_new. ' sto');
        
        if ($group == 'A') {
            $builder->select('
                sto.id_tag, 
                sto.nik_a AS nik, 
                sto.qty_a AS qty, 
                sto.part_number, 
                sto.job_number, 
                sto.area, 
                sto.material_description, 
                sto.updated_a AS updated_at,
                p.plant, 
                p.process
            ');
            $builder->join('master_data_trial p', 'p.part_number = sto.part_number AND p.area = sto.area', 'inner');
            $builder->where('sto.nik_a', $nik);
            $builder->where('sto.active', true);
            $builder->orderBy('sto.updated_a', 'DESC');
        } elseif ($group == 'B') {
            $builder->select('
                sto.id_tag, 
                sto.nik_b AS nik, 
                sto.qty_b AS qty, 
                sto.part_number, 
                sto.job_number, 
                sto.area, 
                sto.material_description, 
                sto.updated_b AS updated_at,
                p.plant, 
                p.process
            ');
            $builder->join('master_data_trial p', 'p.part_number = sto.part_number AND p.area = sto.area', 'inner');
            $builder->where('sto.nik_b', $nik);
            $builder->where('sto.active', true);
            $builder->orderBy('sto.updated_b', 'DESC');
        } else {
            return [];
        }
        
        $query = $builder->get();
        return $query->getResultArray(); 
    }
    
    public function get_all($data)
    {
        $builder = $this->db->table($this->table_sto);
        $builder->select("*");
        
        if ($data !== 'ALL') {
            $builder->where("no_tag", $data);
        }

        $query = $builder->get();
        return $query->getResultArray();
    }

    public function updateQtyByTagSto($tag_sto, $qty, $group, $nik)
    {
        date_default_timezone_set('Asia/Jakarta');
        $builder = $this->db->table($this->table_sto_new);
        $existingData = $builder->where('id_tag', $tag_sto)->get()->getRow();

        if (!$existingData) {
            return false;
        }

        if ($group === 'A') {
            if (!empty($existingData->qty_a)) {
                return 'filled';
            }
            $updateData = [
                'qty_a'      => $qty,
                'nik_a'      => $nik,
                'updated_a'  => date('Y-m-d H:i:s'),
            ];
        } elseif ($group === 'B') {
            if (!empty($existingData->qty_b)) {
                return 'filled';
            }
            $updateData = [
                'qty_b'      => $qty,
                'nik_b'      => $nik,
                'updated_b'  => date('Y-m-d H:i:s'),
            ];
        } else {
            return false; 
        }

        return $builder->where('id_tag', $tag_sto)->update($updateData);
    }


    public function revQtyByTagSto($id_tag, $qty, $group, $nik)
    {
        date_default_timezone_set('Asia/Jakarta');
        $builder = $this->db->table($this->table_sto_new);
        $existingData = $builder->where('id_tag', $id_tag)->get()->getRow();

        if (!$existingData) {
            return false;
        }

        if ($group === 'A') {
            $updateData = [
                'qty_a'      => $qty,
                'nik_a'      => $nik,
                'updated_a'  => date('Y-m-d H:i:s'),
            ];
        } elseif ($group === 'B') {
            $updateData = [
                'qty_b'      => $qty,
                'nik_b'      => $nik,
                'updated_b'  => date('Y-m-d H:i:s'),
            ];
        } else {
            return false; 
        }

        return $builder->where('id_tag', $id_tag)->update($updateData);
    }

    public function getDataArea($data)
    {
        $builder = $this->db->table($this->table_master_trial);
        $builder->select('area, part_number');

        if ($data) {
            $builder->like('area', $data);
        }

        $builder->groupBy('area');
        $users = $builder->get()->getResultArray();
        return $users;
    }

    public function getDataAddress($search = null, $part = null)
    {
        $builder = $this->db->table($this->table_master);
        $builder->select('address, part_number');

        if (!empty($part)) {
            $builder->where('part_number', $part);
        }
        if (!empty($search)) {
            $builder->like('address', $search);
        }
        $builder->groupBy('address');
        $result = $builder->get()->getResultArray();

        return $result;
    }

    public function getDataJobNumber($search = null, $part = null)
    {
        $builder = $this->db->table($this->table_master);
        $builder->select('job_number, part_number');

        if (!empty($part)) {
            $builder->where('part_number', $part);
        }
        if (!empty($search)) {
            $builder->like('job_number', $search);
        }
        $builder->groupBy('job_number');
        $result = $builder->get()->getResultArray();

        return $result;
    }

    public function getDataPartNumber($search = null, $area = null)
    {
        $builder = $this->db->table($this->table_master);
        $builder->select('*');

        if (!empty($area)) {
            $builder->where('area', $area);
        }
        if (!empty($search)) {
            $builder->like('part_number', $search);
        }
        $builder->groupBy('part_number');
        $result = $builder->get()->getResultArray();

        return $result;
    }
    public function getDataPartNumberTrial($search = null, $area = null)
    {
        $builder = $this->db->table($this->table_master_trial);
        $builder->select('*');

        if (!empty($area)) {
            $builder->where('area', $area);
        }
        if (!empty($search)) {
            $builder->like('part_number', $search);
        }
        $builder->groupBy('part_number');
        $result = $builder->get()->getResultArray();

        return $result;
    }
    public function getDataPartJobNumber($search = null, $area = null)
    {
        $builder = $this->db->table($this->table_master_trial);
        $builder->select('*');

        $builder->where('area', $area);
        if (!empty($search)) {
            $builder->groupStart()
                ->like('part_number', $search)
                ->orLike('job_number', $search)
                ->groupEnd();
        }
        $builder->groupBy('part_number');
        $result = $builder->get()->getResultArray();

        return $result;
    }
    public function partNumberFlutter()
    {
        $builder = $this->db->table($this->table_master);
        $builder->select('*');
        $builder->groupBy('part_number');
        $result = $builder->get()->getResultArray();

        return $result;
    }

    public function getDetailByPartNumber($partNumber, $area)
    {
        return $this->db->table($this->table_master_trial)
            ->where('part_number', $partNumber)
            ->where('area', $area)
            ->get()
            ->getRowArray();
    }

    public function getDataPartDesc($search = null, $part = null)
    {
        $builder = $this->db->table($this->table_master);
        $builder->select('material_description, part_number');

        if (!empty($part)) {
            $builder->where('part_number', $part);
        }
        if (!empty($search)) {
            $builder->like('material_description', $search);
        }
        $builder->groupBy('material_description');
        $result = $builder->get()->getResultArray();

        return $result;
    }

    public function getDataType($search = null, $address = null)
    {
        $builder = $this->db->table($this->table_master);
        $builder->select('type, address');

        if (!empty($address)) {
            $builder->where('address', $address);
        }
        if (!empty($search)) {
            $builder->like('type', $search);
        }
        $builder->groupBy('type');
        $result = $builder->get()->getResultArray();

        return $result;
    }


    public function insertDataPartNumber($data)
    {
        return $this->db->table($this->table_master)->insertBatch($data);
    }

    public function findByAreaPartOrJob($area, $part_number, $job_number)
    {
        // var_dump($area, $part_number, $job_number);
        $result =  $this->db->table($this->table_master_trial)->where('area', $area)
            ->groupStart()
                ->where('part_number', $part_number)
                ->orWhere('job_number', $job_number)
            ->groupEnd()
            ->get()
            ->getRowArray();
        // var_dump($this->db->getLastQuery());
        // var_dump($result);
        return $result;
    }

}