<p style="font-size:18pt;text-align:right">INVOICE</p>

<table cellpadding="0" >
    <tr>
        <th width="20%">E-Ticket</th>
        <th width="50%">: <strong>T63785-YN2423</strong></th>
    </tr>
    <tr>
        <th width="20%">No.Invoice</th>
        <th width="50%">: <strong>202109280001</strong></th>
    </tr>
    <tr>
        <th width="20%">Tanggal</th>
        <th width="50%">: <strong>28 Sept 2021</strong></th>
    </tr>
</table>
<p></p>
<table id="tb-item" cellpadding="4" >
    <tr style="background-color:#a9a9a9">
        <th width="10%" style="height: 20px"><strong>No</strong></th>
        <th width="30%" style="height: 20px"><strong>No PO</strong></th>
        <th width="30%" style="height: 20px;text-align:center"><strong>Surat Jalan</strong></th>
        <th width="30%" style="height: 20px"><strong>No GR</strong></th>
    </tr>
    <?php $no = 0; // Inisialisasi variabel $no sebelum loop foreach ?>
    <?php foreach ($data_sap as $item): ?>
        <?php $no++; ?>
    <tr>
        <td style="height: 20px;text-align:center"><?= $no ?></td>
        <td style="height: 20px"><?= $item['EBELN'] ?></td>
        <td style="height: 20px"><?= $item['BKTXT'] ?></td>
        <td style="height: 20px;"><?= $item['BELNR'] ?></td>
    </tr>
    <?php endforeach; ?>
</table>

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
            <p>PT. Mekar Armada Jaya</p>
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