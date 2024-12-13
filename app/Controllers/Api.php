<?php
defined('BASEPATH') or exit('No direct script access allowed');
use \App\Models\M_curl;

class Api extends MX_Controller
{
	function __construct()
	{
		parent::__construct();
		$M_curl = new M_curl();
	}

	public function datatables_get_sap()
	{
		$output['data'] = [];
		$SAP_PARAMS['function'] = $this->input->post('function');
		$pengecualian = ['function','dt'];
		if (isset($_POST['form'])) {
			foreach ($this->input->post('form') as $value) {
				$data[$value['name']] = $value['value'];
				if ($value['value'] != '') {
					$SAP_PARAMS['params'][$value['name']] = $value['value'];
				}
			}
		}
		foreach ($_POST as $key => $value) {
			if (!in_array($key,$pengecualian)) {
				$SAP_PARAMS['params'][$key] = $value;
			}
		}
		$sap = $this->M_curl->execute("POST", $SAP_PARAMS);
		if ($sap['success']) {
			$output['data'] = (isset($sap['data'][$this->input->post('dt')])) ? $sap['data'][$this->input->post('dt')] : [];
		}

		die_data($output);
	}
}
