
<p style="font-size:10pt;text-align:center"><strong>PT. MEKAR ARMADA JAYA</strong></p>
<p style="font-size:10pt;text-align:center"><strong>VERIFIKASI BERKAS TAGIHAN</strong></p>
<table>
    <tr>
    <td>
        <table id="tb-content" cellpadding="0">
            <tr>
                <th width="20%">NAMA SUPLIER</th>
                <th width="40%">: <?= intval($entry['user_generate']) ?> / <?= $entry['company_name']?></th>
            </tr>
            <tr>
                <th width="20%">NO. INVOICE</th>
                <th width="40%">: <?= $entry['no_invoice']?></th>
            </tr>
            <tr>
                <th width="20%">TGL TERIMA INVOICE</th>
                <th width="40%">: <?= date('d/m/y', strtotime($entry['date_approve'])) ?></th>
            </tr>
            <tr>
                <th width="20%">TGL INVOICE</th>
                    <?php
                    $tax_date_parts = explode('/', $entry['tax_date']);
                    $formatted_tax_date = $tax_date_parts[2] . '-' . $tax_date_parts[1] . '-' . $tax_date_parts[0];
                    ?>
                <th width="40%">: <?= date('d/m/y', strtotime($formatted_tax_date)) ?>
                </th>
            </tr>
            <tr>
                <th width="20%">TGL JATUH TEMPO</th>
                <th width="40%">: <?= date('d/m/y', strtotime($entry['deadline'])) ?></th>
            </tr>
            <tr>
                <th width="20%">TOTAL TAGIHAN</th>
                <th width="40%">: Rp <?= number_format($entry['total_payment'], 0, ',', '.') ?></th>
            </tr>
            <tr>
                <th></th>
            </tr>
        </table>
        </td>
    </tr>
</table>

<table>
    <tr>
    <td>
        <table cellspacing="0" cellpadding="3" border="1" style="float:right;width:320px">
        <tr>
            <th width="7%" style="vertical-align: middle; text-align: center;">No</th>
            <th width="42%" style="vertical-align: middle; text-align: center;">Kelengkapan</th>
            <th width="10%" style="vertical-align: middle; text-align: center;">Ada</th>
            <th width="11%" style="vertical-align: middle; text-align: center;">Tidak</th>
            <th width="30%" style="vertical-align: middle; text-align: center;">Keterangan</th>
        </tr>
        <tr>
            <td width="7%" style="vertical-align: middle; text-align: center;">1</td>
            <td width="42%">Invoice/kwitansi Asli</td>
            <td width="10%" style="vertical-align: middle; text-align: center;">V</td>
            <td width="11%"></td>
            <td width="30%"></td>
        </tr>
        <tr>
            <td width="7%" style="vertical-align: middle; text-align: center;">2</td>
            <td width="42%">Faktur Pajak (Jika Ada)</td>
            <td width="10%" style="vertical-align: middle; text-align: center;">V</td>
            <td width="11%"></td>
            <td width="30%">2 Rangkap</td>
        </tr>
        <tr>
            <td width="7%" style="vertical-align: middle; text-align: center;">3</td>
            <td width="42%">Surat Jalan Asli</td>
            <td width="10%" style="vertical-align: middle; text-align: center;">V</td>
            <td width="11%"></td>
            <td width="30%"></td>
        </tr>
        <tr>
            <td width="7%" style="vertical-align: middle; text-align: center;">4</td>
            <td width="42%">GR Doc</td>
            <td width="10%" style="vertical-align: middle; text-align: center;">V</td>
            <td width="11%"></td>
            <td width="30%"></td>
        </tr>
        <tr>
            <td width="7%" style="vertical-align: middle; text-align: center;">5</td>
            <td width="42%">PO Asli</td>
            <td width="10%" style="vertical-align: middle; text-align: center;">V</td>
            <td width="11%"></td>
            <td width="30%"></td>
        </tr>
        <tr>
            <td width="7%" style="vertical-align: middle; text-align: center;">6</td>
            <td width="42%">Progress</td>
            <td width="10%" style="vertical-align: middle; text-align: center;">V</td>
            <td width="11%"></td>
            <td width="30%"><?= $entry['progress']?>%</td>
        </tr>
        <tr>
            <td width="7%" style="vertical-align: middle; text-align: center;">7</td>
            <td width="42%">QCD</td>
            <td width="10%" style="vertical-align: middle; text-align: center;">V</td>
            <td width="11%"></td>
            <td width="30%"><?= $entry['qcd']?></td>
        </tr>
        </table>
        <table cellpadding="0">
            <tr>
                <th></th>
            </tr>
        </table>
        <table cellpadding="0">
            <tr>
                <th width="30%"><strong>SUPPLIER LAMA</strong></th>
                <th width="35%">Cetakan Ke - : <?= $entry['print']?></th>
                <th width="36%"><?= date('d.m.Y / H:i:s'); ?></th>
            </tr>
            <tr>
                <th></th>
            </tr>
        </table>
    </td>
    <td>
        <table cellspacing="0" cellpadding="7" border="1" style="float:right;width:330px">
        <tr>
            <td width="50%" style="vertical-align: middle; text-align: center;">VERIFICATOR</td>
            <td width="50%" style="vertical-align: middle; text-align: center;">SIGN, NAME, DATE</td>
        </tr>
        <tr>
            <td width="10%">1</td>
            <td width="40%">PROCUREMENT</td>
            <td width="50%"></td>
        </tr>
        <tr>
            <td width="10%">3</td>
            <td width="40%">FINANCE</td>
            <td width="50%"></td>
        </tr>
        <tr>
            <td width="40%" style="border-style: none">
                NOMOR PO
            </td>
            <td width="60%" style="border-style: none">
                : <?= $entry['no_po']?>
            </td>
        </tr>
        <tr>
            <td width="40%" style="border-style: none">
                NOMOR VERIFIKASI
            </td>
            <td width="60%" style="border-style: none">
                : <?= (int)$entry['no_zinver'] ?>
            </td>
        </tr>
        </table>
    </td>
    </tr>
    <tr>
    <td></td>
    </tr>
</table>

<p>&nbsp;</p>


<style>
    p, span, table { font-size: 10px}
    table { width: 100%; border: 1px solid #dee2e6; }
    table#tb-item tr th, table#tb-item tr td {
        border:1px solid #000
    }

    table#tb-item {
        width: 50%; /* Mengatur lebar tabel menjadi setengah dari lebar kertas */
        border-collapse: collapse;
        margin: auto; /* Agar tabel berada di tengah halaman */
    }

    table#tb-item th, table#tb-item td, table#tb-out td {
        border: 1px solid #000;
        padding: 8px;
    }
</style>
