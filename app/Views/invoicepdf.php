<p style="font-size:18pt;text-align:right">INVOICE</p>
<span>Kepada Yth.</span><br/>
<table cellpadding="0" >
    <tr>
        <th width="10%">Nama</th>
        <th width="40%">: <strong>M. Basri</strong></th>
        <th width="10%">Sales</th>
        <th width="40%">: <strong>Administrator</strong></th>
    </tr>
    <tr>
        <th width="10%">Alamat</th>
        <th width="40%">: <strong>Jl. Muharto Gang 5 Malang</strong></th>
        <th width="10%">No.Invoice</th>
        <th width="40%">: <strong>202109280001</strong></th>
    </tr>
    <tr>
        <th width="10%">Telp</th>
        <th width="40%">: <strong>081244785625</strong></th>
        <th width="10%">Tanggal</th>
        <th width="40%">: <strong>28 Sept 2021</strong></th>
    </tr>
</table>
<p></p>
<table id="tb-item" cellpadding="4" >
    <tr style="background-color:#a9a9a9">
        <th width="35%" style="height: 20px;text-align:center"><strong>No PO</strong></th>
        <th width="35%" style="height: 20px;text-align:center"><strong>Surat Jalan</strong></th>
        <th width="35%" style="height: 20px;text-align:center"><strong>No GR</strong></th>
    </tr>
    <?php foreach ($data_sap as $item): ?>
        <tr>
            <td style="height: 20px"><?= $item['EBELN'] ?></td>
            <td style="height: 20px;text-align:center"><?= $item['BKTXT'] ?></td>
            <td style="height: 20px;"><?= $item['BELNR'] ?></td>
        </tr>
    <?php endforeach; ?>
</table>
<p>Terbilang: Satu Juta Lima Ratus Ribu Rupiah</p>
<p><u>TRANSFER VIA</u></p>
<p>BCA: IDR<br/>A/C : 164-800-3325<br/>A/N : SOBATCODING.COM</p>
<p>&nbsp;</p>
<table cellpadding="4" >
    <tr>
        <td width="50%" style="height: 20px;text-align:center">
            <p>&nbsp;</p>
        </td>
        <td width="50%" style="height: 20px;text-align:center">
            <p>Malang, 28 Sept 2021</p>
            <p>Hormat kami,</p>
            <p></p>
            <p></p>
            <p></p>
            <p>sobatcoding.com</p>
        </td>
    </tr>
</table>


<style>
    p, span, table { font-size: 12px}
    table { width: 100%; border: 1px solid #dee2e6; }
    table#tb-item tr th, table#tb-item tr td {
        border:1px solid #000
    }
</style>