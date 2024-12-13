<?php 

namespace App\Controllers;

use App\Models\receiving_model;
use App\Models\dokumen_ok_model;

class IndexedStatus extends BaseController
{
    public function receiving()
    {
        $m_receive = new receiving_model();
        $data['unreceive'] = $m_receive->getUnreceivedDropbox();
        return $this->response->setJSON($data);
    }

    public function document_ok()
    {
        $m_document_ok = new dokumen_ok_model();
        $data['unoke'] = $m_document_ok->getUnOkeDocument();
        return $this->response->setJSON($data);
    }
}