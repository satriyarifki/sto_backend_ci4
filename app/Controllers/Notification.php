<?php

namespace App\Controllers;

use CodeIgniter\Controller;

use \IonAuth\Libraries\IonAuth;
use Config\Menu;    
use App\Models\NotificationModel;

class Notification extends BaseController
{
    protected $ionAuth;
    protected $data = [];

    public function __construct()
    {
        $model = new IonAuth();
        $this->ionAuth = $model;
    }

    public function markAsRead()
    {
        $userId = $this->ionAuth->user()->row()->id;

        $request = \Config\Services::request();
        $id = $request->getJSON()->notificationId;

        $notificationModel = new NotificationModel();
        $notificationModel->where('id', $id)
                          ->where('user_id', $userId)
                          ->where('status', 'unread')
                          ->set(['status' => 'read'])
                          ->update();

        // Hitung jumlah notifikasi yang belum dibaca
        $unreadCount = $notificationModel->where('user_id', $userId)
                                         ->where('status', 'unread')
                                         ->countAllResults();

        return $this->response->setJSON(['notificationCount' => $unreadCount]);
    }
}
