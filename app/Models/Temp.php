<?php

namespace App\Models;
use CodeIgniter\Model;

class Temp extends Model
{
    protected $table      = 'users';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;
    protected $allowedFields = ['username', 'email', 'status'];
}