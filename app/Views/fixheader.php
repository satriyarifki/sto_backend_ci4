<?= $this->extend('template/default') ?>
<?= $this->section('content') ?>
<section class="content">
    <div class="container-fluid">
        <div class="card">
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
                            <th>Date</th>
                            <th>Time Approved</th>
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
<style>
    td.details-control {
        background: url('https://raw.githubusercontent.com/DataTables/DataTables/1.10.7/examples/resources/details_open.png') no-repeat center center;
        cursor: pointer;
    }
    tr.shown td.details-control {
        background: url('https://raw.githubusercontent.com/DataTables/DataTables/1.10.7/examples/resources/details_close.png') no-repeat center center;
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
</style>

<script>
    function format(d) {
        var trs = '';
        $.each(d.invoices, function(key, invoice) {
            trs += '<tr><td>' + invoice.invoicing_id + '</td><td>' + invoice.no_invoice + '</td><td>' + invoice.npwp + '</td><td>' + invoice.tax_number + '</td><td>' + invoice.company_name + '</td><td>' + invoice.total_payment + '</td></tr>';
        });
        return '<table class="table table-border table-hover">' +
            '<thead>' +
            '<th>Nomor Verifikasi</th>' +
            '<th>Nomor Invoice</th>' +
            '<th>NPWP</th>' +
            '<th>Nomor Faktur Pajak</th>' +
            '<th>Nama Perusahaan</th>' +
            '<th>Total Pembayaran</th>' +
            '</thead><tbody>' +
            trs +
            '</tbody></table>';
    }

    $(document).ready(function() {
        var table = $('#example').DataTable({
            "oLanguage": {
                "sSearch": "Cari No. Dropbox : "
            },
            "ajax": '<?= base_url()?>getData', // Ubah ini dengan endpoint yang sesuai di controller Anda
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
                        if (data === 'y') {
                            return '<span class="badge badge-success">Approved</span>';
                        } else {
                            return '<span class="badge badge-warning">Pending</span>';
                        }
                    }
                },
                { 
                    "data": "date",
                    "render": function(data, type, row) {
                        if (row.dok_ok === 'y') {
                            return data;
                        } else {
                            return '';
                        }
                    }
                },
                { 
                    "data": "time_dok_ok",
                    "render": function(data, type, row) {
                        if (row.dok_ok === 'y') {
                            return data;
                        } else {
                            return '';
                        }
                    }
                },
                {
                    "data": "dok_ok",
                    "orderable": false,
                    "render": function(data, type, row) {
                        if (data === 'y') {
                            return '<button class="btn-update btn-danger btn-cancel">Cancel Approve</button>';
                        } else {
                            return '<button class="btn-update btn-success btn-approve">Approve</button>';
                        }
                    }
                }
            ]
        });


        $('#example tbody').on('click', 'td.details-control', function() {
            var tr = $(this).closest('tr');
            var row = table.row(tr);

            if (row.child.isShown()) {
                row.child.hide();
                tr.removeClass('shown');
            } else {
                row.child(format(row.data())).show();
                tr.addClass('shown');
            }
        });

        $('#example tbody').on('click', '.btn-update', function() {
            var tr = $(this).closest('tr');
            var row = table.row(tr);
            var data = row.data();

            var newStatus = data.dok_ok === 'y' ? '' : 'y';

            $.ajax({
                url: '<?= base_url()?>update-dokument-ok',
                type: 'POST',
                data: {
                    dropbox_id: data.dropbox_id,
                    dok_ok: newStatus
                },
                success: function(response) {
                    row.data($.extend({}, data, {
                        dok_ok: newStatus
                    })).draw();
                    if (newStatus === 'y') {
                        $(tr).find('.btn-update').text('Cancel Approve');
                    } else {
                        $(tr).find('.btn-update').text('Approve');
                    }
                    location.reload();
                },
                error: function(xhr, status, error) {
                    console.error(error);
                }
            });
        });
    });
</script>

<?= $this->endSection() ?>
