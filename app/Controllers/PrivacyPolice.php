<?php

namespace App\Controllers;

use CodeIgniter\Controller;

use \IonAuth\Libraries\IonAuth;
use Config\Menu;
use App\Models\M_curl;
use App\Models\M_auth;

class PrivacyPolice extends BaseController
{
    protected $ionAuth;
    protected $data = [];

    public function __construct()
    {
        $model = new IonAuth();
        $this->ionAuth = $model;
    }

    public function index()
    {
        $data['title'] = 'Dashboard';
        $data['ionAuth'] = $this->ionAuth;
        
        return $this->_render_page('privacy_police/police', $data);
    }

    public function justRead()
    {
        $data['title'] = 'Dashboard';
        $data['ionAuth'] = $this->ionAuth;
        
        return view('privacy_police/read_privacy_policy', $data);
    }

    public function accept()
    {
        $user = $this->ionAuth->user()->row();

        $model = new M_auth();
        $model->updatePrivacyPolicy($user->id);
        $permissions = unserialize($user->permission);
        
        $redirect_url = base_url('dashboard');

        foreach ($permissions as $permission) {
            if ($permission === 'Module.View.DashboarAdmin') {
                $redirect_url = base_url('dashboard/admin');
                break;
            } elseif ($permission === 'Module.View.DashboarVendor') {
                $redirect_url = base_url('dashboard/vendor');
                break;
            } elseif ($permission === 'Module.View.DashboarMaj') {
                $redirect_url = base_url('dashboard/maj');
                break;
            }
        }
        return $this->response->setJSON(['redirect_url' => $redirect_url]);
    }

}
