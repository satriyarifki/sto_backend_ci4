<?= $this->extend('template/default') ?>

<?= $this->section('content') ?>
<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div id="errorContainer"></div>
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
                        Apakah Anda yakin ingin melanjutkan?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tidak</button>
                        <button type="button" class="btn btn-primary" id="btnContinue">Ya</button>
                    </div>
                    </div>
                </div>
            </div>
        <div id="spinner-container">
            <button class="btn btn-primary">
                <span class="spinner-border spinner-border-sm"></span>
                Loading..
            </button>
        </div>
            <div class="card-header">
                <h3 class="card-title">Verification</h3>
            </div>
            <div class="card-body verify">
                <div class="row">
                    <div class="col-auto">
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-append">
                                    <span class="input-group-text">Nomor PO</span>
                                </div>
                                <input type="text" id="ponumber" name="ponumber" class="form-control" placeholder="Masukkan Nomor PO" style="width: 50%;">
                            </div>
                        </div>
                    </div>
                    <div class="col-auto">
                        <button id="searchData" class="btn btn-info">Search</button>
                    </div>
                    <div class="col-auto">
                        <div class="input-group" id="scanKet">
                            <div class="input-group-append">
                                <span id="scanValue" class="input-group-text"></span>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- <div class="row">
                    <div class="col-auto">
                        <div class="form-group" id="scanForm">
                            <div class="input-group" id="scanKet">
                                <div class="input-group-append">
                                    <span id="scanValue" class="input-group-text"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> -->
                <div id="example1">
                <table id="example" class="display nowrap" style="width:100%">
                    <thead>
                        <tr>
                            <th>
                                <div class="form-check text-center">
                                    <input class="form-check-input" type="checkbox" id="selectAll" style="transform: scale(1.8);">
                                    <label class="form-check-label"></label>
                                </div>
                            </th>
                            <th>No. PO</th>
                            <th>No. GR</th>
                            <th>No. Item</th>
                            <th>No. Surat Jalan</th>
                            <th>Part Number</th>
                            <th>Part Name</th>
                            <th>Qty</th>
                            <th>Harga</th>
                            <th>Tanggal GR</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
                </div>
                <button id="btn-ver" class="btn btn-info"></button>
            </div>
            <div class="card-body fill"></div>
        </div>
</section>

<div class="modal fade" id="lampiranModal" tabindex="-1" role="dialog" aria-labelledby="lampiranModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="lampiranModalLabel">Create QR Code</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-auto">
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-append">
                                    <span class="input-group-text">QR ID</span>
                                </div>
                                <input type="text" id="idqr" name="idqr" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="col-auto">
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-append">
                                    <span class="input-group-text">DPP</span>
                                </div>
                                <input type="text" id="totalpayment" name="totalpayment" class="form-control" placeholder="Masukan Total Harga">
                            </div>
                        </div>
                    </div>
                    <div class="col-auto">
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-append">
                                    <span class="input-group-text">Nomor Surat Transfer Inventory</span>
                                </div>
                                <input type="text" id="noinvoice" name="noinvoice" class="form-control" placeholder="Masukkan Nomor Surat Transfer Inventory">
                            </div>
                        </div>
                    </div>
                    <div class="col-auto">
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-append">
                                    <span class="input-group-text">Tanggal Surat Transfer Inventory</span>
                                </div>
                                <input type="date" id="invoicedate" name="invoicedate" class="form-control" placeholder="Masukkan Tanggal Surat Transfer Inventory">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button id="createButton" class="btn btn-info">Create</button>
            </div>
        </div>
    </div>
</div>

<style>
    #scanKet {
        display: none;
    }

    .modal-xl {
        max-width: 65%;
    }

    #spinner-container {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 1000;
        display: none;
    }

    .card.loading::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 999;
    }

    div.dt-processing>div:last-child {
        display: none;
    }

    input[type="file"]::file-selector-button {
        border-radius: 4px;
        padding: 0 16px;
        height: 40px;
        cursor: pointer;
        background-color: white;
        border: 1px solid rgba(0, 0, 0, 0.16);
        box-shadow: 0px 1px 0px rgba(0, 0, 0, 0.05);
        margin-right: 16px;
        transition: background-color 200ms;
    }

    /* file upload button hover state */
    input[type="file"]::file-selector-button:hover {
        background-color: #f3f4f6;
    }

    /* file upload button active state */
    input[type="file"]::file-selector-button:active {
        background-color: #e5e7eb;
    }

    #example_result tbody tr {
        cursor: grab;
    }
    
    #example_result tbody tr:active {
        cursor: grabbing;
    }

</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@9.17.2/dist/sweetalert2.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/sweetalert2@9.17.2/dist/sweetalert2.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.14.0/Sortable.min.js"></script>

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
            processing: '<div class="spinner-border text-primary" role="status"></div>'
        },
        "paging": false,
    });

    $('#ponumber').focus();
    $('#btn-ver').hide();

    document.getElementById('searchData').addEventListener('click', performSearch)
    document.addEventListener('keydown', performSearch)

    function performSearch(event) {
        if (event.type === 'click') {
            event.preventDefault()
            updateTableContent();
        }
    }

    function updateTableContent() {
        var poNumber = document.getElementById('ponumber').value;

        if (poNumber){
            // $('#spinner-container').show();
            $('#scanValue').text(poNumber);
            $('#scanKet').show();
            $('#example').DataTable().processing(true);
            if (poNumber === '') {
                return;
            }
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'vendor-json-verify', true);
            xhr.setRequestHeader('Content-Type', 'application/json');
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    // Tangani respon dari server
                    var data = JSON.parse(xhr.responseText);
                    if (data.hasOwnProperty('data_sap')) {
                        updateTable(data.data_sap);
                        $('#example1').show();
                        // $('#spinner-container').hide();
                        $('#example').DataTable().processing(false);
                        $('#ponumber').val('');
                        $('#btn-ver').text('Verification').show();
                        $('#btn-ver').prop('disabled', true); 
                    }
                } else {
                    var errorData = JSON.parse(xhr.responseText);
                    if (errorData.hasOwnProperty('error')) {
                        displayError(errorData.error);
                        // $('#spinner-container').hide();
                        $('#example').DataTable().processing(false);
                        $('#scanResult').show();
                        $('#ponumber').val('');
                        // $('#example1').hide();
                        $('#btn-ver').hide();
                    }
                }
            };
            var data = JSON.stringify({poNumber: poNumber});
            xhr.send(data);
        } else {
            toastr.info('Masukan input Nomor PO terlebih dahulu')
        }
    }

    var selectedRowsData = [];

    function updateTable(data) {
        var table = $('#example').DataTable();
        var errorContainer = document.getElementById('errorContainer');
        errorContainer.innerHTML = '';
        table.clear().draw();
        
        data.forEach(function(value, index) {
            var year = value.BUDAT.substr(0, 4);
            var month = value.BUDAT.substr(4, 2);
            var day = value.BUDAT.substr(6, 2);
            var date = day + '-' + month + '-' + year;
            var generatedId = 'row-' + index; 
            var checkbox = '<div class="form-check text-center"><input class="form-check-input check-item" type="checkbox" style="transform: scale(1.8);" data-row-id="' + generatedId + '"><label class="form-check-label"></label></div>';
            var wrbtrValue = parseFloat(value.WRBTR) * 100;
            var formattedWrbtrValue = wrbtrValue.toLocaleString('id-ID', { style: 'currency', currency: 'IDR' });
            table.row.add([
                checkbox,
                value.EBELN,
                value.BELNR,
                value.EBELP,
                value.BKTXT,
                value.MATNR,
                value.TXZ01,
                value.MENGE,
                formattedWrbtrValue,
                date,
            ]).draw();
        });

        $('.check-item').on('change', function() {
            var rowData = table.row($(this).parents('tr')).data();
            var rowId = $(this).data('row-id');
            if ($(this).is(':checked')) {
                if (!selectedRowsData.some(function(item) { return item.id === rowId; })) {
                    selectedRowsData.push({ id: rowId, data: rowData });
                }
            } else {
                selectedRowsData = selectedRowsData.filter(function(item) {
                    return item.id !== rowId;
                });
            }

            updateButtonState();
        });

        $('#selectAll').on('change', function() {
            var isChecked = $(this).is(':checked');
            $('.check-item').prop('checked', isChecked).trigger('change');
        });
    }


    function updateButtonState() {
        if (selectedRowsData.length === 0) {
            $('#btn-ver').prop('disabled', true);
        } else {
            $('#btn-ver').prop('disabled', false);
        }
    }

    $('#btn-ver').on('click', function() {
        if (selectedRowsData.length === 0) {
            $('#btn-ver').prop('disabled', true);
            return;
        } else {
            $('#btn-ver').prop('disabled', false);
        }

        $('#confirmationModal').modal('show');
        $('#btnContinue').off().on('click', function() {
            var checkedRowsData = selectedRowsData.map(function(item) {
                return item.data;
            });
            var checkedOrigin = selectedRowsData.map(function(item) {
                return item.data;
            });

            $.ajax({
                url: '<?= base_url() ?>invoicing/unity-mgl',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ checkedData: checkedRowsData }),
                success: function(response) {
                    $('.verify').hide();
                    $('.fill').empty();

                    var totalAmount = 0.0;

                    function renderTable(data) {
                        var tableHtml = `
                            <p class="text-muted mt-2 mr-auto">*) Sertakan QR Code sebelum mengupload Invoice</p>
                            <table id="example_result" class="display" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>No. PO</th>
                                        <th>Part Number</th>
                                        <th>Part Name</th>
                                        <th>Qty</th>
                                        <th>Harga</th>
                                    </tr>
                                </thead>
                                <tbody id="sortable-table">
                        `;

                        data.forEach(function(rowData, index) {
                            tableHtml += `
                                <tr data-index="${index}">
                                    <td>${rowData[1]}</td>
                                    <td>${rowData[5]}</td>
                                    <td>${rowData[6]}</td>
                                    <td>${rowData[7]}</td>
                                    <td>${rowData[8].toLocaleString('id-ID', { style: 'currency', currency: 'IDR' })}</td>
                                </tr>
                            `;
                        });
                        tableHtml += `
                                </tbody>
                            </table>
                        `;

                        checkedRowsData.forEach(function(rowData) {
                            var strippedString = rowData[8].replace(/[^\d]/g, "");
                            var numericValue = parseFloat(strippedString) / 100;
                            totalAmount += numericValue;
                        });
                        
                        var formattedTotalAmount = totalAmount.toLocaleString('id-ID', { style: 'currency', currency: 'IDR' });
                        $('#idqr').val('<?= $qrid ?>').prop('disabled', true);

                        tableHtml += `
                            <div class="callout callout-info">
                                <h5><i class="icon fas fa-money"></i>Total Harga</h5>
                                <strong>${formattedTotalAmount}</strong>
                            </div>
                        `;
                        return tableHtml;
                    }

                    checkedRowsData = response.result;
                    
                    $('.fill').append(renderTable(response.result));
                    var formHtml = `
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title"><strong>Lampiran</strong></h3>
                            </div>
                            <div class="card-body">
                            <form id="invoiceForm" enctype="multipart/form-data">
                                <div class="row">
                                    <div class="col-auto">
                                        <div class="form-group">
                                            <label for="invoice_file">Upload Surat Transfer Inventory :</label>
                                            <input type="file" class="form-control-file" id="invoice_file" name="invoice_file" accept=".pdf">
                                        </div>
                                    </div>
                                    <div class="col-auto ml-auto">
                                        <div class="form-group">
                                            <button type="button" class="btn btn-info mt-4" data-toggle="modal" data-target="#lampiranModal">Generate QR Code</button>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-danger mt-4 btn-back">Kembali</button>
                                <button type="submit" class="btn btn-primary mt-4">Simpan</button>
                            </form>
                            </div>
                        </div>
                    `;
                    $('.fill').append(formHtml);
                    $('.fill').show();

                    $('#example_result').DataTable({
                        searching: false,
                        paging: false,
                        layout: {
                            topStart: false,
                            topEnd: false,
                            bottomStart: 'pageLength',
                            bottomEnd: 'search',
                            bottom2Start: 'info',
                            bottom2End: 'paging',
                        }    
                    });


                    new Sortable(document.getElementById('sortable-table'), {
                        animation: 150,
                        onEnd: function(evt) {
                            var newData = [];
                            $('#sortable-table tr').each(function() {
                                var index = $(this).data('index');
                                newData.push(response.result[index]);
                            });
                            checkedRowsData = newData;
                            console.log('Updated checkedRowsData (after drag):', checkedRowsData);
                        }
                    });

                    function formatRupiah(angka, prefix) {
                        var number_string = angka.replace(/[^,\d]/g, '').toString(),
                            split = number_string.split(','),
                            sisa = split[0].length % 3,
                            rupiah = split[0].substr(0, sisa),
                            ribuan = split[0].substr(sisa).match(/\d{3}/gi);

                        if (ribuan) {
                            separator = sisa ? '.' : '';
                            rupiah += separator + ribuan.join('.');
                        }

                        rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
                        return prefix == undefined ? rupiah : (rupiah ? prefix + rupiah : '');
                    }

                    $('#totalpayment').on('input', function() {
                        var value = $(this).val();
                        $(this).val(formatRupiah(value, 'Rp. '));
                    });

                    $('#createButton').click(function() {
                        var idqr = $('#idqr').val();
                        var totalpayment = $('#totalpayment').val();
                        var noinvoice = $('#noinvoice').val();
                        var nofaktur = $('#nofaktur').val();
                        var invoicedate = $('#invoicedate').val();

                        // Validasi input
                        var inputs = [
                            { id: '#idqr', message: 'Masukkan input QR ID terlebih dahulu' },
                            { id: '#totalpayment', message: 'Masukkan Total Payment terlebih dahulu' },
                            { id: '#noinvoice', message: 'Masukkan input Nomor Surat Transfer Inventory terlebih dahulu' },
                            { id: '#invoicedate', message: 'Masukkan Tanggal Surat Transfer Inventory terlebih dahulu' }
                        ];

                        var isValid = true;

                        inputs.forEach(function(input) {
                            if ($(input.id).val() === '') {
                                toastr.info(input.message);
                                isValid = false;
                            }
                        });

                        if (isValid) {
                            var url = '<?= base_url()?>invoicingmgl/generate-mgl-qr?' +
                                'idqr=' + encodeURIComponent(idqr) +
                                '&totalpayment=' + encodeURIComponent(totalpayment) +
                                '&noinvoice=' + encodeURIComponent(noinvoice) +
                                '&invoicedate=' + encodeURIComponent(invoicedate);
                            window.location.href = url;
                            $('#lampiranModal').modal('hide');
                        }
                    });

                    $('#invoiceForm').submit(function(e) {
                        e.preventDefault();
                        var invoice = $('#invoice_file')[0].files[0]; 
                        var sendData = {
                            totalAmount: totalAmount,
                            checkDataVerif: checkedOrigin,
                            invoiceFileNonPkp: invoice,
                        };

                        var formData = new FormData();
                        formData.append('totalAmount', sendData.totalAmount);
                        formData.append('checkDataVerif', JSON.stringify(sendData.checkDataVerif));
                        formData.append('invoiceFileNonPkp', sendData.invoiceFileNonPkp);
                        $('#spinner-container').show();
                        $.ajax({
                            url: '<?= base_url() ?>invoicingmgl/makeinvoice',
                            type: 'POST',
                            contentType: false,
                            processData: false,
                            data: formData,
                            dataType: 'json', 
                            success: function(response) {
                                if (response.message == true){
                                    $('#spinner-container').hide();
                                    let successMessage = '<?= session()->getFlashdata("success") ?>';
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Success',
                                        text: response.response,
                                    });
                                } else if(response.message == false){
                                    $('#spinner-container').hide();
                                    let errorMessage = '<?= session()->getFlashdata("error") ?>';
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Failed',
                                        text: response.response,
                                    });
                                }
                            },
                            error: function(xhr, status, error) {
                                $('#spinner-container').hide();
                                console.error(error);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Failed',
                                    text: 'Terjadi kesalahan saat memproses permintaan. Silakan coba lagi nanti.'
                                });
                            }
                        });
                    });

                    $('.btn-back').on('click', function() {
                        $('.fill').hide();
                        $('.verify').show();
                    });
                },
                error: function(xhr, status, error) {
                    if (xhr.status === 403) {
                        toastr.error('Anda tidak memiliki izin untuk melakukan aksi ini');
                    } else {
                        console.error('Terjadi kesalahan:', error);
                    }
                }
            });
            $('#confirmationModal').modal('hide');
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