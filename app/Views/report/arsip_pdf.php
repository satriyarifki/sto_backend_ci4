<?= $this->extend('template/default') ?>

<?= $this->section('content') ?>
<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Arsip PDF</h3>
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
                            <input type="text" id="invoiceNumber" class="form-control" />
                        </div>
                    </div>
                </div>
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
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-append">
                                <span class="input-group-text">Vendor Category</span>
                                <select class="form-control selectVendorCategory" id="vendorCategory">
                                    <option value="">-- Select Category --</option>
                                    <option value="1">PKP</option>
                                    <option value="2">NPKP</option>
                                    <option value="3">MGL</option>
                                </select>
                            </div>
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
                        <th>Nomor Verifikasi</th>
                        <th>Nomor Invoice</th>
                        <th>Tanggal Verifikasi</th>
                        <th>Tanggal Invoice</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
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

    // document.getElementById('startDate').addEventListener('change', function() {
    //     updateTableContent();
    // });
    // document.getElementById('endDate').addEventListener('change', function() {
    //     updateTableContent();
    // });
    // document.getElementById('ponumber').addEventListener('change', function() {
    //     updateTableContent();
    // });

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
        var invoiceNumber = document.getElementById('invoiceNumber').value;
        var vendor = document.getElementById('vendorSelect').value;
        var category = document.getElementById('vendorCategory').value;

        if ((startDate || endDate || invoiceNumber) && vendor){
            $('#example').DataTable().processing(true);
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'arsip-pdf-json', true);
            xhr.setRequestHeader('Content-Type', 'application/json');
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    var data = JSON.parse(xhr.responseText);
                    if (data.hasOwnProperty('result')) {
                        updateTable(data.result);
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
            var data = JSON.stringify({startDate: startDate, endDate: endDate, invoiceNumber: invoiceNumber, vendor: vendor, category: category});
            xhr.send(data);
        } else {
            toastr.info('Masukan input tanggal atau nomor Invoice atau Vendor terlebih dahulu')
        }
    }
    
    function updateTable(data) {
        var table = $('#example').DataTable();
        var errorContainer = document.getElementById('errorContainer');
        errorContainer.innerHTML = '';
        table.clear().draw();

        var vendorCategory = document.getElementById('vendorCategory').value;
        
        // Fungsi untuk mengubah format tanggal
        function formatDate_v2(tanggal) {
            if (!tanggal) return '';
            
            var parts = tanggal.split('/');
            var day = parts[0];
            var month = parseInt(parts[1]);
            var year = parts[2];
            
            var bulan = [
                'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 
                'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
            ];
            var formattedDate = day + ' ' + bulan[month - 1] + ' ' + year;
            return formattedDate;
        }

        data.forEach(function(value) {
            if (vendorCategory == 1) {
                let baseUrlFaktur = "<?= base_url()?>report/view-pdf";
                let baseUrlInvoice = "<?= base_url()?>report/view-pdf-invoice";
                buttons = `<a href="${baseUrlFaktur}?file=${value.file_path}" class="btn btn-info" role="button" aria-pressed="true" target="_blank"><i class="fa fa-eye" aria-hidden="true"></i> Faktur Pajak</a> 
                            <a href="${baseUrlInvoice}?file=${value.file_path_invoice}" class="btn btn-secondary" role="button" aria-pressed="true" target="_blank"><i class="fa fa-eye" aria-hidden="true"></i> Invoice</a>`;

                if (value.file_rev_path !== "") {
                    buttons += ` <a href="${baseUrlFaktur}-faktur-pengganti?file=${value.file_rev_path}" class="btn btn-warning" role="button" aria-pressed="true" target="_blank"><i class="fa fa-eye" aria-hidden="true"></i> Faktur Pengganti</a>`;
                }
            }

            if (vendorCategory == 2) {
                let baseUrlFaktur = "<?= base_url()?>report/view-pdf";
                let baseUrlInvoiceNPKP = "<?= base_url()?>report/view-pdf-invoice-npkp";
                buttons = `<a href="${baseUrlFaktur}?file=${value.file_path}" class="btn btn-info" role="button" aria-pressed="true" target="_blank"><i class="fa fa-eye" aria-hidden="true"></i> Faktur Pajak</a> 
                            <a href="${baseUrlInvoiceNPKP}?file=${value.file_path_invoice}" class="btn btn-secondary" role="button" aria-pressed="true" target="_blank"><i class="fa fa-eye" aria-hidden="true"></i> Invoice NPKP</a>`;
            } else if (vendorCategory == 3) {
                let baseUrlInvoiceMGL = "<?= base_url()?>report/view-pdf-invoice-mgl";
                buttons = `<a href="${baseUrlInvoiceMGL}?file=${value.file_path_invoice}" class="btn btn-secondary" role="button" aria-pressed="true" target="_blank"><i class="fa fa-eye" aria-hidden="true"></i> Invoice MGL</a>`;
            }

            table.row.add([
                value.invoicing_id,
                value.no_invoice,
                value.create_date,
                formatDate_v2(value.tax_date),
                buttons
            ]).draw();
        });
    }


    
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