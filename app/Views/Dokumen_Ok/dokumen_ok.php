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
                <h5 class="card-title">Dokumen OK</h5>
            </div>
            <div class="card-body">
                <table id="example" class="display" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Dropbox ID</th>
                            <th>Document Status</th>
                            <th>Action</th>
                            <th></th>
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
    td.details-control {
        background: url('../img/details_open.png') no-repeat center center;
        cursor: pointer;
    }
    tr.shown td.details-control {
        background: url('../img/details_close.png') no-repeat center center;
    }

    .slider {
        display: none;
    }

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
    function format(d) {
        var trs = '';
        $.each(d.invoices, function(key, invoice) {
            var totalPaymentFormatted = parseFloat(invoice.total_payment).toLocaleString('id-ID', { style: 'currency', currency: 'IDR' });
            var createDateFormatted = formatDate_v2(invoice.tax_date);
            var dateApproveFormatted = formatDate(invoice.date_approve);
            trs += '<tr><td>' + invoice.invoicing_id + '</td><td>' + invoice.no_invoice + '</td><td>' + invoice.npwp + '</td><td>' + createDateFormatted + '</td><td>' + dateApproveFormatted + '</td><td>' + invoice.tax_number + '</td><td>' + invoice.company_name + '</td><td>' + totalPaymentFormatted + '</td></tr>';
        });
        return '<div class="slider">'+
        '<table class="table table-border table-hover">' +
            '<thead>' +
            '<th>Nomor Verifikasi</th>' +
            '<th>Nomor Invoice</th>' +
            '<th>NPWP</th>' +
            '<th>Tanggal Invoice</th>' +
            '<th>Tanggal Terima Invoice</th>' +
            '<th>Nomor Faktur Pajak</th>' +
            '<th>Nama Perusahaan</th>' +
            '<th>Total Pembayaran</th>' +
            '</thead><tbody>' +
            trs +
            '</tbody></table>' +
            '</div>';
    }

    function formatDate(dateString) {
        if (!dateString) return '';
        var date = new Date(dateString);
        
        var day = date.getDate();
        var month = date.toLocaleString('id-ID', { month: 'long' }); 
        var year = date.getFullYear();

        var formattedDate = day + ' ' + month + ' ' + year;
        return formattedDate;
    }

    function formatDate_v2(tanggal) {
        if (!tanggal) return '';
        
        var parts = tanggal.split('/');
        if (parts.length !== 3) return 'Format tanggal tidak valid';
        
        var day = parts[0];
        var month = parseInt(parts[1]);
        var year = parts[2];
        
        if (isNaN(month) || month < 1 || month > 12) return 'Bulan tidak valid';
        
        var bulan = [
            'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];
        var formattedDate = day + ' ' + bulan[month - 1] + ' ' + year;
        return formattedDate;
    }


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
            "ajax": '<?= base_url()?>invoicing/dokumen-ok-json',
            "columns": [
                {
                    "class": 'details-control',
                    "orderable": false,
                    "data": null,
                    "defaultContent": ''
                },
                { "data": "dropbox_id" },
                { 
                    "data": "dok_ok",
                    "render": function(data, type, row) {
                        if (data === 'Y') {
                            return '<span class="badge badge-success">Approved</span>';
                        } else {
                            return '<span class="badge badge-warning">Pending</span>';
                        }
                    }
                },
                {
                    "data": "dok_ok",
                    "orderable": false,
                    "render": function(data, type, row) {
                        if (data === 'Y') {
                            var button = '<button class="btn-update btn btn-danger btn-cancel" data-action="cancel" data-dropbox-id="' + row.dropbox_id + '">Cancel Approve</button>';
                            if (row.in_pud === 'Y') {
                                button = '<button class="btn-update btn btn-danger btn-cancel" data-action="cancel" data-dropbox-id="' + row.dropbox_id + '" disabled>Cancel Approve</button>';
                            }
                            return button;
                        } else {
                            return '<button class="btn-update btn btn-success btn-approve" data-action="approve" data-dropbox-id="' + row.dropbox_id + '">Approve</button>';
                        }
                    }
                },
                {
                    "data": "dok_ok",
                    "orderable": false,
                    "render": function(data, type, row) {
                        if (data === 'Y') {
                            return '<button class="btn btn-secondary on-hold-btn" data-dropbox-id="' + row.dropbox_id + '" data-user-generate="' + row.user_generate + '" disabled>On Hold</button>';
                        } else {
                            return '<button class="btn btn-secondary on-hold-btn" data-dropbox-id="' + row.dropbox_id + '" data-user-generate="' + row.user_generate + '">On Hold</button>';
                        }
                    }
                }
            ]
        });


        $('#example tbody').on('click', 'td.details-control', function () {
            var tr = $(this).closest('tr');
            var row = table.row( tr );
    
            if ( row.child.isShown() ) {
                $('.slider', row.child()).slideUp( function () {
                    row.child.hide();
                    tr.removeClass('shown');
                } );
            }
            else {
                row.child( format(row.data()), 'no-padding' ).show();
                tr.addClass('shown');
                $('.slider', row.child()).slideDown();
            }
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
                    url: '<?= base_url()?>invoicing/update-dokument-ok',
                    type: 'POST',
                    data: {
                        dropbox_id: data.dropbox_id,
                        dok_ok: newStatus
                    },
                    success: function(response) {
                        row.data($.extend({}, data, {
                            dok_ok: newStatus
                        })).draw();
                        if (newStatus === 'Y') {
                            $(tr).find('.btn-update').text('Cancel Approve');
                            Toast.fire({
                                icon: 'success',
                                title: 'Data ' + data.dropbox_id + ' berhasil diapprove. Proses persetujuan telah selesai.'
                            })
                        } else {
                            $(tr).find('.btn-update').text('Approve');
                            Toast.fire({
                                icon: 'error',
                                title: 'Pembatalan persetujuan untuk data ' + data.dropbox_id + '. Silakan pastikan kembali datanya.'
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
