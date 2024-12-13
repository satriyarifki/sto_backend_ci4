<?php namespace App\Models;

use CodeIgniter\Model;
use SAPNWRFC\Connection as SapConnection;
use SAPNWRFC\Exception as SapException;

class M_sap extends Model
{
    protected $setInteger = ['ZQTY_GR', 'ZREV', 'ZBUDGET', 'ZWBUDGET', 'ZPERIOD', 'ZDIFF', 'Z_QC'];

    public function __construct()
    {
        parent::__construct();
        $this->session       = service('session');
        $this->lang          = service('language');
        $this->request       = service('request');
        $this->M_enc         = new M_enc();
        $this->db_majsf      = $this->db->connect('db_majsf');
        $this->config_sap    = $this->M_enc->config();
    }

    public function getFunction($func, $params, $setInteger = null, $defaultMessages = true, $encode = true)
    {
        if ($func == null || $params == null) {
            $response = [
                'success' => false,
                'source'  => substr($this->config_sap['ashost'], 8),
                'message' => 'Parameter yang ada kirim tidak lengkap',
            ];
            return json_encode($response);
        }

        try {
            $c = new SapConnection($this->config_sap);

            if ($setInteger !== null) {
                $this->setInteger = array_merge($setInteger, $this->setInteger);
            }

            $function = $c->getFunction($func);

            foreach ($params as $key => $value) {
                if (in_array($key, $this->setInteger)) {
                    $params[$key] = (int) $value;
                }
            }

            $result = $function->invoke($params);
            array_walk($result, 'trim_value');
            $message = trim($result['MESSAGE']);
            $c->close();

            if ($message == '') {
                $response = [
                    'success' => true,
                    'data'    => $result,
                ];
            } else {
                $messages = $defaultMessages ? $this->lang->line("sap_error")[trim($result['MESSAGE'])] : $result['MESSAGE'];
                $response = [
                    'success' => false,
                    'message' => $messages,
                ];
            }
        } catch (SapException $ex) {
            $error    = 'Exception: ' . $ex->getMessage() . PHP_EOL;
            $response = [
                'success'    => false,
                'message'    => "ERROR: {$error}",
                'error_info' => $ex->getErrorInfo(),
            ];
        }

        $response['params'] = $params;
        $response['source'] = substr($this->config_sap['ashost'], 8);

        if ($encode) {
            $response = json_encode($response);
        }

        return $response;
    }

    public function getFunctionArray($data = null, $defaultMessages = true)
    {
        if ($data == null) {
            $response = [
                'success' => false,
                'source'  => substr($this->config_sap['ashost'], 8),
                'message' => 'Parameter yang ada kirim tidak lengkap',
            ];

            session_destroy();
            return json_encode($response);
        }

        try {
            $c = new SapConnection($this->config_sap);

            $function = $c->getFunction($data[0]['function']);
            $baris    = 2;

            foreach ($data as $key => $value) {
                if (isset($value['set_integer'])) {
                    $this->setInteger = array_merge($value['set_integer'], $this->setInteger);
                }

                foreach ($value['params'] as $key => $v) {
                    if (in_array($key, $this->setInteger)) {
                        $value['params'][$key] = (int) $v;
                    }
                }

                $result = $function->invoke($value['params']);
                array_walk($result, 'trim_value');
                $message = trim($result['MESSAGE']);

                if ($message != '') {
                    break;
                }

                $baris++;
            }

            $c->close();

            if ($message == '') {
                $response = [
                    'success' => true,
                    'data'    => $result,
                ];
            } else {
                $messages = $defaultMessages ? $this->lang->line("sap_error")[trim($result['MESSAGE'])] : $result['MESSAGE'];
                $response = [
                    'success' => false,
                    'message' => $messages,
                    'baris'   => $baris,
                ];
            }
        } catch (SapException $ex) {
            $error    = 'Exception: ' . $ex->getMessage() . PHP_EOL;
            $response = [
                'success'    => false,
                'message'    => "ERROR: {$error}",
                'error_info' => $ex->getErrorInfo(),
                'baris'      => $baris,
            ];
        }

        $response['params'] = $data[0]['params'];
        $response['source'] = substr($this->config_sap['ashost'], 8);

        if ($response['success']) {
            $response = json_encode($response);
        } else {
            $response = json_encode($response);
        }

        session_destroy();
        return $response;
    }

    public function getFunctionStock($func, $params, $defaultMessages = true)
    {
        if ($func == null || $params == null) {
            $response = [
                'success' => false,
                'source'  => substr($this->config_sap['ashost'], 8),
                'message' => 'Parameter yang ada kirim tidak lengkap',
            ];

            return json_encode($response);
        }

        try {
            $c = new SapConnection($this->config_sap);
            $function = $c->getFunction($func);

            $result = $function->invoke($params);
            array_walk($result, 'trim_value');
            $c->close();

            $response = [
                'success' => true,
                'data'    => $result,
            ];
        } catch (SapException $ex) {
            $error    = 'Exception: ' . $ex->getMessage() . PHP_EOL;
            $response = [
                'success'    => false,
                'message'    => "ERROR: {$error}",
                'error_info' => $ex->getErrorInfo(),
            ];
        }

        if ($response['success']) {
            $response = json_encode($response);
        } else {
            $response = json_encode($response);
        }

        session_destroy();
        return $response;
    }
}
