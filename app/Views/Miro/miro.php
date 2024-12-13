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
                <h5 class="card-title">MIRO</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-auto">
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-append">
                                    <span class="input-group-text">Vendor</span>
                                    <select class="form-control select2bs4" id="vendorSelect">
                                        <option value="">-- Select Vendor --</option>
                                    </select>
                                </div>
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
                            <th>Nomor Invoice</th>
                            <th>Nomor Pajak</th>
                            <th>Company Name</th>
                            <th>Nomor Verifikasi</th>
                            <th>Nomor Dropbox</th>
                            <th>Nomor Zinver</th>
                            <th>DPP + PPN</th>
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
    div.dt-processing>div:last-child {
        display: none;
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
            var createDateFormatted = formatDate(invoice.create_date);
            var dateApproveFormatted = formatDate(invoice.date_approve);
            trs += '<tr><td>' + invoice.invoicing_id + '</td><td>' + invoice.no_invoice + '</td><td>' + invoice.no_item + '</td><td>' + invoice.no_gr + '</td><td>' + invoice.no_po + '</td></tr>';
        });
        return '<div class="slider">'+
        '<table class="table table-border table-hover">' +
            '<thead>' +
            '<th>Nomor Verifikasi</th>' +
            '<th>Nomor Invoice</th>' +
            '<th>Nomor Item</th>' +
            '<th>Nomor GR</th>' +
            '<th>Nomor PO</th>' +
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
            },
            layout: {
                topStart: false,
                topEnd: 'search',
                bottomStart: 'pageLength',
                bottom2Start: 'info',
            },
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
                { 'data': 'no_invoice' },
                { 'data': 'tax_number' },
                { 'data': 'company_name' },
                { 'data': 'invoicing_id' },
                { 'data': 'dropbox_id' },
                {
                    'data': 'no_zinver',
                    'render': function(data) {
                        return parseInt(data, 10);
                    }
                },
                { 
                    'data': 'total_payment',
                    'render': function(data) {
                        return 'Rp ' + parseFloat(data).toLocaleString('id-ID', { minimumFractionDigits: 0 });
                    }
                },
                { 
                    'data': 'status_miro',
                    'render': function(data) {
                        if (data === 'Y') {
                            return '<span class="badge badge-success">Approved</span>';
                        } else {
                            return '<span class="badge badge-warning">Pending</span>';
                        }
                    }
                },
                {
                    'data': 'status_miro',
                    'orderable': false,
                    'render': function(data, type, row) {
                        if (data === 'Y') {
                            var button = '<button class="btn-update btn btn-danger btn-cancel" data-action="cancel" data-dropbox-id="' + row.no_invoice + '">Cancel Approve</button>';
                            if (row.status_paid === 'Y') {
                                button = '<button class="btn-update btn btn-danger btn-cancel" data-action="cancel" data-dropbox-id="' + row.no_invoice + '" disabled>Cancel Approve</button>';
                            }
                            return button;
                        } else {
                            return '<button class="btn-update btn btn-success btn-approve" data-action="approve" data-dropbox-id="' + row.no_invoice + '">Approve</button>';
                        }
                    }
                },
                {
                    'data': 'status_miro',
                    'orderable': false,
                    'render': function(data, type, row) {
                        if (data === 'Y') {
                            return '<button class="btn btn-secondary on-hold-btn" data-dropbox-id="' + row.no_invoice + '" data-user-generate="' + row.user_generate + '" disabled>On Hold</button>';
                        } else {
                            return '<button class="btn btn-secondary on-hold-btn" data-dropbox-id="' + row.no_invoice + '" data-user-generate="' + row.user_generate + '">On Hold</button>';
                        }
                    }
                }
            ]
        });

        $('#loadDataBtn').on('click', function() {
            var vendor = document.getElementById('vendorSelect').value;

            if (vendor) {
                $('#example').DataTable().processing(true);
                $.ajax({
                    url: '<?= base_url()?>invoicing/miro-json',
                    type: 'POST',
                    data: { vendor: vendor },
                            
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
            } else {
                toastr.info('Masukan Nomor Invoice atau Zinver serta Vendor nya')
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

            $('#confirmationModal').data('tr', tr);
            $('#confirmationModal').data('row', row);
            $('#confirmationModal').data('action', action);

            if (action === 'approve') {
                $('#confirmationModal .modal-body').text('Apakah Data Invoice ' + data.no_invoice + ' sudah sesuai?');
            } else if (action === 'cancel') {
                $('#confirmationModal .modal-body').text('Apakah anda yakin akan Cancel Data ' + data.no_invoice + '?');
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
                    url: '<?= base_url()?>invoicing/miro-approve',
                    type: 'POST',
                    data: {
                        no_invoice: data.no_invoice,
                        invoicing_id: data.invoicing_id,
                        status_miro: newStatus
                    },
                    success: function(response) {
                        row.data($.extend({}, data, {
                            status_miro: newStatus
                        })).draw();
                        if (newStatus === 'Y') {
                            Toast.fire({
                                icon: 'success',
                                title: 'Data ' + data.no_invoice + ' berhasil diapprove. Proses persetujuan telah selesai.'
                            })
                        } else {
                            $(tr).find('.btn-update').text('Approve');
                            Toast.fire({
                                icon: 'error',
                                title: 'Pembatalan persetujuan untuk data ' + data.no_invoice + '. Silakan pastikan kembali datanya.'
                            })
                        }
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
            var modalLarge = 'modal-lg';
            var modalSmall = 'modal-sm';
            var inputData = $('#inputData').val();
            var vendor = document.getElementById('vendorSelect').value;

            var Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000
            });


            console.log('data', data);
            console.log('action', action);
            console.log('dropboxId', dropboxId);
            console.log('noZinver', data.no_zinver);

            $('#confirmationModal .modal-title').text('Option');
            $('#confirmationModal .modal-body').html(`
                <div style="margin-top: 20px; text-align: center;">
                    <button type="button" class="btn btn-primary" id="btnHoldProcess" >Hold Process</button>
                    <button type="button" class="btn btn-danger" id="btnCancelProcess" >Cancel Process</button>
                </div>
            `);
            $('#confirmationModal .modal-dialog').removeClass(modalLarge).addClass(modalSmall);
            $('#confirmationModal').modal('show');

            ///////////////////////////////////// onhold process /////////////////////////////////////

            $('#btnHoldProcess').off().on('click', function() {
                $('#confirmationModal').modal('hide'); 
                setTimeout(function() {
                    $('#confirmationModal .modal-title').text('Form Proses Hold');
                    $('#confirmationModal .modal-body').html(`
                        <label for="formPesan" style="margin-top: 10px;">Pesan (Data untuk email ke Vendor)</label>
                        <textarea style="height: 250px;" id="formPesan" class="form-control"></textarea>
                        <div style="margin-top: 20px; text-align: right;">
                            <button type="button" class="btn btn-success" id="btnSubmit">Submit</button>
                        </div>
                    `);
                    $('#confirmationModal .modal-dialog').removeClass(modalSmall).addClass(modalLarge);
                    $('#confirmationModal').modal('show');

                    $('#formPesan').on('input', function() {
                        if ($('#formPesan').val().trim() === '') {
                            $('#btnSubmit').prop('disabled', true);
                        } else {
                            $('#btnSubmit').prop('disabled', false);
                        }
                    });

                    $('#btnSubmit').off().on('click', function() {
                        var alasan = $('#formAlasan').val();
                        var pesan = $('#formPesan').val();
                        $('#spinner-container').show();

                        $.ajax({
                            url: '<?= base_url()?>invoicing/on-hold-email-v2',
                            type: 'POST',
                            data: {
                                no_invoice: data.no_invoice,
                                uservendor: user_generate,
                                invoice_id: data.invoices[0].invoicing_id,
                                input_zinver: data.no_zinver,
                                pesan: pesan,
                            },
                            success: function(response) {
                                $('#spinner-container').hide();
                                $('#confirmationModal').modal('hide');
                                Toast.fire({
                                    icon: 'success',
                                    title: 'Data ' + data.no_invoice + ' berhasil ditahan, Email sudah terkirim ke ' + user_generate
                                });

                                if (vendor) {
                                    $('#example').DataTable().processing(true);
                                    $.ajax({
                                        url: '<?= base_url()?>invoicing/miro-json',
                                        type: 'POST',
                                        data: {
                                                vendor: vendor
                                            },
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
                            },
                            error: function(xhr, status, error) {
                                console.error(error);
                            }
                        });
                    });

                }, 500);
            });

            ///////////////////////////////////// onhold process /////////////////////////////////////

            $('#btnCancelProcess').off().on('click', function() {
                $('#confirmationModal').modal('hide'); 
                setTimeout(function() {
                    $('#confirmationModal .modal-title').text('Form Proses Cancel');
                    $('#confirmationModal .modal-body').html(`
                        <label for="formAlasan">Alasan (Data untuk Zinver)</label>
                        <textarea style="height: 100px;" id="formAlasan" class="form-control"></textarea>
                        <label for="formPesan" style="margin-top: 10px;">Pesan (Data untuk email ke Vendor)</label>
                        <textarea style="height: 250px;" id="formPesan" class="form-control"></textarea>
                        <div style="margin-top: 20px; text-align: right;">
                            <button type="button" class="btn btn-success" id="btnSubmit">Submit</button>
                        </div>
                    `);
                    $('#confirmationModal .modal-dialog').removeClass(modalSmall).addClass(modalLarge);
                    $('#confirmationModal').modal('show');
                    
                    $('#formAlasan, #formPesan').on('input', function() {
                        if ($('#formAlasan').val().trim() === '' || $('#formPesan').val().trim() === '') {
                            $('#btnSubmit').prop('disabled', true);
                        } else {
                            $('#btnSubmit').prop('disabled', false);
                        }
                    });

                    $('#btnSubmit').off().on('click', function() {
                        var alasan = $('#formAlasan').val();
                        var pesan = $('#formPesan').val();
                        $('#spinner-container').show();

                        $.ajax({
                            url: '<?= base_url()?>invoicing/cancel-process',
                            type: 'POST',
                            data: {
                                no_invoice: data.no_invoice,
                                uservendor: user_generate,
                                invoice_id: data.invoices[0].invoicing_id,
                                input_zinver: data.no_zinver,
                                alasan: alasan,
                                pesan: pesan,
                            },
                            success: function(response) {
                                $('#spinner-container').hide();
                                $('#confirmationModal').modal('hide');
                                Toast.fire({
                                    icon: 'success',
                                    title: 'Data ' + data.no_invoice + ' berhasil ditahan, Email sudah terkirim ke ' + user_generate
                                });

                                if (vendor) {
                                    $('#example').DataTable().processing(true);
                                    $.ajax({
                                        url: '<?= base_url()?>invoicing/miro-json',
                                        type: 'POST',
                                        data: { 
                                                vendor: vendor
                                            },
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
                            },
                            error: function(xhr, status, error) {
                                console.error(error);
                            }
                        });
                    });

                }, 500); 
            });
        });

        function displayError(error) {
            var table = $('#example').DataTable();
            var errorContainer = document.getElementById('errorContainer');
            errorContainer.innerHTML = '';
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
        $(".select2bs4").select2({
            theme: "bootstrap4",
            placeholder: 'Select a vendor',
            ajax: {
                url: '<?= base_url()?>dashboard/vendor-id',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        search: params.term
                    };
                },
                processResults: function (data) {
                    return {
                        results: $.map(data.result, function (item) {
                            return {
                                text: item.company_name,
                                id: item.vendor_code
                            };
                        })
                    };
                },
                cache: true
            }
        });
        
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