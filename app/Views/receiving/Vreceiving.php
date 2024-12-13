<?= $this->extend('template/default') ?>
    <?= $this->section('content') ?>
    <style>
    .invoices-container {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.5s ease-out;
    }

    .invoices-container.show {
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
                    <table class="display nowrap" style="width:100%" id="dataTable">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Nomor Dropbox</th>
                                <th>Status Receive</th>
                                <th>Tanggal Approve</th>
                                <th>Jam Approve</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        
                        <tbody>
                            <?php 
                            $no = 1;
                            $processedDropboxIds = array(); 
                            foreach ($getReceiving as $mstritem) {
                                if (!in_array($mstritem['dropbox_id'], $processedDropboxIds)) {
                                    ?>
                                    <tr>
                                        <!----------------------------- td 1 No------------------------------------->
                                        <td><?= $no; ?></td>
                                        <!----------------------------- td 2 No. Dropbox---------------------------->
                                        <td><button class="btn btn-default" onclick="showInvoices('<?= $mstritem['dropbox_id']; ?>')"><?= $mstritem['dropbox_id']; ?></button>
                                            <div id="<?= $mstritem['dropbox_id']; ?>-invoices" class="invoices-container"">
                                            No. Invoice :<br>
                                                <?php 
                                                foreach ($getReceiving as $invoiceItem) {
                                                    if ($invoiceItem['dropbox_id'] === $mstritem['dropbox_id']) {
                                                        echo $invoiceItem['no_invoice'] . '<br>';
                                                    }
                                                }
                                                ?>
                                            </div>
                                        </td>
                                        <!----------------------------- td 3 Status Receive---------------------------->
                                        <td>
                                            <?php 
                                            if ($mstritem['status_receive'] == 'y') { 
                                                $statusClass = 'badge-success';
                                                ?>
                                                <p class="badge <?= $statusClass; ?>">
                                                    Sudah Approve
                                                </p>
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
                                            <?php if ($mstritem['date_approve'] == '') { 
                                                ?> - <?php 
                                            } else { 
                                                echo date('d-m-Y', strtotime($mstritem['date_approve']));  
                                            } ?>
                                        </td>
                                        <!----------------------------- td 5 Jam Approve-------------------------------->
                                        <td>
                                            <?php if ($mstritem['time_approve'] == '') { 
                                                ?> - <?php 
                                            } else { 
                                                echo date('H:i', strtotime($mstritem['time_approve'])); 
                                            } ?>
                                        </td>
                                        <!----------------------------- td 6 Action ------------------------------------>
                                        <td>
                                            <?php 
                                            if ($mstritem['status_receive'] == 'y') { 
                                                $btnColorClass = 'btn-danger'; 
                                                $btnText = 'Cancel Approved';
                                                $confirmMessage = 'Apakah anda yakin untuk decline approve No. Dropbox = ('. $mstritem['dropbox_id']. ')?';
                                                ?>
                                                <a href="<?= base_url('invoicing/approvereceiving/' . $mstritem['dropbox_id']); ?>" class="btn <?= $btnColorClass; ?> approve-link" role="button" aria-pressed="true" onclick="return confirm('<?= $confirmMessage; ?>')"><?= $btnText; ?></a>
                                                <?php 
                                            } else { 
                                                $btnColorClass = 'btn-info';
                                                $btnText = 'Approve Receiving';
                                                $confirmMessage = 'Apakah anda yakin untuk approve No. Dropbox = ('. $mstritem['dropbox_id']. ')?';
                                                ?>
                                                <a href="<?= base_url('invoicing/approvereceiving/' . $mstritem['dropbox_id'] . '/y'); ?>" class="btn <?= $btnColorClass; ?> approve-link" role="button" aria-pressed="true" onclick="return confirm('<?= $confirmMessage; ?>')"><?= $btnText; ?></a>
                                                <?php 
                                            } ?>
                                        </td>
                                        <!----------------------------- td END ----------------------------------------->
                                    </tr>
                                    <?php 
                                    $no++; 
                                    $processedDropboxIds[] = $mstritem['dropbox_id']; 
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
        document.getElementById('search').addEventListener('input', function() {
            var searchText = this.value.toLowerCase();
            var filterValue = document.getElementById('filterStatus').value;
            var rows = document.querySelectorAll('#dataTable tbody tr');
            rows.forEach(function(row) {
                var rowText = row.innerText.toLowerCase();
                var statusCell = row.querySelector('td:nth-child(3)').innerText.trim();
                if ((filterValue === 'all' || (filterValue === 'approve' && statusCell === 'Sudah Approve') || (filterValue === 'notapprove' && statusCell === 'Belum Approve')) &&
                    (rowText.includes(searchText))) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        document.getElementById('filterStatus').addEventListener('change', function() {
            var filterValue = this.value;
            var searchText = document.getElementById('search').value.toLowerCase();
            var rows = document.querySelectorAll('#dataTable tbody tr');
            rows.forEach(function(row) {
                var statusCell = row.querySelector('td:nth-child(3)').innerText.trim();
                var rowText = row.innerText.toLowerCase();
                if ((filterValue === 'all' || (filterValue === 'approve' && statusCell === 'Sudah Approve') || (filterValue === 'notapprove' && statusCell === 'Belum Approve')) &&
                    (rowText.includes(searchText))) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        $(function () {
            var table = $('#dataTable').DataTable({
                'processing': true,
                language: {
                    'loadingRecords': '&nbsp;',
                    lengthMenu: '_MENU_ &nbsp Show',
                    search: '<i class="fa fa-search" aria-hidden="true"></i>',
                    emptyTable: '<div style="height: 120px" class="d-flex justify-content-center align-items-center"><div class="text-center"><i style="font-size:24px" class="far">&#xf07c;</i><p>No Data</p></div></div>',
                    processing: '<div class="overlay"><div class="spinner-border text-primary" role="status"></div></div>',
                },
                layout: {
                    topStart: false,
                    topEnd: false,
                    bottomStart: 'pageLength',
                    bottomEnd: 'search',
                    bottom2Start: 'info',
                    bottom2End: 'paging',
                },
                "scrollY": "650px",
                "sScrollX": "100%",
                "scrollCollapse": true,
            });
        });

        document.getElementById('startDate').addEventListener('change', filterTableByDate);
        document.getElementById('endDate').addEventListener('change', filterTableByDate);

        function filterTableByDate() {
            var startDate = document.getElementById('startDate').value;
            var endDate = document.getElementById('endDate').value;
            var rows = document.querySelectorAll('#dataTable tbody tr');
            rows.forEach(function(row) {
                var dateCell = row.querySelector('td:nth-child(4)').innerText.trim();
                var date = new Date(dateCell.split('-').reverse().join('-'));
                if ((startDate === '' || date >= new Date(startDate)) &&
                    (endDate === '' || date <= new Date(endDate))) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        document.getElementById('startDate').addEventListener('change', function() {
            filterTableByDate();
            filterTable();
        });

        document.getElementById('endDate').addEventListener('change', function() {
            filterTableByDate();
            filterTable();
        });


    function showInvoices(dropboxId) {
        var dpbx = document.getElementById(dropboxId + "-invoices");
        dpbx.classList.toggle("show");
    }
    </script>

    </body>
<?= $this->endSection() ?>
