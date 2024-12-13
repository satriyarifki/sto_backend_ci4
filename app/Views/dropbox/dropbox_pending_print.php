<p style="font-size:18pt;text-align:right">PENDING DROPBOX</p>

<table cellpadding="0" >
    <tr>
        <th width="20%">No.Dropbox</th>
        <th width="50%">: <strong><?= $result[0]['dropbox_id']?></strong></th>
    </tr>
    <tr>
        <th width="20%">Tanggal</th>
        <th width="50%">: <strong><?= Date('d M Y');?></strong></th>
    </tr>
</table>
<p></p>
<table id="tb-item" cellpadding="4">
    <tr style="background-color:#a9a9a9">
        <th width="50%" style="height: 20px"><strong>Nomor Invoice</strong></th>
        <th width="50%" style="height: 20px"><strong>Nomor Verifikasi</strong></th>
    </tr>
    <tr>
        <td style="height: 20px"><?= $result[0]['no_invoice'] ?></td>
        <td style="height: 20px"><?= $result[0]['invoicing_id'] ?></td>
    </tr>
</table>

<table cellpadding="4">
    <tr>
        <td style="height: 20px; text-align:left; color:red; font-weight:bold;">
            <p style="font-size: 10px;">Perhatian : Dokumen ini hanya dapat diakses satu kali melalui MSPIN. Pastikan Anda sudah mendownload dan menyimpan dokumen ini sebelum menutup halaman.</p>
        </td>
    </tr>
</table>

<p>&nbsp;</p>
<table cellpadding="4">
    <tr>
        <td style="height: 20px; text-align:center">
            <p>&nbsp;</p>
        </td>
        
        <td style="height: 20px; text-align:center">
            <p></p>
            <p></p>
            <p>&nbsp;</p>
            <p>&nbsp;</p>
            <p>Mengetahui,</p>
            <p></p>
            <p></p>
            <p></p>
            <p>(....................................)</p>
            <p>Finance PT. Mekar Armada Jaya</p>
        </td>
        
        <td style="height: 20px; text-align:center">
            <p style="height: 20px; text-align:right">Bekasi, <?= Date('d M Y'); ?></p>
            <p>&nbsp;</p>
            <p>&nbsp;</p>
            <p>Hormat kami,</p>
            <p></p>
            <p></p>
            <p></p>
            <p>(....................................)</p>
            <p><?= $result[0]['company_name'] ?></p>
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
