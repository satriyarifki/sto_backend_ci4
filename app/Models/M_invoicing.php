<?php

namespace App\Models;

use CodeIgniter\Model;

class M_invoicing extends Model
{
    protected $table = 'invoice';
    protected $table1 = 'gr';
    protected $table2 = 'dropbox';
    protected $primaryKey = 'id'; 
    protected $primaryKey1 = 'invoicing_id'; 
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    public $running_id = "INV";

    public function get_running_id()
    {
        $uniqid_record = $this->db->table('uniq_id_buffer')
                        ->select('Uniqid, Date')
                        ->where('Name', 'invoice')
                        ->get()
                        ->getRowArray();

        $today_formatted = date('Ymd');

        if ($uniqid_record) {
            $record_date = date('Ymd', strtotime($uniqid_record['Date']));

            if ($record_date !== $today_formatted) {
                $next_id_number = 0;
                $this->db->table('uniq_id_buffer')
                    ->where('Name', 'invoice')
                    ->update(['Uniqid' => $next_id_number, 'Date' => date('Y-m-d')]);
            } else {
                $next_id_number = intval($uniqid_record['Uniqid']) + 1;
                $this->db->table('uniq_id_buffer')
                    ->where('Name', 'invoice')
                    ->update(['Uniqid' => $next_id_number]);
            }

            $next_id = sprintf("%04d", $next_id_number);
            $next_invoice_id = $this->running_id . $today_formatted . $next_id;

            return $next_invoice_id;
        } else {
            throw new \Exception('Record dengan Name = "invoice" tidak ditemukan di tabel uniq_id_buffer.');
        }
    }

    public function getData($startDate = null, $endDate = null)
    {
        $startDate = ($startDate === '') ? null : $startDate;
        $endDate = ($endDate === '') ? null : $endDate;
        
        if ($startDate !== null && $endDate !== null) {
            return $this->where('del_note_date >=', $startDate)
                        ->where('del_note_date <=', $endDate)
                        ->findAll();
        } else {
            return $this->findAll();
        }
    }

    public function getGRbyID($id)
    {
        return $this->find($id);
    }

    public function updateGRData($id, $data)
    {
        return $this->update($id, $data);
    }

    public function insert_invoice($data)
    {
        return $this->db->table('invoice')->insert($data);
    }

    public function insert_arsip_pdf($data)
    {
        return $this->db->table('arsip_pdf')->insert($data);
    }

    public function getReceiving($id = false){
        if ($id === false){
            return $this->db->table($this->table2)->get()->getResult();
        }else{
            return $this->db->table($this->table2)->getWhere(['id' => $id]);
        }
    }
    
    public function editReceiving($status_receive, $id){
        $builder = $this->db->table($this->table2);
        $data = ['status_receive' => $status_receive];
        if ($status_receive == 'y') {
            $data['date_approve'] = date("Y-m-d"); // Tambahkan tanggal saat ini jika status_receive disetujui
        }else {
            $data['date_approve'] = null; // Kosongkan date_approve jika status_receive tidak disetujui
        }
        $builder->set($data);
        $builder->where('id', $id);
        return $builder->update();
    }

    public function updateStatusReceive($id, $status)
    {
        if ($status == 'y') {
            return $this->editReceiving('', $id);
        } else {
            return $this->editReceiving('y', $id);
        }
    }

    public function reachstatusdropbox($dropbox)
    {
        ini_set('memory_limit', '256M');
        $query = $this->select('*')
                    ->from($this->table2)
                    ->where('dropbox_id', $dropbox)
                    ->get();

        if ($query->getNumRows() > 0) {
            $result = $query->getRow();

            $data = [
                'dropbox_id' => $result->dropbox_id,
                'expired' => $result->expired,
                'status_receive' => $result->status_receive,
                'date_approve' => $result->date_approve,
                'onhold' => $result->onhold,
                'onhold_datetime' => $result->onhold_datetime,
                'dok_ok' => $result->dok_ok,
                'date_dok_ok' => $result->date_dok_ok
            ];
            return $data;
        } else {
            return null;
        }
    }

    public function reachstatusgr($gr, $item, $po)
    {
        $query = $this->db->query("
            SELECT 
                d.dropbox_id, d.expired, d.status_receive, d.onhold, d.delete_onhold, 
                d.dok_ok, d.in_pud, d.delete_pud, d.out_pud, d.status_miro, d.date_miro
            FROM 
                invoice i
            JOIN 
                dropbox d ON i.invoicing_id = d.invoicing_id
            WHERE 
                i.no_gr = '$gr' AND i.no_item = '$item' AND i.no_po = '$po' AND d.expired = ''
            
            UNION
            
            SELECT 
                d.dropbox_id, d.expired, d.status_receive, d.onhold, d.delete_onhold, 
                d.dok_ok, d.in_pud, d.delete_pud, d.out_pud, d.status_miro, d.date_miro
            FROM 
                invoice_npkp n
            JOIN 
                dropbox d ON n.invoicing_id = d.invoicing_id
            WHERE 
                n.no_gr = '$gr' AND n.no_item = '$item' AND n.no_po = '$po' AND d.expired = ''
            
            UNION
            
            SELECT 
                d.dropbox_id, d.expired, d.status_receive, d.onhold, d.delete_onhold, 
                d.dok_ok, d.in_pud, d.delete_pud, d.out_pud, d.status_miro, d.date_miro
            FROM 
                invoice_mgl m
            JOIN 
                dropbox d ON m.invoicing_id = d.invoicing_id
            WHERE 
                m.no_gr = '$gr' AND m.no_item = '$item' AND m.no_po = '$po' AND d.expired = ''
        ");

        if ($query->getNumRows() > 0) {
            $result = $query->getRow();
            $data['dropbox_id'] = $result->dropbox_id;
            $data['expired'] = $result->expired;
            $data['status_receive'] = $result->status_receive;
            $data['onhold'] = $result->onhold;
            $data['dok_ok'] = $result->dok_ok;
            $data['status_miro'] = $result->status_miro;
            $data['date_miro'] = $result->date_miro;
            $data['delete_onhold'] = $result->delete_onhold;
            $data['delete_pud'] = $result->delete_pud;
            return $data;
        } else {
            return null;
        }
    }


    public function getRegDropBox($id = false)
    {
        if ($id === false) {
            return $this->distinct()->groupBy('invoicing_id')->where('status_generate', '')->findAll();
        } else {
            return $this->getWhere(['id' => $id]);
        }
    }

    public function getDataBasedDate($dateLow, $dateHigh, $invoiceNumber, $vendor_code)
    {
        $builder = $this->db->table('invoice')
			->select('invoicing_id,
                MAX(no_invoice) as no_invoice,
				MAX(user_create) as user_create,
				MAX(company_name) as company_name,
				MAX(create_date) as create_date,
				MAX(total_payment) as total_payment,
				MAX(npwp) as npwp,
				MAX(tax_number) as tax_number,
				MAX(tax_date) as tax_date');
        
        if ($dateLow !== null && $dateHigh !== null) {
            $builder->where("DATE(create_date) BETWEEN '$dateLow' AND '$dateHigh'");
        } elseif ($dateLow !== null) {
            $builder->where("DATE(create_date)", $dateLow);
        } elseif ($dateHigh !== null) {
            $builder->where("DATE(create_date)", $dateHigh);
        }

        if ($invoiceNumber !== null) {
            $builder->where('no_invoice', $invoiceNumber);
        }

        $builder->where('status_generate !=', 'X');
        $builder->where('approve', '');
        $builder->where('user_create', $vendor_code);
        $builder->groupBy('invoicing_id');
        return $builder->get()->getResult();
    }

    public function getDataUnderProcess($dateLow, $dateHigh, $poNumber, $gr_number, $vendorCode)
    {
        $builder = $this->db->table('invoice');
        
        $builder->select('invoicing_id, no_po, no_gr, no_item, no_invoice, create_date, user_create')
                ->where('user_create', $vendorCode);

        if ($dateLow !== null && $dateHigh !== null) {
            $builder->where("DATE(create_date) BETWEEN '$dateLow' AND '$dateHigh'");
        } elseif ($dateLow !== null) {
            $builder->where("DATE(create_date)", $dateLow);
        } elseif ($dateHigh !== null) {
            $builder->where("DATE(create_date)", $dateHigh);
        }

        if ($poNumber !== null) {
            $builder->where('no_po', $poNumber);
        }

        if ($gr_number !== null) {
            $builder->where('no_gr', $gr_number);
        }
        
        $queryInvoice = $builder->getCompiledSelect();

        $builderNpkp = $this->db->table('invoice_npkp');
        $builderNpkp->select('invoicing_id, no_po, no_gr, no_item, no_invoice, create_date, user_create')
                    ->where('user_create', $vendorCode);

        if ($dateLow !== null && $dateHigh !== null) {
            $builderNpkp->where("DATE(create_date) BETWEEN '$dateLow' AND '$dateHigh'");
        } elseif ($dateLow !== null) {
            $builderNpkp->where("DATE(create_date)", $dateLow);
        } elseif ($dateHigh !== null) {
            $builderNpkp->where("DATE(create_date)", $dateHigh);
        }

        if ($poNumber !== null) {
            $builderNpkp->where('no_po', $poNumber);
        }

        if ($gr_number !== null) {
            $builderNpkp->where('no_gr', $gr_number);
        }

        $queryNpkp = $builderNpkp->getCompiledSelect();

        $builderMgl = $this->db->table('invoice_mgl');
        $builderMgl->select('invoicing_id, no_po, no_gr, no_item, no_invoice, create_date, user_create')
                ->where('user_create', $vendorCode);

        if ($dateLow !== null && $dateHigh !== null) {
            $builderMgl->where("DATE(create_date) BETWEEN '$dateLow' AND '$dateHigh'");
        } elseif ($dateLow !== null) {
            $builderMgl->where("DATE(create_date)", $dateLow);
        } elseif ($dateHigh !== null) {
            $builderMgl->where("DATE(create_date)", $dateHigh);
        }

        if ($poNumber !== null) {
            $builderMgl->where('no_po', $poNumber);
        }

        if ($gr_number !== null) {
            $builderMgl->where('no_gr', $gr_number);
        }

        $queryMgl = $builderMgl->getCompiledSelect();
        $finalQuery = $this->db->query($queryInvoice . " UNION ALL " . $queryNpkp . " UNION ALL " . $queryMgl);
        
        return $finalQuery->getResult();
    }

}