<?php

namespace App\Models;

use CodeIgniter\Model;

class M_auth extends Model
{
    protected $table = 'users'; // Gantilah 'nama_tabel' dengan nama tabel sebenarnya
    protected $table1 = 'purchasing_group';
    protected $primaryKey = 'id'; // Gantilah 'id' dengan primary key tabel Anda
    protected $returnType     = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = ['username', 'password', 'company_title', 'company_name', 'npwp_number', 'abbreviated_name', 'abbreviated_supplier', 
    'estabilished_date', 'company_website', 'supplier_category', 'vendor_code', 'join_date', 'supplier_group', 'official_letter_attachment', 'country',
    'provience', 'city', 'zip_code', 'company_phone_number', 'company_fax_number', 'logo_attachment', 'capital', 'asset_value',
    'supplier_affiliation', 'company_clasification', 'technical_assistant', 'start_operation_date', 'currency', 'cp_username', 'cp_name',
    'cp_number', 'cp_title', 'cp_email1', 'cp_email2', 'address']; // Sesuaikan dengan kolom-kolom tabel Anda

    public function generateId()
    {
        // Mendapatkan ID terakhir dari tabel
        $lastId = $this->select('id')->orderBy('id', 'DESC')->first();

        // Jika tidak ada data, mulai dari ID 1
        if (!$lastId) {
            return 1;
        }

        // Menghasilkan ID baru dengan menambahkan 1 ke ID terakhir
        return $lastId['id'] + 1;
    }

    private function check_exist($data, $id = null)
    {
        $builder = $this->db->table($this->table);

        if ($id != null) {
            $builder->where($this->key . " != ", $id);
        }

        $builder->where('username', $data['username']);
        $this->exist = $builder->countAllResults();

        if ($this->exist) {
            return true;
        }

        return false;
    }

    public function datainsert($data)
    {
        // if ($data === null) {
        //     return false;
        // } else {
        //     if ($this->check_exist($data)) {
        //         return false;
        //     }

        //     $this->db->transStart();
        //     $this->db->table($this->table)->insert($data);

        //     if ($this->db->transStatus() === false) {
        //         $this->db->transRollback();
        //         return false;
        //     } else {
        //         $this->db->transCommit();
        //         return true;
        //     }
        // }
        if ($data === null) {
                return false;
        } else {
            $this->db->table($this->table)->insert($data);
        }

    }

    public function insertUserGroup($data)
    {
        if ($data === null) {
            return false;
        } else {
            $this->db->table('users_groups')->insert($data);
        }
    }

    public function data_user()
    {
        return $this->findAll();
    }

    public function query_user()
    {
        $builder = $this->db->table($this->table);
        $builder->select('users.*');
        $builder->join('users_groups', 'users.id = users_groups.user_id');
        $builder->join('groups', 'users_groups.group_id = groups.id');
        $builder->where('groups.name !=', 'admin');
        
        $query = $builder->get();
        return $query->getResult();
    }

    public function getPermissionById($userId)
    {
        $result = $this->select('permission')
                       ->where('id', $userId)
                       ->first();
        if ($result && isset($result['permission'])) {
            $result['permission'] = unserialize($result['permission']);
        }
        return $result;
    }

    public function updatePermissions($userId, $permissions)
    {
        $serializedPermissions = serialize($permissions);
        $data = [
            'permission' => $serializedPermissions,
        ];
        $result = $this->db->table($this->table)
                ->where('id', $userId)
                ->update($data);
        return $result;
    }

    public function getvendor($search = null)
    {
        $builder = $this->db->table($this->table);
        $builder->select('users.vendor_code, users.company_name');
        $builder->join('users_groups', 'users_groups.user_id = users.id', 'left');
        $builder->where('users_groups.group_id', 3);

        if ($search) {
            $builder->like('users.company_name', $search);
        }

        $users = $builder->get()->getResultArray();
        return $users;
    }

    public function updatePrivacyPolicy($userId)
    {
        $data = [
            'privacy_police' => 1,
        ];
        $result = $this->db->table($this->table)
                ->where('id', $userId)
                ->update($data);
                
        return $result;
    }
}