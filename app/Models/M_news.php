<?php

namespace App\Models;
 
use CodeIgniter\Model;

class M_news extends Model
{
    protected $table = 'news';
    protected $primaryKey = 'id';
    protected $allowedFields = ['title', 'content', 'path_image'];
}