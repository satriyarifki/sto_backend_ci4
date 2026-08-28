<table>
    <tr>
        <td></td>
    </tr>
</table>
<table id="tb-item" cellpadding="4" border="0" style="width: 100%;">
<tr>
        <td width="30%" height="45px"><strong>Nomor</strong></td>
        <td width="70%" style="font-size: 30px;"><strong><?= $id_tag ?></strong></td>
    </tr>
    <tr>
        <td width="30%" height="45px"><strong>Tanggal</strong></td>
        <td width="70%"><?= date('Y-m-d H:i:s'); ?></td>
    </tr>
    <tr>
        <td width="30%" height="45px"><strong>Customer</strong></td>
        <td width="70%"><?= $customer ?></td>
    </tr>
    <!-- <tr>
        <td width="30%" height="45px"><strong>Type</strong></td>
        <td width="70%"><?= $type ?></td>
    </tr> -->
    <tr>
        <td width="30%" height="45px"><strong>Area</strong></td>
        <td width="70%"><?= $area ?></td>
    </tr>
    <tr>
        <td width="30%" height="45px"><strong>Job No</strong></td>
        <td width="70%"><?= $job_number ?></td>
    </tr>
    <tr>
        <td width="30%" height="45px"><strong>Part No</strong></td>
        <td width="70%"><?= $part_number ?></td>
    </tr>
    <tr>
        <td width="30%" height="45px"><strong>Part Desc</strong></td>
        <td width="70%" style="font-size: 20px;"><?= $part_desc ?></td>
    </tr>
    <tr>
        <td width="30%" height="45px"><strong>QTY</strong></td>
        <td width="70%" style="font-size: 20px;"></td>
    </tr>
</table>

<table cellpadding="4" border="1" style="margin-top: 10px; width: 100%;">
    <tr>
        <td width="50%" height="30px" align="center" style="font-size: 22px;"><strong>Penghitung 1</strong></td>
        <td width="50%" height="30px" align="center" style="font-size: 22px;"><strong>Penghitung 2</strong></td>
    </tr>
    <tr>
        <td width="50%" height="150px" align="center"></td>
        <td width="50%" height="150px" align="center"></td>
    </tr>
    <tr>
        <td width="50%" height="30px" align="left" style="font-size: 22px;"><strong>Nama :</strong></td>
        <td width="50%" height="30px" align="left" style="font-size: 22px;"><strong>Nama :</strong></td>
    </tr>
    <tr>
        <td width="50%" height="30px" align="center" style="font-size: 22px;"><strong>Pencatat 1</strong></td>
        <td width="50%" height="30px" align="center" style="font-size: 22px;"><strong>Pencatat 2</strong></td>
    </tr>
    <tr>
        <td width="50%" height="150px" align="center"></td>
        <td width="50%" height="150px" align="center"></td>
    </tr>
    <tr>
        <td width="50%" height="30px" align="left" style="font-size: 22px;"><strong>Nama :</strong></td>
        <td width="50%" height="30px" align="left" style="font-size: 22px;"><strong>Nama :</strong></td>
    </tr>
</table>

<style>
    p, span, table { font-size: 27px}
    table { width: 100%; border: 1px solid #dee2e6; }
    table#tb-item tr th, table#tb-item tr td {
        border:1px solid #000
    }
</style>
