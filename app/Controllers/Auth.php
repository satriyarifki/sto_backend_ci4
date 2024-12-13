<?php 

namespace App\Controllers;

class Auth extends \IonAuth\Controllers\Auth
{
    /**
     * If you want to customize the views,
     *  - copy the ion-auth/Views/auth folder to your Views folder,
     *  - remove comment
     */
    protected $viewsFolder = 'auth';

    public function check_permission($permission)
    {
        $current_user = $this->ionAuth->user()->row();
        $permissions = unserialize($current_user->permission);

        if (!in_array($permission, $permissions)) {
            echo view('template/error_403');
            exit;
        }
    }
}