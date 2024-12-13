<?= $this->extend('template/default') ?>
<?= $this->section('content') ?>

<style>

.btn:active {
    transform: scale(1.1); 
    transition: transform 0.3s ease; 
}

</style>

<!-- Modal konfirmasi -->
<div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Konfirmasi Tindakan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p id="modalContent"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="confirmButton">Ya</button>
                
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <div class="form-group row">
                        <label for="search" class="col-sm-2 col-form-label">Cari No. Inovice</label>
                            <input type="text" id="search" name="search" class="form-control" style="width: 180px;" placeholder="Masukkan No. Invoice...">
                        <label for="startDate" class="col-sm-2 col-form-label" style="margin-left: 100px;">Tanggal Awal</label>
                            <input type="date" id="startDate" class="form-control" style="width: 180px;">
                    </div>

                    <div class="form-group row">
                        <label for="search" class="col-sm-2 col-form-label" >Cari Status</label>
                        <select id="filterStatus" style="width: 150px;" class="form-control">
                            <option value="all">Semua</option>
                            <option value="approve">Sudah</option>
                            <option value="notapprove">Belum</option>
                        </select>

                        <label for="endDate" class="col-sm-2 col-form-label" style="margin-left: 130px;">Tanggal Akhir</label>
                            <input type="date" id="endDate" class="form-control" style="width: 180px;">
                    </div>
                    <table class="table table-bordered table-striped" id="dataTable">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Nomor Invoice</th>
                                <th>Nomor Dropbox</th>
                                <th>Status Send</th>
                                <th>Tanggal Send</th>
                                <th>Waktu Send</th>
                                <th>No Zinver</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            foreach ($getInvoiceAfterDokOk as $mstritem) {
                                if ($mstritem['dok_ok'] == 'y') {
                                    ?>
                                    <tr>
                                        <td><?= $no; ?></td>
                                        <td><?= $mstritem['no_invoice']; ?></td>
                                        <td><?= $mstritem['dropbox_id']; ?></td>
                                        <td>
                                            <?php if ($mstritem['status_InvoiceAfterDokOk'] == 'y') { ?>
                                                <p class="badge badge-success">Sudah</p>
                                            <?php } else { ?>
                                                <p class="badge badge-warning">Belum</p>
                                            <?php } ?>
                                        </td>
                                        <!----------------------------- td 4 Tanggal Approve---------------------------->
                                        <td>
                                                <?php if ($mstritem['date_send_to_finance'] == '') { 
                                                    ?> - <?php 
                                                } else { 
                                                    echo date('d-m-Y', strtotime($mstritem['date_send_to_finance']));  
                                                } ?>
                                            </td>
                                            <!----------------------------- td 5 Jam Approve-------------------------------->
                                            <td>
                                                <?php if ($mstritem['time_send_to_finance'] == '') { 
                                                    ?> - <?php 
                                                } else { 
                                                    echo date('H:i', strtotime($mstritem['time_send_to_finance'])); 
                                                } ?>
                                            </td>
                                        
    
                                        <!-------------------------------------------------- Action TD START------------------------------------------>
                                        <td><?= $mstritem['no_zinver']; ?></td>
                                        <td>
                                            <?php 
                                            if ($mstritem['status_InvoiceAfterDokOk'] == 'y') { ?>
                                                
                                                <?php 
                                                if ($mstritem['print'] === '0') { ?>
                                                    <!-------------------------------------------------- Jika BELUM Print --------------------------------->
                                                    <a href="#" class="btn btn-warning print-link" data-invoice-number="<?= $mstritem['no_invoice']; ?>" data-action-text="Print">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-printer" viewBox="0 0 16 16"><path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1"/><path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1"/></svg> 
                                                    BELUM PRINT
                                                    </a>
                                                    <a href="#" class="btn btn-secondary approve-link" data-toggle="modal" data-target="#confirmModal" data-invoice-number="<?= $mstritem['no_invoice']; ?>" data-action-text="Send to Finance" data-action-type="approve">Send to Finance</a>
                                                    <?php 
                                                } else { 
                                                    ?>
                                                    <!-------------------------------------------------- Jika SUDAH Print -------------------------------->
                                                    <a href="#" class="btn btn-info print-link" data-invoice-number="<?= $mstritem['no_invoice']; ?>" data-action-text="Apakah Anda yakin untuk print Nomor Invoice ">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-printer" viewBox="0 0 16 16"><path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1"/><path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1"/></svg> 
                                                    <?= $mstritem['print'] ?>
                                                    </a>
                                                    <a href="#" class="btn btn-danger cancel-approve-link" data-invoice-number="<?= $mstritem['no_invoice']; ?>" data-action-text="Apakah anda yakin untuk melakukan pembatalan Send to Finance untuk Nomor Invoice " data-action-type="approve">Cancel Send to Finance</a>                                          
                                                    <?php 
                                                } ?>
                                            <?php 
                                            } else { ?>
                                            <!-------------------------------------------------- Jika BELUM Send ------------------------------------------------->
                                                <?php 
                                                if ($mstritem['print'] === '0') { ?>
                                                <!-------------------------------------------------- Jika BELUM Print ------------------------------------>
                                                    <a href="#" class="btn btn-warning print-link" data-invoice-number="<?= $mstritem['no_invoice']; ?>" data-action-text="Apakah Anda yakin untuk print Nomor Invoice ">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-printer" viewBox="0 0 16 16"><path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1"/><path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1"/></svg> 
                                                    0
                                                    </a>    
                                                    <a href="#" class="btn btn-secondary cant-approve-link" data-toggle="modal" data-target="#confirmModal" data-invoice-number="<?= $mstritem['no_invoice']; ?>" data-action-text="Mohon maaf tidak bisa melakukan Send to Finance karena belum melakukan print untuk Nomor Invoice " data-action-type="cantapprove">Send to Finance</a>
                                                    <?php 
                                                } else { ?>
                                                <!-------------------------------------------------- Jika SUDAH Print ------------------------------------>
                                                    <a href="#" class="btn btn-info print-link" data-invoice-number="<?= $mstritem['no_invoice']; ?>" data-action-text="Apakah Anda yakin untuk print Nomor Invoice ">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-printer" viewBox="0 0 16 16"><path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1"/><path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1"/></svg> 
                                                    <?= $mstritem['print'] ?>
                                                    </a>    
                                                    <a href="#" class="btn btn-info approve-link" data-invoice-number="<?= $mstritem['no_invoice']; ?>" data-action-text="Apakah Anda yakin untuk Send to Finance Nomor Invoice " data-action-type="approve">Send to Finance</a>
                                                    <?php 
                                                } ?>
                                            <?php 
                                        } ?>
                                        </td>
                                    </tr>
                                    <?php 
                                    $no++; 
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
    var modal = document.getElementById('confirmModal');
    var modalContent = document.getElementById('modalContent');
    var confirmButton = document.getElementById('confirmButton');
    var printLinks = document.querySelectorAll('.print-link');
    var approveLinks = document.querySelectorAll('.approve-link');
    var CancelapproveLinks = document.querySelectorAll('.cancel-approve-link');
    var CantapproveLinks = document.querySelectorAll('.cant-approve-link');
    var invoiceNumber;

    printLinks.forEach(function (link) {
        link.addEventListener('click', function (event) {
            event.preventDefault();

            invoiceNumber = this.dataset.invoiceNumber; 
            var actionText = this.dataset.actionText;

            modalContent.textContent = actionText + invoiceNumber + '?';
            $('#confirmModal').modal('show');

            confirmButton.dataset.actionType = 'print';
        });
    });

    approveLinks.forEach(function (link) {
        link.addEventListener('click', function (event) {
            event.preventDefault();
            invoiceNumber = this.dataset.invoiceNumber; 
            var actionText = this.dataset.actionText;

            modalContent.textContent = actionText + invoiceNumber + '?';
            $('#confirmModal').modal('show');

            confirmButton.dataset.actionType = 'approve';
        });
    });

    CancelapproveLinks.forEach(function (link) {
        link.addEventListener('click', function (event) {
            event.preventDefault();
            invoiceNumber = this.dataset.invoiceNumber; 
            var actionText = this.dataset.actionText; 

            modalContent.textContent = actionText + invoiceNumber + '?';
            $('#confirmModal').modal('show');
            confirmButton.dataset.actionType = 'cancelapprove';
        });
    });

    CantapproveLinks.forEach(function (link) {
        link.addEventListener('click', function (event) {
            event.preventDefault();
            invoiceNumber = this.dataset.invoiceNumber;
            var actionText = this.dataset.actionText;
            modalContent.textContent = actionText + invoiceNumber ;
            $('#confirmModal').modal('show');
            confirmButton.dataset.actionType = 'cantapprove';
        });
    });


    confirmButton.addEventListener('click', function () {
        var printLink = "<?= base_url('invoicing/printdok_ok/'); ?>" + invoiceNumber + '/y';
        var approveLink = "<?= base_url('invoicing/approveInvoiceAfterDokOk/'); ?>" + invoiceNumber + '/y';
        var cancelapproveLink = "<?= base_url('invoicing/approveInvoiceAfterDokOk/'); ?>" + invoiceNumber;

        if (this.dataset.actionType === 'print') {
            window.location.href = printLink;
            console.log('Tindakan Print dikonfirmasi', invoiceNumber);
        } else if (this.dataset.actionType === 'approve') {
            window.location.href = approveLink;
            console.log('Tindakan Approve dikonfirmasi', invoiceNumber);
        } else if (this.dataset.actionType === 'cancelapprove') {
            window.location.href = cancelapproveLink;
            console.log('Tindakan Cancel Send dikonfirmasi', invoiceNumber);
        } else if (this.dataset.actionType === 'cantapprove') {
            
            console.log('Tindakan Cancel Send dikonfirmasi', invoiceNumber);
        }

        $('#confirmModal').modal('hide');
    });


    if (confirmButton.dataset.actionType === 'cantapprove') {
        confirmButton.style.display = 'none'; 
    } else {
        confirmButton.style.display = 'inline';
    }


//Jika ingin ngasih filter tanggal, hapus semua comment yang berhubungan dengan tanggal

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
        var statusCell = row.querySelector('td:nth-child(4)').innerText.trim();
        var dateCell = row.querySelector('td:nth-child(5)').innerText.trim();

        var date = new Date(dateCell.split('-').reverse().join('-')); // Ubah format tanggal untuk perbandingan

        var searchTextMatch = rowText.includes(searchText);
        var statusMatch = filterValue === 'all' || (filterValue === 'approve' && statusCell === 'Sudah') || (filterValue === 'notapprove' && statusCell === 'Belum');
        var startDateMatch = startDate === '' || date >= new Date(startDate);
        var endDateMatch = endDate === '' || date <= new Date(endDate);

        if (searchTextMatch && statusMatch && startDateMatch && endDateMatch) {
        
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

$(function () {
    let table = new DataTable('#dataTable');
});

function filterTableByDate() {
    filterTable();
}
</script>
<?= $this->endSection() ?>
