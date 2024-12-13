<?= $this->extend('template/default') ?>
<?= $this->section('content') ?>
<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Registration Dropbox</h3>
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
                                <span class="input-group-text">Nomor Invoice</span>
                            </div>
                            <input type="text" id="invoicenumber" class="form-control" />
                        </div>
                    </div>
                </div>
                <div class="col-auto">
                    <button id="searchData" class="btn btn-info">Search</button>
                </div>
            </div>
            <div id="errorContainer"></div>
            <table id="example" class="display nowrap" style="width:100%">
                <thead>
                    <tr>
                        <th>Select</th>
                        <th>No. Invoice</th>
                        <th>No. Verifikasi</th>
                        <th>User Create</th>
                        <th>Company Name</th>
                        <th>Create Date</th>
                        <th>Total Payment</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
            <button id="btn-ver" class="btn btn-info"></button>
        </div>
        </div>
</section>

<div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog" aria-labelledby="confirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmationModalLabel">Pilih Tanggal</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="text" id="datepicker" class="form-control" placeholder="Pilih Tanggal">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">
                    <i class="fa fa-ban" aria-hidden="true"></i> Batal
                </button>
                <button type="button" class="btn btn-primary" id="btnContinue">
                    <i class="fa fa-check-circle" aria-hidden="true"></i> Lanjutkan
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@9.17.2/dist/sweetalert2.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/sweetalert2@9.17.2/dist/sweetalert2.min.css">
<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>

<style>
    div.dt-processing>div:last-child {
        display: none;
    }

    .spinner-border {
        width: 50px;
        height: 50px;
    }
</style>

<script>
$(document).ready(function(){
    $('#successModal').modal('show');

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
        "scrollY": "650px",
        "sScrollX": "100%",
        "scrollCollapse": true,     
    });

    $('#btn-ver').hide();

    $('#datepicker').datepicker({
        dateFormat: 'yy-mm-dd',
        beforeShowDay: function(date) {
            var day = date.getDay();
            if (day === 2 || day === 3) {
                return [true, '', 'Tersedia'];
            } else {
                return [false, '', 'Nonaktif'];
            }
        }
    });

    document.getElementById('searchData').addEventListener('click', performSearch)
    document.addEventListener('keydown', performSearch)

    function performSearch(event) {
        if (event.type === 'click') {
            event.preventDefault()
            updateTableContent()
        }
    }

    function updateTableContent() {
        var startDate = document.getElementById('startDate').value;
        var endDate = document.getElementById('endDate').value;
        var invoiceNumber = document.getElementById('invoicenumber').value;

        if (startDate || endDate || invoiceNumber){
            $('#example').DataTable().processing(true);

            // Kirim permintaan AJAX ke server
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'npkp-regris-json', true);
            xhr.setRequestHeader('Content-Type', 'application/json');
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    var data = JSON.parse(xhr.responseText);
                    if (data.hasOwnProperty('result')) {
                        updateTable(data.result);
                        $('#example').DataTable().processing(false);
                        $('#btn-ver').text('Register').show();
                        $('#btn-ver').prop('disabled', true); 
                    } else if (data.hasOwnProperty('error')) {
                        displayError(data.error);
                        $('#example').DataTable().processing(false);
                    }
                } else {
                    var errorData = JSON.parse(xhr.responseText);
                    if (errorData.hasOwnProperty('error')) {
                        displayError(errorData.error);
                        $('#example').DataTable().processing(false);
                        $('#btn-ver').hide();
                    }
                }
            };
            var data = JSON.stringify({startDate: startDate, endDate: endDate, invoiceNumber: invoiceNumber});
            xhr.send(data);
        } else {
            toastr.info('Masukan input tanggal atau nomor Invoice terlebih dahulu')
        }
    }
    
    function updateTable(data) {
        var table = $('#example').DataTable();
        var errorContainer = document.getElementById('errorContainer');
        errorContainer.innerHTML = '';
        table.clear().draw();
        data.forEach(function(value) {
            var checkbox = '<div class="form-check text-center"><input class="form-check-input check-item" type="checkbox" style="transform: scale(1.8);"><label class="form-check-label"></label></div>';
            var formattedTotalPayment = parseFloat(value['total_payment']).toLocaleString('id-ID', { style: 'currency', currency: 'IDR' });
            table.row.add([
                checkbox,
                value['no_invoice'],
                value['invoicing_id'],
                value['user_create'],
                value['company_name'],
                value['create_date'],
                formattedTotalPayment,
            ]).draw();
        });

        $('.check-item').on('change', function() {
            var checkedRowsData = [];
            $('.check-item').each(function() {
                if ($(this).is(':checked')) {
                    var rowData = table.row($(this).parents('tr')).data();
                    checkedRowsData.push(rowData);
                }
            });

            if (checkedRowsData.length === 0) {
                $('#btn-ver').prop('disabled', true);
            } else {
                $('#btn-ver').prop('disabled', false);
            }
        });
    }

    $('#btn-ver').on('click', function() {
        var checkedRowsData = [];
        $('.check-item').each(function() {
            if ($(this).is(':checked')) {
                var rowData = table.row($(this).parents('tr')).data();
                checkedRowsData.push(rowData);
            }
        });

        if (checkedRowsData.length === 0) {
            $('#btn-ver').prop('disabled', true);
            return;
        } else {
            $('#btn-ver').prop('disabled', false);
        }

        $('#confirmationModal').modal('show');
        $('#datepicker').datepicker({
            dateFormat: 'yy-mm-dd',
            changeMonth: true,
            changeYear: true
        });

        $('#btnContinue').on('click', function() {
            var selectedDate = $('#datepicker').val();

            if (selectedDate === '') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Pilih Tanggal',
                    text: 'Harap pilih tanggal sebelum melanjutkan.',
                });
                return;
            }

            $.ajax({
                url: '<?= base_url() ?>dropbox/datapushdropboxnpkp',
                type: 'POST',
                contentType: 'application/json',
                dataType: 'json',
                data: JSON.stringify({ checkedData: checkedRowsData, date: selectedDate }),
                success: function(response) {
                    $('#confirmationModal').modal('hide');
                    if (response.message == true){
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.response,
                        });
                        updateTableContent();
                    } else if(response.message == false){
                        Swal.fire({
                            icon: 'error',
                            title: 'Failed',
                            text: response.response,
                        });
                        updateTableContent();
                    }
                },
                error: function(xhr, status, error) {
                    console.error(error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Failed',
                        text: 'Terjadi kesalahan saat memproses permintaan. Silakan coba lagi nanti.'
                    });
                }
            });
            
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
    let table = new DataTable('#example');
});
</script>
<?= $this->endSection() ?>