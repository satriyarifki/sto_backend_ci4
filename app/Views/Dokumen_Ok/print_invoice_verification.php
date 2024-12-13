<?= $this->extend('template/default') ?>

<?= $this->section('content') ?>
<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog" aria-labelledby="confirmationModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
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
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tidak</button>
                        <button type="button" class="btn btn-primary" id="btnContinue">Ya</button>
                    </div>
                    </div>
                </div>
            </div>
            <div class="card-header">
                <h3 class="card-title">Print Invoice Verification</h3>
            </div>
            <div class="card-body">
                <div id="errorContainer"></div>
                <table id="example" class="display nowrap" style="width:100%">
                    <thead>
                        <tr>
                            <th>Nomor Invoice</th>
                            <th>Nomor Dropbox</th>
                            <th>Nomor Verifikasi</th>
                            <th>Company Name</th>
                            <th>User Generate</th>
                            <th>Nomor Zinver</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
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

$(document).ready(function(){
    $('#successModal').modal('show');

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
    });

    updateTableContent();

    function updateTableContent() {
        $('#example').DataTable().processing(true);

        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'document-invoice', true);
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                var data = JSON.parse(xhr.responseText);
                if (data.hasOwnProperty('getInvoiceAfterDokOk')) {
                    updateTable(data.getInvoiceAfterDokOk);
                    $('#example').DataTable().processing(false);
                }
            } else {
                var errorData = JSON.parse(xhr.responseText);
                if (errorData.hasOwnProperty('error')) {
                    displayError(errorData.error);
                    $('#example').DataTable().processing(false);
                }
            }
        };
        var data = JSON.stringify({});
        xhr.send(data);
    }
    
    function updateTable(data) {
        var table = $('#example').DataTable();
        var errorContainer = document.getElementById('errorContainer');
        errorContainer.innerHTML = '';
        table.clear().draw();
        data.forEach(function(value) {
            var sendToFinanceDisabled = value.print == 0 ? 'disabled' : '';
            var holdBtnDisabled = value.print != 0 ? 'disabled' : '';
            var deletePud = value.print == 0 ? 'disabled' : '';
            if (value.no_zinver != '' || value.no_zinver === null) {
                modalTitle = 'Konfirmasi';
                modalBodyContent = 'Apakah ingin melakukan print lagi ?';
                value.no_zinver = parseInt(value.no_zinver, 10);
            } else {
                modalTitle = 'Isi tanggal jatuh tempo';
                modalBodyContent = '<input type="date" id="tanggalJatuhTempo" class="form-control">';
            }
            table.row.add([
                value.no_invoice,
                value.dropbox_id,
                value.invoicing_id,
                value.company_name,
                value.user_generate,
                value.no_zinver,
                '<button class="btn btn-info print-btn"><i class="fa fa-print" aria-hidden="true"></i>  ' + value.print + '</button> <button class="btn btn-danger delete-btn" '+ deletePud + '><i class="fa fa-trash" aria-hidden="true"></i></button> <button class="btn btn-secondary finance-btn" ' + sendToFinanceDisabled + '>Send to Finance</button> <button class="btn btn-primary hold-btn" ' + holdBtnDisabled + '>Hold</button>'
            ]).draw();
        });
    }

    $('#example tbody').on('click', 'button.print-btn', function() {
        var tr = $(this).closest('tr');
        var row = table.row(tr);
        var noInvoice = row.data()[0];
        var noDropbox = row.data()[1];
        var noZinver = row.data()[5];

        console.log('no zinver open modal = ', noZinver);

        if (noZinver === '' || noZinver === null) {
            $('#confirmationModal .modal-title').html('Isi tanggal jatuh tempo');
            $('#confirmationModal .modal-body').html(`
                <div class="input-group">
                    <div class="input-group-append">
                        <span class="input-group-text">Progress (%)</span>
                    </div>
                    <input type="text" id="progress" class="form-control">
                </div>
                <label for="tanggalJatuhTempo" style="margin-top: 10px;">Tanggal Jatuh Tempo</label>
                <input type="date" id="tanggalJatuhTempo" class="form-control" style="margin-bottom: 10px;">
                <label for="qcd">QCD</label>
                <select id="qcd" class="form-control" style="margin-bottom: 15px;">
                    <option value=""></option>
                    <option value="RO (Repeat Order)">RO (Repeat Order)</option>
                    <option value="Autorize">Autorize</option>
                    <option value="Single Supplier">Single Supplier</option>
                    <option value="QCD">QCD</option>
                </select>
            `);
            $('#btnContinue').prop('disabled', true);
            $('#confirmationModal').modal('show');

            function checkFormFields() {
                var tanggalJatuhTempo = $('#tanggalJatuhTempo').val();
                var progress = $('#progress').val();
                var qcd = $('#qcd').val();

                if (tanggalJatuhTempo && progress && qcd) {
                    $('#btnContinue').prop('disabled', false);
                } else {
                    $('#btnContinue').prop('disabled', true);
                }
            }

            $('#tanggalJatuhTempo, #progress, #qcd').on('input change', checkFormFields);
        } else {
            $('#confirmationModal .modal-title').html('Konfirmasi');
            $('#confirmationModal .modal-body').text('Apakah ingin melakukan print lagi ?');
            $('#confirmationModal').modal('show');
        }

        $('#btnContinue').off('click').on('click', function() {
            var tanggalJatuhTempo = $('#tanggalJatuhTempo').val();
            var progress = $('#progress').val();
            var qcd = $('#qcd').val();
            console.log('no zinver button continue = ', noZinver);
            if (noZinver === '' || noZinver === null) {
                if (tanggalJatuhTempo === '') {
                    toastr.info('Masukan tanggal jatuh tempo terlebih dahulu');
                } else {
                    $.ajax({
                        url: '<?= base_url()?>invoicing/printdok_ok',
                        type: 'POST',
                        data: {
                            no_invoice: noInvoice,
                            no_dropbox: noDropbox,
                            tanggal_jatuh_tempo: tanggalJatuhTempo,
                            progress: progress,
                            qcd: qcd
                        },
                        success: function(response) {
                            var url = '<?= base_url()?>invoicing/printdok_ok_count?no_invoice=' + encodeURIComponent(noInvoice) + '&no_dropbox=' + encodeURIComponent(noDropbox);
                            window.location.href = url;
                        },
                        error: function(xhr, status, error) {
                            toastr.error('Terjadi kesalahan saat mengirim data');
                        }
                    });
                }
            } else {
                var url = '<?= base_url()?>invoicing/printdok_ok_count?no_invoice=' + encodeURIComponent(noInvoice) + '&no_dropbox=' + encodeURIComponent(noDropbox);
                window.location.href = url;
            }
            // $('#confirmationModal').modal('hide');
        });
    });

    $('#example tbody').on('click', 'button.delete-btn', function() {
        var tr = $(this).closest('tr');
        var row = table.row(tr);
        var noinvoice = row.data()[0];
        var invoicing_id = row.data()[2];
        var nozinver = row.data()[5];

        console.log('nodropbox open modal = ', invoicing_id);

        $('#confirmationModal .modal-title').html('Apa alasan anda menghapus data tersebut ?');
        $('#confirmationModal .modal-body').html('<input type="text" id="formAlasan" class="form-control">');
        $('#confirmationModal').modal('show');

        $('#btnContinue').off('click').on('click', function() {
            var formAlasan = $('#formAlasan').val();
            $.ajax({
                url: '<?= base_url()?>invoicing/delete-pud', // Ganti dengan URL controller yang sesuai
                type: 'POST',
                data: {
                    invoicing_id: invoicing_id,
                    nozinver: nozinver,
                    formAlasan: formAlasan,
                },
                success: function(response) {
                    $('#confirmationModal').modal('hide');
                    toastr.success('Data ' + noinvoice + ' berhasil dihapus');
                    updateTableContent();
                },
                error: function(xhr, status, error) {
                    toastr.error('Terjadi kesalahan saat mengirim data');
                }
            });
            // $('#confirmationModal').modal('hide');
        });
    });

    $('#example tbody').on('click', 'button.finance-btn', function() {
        var tr = $(this).closest('tr');
        var row = table.row(tr);
        var noinvoice = row.data()[0];
        var invoicing_id = row.data()[2];
        var nozinver = row.data()[5];

        $('#confirmationModal .modal-title').html('Confirm');
        $('#confirmationModal .modal-body').html('Pastikan data sudah valid sebelum mengirim ke Finance');
        // $('#confirmationModal .modal-body').html('<label for="formNik">NIK</label><input type="text" id="formNik" class="form-control">');
        $('#confirmationModal').modal('show');

        $('#btnContinue').off('click').on('click', function() {
            var formNik = $('#formNik').val();
            $.ajax({
                url: '<?= base_url()?>invoicing/out-pud',
                type: 'POST',
                data: {
                    invoicing_id: invoicing_id,
                    nozinver: nozinver
                    // formNik: formNik
                },
                success: function(response) {
                    $('#confirmationModal').modal('hide');
                    toastr.success('Data ' + noinvoice + ' berhasil terkirim ke finance');
                    updateTableContent();
                },
                error: function(xhr, status, error) {
                    toastr.error('Terjadi kesalahan saat mengirim data');
                }
            });
            // $('#confirmationModal').modal('hide');
        });
    });

    $('#example tbody').on('click', '.hold-btn', function() {
        var tr = $(this).closest('tr');
        var row = table.row(tr);
        var data = row.data();
        var modalLarge = 'modal-lg';
        var modalSmall = 'modal-sm';

        var Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000
        });


        console.log('data', data);
        console.log('invoiceId', data[2]);
        console.log('user_generate', data[4]);

        $('#confirmationModal .modal-title').text('Option');
        $('#confirmationModal .modal-body').html(`
            <div style="margin-top: 20px; text-align: center;">
                <button type="button" class="btn btn-primary" id="btnHoldProcess" >Hold Process</button>
                <button type="button" class="btn btn-danger" id="btnCancelProcess" >Cancel Process</button>
            </div>
        `);
        $('#confirmationModal .modal-dialog').removeClass(modalLarge).addClass(modalSmall);
        $('#confirmationModal').modal('show');

        $('#btnCancelProcess').off().on('click', function() {
            $('#confirmationModal').modal('hide'); 
            setTimeout(function() {
                $('#confirmationModal .modal-title').text('Form Proses Cancel');
                $('#confirmationModal .modal-body').html(`
                    <label for="formAlasan">Alasan Cancel</label>
                    <textarea style="height: 100px;" id="formAlasan" class="form-control"></textarea>
                    <label for="formPesan" style="margin-top: 10px;">Pesan (Data untuk email ke Vendor)</label>
                    <textarea style="height: 250px;" id="formPesan" class="form-control"></textarea>
                    <div style="margin-top: 20px; text-align: right;">
                        <button type="button" class="btn btn-success" id="btnSubmit">Submit</button>
                    </div>
                `);
                $('#confirmationModal .modal-dialog').removeClass(modalSmall).addClass(modalLarge);
                $('#confirmationModal').modal('show');
                
                $('#formPesan').on('input', function() {
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
                        url: '<?= base_url()?>invoicing/cancel-process-from-pud',
                        type: 'POST',
                        data: {
                            no_invoice: data[0],
                            uservendor: data[4],
                            invoice_id: data[2],
                            alasan: alasan,
                            pesan: pesan,
                        },
                        success: function(response) {
                            $('#spinner-container').hide();
                            $('#confirmationModal').modal('hide');
                            Toast.fire({
                                icon: 'success',
                                title: 'Data ' + data.no_invoice + ' berhasil ditahan, Email sudah terkirim ke ' + data[3]
                            });
                        },
                        error: function(xhr, status, error) {
                            console.error(error);
                        }
                    });
                });

            }, 500); 
        });
    });

    $(document).on('click', '.approve-link', function(e) {
        e.preventDefault();
        var url = $(this).attr('href');

        $.ajax({
            url: url,
            type: 'GET',
            success: function(response) {
                alert('SAP berhasil disetujui!');
                updateTableContent();
            },
            error: function(xhr, status, error) {
                console.error(error); 
            }
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
    let minDate, maxDate;
    
    // Custom filtering function which will search data in column four between two values
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