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
                            <th>Total Payment</th>
                            <th>Action</th>
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
                search: '<div class="input-group-append"><span class="input-group-text">Nomor Invoice</span></div>',
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
                "sSearch": "Cari No. Invoice : "
            },
            "ajax": '<?= base_url()?>transaction/get-unregister',
            "columns": [
                { "data": "no_invoice" },
                { "data": "invoicing_id" },
                { "data": "company_name" },
                { 
                    'data': 'total_payment',
                    'render': function(data) {
                        return 'Rp ' + parseFloat(data).toLocaleString('id-ID', { minimumFractionDigits: 0 });
                    }
                },
                {
                    "data": "approve",
                    "orderable": false,
                    "render": function(data, type, row) {
                        return '<button class="btn-update btn btn-danger btn-cancel" data-action="cancel" data-invoicing-id="' + row.invoicing_id + '">Cancel Verify</button>';
                    }
                }
            ]
        });

        $('#example tbody').on('click', '.btn-update', function() {
            var tr = $(this).closest('tr');
            var row = table.row(tr);
            var data = row.data();
            var action = $(this).data('action');
            var invoicingId = $(this).data('invoicing-id');
            var modalSizeClass = 'modal-sm';

            $('#confirmationModal').data('tr', tr);
            $('#confirmationModal').data('row', row);
            $('#confirmationModal').data('action', action);

            $('#confirmationModal .modal-body').text('Apakah anda yakin akan Cancel Data ' + invoicingId + '?');

            $('#confirmationModal .modal-title').text('Konfirmasi');
            $('#confirmationModal .modal-dialog').removeClass('modal-lg').addClass(modalSizeClass);
            $('#confirmationModal').modal('show');

            $('#btnContinue').off().on('click', function() {
                var tr = $('#confirmationModal').data('tr');
                var row = $('#confirmationModal').data('row');
                var action = $('#confirmationModal').data('action');
                $('#spinner-container').show();

                var Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });

                $.ajax({
                    url: '<?= base_url()?>transaction/cancel-verify-json',
                    type: 'POST',
                    data: {
                        no_invoice: data.no_invoice,
                        invoicing_id: data.invoicing_id
                    },
                    success: function(response) {
                        $('#spinner-container').hide();
                        $('#confirmationModal').modal('hide');
                        Toast.fire({
                            icon: 'success',
                            title: 'Data ' + data.invoicing_id + ' berhasil diapprove. Proses persetujuan telah selesai.'
                        })
                        $('#example').DataTable().ajax.reload(null, false);
                    },
                    error: function(xhr, status, error) {
                        console.error(error);
                    }
                });
            });
        });
    });
</script>

<?= $this->endSection() ?>
