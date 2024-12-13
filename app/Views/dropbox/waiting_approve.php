<?= $this->extend('template/default') ?>
<?= $this->section('content') ?>
<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog" aria-labelledby="confirmationModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="confirmationModalLabel"></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body"></div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-ban" aria-hidden="true"></i></button>
                        <button type="button" class="btn btn-primary" id="btnContinue"><i class="fa fa-check-circle" aria-hidden="true"></i></button>
                    </div>
                    </div>
                </div>
            </div>
            <div class="card-header">
                <h5 class="card-title"> </h5>
            </div>
            <div class="card-body">
                <table id="example" class="display" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>Nomor Invoice</th>
                            <th>Nomor Verifikasi</th>
                            <th>Nama Vendor</th>
                            <th>Tanggal Pengajuan</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
<div id="spinner-container">
    <button class="btn btn-primary">
        <span class="spinner-border spinner-border-sm"></span>
        Loading..
    </button>
</div>
<style>
    table.dataTable tbody td.no-padding {
        padding: 0;
    }

    .badge-success {
        background-color: #28a745;
        color: white;
        padding: 0.25em 0.4em;
        border-radius: 0.2em;
    }
    .badge-warning {
        background-color: #ffc107;
        color: black;
        padding: 0.25em 0.4em;
        border-radius: 0.2em;
    }
    .btn-update {
        padding: 0.25em 0.5em;
        cursor: pointer;
    }

    #spinner-container {
        position: fixed; 
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 1051; 
        display: none;
    }
</style>

<script>
    $(document).ready(function() {
        var table = $('#example').DataTable({
            'processing': true,
            language: {
                'loadingRecords': '&nbsp;',
                lengthMenu: '_MENU_ &nbsp Show',
                search: '<div class="input-group-append"><span class="input-group-text">Dropbox ID</span></div>',
                emptyTable: '<div style="height: 120px" class="d-flex justify-content-center align-items-center"><div class="text-center"><i style="font-size:24px" class="far">&#xf07c;</i><p>No Data</p></div></div>',
            },
            layout: {
                topStart: false,
                topEnd: false,
                bottomStart: 'pageLength',
                topStart: 'search',
                bottom2Start: 'info',
            },
            "scrollY": "650px",
            "sScrollX": "100%",
            "scrollCollapse": true,
            "oLanguage": {
                "sSearch": "Cari No. Dropbox : "
            },
            "ajax": '<?= base_url()?>exception/get-exception-document-vendor',
            "columns": [
                { "data": "no_invoice" },
                { "data": "invoicing_id" },
                { "data": "company_name" },
                { "data": "generated_date" },
                { 
                    "data": "approve",
                    "render": function(data, type, row) {
                        if (data === 'Y') {
                            return '<span class="badge badge-success">Approved</span>';
                        } else {
                            return '<span class="badge badge-warning">Waiting......</span>';
                        }
                    }
                }
            ]
        });

        $('#example tbody').on('click', '.btn-update', function() {
            var tr = $(this).closest('tr');
            var row = table.row(tr);
            var data = row.data();
            var action = $(this).data('action');
            var dropboxId = $(this).data('dropbox-id');
            var modalSizeClass = 'modal-sm';

            $('#confirmationModal').data('tr', tr);
            $('#confirmationModal').data('row', row);
            $('#confirmationModal').data('action', action);

            if (action === 'approve') {
                $('#confirmationModal .modal-body').text('Apakah Data Dropbox ' + dropboxId + ' sudah sesuai?');
            } else if (action === 'cancel') {
                $('#confirmationModal .modal-body').text('Apakah anda yakin akan Cancel Data ' + dropboxId + '?');
            }

            $('#confirmationModal .modal-title').text('Konfirmasi');
            $('#confirmationModal .modal-dialog').removeClass('modal-lg').addClass(modalSizeClass);
            $('#confirmationModal').modal('show');

            $('#btnContinue').off().on('click', function() {
                var tr = $('#confirmationModal').data('tr');
                var row = $('#confirmationModal').data('row');
                var action = $('#confirmationModal').data('action');

                var Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });

                var newStatus = action === 'approve' ? 'Y' : 'N'

                $.ajax({
                    url: '<?= base_url()?>exception/update-exception-dropbox',
                    type: 'POST',
                    data: {
                        no_invoice: data.no_invoice,
                        approve: newStatus
                    },
                    success: function(response) {
                        row.data($.extend({}, data, {
                            approve: newStatus
                        })).draw();
                        if (newStatus === 'Y') {
                            $(tr).find('.btn-update').text('Cancel Approve');
                            Toast.fire({
                                icon: 'success',
                                title: 'Data ' + data.approve + ' berhasil diapprove. Proses persetujuan telah selesai.'
                            })
                        } else {
                            $(tr).find('.btn-update').text('Approve');
                            Toast.fire({
                                icon: 'error',
                                title: 'Pembatalan persetujuan untuk data ' + data.no_invoice + '. Silakan pastikan kembali datanya.'
                            })
                        }
                        fetchUnreceivedData();
                        $('#confirmationModal').modal('hide');
                    },
                    error: function(xhr, status, error) {
                        console.error(error);
                    }
                });
            });
        });


        $('#example tbody').on('click', '.on-hold-btn', function() {
            var tr = $(this).closest('tr');
            var row = table.row(tr);
            var data = row.data();
            var action = $(this).data('action');
            var dropboxId = $(this).data('dropbox-id');
            var user_generate = $(this).data('user-generate');
            var modalSizeClass = 'modal-lg';

            // $('#confirmationModal').data('tr', tr);
            // $('#confirmationModal').data('row', row);
            // $('#confirmationModal').data('action', action);

            console.log('data', data);
            console.log('action', action);
            console.log('dropboxId', dropboxId);

            $('#confirmationModal .modal-title').text('Isi pesan Email');
            $('#confirmationModal .modal-body').html('<textarea style="height: 250px;" type="text" id="formPesan" class="form-control textarea-lg"></textarea>');
            $('#confirmationModal .modal-dialog').removeClass('modal-sm').addClass(modalSizeClass);
            $('#confirmationModal').modal('show');

            $('#btnContinue').prop('disabled', true);

            $('#formPesan').on('input', function() {
                if ($(this).val().trim() === '') {
                    $('#btnContinue').prop('disabled', true);
                } else {
                    $('#btnContinue').prop('disabled', false);
                }
            });

            $('#btnContinue').off().on('click', function() {
                var tr = $('#confirmationModal').data('tr');
                var row = $('#confirmationModal').data('row');
                var action = $('#confirmationModal').data('action');
                var pesan = $('#formPesan').val();
                $('#spinner-container').show();
                var Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });

                $.ajax({
                    url: '<?= base_url()?>invoicing/on-hold-email',
                    type: 'POST',
                    data: {
                        dropbox_id: data.dropbox_id,
                        uservendor: user_generate,
                        invoice_id: data.invoices[0].invoicing_id,
                        pesan: pesan,
                    },
                    success: function(response) {
                        $('#spinner-container').hide();
                        $('#confirmationModal').modal('hide');
                        Toast.fire({
                            icon: 'success',
                            title: 'Data ' + data.dropbox_id + ' berhasil ditahan, Email sudah terkirim ke ' + user_generate
                        })
                        table.ajax.reload();
                    },
                    error: function(xhr, status, error) {
                        console.error(error);
                    }
                });
            });
        });


        function fetchUnreceivedData() {
            $.ajax({
                url: '<?= base_url() ?>indexedstatus/document-status',
                method: 'GET',
                success: function(data) {
                    $('#unoke-count').text(data.unoke.length);
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching data:', error);
                }
            });
        }
    });
</script>

<?= $this->endSection() ?>
