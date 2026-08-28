



<p style="font-size:18pt;text-align:right">DROPBOX</p>

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
        <th width="10%" style="height: 20px"><strong>No</strong></th>
        <th width="45%" style="height: 20px"><strong>Nomor Invoice</strong></th>
        <th width="45%" style="height: 20px"><strong>Nomor Verifikasi</strong></th>
    </tr>
    <?php
    $groupedItems = [];
    $totalSum = 0;

    foreach ($result as $item):
        $no_invoice = $item['no_invoice'];
        $total_payment = $item['total_payment'];
        $invoicing_id = $item['invoicing_id'];
        $totalSum += $total_payment;
        if (!isset($groupedItems[$no_invoice][$total_payment])) {
            $groupedItems[$no_invoice][$total_payment] = [];
        }

        if (!in_array($invoicing_id, $groupedItems[$no_invoice][$total_payment])) {
            $groupedItems[$no_invoice][$total_payment][] = $invoicing_id;
        }
    endforeach;

    $no = 0;
    foreach ($groupedItems as $no_invoice => $invoices):
        foreach ($invoices as $total_payment => $invoicing_ids):
            $no++;
            $rowspan = count($invoicing_ids);   
            foreach ($invoicing_ids as $key => $invoicing_id): ?>
                <tr>
                    <?php if ($key === 0): ?>
                        <td style="height: 20px;text-align:center" rowspan="<?= $rowspan ?>"><?= $no ?></td>
                        <td style="height: 20px" rowspan="<?= $rowspan ?>"><?= $no_invoice ?></td>
                    <?php endif; ?>
                    <td style="height: 20px"><?= $invoicing_id ?></td>
                </tr>
            <?php endforeach;
        endforeach;
    endforeach; ?>
</table>

<p>&nbsp;</p>
<table cellpadding="4" >
    <tr>
        <td width="50%" style="height: 20px;text-align:center">
            <p>&nbsp;</p>
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
