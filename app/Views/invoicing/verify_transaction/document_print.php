<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5><strong>Nomor Invoice</strong><p>1234556</p></h5>
                    <h5><strong>Tanggal : </strong><p><?= date('d F Y') ?></p></h5>
                </div>
                <div class="ml-auto">
                    <?php echo $styleRound ?>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No PO</th>
                                <th>Surat Jalan</th>
                                <th>No GR</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data_sap as $item): ?>
                                <tr>
                                    <td><?= $item['EBELN'] ?></td>
                                    <td><?= $item['BKTXT'] ?></td>
                                    <td><?= $item['BELNR'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                <h5><strong>E-Ticket</strong><p>??????</p></h5>
            </div>
        </div>
    </div>
</section>