<?= $this->extend('template/default') ?>
<?= $this->section('content') ?>

<style>
.invoices-container {
    /*display: none;
    opacity: 0;
    transition: transform 0.5s ease; */

    max-height: 0;
    overflow: hidden;
    transition: max-height 0.5s ease-out;
}

.invoices-container.show {
    /* display: block;
    transition: transform 0.5s ease;
    opacity: 1; */
    transition: max-height 2.5s ease-in;
    max-height: 1000px;
}

.invoice-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}

.invoice-table th, .invoice-table td {
    border: 1px solid #ddd;
    padding: 8px;
}

.invoice-table th {
    background-color: #f2f2f2;
    text-align: left;
}

.btn:active {
    transform: scale(1.1); 
    transition: transform 0.3s ease; 
}

</style>
<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <div class="form-group row">
                        <label for="search" class="col-sm-2 col-form-label">Cari No. Dropbox</label>
                            <input type="text" id="search" name="search" class="form-control" style="width: 180px;" placeholder="Masukkan No. Dropbox...">
                        <label for="startDate" class="col-sm-2 col-form-label" style="margin-left: 100px;">Tanggal Awal</label>
                            <input type="date" id="startDate" class="form-control" style="width: 180px;">
                    </div>

                    <div class="form-group row">
                        <label for="search" class="col-sm-2 col-form-label" >Cari Status</label>
                        <select id="filterStatus" style="width: 150px;" class="form-control">
                            <option value="all">Semua</option>
                            <option value="approve">Ok</option>
                            <option value="notapprove">Belum Approve</option>
                        </select>

                        <label for="endDate" class="col-sm-2 col-form-label" style="margin-left: 130px;">Tanggal Akhir</label>
                            <input type="date" id="endDate" class="form-control" style="width: 180px;">
                    </div>
                    <table class="table table-bordered table-striped" id="dataTable">
                        
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Nomor Dropbox</th>
                                <th>Status Dokumen</th>
                                <th>Tanggal Approve</th>
                                <th>Jam Approve</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php 
                            $no = 1;
                            $processedDropboxIds = array();
                            foreach ($getDokumen_Ok as $mstritem) {
                                if ($mstritem['status_receive'] == 'y') {
                                    if (!in_array($mstritem['dropbox_id'], $processedDropboxIds)) {
                                        ?>
                                        <tr>
                                            <td><?= $no; ?></td>
                                            <td>
                                                <button class="btn btn-default" onclick="showInvoices('<?= $mstritem['dropbox_id']; ?>')">
                                                    <?= $mstritem['dropbox_id']; ?>
                                                </button>
                                                <div id="<?= $mstritem['dropbox_id']; ?>-invoices" class="invoices-container">
                                                    Detail Dokumen :
                                                    <table class="invoice-table">
                                                        <thead>
                                                            <tr>
                                                                <th>Nomor Invoice</th>
                                                                <th>Nomor Verifikasi</th>
                                                                <th>Company Name</th>
                                                                <th>NPWP</th>
                                                                <th>Tanggal Invoice</th>
                                                                <th>Tanggal Terima Invoice</th>
                                                                <th>User</th>
                                                                <th>Total Pembayaran</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($getDokumen_Ok as $invoiceItem): ?>
                                                                <?php if ($invoiceItem['dropbox_id'] === $mstritem['dropbox_id']): ?>
                                                                    <tr>
                                                                        <td><?= $invoiceItem['no_invoice']; ?></td>
                                                                        <td><?= $invoiceItem['invoicing_id']; ?></td>
                                                                        <td><?= $invoiceItem['company_name']; ?></td>
                                                                        <td><?= $invoiceItem['npwp']; ?></td>
                                                                        <td><?= date('d F Y', strtotime($invoiceItem['create_date'])); ?></td>
                                                                        <td><?= date('d F Y', strtotime($invoiceItem['date_approve'])); ?></td>
                                                                        <td><?= $invoiceItem['user_generate']; ?></td>
                                                                        <td><?= 'Rp ' . number_format($invoiceItem['total_payment'], 0, ',', '.'); ?></td>
                                                                    </tr>
                                                                <?php endif; ?>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if ($mstritem['dok_ok'] == 'y') { 
                                                    $statusClass = 'badge-success';
                                                    ?>
                                                    <p class="badge <?= $statusClass; ?>">Ok</p>
                                                    <?php 
                                                } else { 
                                                    $statusClass = 'badge-warning';
                                                    ?>
                                                    <p class="badge <?= $statusClass; ?>">
                                                    Belum Approve
                                                    </p>
                                                    <?php 
                                                } ?>
                                            </td>
                                            <td>
                                                <?php if ($mstritem['date_dok_ok'] == '') { 
                                                    ?>-
                                                <?php 
                                                } else { 
                                                echo date('d-m-Y', strtotime($mstritem['date_dok_ok']));
                                                } ?>
                                            </td>
                                            <td>
                                                <?php 
                                                if ($mstritem['time_dok_ok'] == '') { 
                                                    ?> - <?php 
                                                } else { 
                                                    echo date('H:i', strtotime($mstritem['time_dok_ok']));
                                                } ?>
                                            </td>
                                            <td>
                                                <?php 
                                                if ($mstritem['dok_ok'] == 'y') { 
                                                    $btnColorClass = 'btn-danger'; 
                                                    $btnText = 'Cancel Approved';
                                                    $confirmMessage = 'Apakah anda yakin untuk decline approve No. Dropbox = ('. $mstritem['dropbox_id']. ')?';
                                                    ?>
                                                    <a href="<?= base_url('invoicing/approvedok_ok/' . $mstritem['dropbox_id']); ?>" class="btn <?= $btnColorClass; ?> approve-link" role="button" aria-pressed="true" onclick="return confirm('<?= $confirmMessage; ?>')"><?= $btnText; ?></a>
                                                
                                                <?php
                                                } else { 
                                                    $btnColorClass = 'btn-info'; 
                                                    $btnText = 'Approve Dokumen';
                                                    $confirmMessage = 'Apakah anda yakin untuk approve No. Dropbox = ('. $mstritem['dropbox_id']. ')?';
                                                    ?>
                                                    <a href="<?= base_url('invoicing/approvedok_ok/' . $mstritem['dropbox_id'] . '/y'); ?>" class="btn <?= $btnColorClass; ?> approve-link" role="button" aria-pressed="true" onclick="return confirm('<?= $confirmMessage; ?>')"><?= $btnText; ?></a>
                                                    <?php 
                                                } ?>
                                            </td>
                                        </tr>
                                        <?php 
                                        $no++; 
                                        $processedDropboxIds[] = $mstritem['dropbox_id']; 
                                    }
                                }
                            } ?>
                        </tbody>

                    </table>
                </div>
            </div>
        </div>
    </div>
</section>


<script>
document.getElementById('search').addEventListener('input', filterTable);
document.getElementById('filterStatus').addEventListener('change', filterTable);
document.getElementById('startDate').addEventListener('change', filterTableByDate);
document.getElementById('endDate').addEventListener('change', filterTableByDate);

function filterTable() {
    var searchText = document.getElementById('search').value.toLowerCase();
    var filterValue = document.getElementById('filterStatus').value;
    var startDate = document.getElementById('startDate').value;
    var endDate = document.getElementById('endDate').value;

    var rows = document.querySelectorAll('#dataTable tbody tr');
    rows.forEach(function(row) {
        var rowText = row.innerText.toLowerCase();
        var statusCell = row.querySelector('td:nth-child(3)').innerText.trim();
        var dateCell = row.querySelector('td:nth-child(4)').innerText.trim();

        var date = new Date(dateCell.split('-').reverse().join('-')); // Ubah format tanggal untuk perbandingan

        var searchTextMatch = rowText.includes(searchText);
        var statusMatch = filterValue === 'all' || (filterValue === 'approve' && statusCell === 'Ok') || (filterValue === 'notapprove' && statusCell === 'Belum Approve');
        var startDateMatch = startDate === '' || date >= new Date(startDate);
        var endDateMatch = endDate === '' || date <= new Date(endDate);

        if (searchTextMatch && statusMatch && startDateMatch && endDateMatch) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function filterTableByDate() {
    filterTable();
}


function showInvoices(dropboxId) {
    var dpbx = document.getElementById(dropboxId + "-invoices");
    dpbx.classList.toggle("show");
}
</script>

</body>
<?= $this->endSection() ?>

