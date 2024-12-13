<?= $this->extend('template/default') ?>
<?= $this->section('content') ?>
<section class="content">
    <div class="container-fluid">

    <div class="row">
        <div class="col-md-12">
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
                    <div class="modal-body">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-ban" aria-hidden="true"></i></button>
                        <button type="button" class="btn btn-primary" id="btnContinue"><i class="fa fa-check-circle" aria-hidden="true"></i></button>
                    </div>
                    </div>
                </div>
            </div>
            <div class="card-header">
                <h5 class="card-title">Receiving</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-auto">
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-append">
                                    <span class="input-group-text">Nomor Dropbox</span>
                                </div>
                                <input type="text" id="inputData" name="inputData" class="form-control" placeholder="Masukkan Nomor Dropbox" style="width: 50%;">
                            </div>
                        </div>
                    </div>
                    <div class="col-auto">
                        <button id="loadDataBtn" class="btn btn-info">Search</button>
                    </div>
                </div>
                <div id="errorContainer"></div>
                <table id="example" class="display" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Nomor Dropbox</th>
                            <th>Company Name</th>
                            <th>Document Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
        </div>
    </div>
    </div>
</section>

<style>
    div.dt-processing>div:last-child {
        display: none;
    }

    .spinner-border {
        width: 50px;
        height: 50px;
    }

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
</style>

<script>

    function format(d) {    
        var trs = '';
        $.each(d.invoices, function(key, invoice) {
            var totalPaymentFormatted = parseFloat(invoice.total_payment).toLocaleString('id-ID', { style: 'currency', currency: 'IDR' });
            var createDateFormatted = formatDate(invoice.create_date);
            var dateApproveFormatted = formatDate(invoice.date_approve);
            trs += '<tr><td>' + invoice.invoicing_id + '</td><td>' + invoice.no_invoice + '</td></tr>';
        });
        return '<div class="slider">'+
        '<table class="table table-border table-hover">' +
            '<thead>' +
            '<th>Nomor Verifikasi</th>' +
            '<th>Nomor Invoice</th>' +
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
                search: '<i class="fa fa-search" aria-hidden="true"></i>',
                emptyTable: '<div style="height: 120px" class="d-flex justify-content-center align-items-center"><div class="text-center"><i style="font-size:24px" class="far">&#xf07c;</i><p>No Data</p></div></div>',
                processing: '<div class="overlay"><div class="spinner-border text-primary" role="status"></div></div>',
                paginate: {
                    first: "<i style='font-size:18px' class='fas'>&#xf100;</i>",
                    last: "<i style='font-size:18px' class='fas'>&#xf101;</i>",
                    next: "<i style='font-size:18px' class='fas'>&#xf105;</i>",
                    previous: "<i style='font-size:18px' class='fas'>&#xf104;</i>",
                },
            },
            layout: {
                topStart: false,
                topEnd: false,
                bottomStart: 'pageLength',
                bottomEnd: 'search',
                bottom2Start: 'info',
                bottom2End: 'paging',
            },
            bFilter : false,
            "scrollY": "650px",
            "sScrollX": "100%",
            "scrollCollapse": true,
            "ajax": null,
            'columns': [
                {
                    'class': 'details-control',
                    'orderable': false,
                    'data': null,
                    'defaultContent': ''
                },
                { 'data': 'dropbox_id' },
                { 'data': 'company_name' },
                { 
                    'data': 'status_receive',
                    'render': function(data) {
                        if (data === 'Y') {
                            return '<span class="badge badge-success">Approved</span>';
                        } else {
                            return '<span class="badge badge-warning">Pending</span>';
                        }
                    }
                },
                {
                    'data': 'status_receive',
                    'orderable': false,
                    'render': function(data, type, row) {
                        if (data === 'Y') {
                            var button = '<button class="btn-update btn btn-danger btn-cancel" data-action="cancel" data-dropbox-id="' + row.dropbox_id + '">Cancel Approve</button>';
                            if (row.dok_ok === 'Y') {
                                button = '<button class="btn-update btn btn-danger btn-cancel" data-action="cancel" data-dropbox-id="' + row.dropbox_id + '" disabled>Cancel Approve</button>';
                            }
                            return button;
                        } else {
                            return '<button class="btn-update btn btn-success btn-approve" data-action="approve" data-dropbox-id="' + row.dropbox_id + '">Approve</button>';
                        }
                    }
                },
                { 
                    'data': 'dok_ok',
                    'visible': false
                }
            ]
        });

        $('#loadDataBtn').on('click', function() {
            var inputData = $('#inputData').val();
            $('#example').DataTable().processing(true);
            if (inputData) {
                $.ajax({
                    url: '<?= base_url()?>invoicing/received-json',
                    type: 'POST',
                    data: { dropbox_id: inputData },
                    success: function(response) {
                        $('#example').DataTable().processing(false);
                        if (response.data.status === false) {
                            displayError(response.data.error);
                        } else {
                            var errorContainer = document.getElementById('errorContainer');
                            errorContainer.innerHTML = '';
                            table.clear().rows.add(response.data).draw();
                        }
                    },
                    error: function() {
                        alert('Terjadi kesalahan pada server.');
                    }
                });
            }
        });

        $('#example tbody').on('click', 'td.details-control', function () {
            var tr = $(this).closest('tr');
            var row = table.row(tr);

            if (row.child.isShown()) {
                $('.slider', row.child()).slideUp(function () {
                    row.child.hide();
                    tr.removeClass('shown');
                });
            } else {
                row.child(format(row.data()), 'no-padding').show();
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

            console.log(dropboxId);

            $('#confirmationModal').data('tr', tr);
            $('#confirmationModal').data('row', row);
            $('#confirmationModal').data('action', action);

            if (action === 'approve') {
                $('#confirmationModal .modal-body').text('Apakah Data Dropbox ' + dropboxId + ' sudah sesuai?');
            } else if (action === 'cancel') {
                $('#confirmationModal .modal-body').text('Apakah anda yakin akan Cancel Data ' + dropboxId + '?');
            }

            $('#confirmationModal .modal-title').text('Konfirmasi');
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
                    url: '<?= base_url()?>invoicing/approvereceiving',
                    type: 'POST',
                    data: {
                        dropbox_id: data.dropbox_id,
                        status_receive: newStatus
                    },
                    success: function(response) {
                        row.data($.extend({}, data, {
                            status_receive: newStatus
                        })).draw();
                        if (newStatus === 'Y') {
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
                        fetchUnokData();
                        $('#confirmationModal').modal('hide');
                    },
                    error: function(xhr, status, error) {
                        console.error(error);
                    }
                });
            });
        });

        function fetchUnokData() {
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

        function fetchUnreceivedData() {
            $.ajax({
                url: '<?= base_url() ?>indexedstatus/receiving-status',
                method: 'GET',
                success: function(data) {
                    $('#unreceive-count').text(data.unreceive.length);
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching data:', error);
                }
            });
        }

        function displayError(error) {
            var table = $('#example').DataTable();
            var errorContainer = document.getElementById('errorContainer');
            errorContainer.innerHTML = ''; // Membersihkan pesan sebelumnya (jika ada)
            table.clear().draw();
            if (Array.isArray(error)) {
                error.forEach(function(message) {
                    errorContainer.innerHTML += '<div class="alert alert-danger">' + message + '</div>';
                });
            } else {
                errorContainer.innerHTML = '<div class="alert alert-danger">' + error + '</div>';
            }
        }
    });


    $(function () {
        
        // DataTables initialisation
        let minDate, maxDate;
        
        DataTable.ext.search.push(function (settings, data, dataIndex) {
            let min = minDate.val();
            let max = maxDate.val();
            let date = new Date(data[4]);
        
            if (
                (min === null && max === null) ||
                (min === null && date <= max) ||
                (min <= date && max === null) ||
                (min <= date && date <= max)
            ) {
                return true;
            }
            return false;
        });
        
        // Create date inputs
        minDate = new DateTime('#min', {
            format: 'DD MMMM YYYY'
        });
        maxDate = new DateTime('#max', {
            format: 'DD MMMM YYYY'
        });
        
        // DataTables initialisation
        let table = new DataTable('#example');
        
        // Refilter the table
        document.querySelectorAll('#min, #max').forEach((el) => {
            el.addEventListener('change', () => table.draw());
        });

    });
</script>

<?= $this->endSection() ?>