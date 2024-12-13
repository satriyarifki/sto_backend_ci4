<?php

namespace App\Controllers;

class Qrpython extends BaseController
{
    public function runPythonScript()
    {

        ///-------------INI UNTUK localhost:8080/ -----------------------///
        // $pdfPath = "faktur/0468FAKTUR.pdf";
        // $command = escapeshellcmd("python3 ../main.py " . escapeshellarg($pdfPath));
        
        // exec($command, $output, $result);

        // if (!empty($output)) {
        //     $json_output = json_decode($output[0], true);
    
        //     if (isset($json_output['error'])) {
        //         $data['response'] = $json_output['error'];
        //         $data['message'] = false;
        //     } else {
        //         $xmlString = file_get_contents($json_output['output']);
        //         $xml = simplexml_load_string($xmlString);

        //         $data['nomorFaktur'] = (string) $xml->kdJenisTransaksi . $xml->fgPengganti . $xml->nomorFaktur;
        //         $data['tanggalFaktur'] = (string) $xml->tanggalFaktur;
        //         $data['alamatLawanTransaksi'] = (string) $xml->alamatLawanTransaksi;
        //         $data['npwpLawanTransaksi'] = (string) $xml->npwpLawanTransaksi;
        //         $data['dpp'] = (float) $xml->jumlahDpp;
        //         $data['ppn'] = (float) $xml->jumlahPpn;
        //     }
        // } else {
        //     $data['response'] = "Sistem tidak mendeteksi adanya QR Code";
        //     $data['message'] = false;
        // }

        // echo json_encode($data);
        // echo "Status: " . $result;

        ///-------------INI UNTUK localhost:8080/ -----------------------///


        
        ///-------------INI UNTUK BACA NAMA localhost:8080/ -----------------------///

        $pdfPath = "faktur/010.008-24.96305091.pdf";
        $name_faktur1 = "KONG Al BOEN";
        $name_faktur2 = "WASTA KARNESA";
        $name_faktur3 = "Farhan Eka Widarto";
        $command = escapeshellcmd("python3 ../main_name.py " . escapeshellarg($pdfPath) . " " . escapeshellarg($name_faktur1) . " " . escapeshellarg($name_faktur2) . " " . escapeshellarg($name_faktur3));
        
        exec($command, $output, $result);

        if (!empty($output)) {
            $json_output = json_decode($output[0], true);
    
            if (isset($json_output['error'])) {
                $data['response'] = $json_output['error'];
                $data['message'] = false;
            } else {
                $data['response'] = $json_output['output'];
            }
        } else {
            $data['response'] = "Sistem tidak mendeteksi adanya QR Code";
            $data['message'] = false;
        }

        echo json_encode($data);

        ///-------------INI UNTUK BACA NAMA localhost:8080/ -----------------------///



        ///-------------INI UNTUK 192.168.120.7/portal-supplier-maj/ -----------------------///

        // $pdfPath = "faktur/MAJ_1.pdf";
        // $command = escapeshellcmd("python3 ../main_inv.py " . escapeshellarg($pdfPath));
        
        // exec($command, $output, $result);

        // if (!empty($output)) {
        //     $json_output = json_decode($output[0], true);
    
        //     if (isset($json_output['error'])) {
        //         $data['response'] = $json_output['error'];
        //         $data['message'] = false;
        //     } else {
        //         $data['output'] = $json_output;
        //     }
        // } else {
        //     $data['response'] = "Sistem tidak mendeteksi adanya QR Code";
        //     $data['message'] = false;
        // }

        // echo json_encode($data);
    
        // //Tampilkan status eksekusi
        // echo "Status: " . $result;


        ///-------------INI UNTUK 192.168.120.7/portal-supplier-maj/ -----------------------///
    }
}
