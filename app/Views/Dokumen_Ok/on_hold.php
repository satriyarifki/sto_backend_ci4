<?= $this->extend('template/default') ?>
<?= $this->section('content') ?>
<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog" aria-labelledby="confirmationModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
                    <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="confirmationModalLabel">Konfirmasi</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tidak</button>
                        <button type="button" class="btn btn-primary" id="btnContinue">Ya</button>
                    </div>
                    </div>
                </div>
            </div>
            <div class="card-header">
                <h5 class="card-title">On Hold</h5>
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
        position: fixed; /* Agar elemen tetap di tempat meskipun halaman di-scroll */
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 1051; /* Harus lebih tinggi dari z-index modal Bootstrap yang biasanya 1050 */
        display: none;
    }
</style>

<script>
    function format(d) {
        var trs = '';
        $.each(d.invoices, function(key, invoice) {
            var totalPaymentFormatted = parseFloat(invoice.total_payment).toLocaleString('id-ID', { style: 'currency', currency: 'IDR' });
            var createDateFormatted = formatDate(invoice.create_date);
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
            "ajax": '<?= base_url()?>invoicing/on-hold-json', // Ubah ini dengan endpoint yang sesuai di controller Anda
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
                    "data": null,
                    "orderable": false,
                    "render": function(data, type, row) {
                        return '<button class="btn-restore btn btn-info" data-dropbox-id="' + row.dropbox_id + '">Restore</button>';
                    }
                },
                {
                    "data": "onhold_datetime",
                    "orderable": false,
                    "render": function(data, type, row) {
                        return '<button class="btn-delete btn btn-danger" data-dropbox-id="' + row.dropbox_id + '" data-user-generate="' + row.user_generate + '">Delete</button>';
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

        $('#example tbody').on('click', '.btn-restore', function() {
            var tr = $(this).closest('tr');
            var row = table.row(tr);
            var data = row.data();
            var dropboxId = $(this).data('dropbox-id');

            console.log(dropboxId);
            console.log(data);

            // Simpan informasi yang relevan dalam modal
            $('#confirmationModal').data('tr', tr);
            $('#confirmationModal').data('row', row);
            $('#confirmationModal .modal-body').text('Apakah Data Dropbox ' + dropboxId + ' sudah sesuai dengan revisi yang diharapkan?');

            $('#confirmationModal').modal('show');
            
            $('#btnContinue').off().on('click', function() {
                var tr = $('#confirmationModal').data('tr');
                var row = $('#confirmationModal').data('row');
                var Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });

                $.ajax({
                    url: '<?= base_url()?>invoicing/restore-onhold',
                    type: 'POST',
                    data: {
                        dropbox_id: data.dropbox_id,
                    },
                    success: function(response) {
                        Toast.fire({
                            icon: 'success',
                            title: 'Data ' + data.dropbox_id + ' Berhasil dipulihkan, lakukan verifikasi pada halaman Dokumen OK'
                        })
                        table.ajax.reload();
                        $('#confirmationModal').modal('hide');
                    },
                    error: function(xhr, status, error) {
                        console.error(error);
                    }
                });
            });
        });

        $('#example tbody').on('click', '.btn-delete', function() {
            var tr = $(this).closest('tr');
            var row = table.row(tr);
            var data = row.data();
            var dropboxId = $(this).data('dropbox-id');
            var user_generate = $(this).data('user-generate');

            console.log(dropboxId);
            console.log(data);

            $('#confirmationModal').data('tr', tr);
            $('#confirmationModal').data('row', row);
            $('#confirmationModal .modal-body').text('Apakah anda yakin akan menghapus data ' + dropboxId + ' ?');

            $('#confirmationModal').modal('show');
            
            $('#btnContinue').off().on('click', function() {
                var tr = $('#confirmationModal').data('tr');
                var row = $('#confirmationModal').data('row');
                $('#spinner-container').show();
                var Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });

                $.ajax({
                    url: '<?= base_url()?>invoicing/onhold-delete',
                    type: 'POST',
                    data: {
                        dropbox_id: data.dropbox_id,
                        no_invoice: data.invoices[0].no_invoice,
                        uservendor: user_generate,
                    },
                    success: function(response) {
                        $('#spinner-container').hide();
                        Toast.fire({
                            icon: 'success',
                            title: 'Data ' + data.dropbox_id + ' Berhasil dihapus'
                        })
                        table.ajax.reload();
                        $('#confirmationModal').modal('hide');
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
