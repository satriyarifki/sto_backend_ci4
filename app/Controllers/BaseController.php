<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use App\Models\receiving_model;
use \IonAuth\Libraries\IonAuth;
use App\Models\dokumen_ok_model;
use App\Models\miro_model;
use App\Models\NotificationModel;
// 
/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Instance of the main Request object.
     *
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var array
     */
    protected $helpers = ['number'];

    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */
    // protected $session;

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.

        // E.g.: $this->session = \Config\Services::session();
    }

    public function _render_page($view, $data = NULL, $returnhtml = FALSE)
    {
        // Logika autentikasi
        $this->ion_auth = new IonAuth();
        $this->session = \Config\Services::session();

        if (!$this->ion_auth->loggedIn()) {
            return \Config\Services::response()->setStatusCode(401)->setBody(view('template/error_401'));
        }
        if ($this->ion_auth->inGroup('admin')) {
            $data['role'] = 'admin';
        } else {
            $data['role'] = 'user';
        }

        $data['permissions'] = $this->ionAuth->user()->row()->permission;

        $userId = $this->ionAuth->user()->row()->id;
        if ($this->ionAuth->inGroup('vendor', $userId)) {
            $notificationModel = new NotificationModel();
            $notifications = $notificationModel->where('user_id', $userId)
                                                ->where('status', 'unread')
                                                ->orderBy('created_at', 'DESC')
                                                ->findAll();
            $data['notifications'] = $notifications;
            $data['notificationCount'] = count($notifications);
        }

        if (!is_file(APPPATH . 'Views/' . $view . '.php')) {
            return $this->response->setStatusCode(404)->setBody(view('template/error_404'));
        }
        echo view($view, $data);
    }

    public function check_permission($permission)
    {
        $current_user = $this->ionAuth->user()->row();

        if (!$this->ionAuth->loggedIn()) {
            return \Config\Services::response()->setStatusCode(401)->setBody(view('template/error_401'));
        }

        if ($this->ionAuth->isAdmin()) {
            return; 
        }

        $permissions = unserialize($current_user->permission);
        if (!in_array($permission, $permissions)) {
            http_response_code(403);
            echo view('template/error_403');
            exit;
        }
    }
}
