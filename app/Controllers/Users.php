<?php

namespace App\Controllers;

use \IonAuth\Libraries\IonAuth;
use \PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\M_user;
use App\Models\M_auth;
use CodeIgniter\Files\File;
use App\Models\M_curl;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as WriteClsx;
use PhpOffice\PhpSpreadsheet\Style\Conditional;

class Users extends BaseController
{
    protected $ionAuth;
    public function __construct()
    {
        $model = new IonAuth();
        $this->ionAuth = $model;
    }

    public function index()
    {
        helper('form', 'url');
        $data['ionAuth'] = $this->ionAuth;
        $data['title'] = 'Profile';
        $data['current_user'] = $this->ionAuth->user()->row();

        return $this->_render_page('user/profile/profile_view', $data);
    }

    public function update()
    {

        helper('form');
        $M_auth = new M_auth();
        $data['ionAuth'] = $this->ionAuth;
        $data['title'] = 'Edit';
        $data['current_user'] = $this->ionAuth->user()->row();
        $data['validation'] =  \Config\Services::validation();

        return $this->_render_page('user/profile/profile_edit', $data);
    }

    public function stored($id)
    {
        helper('form');
        $M_auth = new M_auth();
        $data['ionAuth'] = $this->ionAuth;
        $user = $M_auth->find($id);
        if(empty($user)){
            session()->setFlashdata('error', 'Data tidak ditemukan');
            return redirect()->to('users');
        }

        $rules = [
            'company_title' => [
                'rules' => 'required|min_length[2]',
                'errors' => [
                    'required' => 'Company Title must be filled',
                    'min_length' => 'Company Title must have at least 2 Characters'
                ]
            ],
            'company_name' => [
                'rules' => 'required|min_length[5]',
                'errors' => [
                    'required' => 'Company Name must be filled',
                    'min_length' => 'Company Name must have at least 5 Characters'
                ]
            ],
            'npwp_number' => [
                'rules' => 'required|regex_match[/^\d{15}$/]',
                'errors' => [
                    'required' => 'Company Name must be filled',
                    'regex_match' => 'field does not match the regular expression'
                ]
            ],
            'abbreviated_name' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'Abbreviated Name must be filled',
                ]
            ],
            'supplier_abbreviated' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'Supplier Abbreviated must be filled',
                ]
            ],
            'established_date' => [
                'rules' => 'required|valid_date[dd/mm/yyyy]',
                'errors' => [
                    'required' => 'Established Date must be filled',
                    'valid_date' => 'field does not contain a valid date',
                ]
            ],
            'company_website' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'Company Website must be filled',
                ]
            ],
            'supplier_category' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'Supplier Category must be filled',
                ]
            ],
            'vendor_code' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'Vendor Code must be filled',
                ]
            ],
            'join_date_maj' => [
                'rules' => 'required|valid_date[dd/mm/yyyy]',
                'errors' => [
                    'required' => 'Join Date must be filled',
                    'valid_date' => 'field does not contain a valid date',
                ]
            ],
            'supplier_group' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'Supplier Group must be filled',
                ]
            ],
            'official_letter_attachment' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'Official Letter must be filled',
                ]
            ],
            'country' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'Country must be filled',
                ]
            ],
            'province' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'Provience must be filled',
                ]
            ],
            'city' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'City must be filled',
                ]
            ],
            'zip_code' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'City must be filled',
                ]
            ],
            'supplier_affiliation' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'Supplier Affilation must be filled',
                ]
            ],
            'company_phone_number' => [
                'rules' => 'required|numeric|min_length[10]|max_length[15]',
                'errors' => [
                    'required' => 'Company Phone Number must be filled',
                    'numeric' => 'field contains anything other than numeric characters',
                    'min_length' => 'field is shorter than the parameter value', 
                    'max_length' => 'field is longer than the parameter value',
                ]
            ],
            'company_fax_number' => [
                'rules' => 'required|numeric|min_length[10]|max_length[15]',
                'errors' => [
                    'required' => 'Company Fax Number must be filled',
                    'numeric' => 'field contains anything other than numeric characters',
                    'min_length' => 'field is shorter than the parameter value', 
                    'max_length' => 'field is longer than the parameter value',
                ]
            ],
            'logo_attachment' => [
                'rules' => 'uploaded[logo_attachment]',
                'errors' => [
                    'uploaded' => 'Image must be filled',
                ]
            ],
            'capital' => [
                'rules' => 'required|numeric|greater_than_equal_to[0]',
                'errors' => [
                    'required' => 'Capital must be filled',
                    'numeric' => 'field contains anything other than numeric characters',
                    'greater_than_equal_to' => 'field is less than the parameter value',
                ]
            ],
            'asset_value' => [
                'rules' => 'required|numeric|greater_than_equal_to[0]',
                'errors' => [
                    'required' => 'Assets Value must be filled',
                    'numeric' => 'field contains anything other than numeric characters',
                    'greater_than_equal_to' => 'field is less than the parameter value',
                ]
            ],
            'company_clasification' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'Company Clasification must be filled',
                ]
            ],
            'technical_assistant' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'technical Assistant must be filled',
                ]
            ],
            'start_operation_date' => [
                'rules' => 'required|valid_date[dd/mm/yyyy]',
                'errors' => [
                    'required' => 'Start Operation Date must be filled',
                    'valid_date' => 'field does not contain a valid date',
                ]
            ],
            'currency' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'Currency must be filled',
                ]
            ],
            'cp_username' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'Contact Person Username must be filled',
                ]
            ],
            'contact_person_name' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'Contact Person Name must be filled',
                ]
            ],
            'contact_number|numeric|min_length[10]|max_length[15]' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'Contact Person Number must be filled',
                    'numeric' => 'field contains anything other than numeric characters',
                    'min_length' => 'field is shorter than the parameter value', 
                    'max_length' => 'field is longer than the parameter value',
                ]
            ],
            'contact_person_title' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'Contact Person Title must be filled',
                ]
            ],
            'contact_person_email_1' => [
                'rules' => 'required|valid_email|valid_emails',
                'errors' => [
                    'required' => 'Contact Person Email 1 must be filled',
                    'valid_email' => 'field does not contain a valid email address',
                    'valid_emails' => 'value provided in a comma separated list is not a valid email'
                ]
            ],
            'contact_person_email_2' => [
                'rules' => 'required|valid_email|valid_emails',
                'errors' => [
                    'required' => 'Contact Person Email 2 must be filled',
                    'valid_email' => 'field does not contain a valid email address',
                    'valid_emails' => 'value provided in a comma separated list is not a valid email'
                ]
            ],
            'address' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'Address must be filled',
                ]
            ],
        ];

        // $path = $this->request->getFile('logo_attachment')->store('img/customer', $user['username'] . '.' . 'png');

        if($this->validate($rules)){
            $logo = $this->request->getFile('logo_attachment');
            $logo->move(FCPATH . 'img/customer', $user['username'] . date('YmdHis') . '.png');

            if($this->request->getMethod() == 'post'){
                $data = [
                    'company_title' => $this->request->getPost('company_title'),
                    'company_name' => $this->request->getPost('company_name'),
                    'npwp_number' => $this->request->getPost('npwp_number'),
                    'abbreviated_name' => $this->request->getPost('abbreviated_name'),
                    'abbreviated_supplier' => $this->request->getPost('supplier_abbreviated'),
                    'estabilished_date' => $this->request->getPost('established_date'),
                    'company_website' => $this->request->getPost('company_website'),
                    'supplier_category' => $this->request->getPost('supplier_category'),
                    'vendor_code' => $this->request->getPost('vendor_code'),
                    'join_date' => $this->request->getPost('join_date_maj'),
                    'supplier_group' => $this->request->getPost('supplier_group'),
                    'official_letter_attachment' => $this->request->getPost('official_letter_attachment'),
                    'country' => $this->request->getPost('country'),
                    'provience' => $this->request->getPost('province'),
                    'city' => $this->request->getPost('city'),
                    'zip_code' => $this->request->getPost('zip_code'),
                    'supplier_affiliation' => $this->request->getPost('supplier_affiliation'),
                    'company_phone_number' => $this->request->getPost('company_phone_number'),
                    'company_fax_number' => $this->request->getPost('company_fax_number'),
                    'logo_attachment' => $user['username'] . date('YmdHis') . '.png',
                    'capital' => $this->request->getPost('capital'),
                    'asset_value' => $this->request->getPost('asset_value'),
                    'company_clasification' => $this->request->getPost('company_clasification'),
                    'technical_assistant' => $this->request->getPost('technical_assistant'),
                    'start_operation_date' => $this->request->getPost('start_operation_date'),
                    'currency' => $this->request->getPost('currency'),
                    'cp_username' => $this->request->getPost('cp_username'),
                    'cp_name' => $this->request->getPost('contact_person_name'),
                    'cp_number' => $this->request->getPost('contact_number'),
                    'cp_title' => $this->request->getPost('contact_person_title'),
                    'cp_email1' => $this->request->getPost('contact_person_email_1'),
                    'cp_email2' => $this->request->getPost('contact_person_email_2'),
                    'address' => $this->request->getPost('address'),
                ];
                
                if($M_auth->update($id, $data)){
                    session()->setFlashdata('success', 'Data berhasil disimpan');
                    return redirect()->to('users');
                }
            }
        } else {
            $data['validation'] = $this->validator;
            $this->ionAuth = new IonAuth();
            $M_auth = new M_auth();
            $data['title'] = 'Edit';
            $data['current_user'] = $this->ionAuth->user()->row();
            return $this->_render_page('user/profile/profile_edit', $data);
        }
        
    }


    public function upload_user()
    {
        helper('form', 'url', 'upload');
        $data['ionAuth'] = $this->ionAuth;
        $data['current_user'] = $this->ionAuth->user()->row();
        $data['title'] = 'Upload Profile';

        return $this->_render_page('user/upload-user/upload_user', $data);
    }


    public function stored_user()
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(300);
        helper('form', 'url', 'upload');
        $data['ionAuth'] = $this->ionAuth;
        $M_auth = new M_auth();
        $file_mimes = ['text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/xls', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'text/plain', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
        
        if ($this->request->getFile('fileUpload')->isValid() && in_array($this->request->getFile('fileUpload')->getMimeType(), $file_mimes)) {
            $notif['notif_status']  = false;

            $file = $this->request->getFile('fileUpload');
            $spreadsheet = IOFactory::load($file->getPathname());
            $sheetData = $spreadsheet->getActiveSheet()->toArray();
            $line = 1;

            $excel_header = array_flip(array(
                'vendor_code'               => "Vendor Code",
                'company_title'             => "Company Title",
                'company_name'              => "Company Name",
                'vendor_category'           => "Vendor Category",
                'address'                   => "Address",
                'country'                   => "Country",
                'provience'                 => "Province",
                'city'                      => "City",
                'npwp_number'               => "NPWP Number",
                'established_date'          => "Established Date",
                'email'                     => "Email Company",
                'company_website'           => "Company Website",
                'supplier_affiliation'      => "Supplier Affiliation",
                'company_phone_number'      => "Company Phone Number",
                'company_clasification'     => "Company Clasification",
                'supplier_category'         => "Supplier Category",
                'join_date'                 => "Join Date",
                'group_1'                   => "Purchasing Group 1",
                'group_2'                   => "Purchasing Group 2",
                'group_3'                   => "Purchasing Group 3",
                'group_4'                   => "Purchasing Group 4",
                'group_5'                   => "Purchasing Group 5",
                'group_6'                   => "Purchasing Group 6",
                'group_7'                   => "Purchasing Group 7",
                'group_8'                   => "Purchasing Group 8",
                'group_9'                   => "Purchasing Group 9",
                'group_10'                  => "Purchasing Group 10",
                'group_11'                  => "Purchasing Group 11",
                'group_12'                  => "Purchasing Group 12",
                'group_13'                  => "Purchasing Group 13",
                'group_14'                  => "Purchasing Group 14",
                'group_15'                  => "Purchasing Group 15",
                'group_16'                  => "Purchasing Group 16",
                'group_17'                  => "Purchasing Group 17",
                'group_18'                  => "Purchasing Group 18",
                'group_19'                  => "Purchasing Group 19",
                'group_20'                  => "Purchasing Group 20",
                'group_21'                  => "Purchasing Group 21",
                'group_22'                  => "Purchasing Group 22",
                'group_23'                  => "Purchasing Group 23",
                'pic_faktur_1'              => "PIC Faktur Pajak 1",
                'pic_faktur_2'              => "PIC Faktur Pajak 2",
                'pic_faktur_3'              => "PIC Faktur Pajak 3",
                'pic_user'                  => "PIC User",
                'cp_name'                   => "Contact Person Name",
                'cp_number'                 => "Contact Person Number",
                'cp_title'                  => "Contact Person Title",
                'cp_email1'                 => "Contact Person Email",
                'password'                  => "Password",
                ''                          => "",
            ));

            foreach (array_slice($sheetData, 0, 1) as $key => $value) {
                foreach ($value as $k => $v) {
                    if ($value != "") {
                        $array_key[$k] = $excel_header[$v];
                    }
                }
            }

            $permissions_pkp = [
                "Module.View.DashboarVendor",
                "Module.View.GoodsReceiptMaj",
                "Module.View.MajTrackData",
                "Module.View.MajTrackDropbox",
                "Module.View.Invoice",
                "Module.Create.Invoice",
                "Module.View.Qrgenerate",
                "Module.View.RegistrationDropbox",
                "Module.Create.RegistrationDropbox",
                "Module.View.PrintDropbox",
            ];
            
            $permissions_npkp = [
                "Module.View.DashboarVendor",
                "Module.View.GoodsReceiptMaj",
                "Module.View.MajTrackData",
                "Module.View.MajTrackDropbox",
                "Module.Create.Invoice",
                "Module.View.InvoiceNonPkp",
                "Module.View.RegistrationDropboxNpkp",
                "Module.Create.RegistrationDropboxNpkp",
                "Module.View.PrintDropboxNpkp",
            ];

            foreach (array_slice($sheetData, 2) as $key => $value) {
                if ($value[0] != "") {
                    foreach ($value as $k => $v) {
                        $new_value[$array_key[$k]] = $v;
                    }

                    if ($new_value['vendor_category'] == 'PKP') {
                        $selected_permissions = $permissions_pkp;
                    } elseif ($new_value['vendor_category'] == 'NPKP') {
                        $selected_permissions = $permissions_npkp;
                    }
                    
                    $hashed_password = password_hash($new_value['password'], PASSWORD_DEFAULT);
                    
                    $data = array(
                        'id'                        => $M_auth->generateId(),
                        'vendor_code'               => $new_value['vendor_code'],
                        'password'                  => $hashed_password,
                        'permission'                => serialize($selected_permissions),
                        'company_title'             => $new_value['company_title'],
                        'company_name'              => $new_value['company_name'],
                        'address'                   => $new_value['address'],
                        'country'                   => $new_value['country'],
                        'provience'                 => $new_value['provience'],
                        'city'                      => $new_value['city'],
                        'npwp_number'               => $new_value['npwp_number'],
                        'established_date'          => $new_value['established_date'],
                        'email'                     => $new_value['email'],
                        'company_website'           => $new_value['company_website'],
                        'supplier_affiliation'      => $new_value['supplier_affiliation'],
                        'company_phone_number'      => $new_value['company_phone_number'],
                        'company_clasification'     => $new_value['company_clasification'],
                        'supplier_category'         => $new_value['supplier_category'],
                        'join_date'                 => $new_value['join_date'],
                        'group_1'                   => $new_value['group_1'],
                        'group_2'                   => $new_value['group_2'],
                        'group_3'                   => $new_value['group_3'],
                        'group_4'                   => $new_value['group_4'],
                        'group_5'                   => $new_value['group_5'],
                        'group_6'                   => $new_value['group_6'],
                        'group_7'                   => $new_value['group_7'],
                        'group_8'                   => $new_value['group_8'],
                        'group_9'                   => $new_value['group_9'],
                        'group_10'                  => $new_value['group_10'],
                        'group_11'                  => $new_value['group_11'],
                        'group_12'                  => $new_value['group_12'],
                        'group_13'                  => $new_value['group_13'],
                        'group_14'                  => $new_value['group_14'],
                        'group_15'                  => $new_value['group_15'],
                        'group_16'                  => $new_value['group_16'],
                        'group_17'                  => $new_value['group_17'],
                        'group_18'                  => $new_value['group_18'],
                        'group_19'                  => $new_value['group_19'],
                        'group_20'                  => $new_value['group_20'],
                        'group_21'                  => $new_value['group_21'],
                        'group_22'                  => $new_value['group_22'],
                        'group_23'                  => $new_value['group_23'],
                        'pic_faktur_1'              => $new_value['pic_faktur_1'],
                        'pic_faktur_2'              => $new_value['pic_faktur_2'],
                        'pic_faktur_3'              => $new_value['pic_faktur_3'],
                        'pic_user'                  => $new_value['pic_user'],
                        'cp_name'                   => $new_value['cp_name'],
                        'cp_number'                 => $new_value['cp_number'],
                        'cp_title'                  => $new_value['cp_title'],
                        'cp_email1'                 => $new_value['cp_email1'],
                        'active'                    => '1',
                    );
                    $M_auth = new M_auth();
                    $insert = $M_auth->datainsert($data);
                    $user_id = $data['id'];

                    if ($user_id) {
                        $users_groups_data = [
                            'user_id' => $user_id,
                            'group_id' => 3
                        ];
                        $M_auth->insertUserGroup($users_groups_data);
                    }
                }
                $line++;
            }
    
            return redirect()->to('dashboard/admin');
        } else {
            return redirect()->to('dashboard/admin');
        }
    }


    public function temp()
    {
        $this->ionAuth    = new \IonAuth\Libraries\IonAuth();
        $user = $this->ionAuth->user()->row();
        echo $user->email;
    }

    public function getpart()
    {
        $output['data'] = [];
        $M_curl = new M_curl();
        
        $this->SAP_PARAMS['function'] = 'Z_QC';
        $this->SAP_PARAMS['params'] = [
            'RPT' => 'P_MATNR_FERT',
        ];

        $sap = $M_curl->execute("POST", $this->SAP_PARAMS);

        if ($sap['success']) {
            $output['data'] = (isset($sap['data']['ZTBL_SELECTION'])) ? $sap['data']['ZTBL_SELECTION'] : [];
        }

        $this->SAP_PARAMS['params'] = [
            'RPT' => 'P_MATNR_HALB',
        ];

        $sap = $M_curl->execute("POST", $this->SAP_PARAMS);

        if ($sap['success']) {
            if (isset($sap['data']['ZTBL_SELECTION'])) {
                $output['data'] = array_merge($output['data'], $sap['data']['ZTBL_SELECTION']);
            }
        }

        print_r($output);
    }

    public function template()
    {
        $set_data_row_length = 1000;
        $spreadsheet = new Spreadsheet();

        $user_header = array(
            'vendor_code'               => "Vendor Code",
            'company_title'             => "Company Title",
            'company_name'              => "Company Name",
            'vendor_category'           => "Vendor Category",
            'address'                   => "Address",
            'country'                   => "Country",
            'provience'                 => "Province",
            'city'                      => "City",
            'npwp_number'               => "NPWP Number",
            'established_date'          => "Established Date",
            'email'                     => "Email Company",
            'company_website'           => "Company Website",
            'supplier_affiliation'      => "Supplier Affiliation",
            'company_phone_number'      => "Company Phone Number",
            'company_clasification'     => "Company Clasification",
            'supplier_category'         => "Supplier Category",
            'join_date'                 => "Join Date",
            'group_1'                   => "Purchasing Group 1",
            'group_2'                   => "Purchasing Group 2",
            'group_3'                   => "Purchasing Group 3",
            'group_4'                   => "Purchasing Group 4",
            'group_5'                   => "Purchasing Group 5",
            'group_6'                   => "Purchasing Group 6",
            'group_7'                   => "Purchasing Group 7",
            'group_8'                   => "Purchasing Group 8",
            'group_9'                   => "Purchasing Group 9",
            'group_10'                  => "Purchasing Group 10",
            'group_11'                  => "Purchasing Group 11",
            'group_12'                  => "Purchasing Group 12",
            'group_13'                  => "Purchasing Group 13",
            'group_14'                  => "Purchasing Group 14",
            'group_15'                  => "Purchasing Group 15",
            'group_16'                  => "Purchasing Group 16",
            'group_17'                  => "Purchasing Group 17",
            'group_18'                  => "Purchasing Group 18",
            'group_19'                  => "Purchasing Group 19",
            'group_20'                  => "Purchasing Group 20",
            'group_21'                  => "Purchasing Group 21",
            'group_22'                  => "Purchasing Group 22",
            'group_23'                  => "Purchasing Group 23",
            'pic_faktur_1'              => "PIC Faktur Pajak 1",
            'pic_faktur_2'              => "PIC Faktur Pajak 2",
            'pic_faktur_3'              => "PIC Faktur Pajak 3",
            'pic_user'                  => "PIC User",
            'cp_name'                   => "Contact Person Name",
            'cp_number'                 => "Contact Person Number",
            'cp_title'                  => "Contact Person Title",
            'cp_email1'                 => "Contact Person Email",
            'password'                  => "Password",
        );        

        $excel_header_user_info = array(
            'vendor_code'               => "*Wajib diisi",
            'company_title'             => "",
            'company_name'              => "",
            'vendor_category'           => "*Isi dengan 'PKP' atau 'NPKP'",
            'address'                   => "",
            'country'                   => "",
            'provience'                 => "",
            'city'                      => "",
            'npwp_number'               => "",
            'established_date'          => "",
            'email'                     => "*Pastikan email setiap vendor berbeda",
            'company_website'           => "",
            'supplier_affiliation'      => "",
            'company_phone_number'      => "",
            'company_clasification'     => "",
            'supplier_category'         => "",
            'join_date'                 => "",
            'group_1'                   => "",
            'group_2'                   => "",
            'group_3'                   => "",
            'group_4'                   => "",
            'group_5'                   => "",
            'group_6'                   => "",
            'group_7'                   => "",
            'group_8'                   => "",
            'group_9'                   => "",
            'group_10'                  => "",
            'group_11'                  => "",
            'group_12'                  => "",
            'group_13'                  => "",
            'group_14'                  => "",
            'group_15'                  => "",
            'group_16'                  => "",
            'group_17'                  => "",
            'group_18'                  => "",
            'group_19'                  => "",
            'group_20'                  => "",
            'group_21'                  => "",
            'group_22'                  => "",
            'group_23'                  => "",
            'pic_faktur_1'              => "*Diisi untuk verifikasi Faktur Pajak",
            'pic_faktur_2'              => "*Diisi untuk verifikasi Faktur Pajak",
            'pic_faktur_3'              => "*Diisi untuk verifikasi Faktur Pajak",
            'pic_user'                  => "*Diisi untuk user input Invoice",
            'cp_name'                   => "",
            'cp_number'                 => "",
            'cp_title'                  => "",
            'cp_email1'                 => "*Pastikan email aktif",
            'password'                  => "",
        );

        $excel_success_style = array(
            'font' => [
                'color' => ['argb' => "000000"],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color'    => ['argb' => "dd27ff00"],
            ],
        );

        $excel_header_style = array(
            'font' => [
                'bold' => true,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color'       => ['argb' => '000000'],
                ],
            ],
            'fill' => [
                'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'argb' => 'c0c0c0',
                ],
            ],
        );

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray($user_header, NULL, 'A1');
        $sheet->fromArray($excel_header_user_info, NULL, 'A2');
        $sheet->setTitle('Main Sheet');

        // Set style untuk baris pertama (A1:B1)
        $sheet->getStyle('A1:AW1')->applyFromArray($excel_header_style);
        $sheet->getStyle('A1:AW1')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_NONE);
        $sheet->getRowDimension('1')->setRowHeight(20);

        // Set style untuk baris kedua (A2:B2)
        $sheet->getStyle('A2:AW2')->applyFromArray($excel_header_style); 
        $sheet->getStyle('A2:AW2')->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED));
        $sheet->getStyle('A2:AW2')->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_NONE);
        $sheet->getStyle('A2:AW2')->getFont()->setSize(8);
        
        // Menetapkan lebar kolom
        $columns = array_merge(
            range('A', 'Z'), 
            array_map(function($letter) { return 'A' . $letter; }, range('A', 'W'))
        );
        
        foreach ($columns as $columnID) {
            $sheet->getColumnDimension($columnID)->setWidth(30);
        }

        $writer = new WriteClsx($spreadsheet);

        for ($i = 3; $i < $set_data_row_length; $i++) {
            $conditional2 = new Conditional();
            $conditional2->setConditionType(Conditional::CONDITION_EXPRESSION)
                ->addCondition('$I$' . $i . '="OK"')
                ->getStyle()->applyFromArray($excel_success_style);
            $list_check = [];
            foreach ($columns as $columnID) {
                $sheet->getStyle($columnID . $i)->setConditionalStyles([$conditional2]);
                array_push($list_check, $columnID . $i . '=""');
            }
            $sheet->setCellValue('AX' . $i, '=IF((OR(' . implode(',', $list_check) . ')),"Pastikan diisi semua","OK sudah lengkap")');
        }

        $spreadsheet->setActiveSheetIndex(0);
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="Template Master Vendor.xlsx"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
    }
}
