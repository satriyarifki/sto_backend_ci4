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
                    <!---------------------------------- Search User Input START-------------------------------------------->
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
                            <option value="approve">Sudah Approve</option>
                            <option value="notapprove">Belum Approve</option>
                        </select>

                        <label for="endDate" class="col-sm-2 col-form-label" style="margin-left: 130px;">Tanggal Akhir</label>
                            <input type="date" id="endDate" class="form-control" style="width: 180px;">
                    </div>
                    <!---------------------------------- Search User Input END-------------------------------------------->
                    
                    <!-------------------------------------- Table START-------------------------------------------------->
                    <table class="table table-bordered table-striped" id="dataTable">
                        
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Nomor Dropbox</th>
                                <th>Status Miro</th>
                                <th>Tanggal Approve</th>
                                <th>Jam Approve</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php 
                            $no = 1;
                            $processedDropboxIds = array();
                            foreach ($getMiro as $mstritem) {
                                if ($mstritem['dok_ok'] == 'y') {
                                    if (!in_array($mstritem['dropbox_id'], $processedDropboxIds)) {
                                        ?>
                                        <tr>
                                            <!----------------------------- td 1 No------------------------------------->
                                            <td><?= $no; ?></td>
                                            <!----------------------------- td 2 No. Dropbox---------------------------->
                                            <td><button class="btn btn-default" onclick="showInvoices('<?= $mstritem['dropbox_id']; ?>')"><?= $mstritem['dropbox_id']; ?></button>
                                                <div id="<?= $mstritem['dropbox_id']; ?>-invoices" class="invoices-container">
                                                    No. Invoice :<br>
                                                    <?php 
                                                    // Tampilkan nomor invoice terkait dengan dropbox_id
                                                    foreach ($getMiro as $invoiceItem) {
                                                        if ($invoiceItem['dropbox_id'] === $mstritem['dropbox_id']) {
                                                            echo $invoiceItem['no_invoice'] . '<br>';
                                                        }
                                                    }
                                                    ?>
                                                </div>
                                            </td>
                                            <!----------------------------- td 3 Status Dokumen---------------------------->
                                            <td>
                                                <?php if ($mstritem['status_miro'] == 'y') { 
                                                    $statusClass = 'badge-success';
                                                    ?>
                                                    <p class="badge <?= $statusClass; ?>">Sudah Approve</p>
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
                                            <!----------------------------- td 4 Tanggal Approve---------------------------->
                                            <td>
                                                <?php if ($mstritem['date_miro'] == '') { 
                                                    ?>-
                                                <?php 
                                                } else { 
                                                echo date('d-m-Y', strtotime($mstritem['date_miro']));
                                                } ?>
                                            </td>
                                            <!----------------------------- td 5 Jam Approve-------------------------------->
                                            <td>
                                                <?php 
                                                if ($mstritem['time_miro'] == '') { 
                                                    ?> - <?php 
                                                } else { 
                                                    echo date('H:i', strtotime($mstritem['time_miro']));
                                                } ?>
                                            </td>
                                            <!----------------------------- td 6 Action ------------------------------------>
                                            <td>
                                                <?php 
                                                if ($mstritem['status_miro'] == 'y') { 
                                                    $btnColorClass = 'btn-danger'; 
                                                    $btnText = 'Cancel Approved';
                                                    $confirmMessage = 'Apakah anda yakin untuk decline approve No. Dropbox = ('. $mstritem['dropbox_id']. ')?';
                                                    ?>
                                                    <a href="<?= base_url('invoicing/approvemiro/' . $mstritem['dropbox_id']); ?>" class="btn <?= $btnColorClass; ?> approve-link" role="button" aria-pressed="true" onclick="return confirm('<?= $confirmMessage; ?>')"><?= $btnText; ?></a>
                                                    <?php 
                                                } else { 
                                                    $btnColorClass = 'btn-info'; 
                                                    $btnText = 'Approve Dokumen';
                                                    $confirmMessage = 'Apakah anda yakin untuk approve No. Dropbox = ('. $mstritem['dropbox_id']. ')?';
                                                    ?>
                                                    <a href="<?= base_url('invoicing/approvemiro/' . $mstritem['dropbox_id'] . '/y'); ?>" class="btn <?= $btnColorClass; ?> approve-link" role="button" aria-pressed="true" onclick="return confirm('<?= $confirmMessage; ?>')"><?= $btnText; ?></a>
                                                    <?php 
                                                } ?>
                                            </td>
                                            <!----------------------------- td END ----------------------------------------->
                                        </tr>
                                        <?php 
                                        $no++; 
                                        // Tambahkan dropbox_id ke dalam array sementara
                                        $processedDropboxIds[] = $mstritem['dropbox_id']; 
                                    }
                                }
                            } ?>
                        </tbody>

                    </table>
                <!---------------------------------------- Table END-------------------------------------------------->
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
        var statusMatch = filterValue === 'all' || (filterValue === 'approve' && statusCell === 'Sudah Approve') || (filterValue === 'notapprove' && statusCell === 'Belum Approve');
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

