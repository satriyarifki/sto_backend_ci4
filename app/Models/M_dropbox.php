<?php

namespace App\Models;

use CodeIgniter\Model;

class M_dropbox extends Model
{
    protected $table = 'dropbox'; 
    protected $table1 = 'invoice';
    protected $table2 = 'invoice_npkp';
    protected $table3 = 'invoice_mgl';
    protected $table_user = 'users';
    public $running_id = "DBP";

    public function sendData($checkedItems)
    {
        return $this->db->table('dropbox')->insert($data);
    }

    public function get_running_id()
    {
        $uniqid_record = $this->db->table('uniq_id_buffer')
                        ->select('Uniqid, Date')
                        ->where('Name', 'dropbox')
                        ->get()
                        ->getRowArray();

        $today_formatted = date('Ymd');

        if ($uniqid_record) {
            $record_date = date('Ymd', strtotime($uniqid_record['Date']));

            if ($record_date !== $today_formatted) {
                $next_id_number = 0;
                $this->db->table('uniq_id_buffer')
                    ->where('Name', 'dropbox')
                    ->update(['Uniqid' => $next_id_number, 'Date' => date('Y-m-d')]);
            } else {
                // Ambil nilai Uniqid saat ini dan increment
                $next_id_number = intval($uniqid_record['Uniqid']) + 1;
                $this->db->table('uniq_id_buffer')
                    ->where('Name', 'dropbox')
                    ->update(['Uniqid' => $next_id_number]);
            }

            $next_id = sprintf("%04d", $next_id_number);
            $next_invoice_id = $this->running_id . $today_formatted . $next_id;

            return $next_invoice_id;
        } else {
            throw new \Exception('Record dengan Name = "dropbox" tidak ditemukan di tabel uniq_id_buffer.');
        }
    }

    public function archieve_update_pud($dropbox_id = null)
    {
        if ($dropbox_id) {
            $currentDate = date('Y-m-d');
            $currentTime = date('H:i:s');

            $this->db->table($this->table)
                ->where('dropbox_id', $dropbox_id)
                ->update([
                    'status_archieve_pud' => 'Y',
                    'date_archieve_pud' => $currentDate,
                    'time_archieve_pud' => $currentTime
                ]);
            return true; 
        }
        return false; 
    }

    public function archieve_update_finance($no_invoice = null)
    {
        if ($no_invoice) {
            $currentDate = date('Y-m-d');
            $currentTime = date('H:i:s');

            $this->db->table($this->table)
                ->where('no_invoice', $no_invoice)
                ->where('active', '')
                ->where('onhold', 'N')
                ->where('expired', '')
                ->update([
                    'status_archieve_finance' => 'Y',
                    'date_archieve_finance' => $currentDate,
                    'time_archieve_finance' => $currentTime
                ]);
            return true; 
        }
        return false; 
    }

    public function isDuplicateActiveData($dropbox_id, $no_invoice)
    {
        return $this->where('dropbox_id', $dropbox_id)
                    ->where('no_invoice', $no_invoice)
                    ->where('expired', '') 
                    ->where('onhold', 'N') 
                    ->where('active', '')
                    ->countAllResults() > 0;
    }

    public function getAll($date = null, $miro = null, $onhold = null, $dropboxId = null, $noInvoice = null)
    {
        $builder = $this->db->table($this->table);
        $builder->select('dropbox.*, total_payments.total_payment');
        $builder->where('expired', '');

        if ($date) {
            $builder->where('DATE_FORMAT(generated_date, "%Y-%m")', date('Y-m', strtotime($date)));
        } else {
            $builder->where('DATE_FORMAT(generated_date, "%Y-%m")', date('Y-m'));
        }

        if ($miro) {
            $builder->where('status_miro', 'Y');
        } else {
            $builder->where('status_miro', 'N');
        }

        if ($onhold) {
            $builder->where('onhold', 'Y');
        } else {
            $builder->where('onhold', 'N');
        }

        if ($dropboxId) {
            $builder->where('dropbox_id', $dropboxId);
            $builder->where('status_receive', 'Y');
        }

        if ($noInvoice) {
            $builder->where('no_invoice', $noInvoice);
            $builder->where('out_pud', 'Y');
        }

        $subquery = "(SELECT invoicing_id, total_payment 
                        FROM {$this->table1} 
                    UNION 
                    SELECT invoicing_id, total_payment 
                        FROM {$this->table2} 
                    UNION 
                    SELECT invoicing_id, total_payment 
                        FROM {$this->table3}
                    ) as total_payments";

        $builder->join($subquery, 'dropbox.invoicing_id = total_payments.invoicing_id', 'left');
        $builder->groupBy('dropbox.invoicing_id');

        $query = $builder->get();
        return $query->getResult();
    }


    public function DropboxGet($dropboxId)
    {
        $builder = $this->db->table($this->table);
        $builder->select('dropbox.*, total_payments.total_payment');
        $builder->where('expired', '');
        $builder->where('dropbox_id', $dropboxId);
        $builder->where('status_receive', 'Y');

        $subquery = "(SELECT invoicing_id, total_payment 
                        FROM {$this->table1} 
                    UNION 
                    SELECT invoicing_id, total_payment 
                        FROM {$this->table2} 
                    UNION 
                    SELECT invoicing_id, total_payment 
                        FROM {$this->table3}
                    ) as total_payments";

        $builder->join($subquery, 'dropbox.invoicing_id = total_payments.invoicing_id', 'left');
        $builder->groupBy('dropbox.invoicing_id');

        $query = $builder->get();
        return $query->getResult();
    }

    public function getInvoice($noInvoice)
    {
        $builder = $this->db->table($this->table);
        $builder->select('dropbox.*, total_payments.total_payment');
        $builder->where('expired', '');
        $builder->where('onhold', 'N');
        $builder->where('active', '');
        $builder->where('no_invoice', $noInvoice);
        $builder->where('out_pud', 'Y');

        $subquery = "(SELECT invoicing_id, total_payment 
                        FROM {$this->table1} 
                    UNION 
                    SELECT invoicing_id, total_payment 
                        FROM {$this->table2} 
                    UNION 
                    SELECT invoicing_id, total_payment 
                        FROM {$this->table3}
                    ) as total_payments";

        $builder->join($subquery, 'dropbox.invoicing_id = total_payments.invoicing_id', 'left');
        $builder->groupBy('dropbox.invoicing_id');

        $query = $builder->get();
        return $query->getResult();
    }

    public function insertDropbox($data)
    {
        $invoicing_id = $data['invoicing_id'];
        $this->db->table('invoice')
                ->where('invoicing_id', $invoicing_id)
                ->update(['status_generate' => 'X']);
        return $this->db->table('dropbox')->insert($data);
    }

    public function getDropBox()
    {
        return $this->distinct()->groupBy('dropbox_id')->findAll();
    }

    public function getInvoicing($dropboxFilter)
    {
        return $this->select('invoicing_id')->where('dropbox_id', $dropboxFilter)->findAll();
    }

    public function insertDropboxnpkp($data)
    {
        $invoicing_id = $data['invoicing_id'];
        $this->db->table('invoice_npkp')
                ->where('invoicing_id', $invoicing_id)
                ->update(['status_generate' => 'X']);
        return $this->db->table('dropbox')->insert($data);
    }

    public function insertDropboxmgl($data)
    {
        $invoicing_id = $data['invoicing_id'];
        $this->db->table('invoice_mgl')
                ->where('invoicing_id', $invoicing_id)
                ->update(['status_generate' => 'X']);
        return $this->db->table('dropbox')->insert($data);
    }

    public function getDataBasedDate($dateLow, $dateHigh, $vendor_code)
    {
        if ($dateLow !== null && $dateHigh !== null) {
            $query = $this->db->table($this->table)
                    ->select('dropbox_id, MAX(generated_date) as generated_date, MAX(generated_time) as generated_time, MAX(user_generate) as user_generate, MAX(company_name) as company_name') 
                    ->where('generated_date >=', $dateLow)
                    ->where('generated_date <=', $dateHigh)
                    ->where('user_generate', $vendor_code)
                    ->groupBy('dropbox_id')
                    ->get();
            return $query->getResultArray();
        } elseif ($dateLow !== null) {
            $query = $this->db->table($this->table)
                    ->select('dropbox_id, MAX(generated_date) as generated_date, MAX(generated_time) as generated_time, MAX(user_generate) as user_generate, MAX(company_name) as company_name') 
                    ->where('generated_date =', $dateLow)
                    ->where('user_generate', $vendor_code)
                    ->groupBy('dropbox_id')
                    ->get();
            return $query->getResultArray();
        } elseif ($dateHigh !== null) {
            $query = $this->db->table($this->table)
                    ->select('dropbox_id, MAX(generated_date) as generated_date, MAX(generated_time) as generated_time, MAX(user_generate) as user_generate, MAX(company_name) as company_name') 
                    ->where('generated_date =', $dateHigh)
                    ->where('user_generate', $vendor_code)
                    ->groupBy('dropbox_id')
                    ->get();
            return $query->getResultArray();
        } else {
            return [];
        }
    }

    public function getDataBasedID($data)
    {
        $query = $this->db->table($this->table)
                ->select($this->table.'.dropbox_id, '.$this->table1.'.total_payment, '.$this->table1.'.company_name, '.$this->table1.'.no_gr, '.$this->table1.'.no_item, '.$this->table1.'.no_po, '.$this->table1.'.invoicing_id, '.$this->table1.'.no_invoice') 
                ->join($this->table1, $this->table.'.invoicing_id = '.$this->table1.'.invoicing_id')
                ->where($this->table.'.dropbox_id', $data)
                ->get();
        return $query->getResultArray();
    }

    public function getDataBasedIDNpkp($data)
    {
        $query = $this->db->table($this->table)
                ->select($this->table.'.dropbox_id, '.$this->table2.'.total_payment, '.$this->table2.'.company_name, '.$this->table2.'.no_gr, '.$this->table2.'.no_item, '.$this->table2.'.no_po, '.$this->table2.'.invoicing_id, '.$this->table2.'.no_invoice') 
                ->join($this->table2, $this->table.'.invoicing_id = '.$this->table2.'.invoicing_id')
                ->where($this->table.'.dropbox_id', $data)
                ->get();
        return $query->getResultArray();
    }

    public function getDataBasedIDMgl($data)
    {
        $query = $this->db->table($this->table)
                ->select($this->table.'.dropbox_id, '.$this->table3.'.total_payment, '.$this->table3.'.company_name, '.$this->table3.'.no_gr, '.$this->table3.'.no_item, '.$this->table3.'.no_po, '.$this->table3.'.invoicing_id, '.$this->table3.'.no_invoice') 
                ->join($this->table3, $this->table.'.invoicing_id = '.$this->table3.'.invoicing_id')
                ->where($this->table.'.dropbox_id', $data)
                ->get();
        return $query->getResultArray();
    }

    public function getDataforZinver($noInvoice, $noDropbox)
    {
        $builder = $this->db->table($this->table);
        $builder->select($this->table.'.dropbox_id, '.$this->table.'.progress, '.$this->table.'.qcd, '.$this->table.'.user_generate, '.$this->table.'.no_zinver, '.$this->table.'.date_approve, '.$this->table.'.deadline, '.$this->table.'.print, '.$this->table.'.company_name,
            COALESCE(invoice.invoicing_id, invoice_npkp.invoicing_id, invoice_mgl.invoicing_id) as invoicing_id,
            COALESCE(invoice.no_invoice, invoice_npkp.no_invoice, invoice_mgl.no_invoice) as no_invoice,
            COALESCE(invoice.company_name, invoice_npkp.company_name, invoice_mgl.company_name) as company_name,
            COALESCE(invoice.total_payment, invoice_npkp.total_payment, invoice_mgl.total_payment) as total_payment,
            COALESCE(invoice.no_po, invoice_npkp.no_po, invoice_mgl.no_po) as no_po,
            COALESCE(invoice.tax_date, invoice_npkp.date_invoice, invoice_mgl.date_invoice) as tax_date,
            '.$this->table1.'.create_date');
        
        $builder->join('invoice', $this->table.'.invoicing_id = invoice.invoicing_id', 'left');
        $builder->join('invoice_npkp', $this->table.'.invoicing_id = invoice_npkp.invoicing_id', 'left');
        $builder->join('invoice_mgl', $this->table.'.invoicing_id = invoice_mgl.invoicing_id', 'left');

        $builder->where($this->table.'.no_invoice', $noInvoice);
        $builder->where($this->table.'.dropbox_id', $noDropbox);
        
        $builder->groupBy($this->table.'.no_invoice, '.$this->table.'.invoicing_id');
        
        $query = $builder->get();
        return $query->getResultArray();
    }

    public function updateDataZinver($invoicing_id, $no_zinver, $deadline, $progress, $qcd, $nik)
    {
        $data = [
            'in_pud' => 'Y',
            'date_in_pud' => date('Y-m-d'),
            'user_create_in_pud' => $nik,
            'no_zinver' => $no_zinver,
            'deadline' => $deadline,
            'progress' => $progress,
            'qcd' => $qcd
        ];

        return $this->db->table($this->table)
                        ->where('invoicing_id', $invoicing_id)
                        ->update($data);
    }

    public function deleteDataZinver($invoicing_id)
    {
        $data = [
            'in_pud' => 'N',
            'date_in_pud' => NULL,
            'user_create_in_pud' => NULL,
            'no_zinver' => '',
            'print' => 0
        ];
        return $this->db->table($this->table)
                        ->where('invoicing_id', $invoicing_id)
                        ->update($data);
    }
    
    public function OutPudDataZinver($invoicing_id, $nik)
    {
        $data = [
            'out_pud' => 'Y',
            'date_out_pud' => date('Y-m-d'),
            'user_create_out_pud' => $nik,
        ];
        return $this->db->table($this->table)
                        ->where('invoicing_id', $invoicing_id)
                        ->update($data);
    }

    public function getDataMiro()
    {
        $builder = $this->db->table($this->table);
        $builder->select('dropbox.dropbox_id, dropbox.dok_ok, dropbox.date_dok_ok, dropbox.time_dok_ok, dropbox.date_approve,
                        invoice.invoicing_id, invoice.npwp, invoice.create_date, invoice.tax_number, invoice.company_name, invoice.no_invoice, invoice.total_payment');
        $builder->join('invoice', 'dropbox.invoicing_id = invoice.invoicing_id');
        $builder->where('dropbox.status_receive !=', '');
        $query = $builder->get();
        $results = [];
        foreach ($query->getResultArray() as $row) {
            $dropboxId = $row['dropbox_id'];
            $invoicingId = $row['invoicing_id'];

            if (!isset($results[$dropboxId])) {
                $results[$dropboxId] = [
                    'dropbox_id' => $row['dropbox_id'],
                    'date' => $row['date_dok_ok'],
                    'dok_ok' => $row['dok_ok'],
                    'time_dok_ok' => $row['time_dok_ok'],
                    'invoices' => []
                ];
            }

            if (!isset($results[$dropboxId]['invoices'][$invoicingId])) {
                $results[$dropboxId]['invoices'][$invoicingId] = [
                    'invoicing_id' => $row['invoicing_id'],
                    'no_invoice' => $row['no_invoice'],
                    'npwp' => $row['npwp'],
                    'create_date' => $row['create_date'],
                    'date_approve' => $row['date_approve'],
                    'tax_number' => $row['tax_number'],
                    'company_name' => $row['company_name'],
                    'total_payment' => $row['total_payment']
                ];
            }
        }

        foreach ($results as &$result) {
            $result['invoices'] = array_values($result['invoices']);
        }
        return array_values($results);
    }


    public function getDropboxTrackVendor($dropbox_id = null, $vendor_code, $startDate = null, $endDate = null)
    {
        $subquery1 = $this->db->table('invoice')
            ->select('invoicing_id, no_po, no_item, no_gr')
            ->getCompiledSelect();

        $subquery2 = $this->db->table('invoice_npkp')
            ->select('invoicing_id, no_po, no_item, no_gr')
            ->getCompiledSelect();

        $subquery3 = $this->db->table('invoice_mgl')
            ->select('invoicing_id, no_po, no_item, no_gr')
            ->getCompiledSelect();

        $combinedInvoice = '(' . $subquery1 . ' UNION ALL ' . $subquery2 . ' UNION ALL ' . $subquery3 . ') AS invoice_combined';

        $builder = $this->db->table('dropbox');
        $builder->select('dropbox.dropbox_id, dropbox.generated_date, dropbox.due_date, invoice_combined.invoicing_id, invoice_combined.no_po, invoice_combined.no_item, invoice_combined.no_gr, dropbox.company_name, dropbox.no_invoice');
        $builder->join($combinedInvoice, 'dropbox.invoicing_id = invoice_combined.invoicing_id');
        $builder->where('dropbox.user_generate', $vendor_code);

        if (!is_null($dropbox_id)) {
            $builder->where('dropbox.dropbox_id', $dropbox_id);
        }
        
        if (!is_null($startDate) && !is_null($endDate)) {
            $builder->where('dropbox.generated_date >=', $startDate);
            $builder->where('dropbox.generated_date <=', $endDate);
        } elseif (!is_null($startDate)) {
            $builder->where('dropbox.generated_date', $startDate);
        } elseif (!is_null($endDate)) {
            $builder->where('dropbox.generated_date', $endDate);
        }

        $query = $builder->get();
        $results = [];

        foreach ($query->getResultArray() as $row) {
            $dropboxId = $row['dropbox_id'];
            $invoicingId = $row['invoicing_id'];

            if (!isset($results[$dropboxId])) {
                $results[$dropboxId] = [
                    'dropbox_id' => $row['dropbox_id'],
                    'generated_date' => $row['generated_date'],
                    'due_date' => $row['due_date'],
                    'company_name' => $row['company_name'],
                    'invoices' => []
                ];
            }

            if (!isset($results[$dropboxId]['invoices'][$invoicingId])) {
                $results[$dropboxId]['invoices'][$invoicingId] = [
                    'invoicing_id' => $row['invoicing_id'],
                    'no_invoice' => $row['no_invoice'],
                    'no_po' => $row['no_po'],
                    'no_item' => $row['no_item'],
                    'no_gr' => $row['no_gr'],
                ];
            }
        }

        // Ubah key associative menjadi array numerik
        foreach ($results as &$result) {
            $result['invoices'] = array_values($result['invoices']);
        }

        return array_values($results);
    }


    public function getEmailData($vendor_code)
    {
        $builder = $this->db->table($this->table_user);
        $builder->select('id, cp_email1, company_name, cp_name');
        $builder->where('vendor_code', $vendor_code);
        $query = $builder->get();
        return $query->getRow();
    }

    public function getDropboxNoMiro(){
        $builder = $this->db->table($this->table);
        $builder->where('date_miro', null);
        $builder->where('onhold', 'N');
        $builder->where('status_receive', 'Y');
	    $builder->where('expired', '');
        $builder->where('active', '');
        
        $query = $builder->get();
        return $query->getResult();
    }
    public function getDropboxMiro(){
        $builder = $this->db->table($this->table);
        $builder->where('date_miro IS NOT NULL');
        $builder->where('expired', '');
        $query = $builder->get();
        return $query->getResult();
    }

    public function getGR($invoicing_id)
    {
        $builder = $this->db->table('dropbox');

        $builder->select('
            COALESCE(invoice.no_gr, invoice_npkp.no_gr, invoice_mgl.no_gr) as no_gr,
            COALESCE(invoice.no_item, invoice_npkp.no_item, invoice_mgl.no_item) as no_item,
            COALESCE(invoice.no_po, invoice_npkp.no_po, invoice_mgl.no_po) as no_po
        ');
        
        $builder->join('invoice', 'dropbox.invoicing_id = invoice.invoicing_id', 'left');
        $builder->join('invoice_npkp', 'dropbox.invoicing_id = invoice_npkp.invoicing_id', 'left');
        $builder->join('invoice_mgl', 'dropbox.invoicing_id = invoice_mgl.invoicing_id', 'left');

        $builder->where('dropbox.invoicing_id', $invoicing_id);

        $query = $builder->get();
        return $query->getResultArray(); 
    }

}
