<?= $this->extend('template/default') ?>
<?= $this->section('content') ?>
<section class="content">
    <div class="container-fluid">
    <div class="row">
        <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box total-gr-unverified" data-indikasi="UNVERIFIED">
            <span class="info-box-icon bg-info elevation-1"><i class="fa fa-book"></i></span>

            <div class="info-box-content">
            <span class="info-box-text">Total GR Unverified</span>
            <span class="info-box-number" id="gruniverified_val"></span>
            </div>
        </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box total-process mb-3" data-indikasi="PROCESS">
            <span class="info-box-icon bg-danger elevation-1"><i class="fa fa-spinner"></i></span>

            <div class="info-box-content">
            <span class="info-box-text">Total in Process</span>
            <span class="info-box-number" id="processtotal_val"></span>
            </div>
        </div>
        </div>

        <div class="clearfix hidden-md-up"></div>
        <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box total-received mb-3" data-indikasi="RECEIVED">
            <span class="info-box-icon bg-success elevation-1"><i class="fa fa-bookmark"></i></span>

            <div class="info-box-content">
            <span class="info-box-text">Total Received</span>
            <span class="info-box-number" id="receivedtotal_val"></span>
            </div>
        </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box total-doc mb-3" data-indikasi="ALL">
            <span class="info-box-icon bg-warning elevation-1"><i class="fa fa-folder"></i></span>

            <div class="info-box-content">
            <span class="info-box-text">Total All Document</span>
            <span class="info-box-number" id="all_val"></span>
            </div>
        </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title"></h5>
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
                                    <span class="input-group-text">Vendor</span>
                                    <select class="form-control select2bs4" id="vendorSelect">
                                        <option value="">-- Select Vendor --</option>
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
                            <th>No. PO</th>
                            <th>No. GR</th>
                            <th>No. Item</th>
                            <th>Part Number</th>
                            <th>Part Name</th>
                            <th>Qty</th>
                            <th>Tanggal GR</th>
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
</style>

<script>
$(document).ready(function(){
    $('#successModal').modal('show');

    <?php if (session()->getFlashdata('toastr_success')): ?>
        toastr.success('<?= session()->getFlashdata('toastr_success') ?>');
    <?php endif; ?>

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

    document.getElementById('searchData').addEventListener('click', performSearch)
    document.addEventListener('keydown', performSearch)

    function performSearch(event) {
        if (event.type === 'click') {
            event.preventDefault()
            updateTableContent()
        }
    }

    document.querySelectorAll('.info-box').forEach(function(box) {
        box.addEventListener('click', function() {
            updateValueContent(this);
        });
    });

    function updateTableContent() {
        var startDate = document.getElementById('startDate').value;
        var endDate = document.getElementById('endDate').value;
        var vendor = document.getElementById('vendorSelect').value;

        if ((startDate || endDate) && vendor){
            $('#example').DataTable().processing(true);
    
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'dash-admin', true);
            xhr.setRequestHeader('Content-Type', 'application/json');
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    var data = JSON.parse(xhr.responseText);
                    if (data.hasOwnProperty('data_sap')) {
                        updateTable(data.data_sap);
                        $('#example').DataTable().processing(false);
                    }

                    document.querySelector('.card-title').textContent = 'All Data Document';

                    if (data.hasOwnProperty('jumlah_ALL')) {
                        document.getElementById('all_val').innerHTML = data.jumlah_ALL;
                    }
                    if (data.hasOwnProperty('jumlah_Process')) {
                        document.getElementById('processtotal_val').innerHTML = data.jumlah_Process;
                    }
                    if (data.hasOwnProperty('jumlah_GR')) {
                        document.getElementById('receivedtotal_val').innerHTML = data.jumlah_GR;
                    }
                    if (data.hasOwnProperty('jumlah_non_GR')) {
                        document.getElementById('gruniverified_val').innerHTML = data.jumlah_non_GR;
                    }
                } else {
                    var errorData = JSON.parse(xhr.responseText);
                    if (errorData.hasOwnProperty('error')) {
                        displayError(errorData.error);
                        $('#example').DataTable().processing(false);
                    }
                }
            };
            var data = JSON.stringify({startDate: startDate, endDate: endDate, vendor: vendor});
            xhr.send(data);
        } else {
            toastr.info('Masukan input tanggal dan pilih vendor terlebih dahulu')
        }
    }
    
    function updateTable(data) {
        var table = $('#example').DataTable();
        var errorContainer = document.getElementById('errorContainer');
        errorContainer.innerHTML = '';
        table.clear().draw();
        data.forEach(function(value) {
            var year = value.BUDAT.substr(0, 4);
            var month = value.BUDAT.substr(4, 2);
            var day = value.BUDAT.substr(6, 2);
            var date = day + '-' + month + '-' + year;
            table.row.add([
                value.EBELN,
                value.BELNR,
                value.EBELP,
                value.MATNR,
                value.TXZ01,
                value.MENGE,
                date
            ]).draw();
        });
    }

    function updateValueContent(element){
        var indikasi = element.dataset.indikasi;
        var startDate = document.getElementById('startDate').value;
        var endDate = document.getElementById('endDate').value;
        var vendor = document.getElementById('vendorSelect').value;

        $('#example').DataTable().processing(true);

        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'value-maj', true);
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                var data = JSON.parse(xhr.responseText);
                if (data.hasOwnProperty('result')) {
                    updateTable(data.result);
                    $('#example').DataTable().processing(false);
                }
                if (data.hasOwnProperty('table_header')) {
                    document.querySelector('.card-title').textContent = data.table_header;
                }
            } else {
                var errorData = JSON.parse(xhr.responseText);
                if (errorData.hasOwnProperty('error')) {
                    displayError(errorData.error);
                    $('#example').DataTable().processing(false);
                }
            }
        };
        var data = JSON.stringify({indikasi: indikasi, startDate: startDate, endDate: endDate, vendor: vendor});
        xhr.send(data);
    }

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