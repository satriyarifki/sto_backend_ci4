<?= $this->extend('template/default') ?>
<?= $this->section('content') ?>
<section class="content">
    <div class="container-fluid">

    <div class="row">
        <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Dropbox Process</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-auto">
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-append">
                                    <span class="input-group-text">Start Date</span>
                                </div>
                                <input type="date" id="startDate" class="form-control" />
                            </div>
                        </div>
                    </div>
                    <div class="col-auto text-center align-self-center">
                        <p>to</p>
                    </div>
                    <div class="col-auto">
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-append">
                                    <span class="input-group-text">End Date</span>
                                </div>
                                <input type="date" id="endDate" class="form-control" />
                            </div>
                        </div>
                    </div>
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
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-append">
                                    <span class="input-group-text">Vendor</span>
                                    <select 
                                        class="form-control select2bs4" 
                                        id="vendorSelect" 
                                        style="width: 300px;"
                                        <?php echo ($ionAuth->inGroup('vendor')) ? 'disabled' : ''; ?>>
                                        <option value="">-- Select Vendor --</option>
                                        <?php if($ionAuth->inGroup('vendor')): ?>
                                            <option value="<?= $current_user->vendor_code ?>" selected>
                                                <?= $current_user->company_name ?>
                                            </option>
                                        <?php endif; ?>
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
                            <th>Status</th>
                            <th>Tanggal Proses</th>
                            <th>Nomor Dropbox</th>
                            <th>Tanggal Dropbox</th>
                            <th>Tanggal Submit</th>
                            <th>Company Name</th>
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
            trs += '<tr><td>' + invoice.invoicing_id + '</td><td>' + invoice.no_invoice + '</td><td>' + invoice.no_po + '</td><td>' + invoice.no_item + '</td><td>' + invoice.no_gr + '</td></tr>';
        });
        return '<div class="slider">'+
        '<table class="table table-border table-hover">' +
            '<thead>' +
            '<th>Nomor Verifikasi</th>' +
            '<th>Nomor Invoice</th>' +
            '<th>Nomor PO</th>' +
            '<th>Nomor Item</th>' +
            '<th>Nomor GR</th>' +
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

        function formatDate(dateString) {
            if (dateString) {
                var dateParts = dateString.split('-');
                var year = dateParts[0];  // Tahun
                var month = dateParts[1];  // Bulan
                var day = dateParts[2];    // Hari

                // Array nama bulan dalam Bahasa Indonesia
                var monthNames = [
                    "Januari", "Februari", "Maret", "April", "Mei", "Juni",
                    "Juli", "Agustus", "September", "Oktober", "November", "Desember"
                ];

                return parseInt(day) + ' ' + monthNames[parseInt(month) - 1] + ' ' + year;
            }
            return 'N/A';
        }
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
                { 
                    'data': 'status', 
                    'render': function(data, type, row) {
                        return '<button class="btn ' + row.approveClass + '">' + data + '</button>';
                    }
                },
                { 
                    'data': 'dateTime',
                    'render': function(data, type, row) {
                        return formatDate(row.dateTime);
                    }
                },
                { 'data': 'dropbox_id' },
                { 
                    'data': 'generated_date',
                    'render': function(data, type, row) {
                        return formatDate(row.generated_date);
                    }
                },
                { 
                    'data': 'due_date',
                    'render': function(data, type, row) {
                        return formatDate(row.due_date);
                    }
                },
                { 'data': 'company_name' },
            ]
        });

        $('#loadDataBtn').on('click', function() {
            var inputData = $('#inputData').val();
            var startDate = $('#startDate').val();
            var endDate = $('#endDate').val();
            var vendorSelect = $('#vendorSelect').val();

            if ((startDate || endDate || inputData) && vendorSelect) {
                table.processing(true);
                $.ajax({
                    url: '<?= base_url()?>invoicing/dropbox-json-maj',
                    type: 'POST',
                    data: {
                        dropbox_id: inputData,
                        startDate: startDate,
                        endDate: endDate,
                        vendorSelect: vendorSelect,
                    },
                    success: function(response) {
                        table.processing(false);
                        if (response.data.status === false) {
                            displayError(response.data.error);
                        } else {
                            var data = response.data;
                            var errorContainer = document.getElementById('errorContainer');
                            errorContainer.innerHTML = '';
                            var requests = data.map(function(item) {
                                return $.ajax({
                                    url: '<?= base_url()?>invoicing/status-dropbox',
                                    method: 'POST',
                                    data: {
                                        dropbox_id: item.dropbox_id
                                    },
                                    dataType: 'json'
                                }).then(function(response) {
                                    if (response.result.expired == "X") {
                                        item.status = 'Expired';
                                        item.approveClass = 'btn-danger';
                                        item.dateTime = '';
                                    } else {
                                        if (response.result.status_receive == "Y") {
                                            if (response.result.dok_ok == "Y") {
                                                item.status = 'Verifiying';
                                                item.approveClass = 'btn-info'; 
                                                item.dateTime = response.result.date_dok_ok; 
                                            } else {
                                                item.status = 'Received';
                                                item.approveClass = 'btn-secondary';
                                                item.dateTime = response.result.date_approve; 
                                            }
                                        } else {
                                            item.status = 'Delivery';
                                            item.approveClass = 'btn-warning';
                                            item.dateTime = '';  
                                        }
                                    }
                                }, function() {
                                    item.status = 'Unknown';
                                    item.approveClass = 'btn-default';
                                    item.dateTime = '';  
                                });
                            });

                            $.when.apply($, requests).done(function() {
                                table.clear().rows.add(data).draw();
                            });
                        }
                    },
                    error: function() {
                        table.processing(false);
                        alert('Terjadi kesalahan pada server.');
                    }
                });
            } else {
                toastr.info('Masukan input tanggal atau nomor dropbox terlebih dahulu')
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
                        $('#confirmationModal').modal('hide');
                    },
                    error: function(xhr, status, error) {
                        console.error(error);
                    }
                });
            });
        });

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
        
        var vendorCode = '<?= $current_user->vendor_code ?>';

        <?php if($ionAuth->inGroup('maj') || $ionAuth->inGroup('admin')) :?>
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

        <?php elseif ($ionAuth->inGroup('vendor')): ?>
            var newOption = new Option('<?= $current_user->company_name ?>', vendorCode, true, true);
            $('#vendorSelect').append(newOption).trigger('change');
            $('#vendorSelect').prop('disabled', true);
        <?php endif; ?>

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