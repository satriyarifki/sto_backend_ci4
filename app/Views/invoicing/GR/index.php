<?= $this->extend('template/default') ?>

<?= $this->section('content') ?>
<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Good Receipt</h3>
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
                                <span class="input-group-text">Nomor PO</span>
                            </div>
                            <input type="text" id="ponumber" class="form-control" />
                        </div>
                    </div>
                </div>
                <div class="col-auto">
                    <button id="searchData" class="btn btn-info">Search</button>
                </div>
                <div class="col-auto ml-auto">
                    <button id="exportData" class="btn btn-success" disabled>Export to Excel</button>
                </div>
            </div>
            <div id="errorContainer"></div>
            <table id="example" class="display nowrap" style="width:100%">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>No. Item</th>
                        <th>Status</th>
                        <th>No. PO</th>
                        <th>No. GR</th>
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
        var poNumber = document.getElementById('ponumber').value;

        if (startDate || endDate || poNumber){
            $('#example').DataTable().processing(true);
    
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'real', true);
            xhr.setRequestHeader('Content-Type', 'application/json');
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    var data = JSON.parse(xhr.responseText);
                    if (data.hasOwnProperty('data_sap')) {
                        updateTable(data.data_sap);
                        $('#example').DataTable().processing(false);
                        document.getElementById('exportData').disabled = false;
                    }
                } else {
                    var errorData = JSON.parse(xhr.responseText);
                    if (errorData.hasOwnProperty('error')) {
                        displayError(errorData.error);
                        $('#example').DataTable().processing(false);
                        document.getElementById('exportData').disabled = true;
                    }
                }
            };
            var data = JSON.stringify({startDate: startDate, endDate: endDate, poNumber: poNumber});
            xhr.send(data);
        } else {
            toastr.info('Masukan input tanggal atau nomor PO terlebih dahulu')
        }

    }
    
    function updateTable(data) {
        var table = $('#example').DataTable();
        var errorContainer = document.getElementById('errorContainer');
        errorContainer.innerHTML = '';
        table.clear().draw();
        var $no = 1; 
        data.forEach(function(value) {
            var year = value.BUDAT.substr(0, 4);
            var month = value.BUDAT.substr(4, 2);
            var day = value.BUDAT.substr(6, 2);
            var date = day + '-' + month + '-' + year;
            var approve = 'Approved SAP';
            var approveClass = 'btn-info';
            var wrbtrValue = parseFloat(value.WRBTR) * 100;
            var formattedWrbtrValue = wrbtrValue.toLocaleString('id-ID', { style: 'currency', currency: 'IDR' });
            table.row.add([
                $no++,
                value.EBELP,
                '<button class="btn ' + approveClass + '">' + approve + '</button>',
                value.EBELN,
                value.BELNR,
                value.MATNR,
                value.TXZ01,
                value.MENGE,
                formattedWrbtrValue,
                date,
            ]).draw();
        });
    }

    document.getElementById('exportData').addEventListener('click', function() {
        var startDate = document.getElementById('startDate').value;
        var endDate = document.getElementById('endDate').value;
        var poNumber = document.getElementById('ponumber').value;

        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'real', true);
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                var data = JSON.parse(xhr.responseText);
                if (data.hasOwnProperty('data_sap')) {
                    var headers = ["No.", "No. Item", "Status", "No. PO", "No. GR", "Part Number", "Part Name", "Qty", "Tanggal GR"];
                    var tableData = [];
                    tableData.push(headers);

                    var $no = 1;
                    data.data_sap.forEach(function(value) {
                        var year = value.BUDAT.substr(0, 4);
                        var month = value.BUDAT.substr(4, 2);
                        var day = value.BUDAT.substr(6, 2);
                        var date = day + '-' + month + '-' + year;
                        var approve = 'Approved SAP';
                        tableData.push([
                            $no++,
                            value.EBELP,
                            approve,
                            value.EBELN,
                            value.BELNR,
                            value.MATNR,
                            value.TXZ01,
                            value.MENGE,
                            date
                        ]);
                    });

                    var wb = XLSX.utils.book_new();
                    var ws = XLSX.utils.aoa_to_sheet(tableData);
                    XLSX.utils.book_append_sheet(wb, ws, 'Sheet1');
                    XLSX.writeFile(wb, 'data.xlsx');
                }
            }
        };
        var data = JSON.stringify({startDate: startDate, endDate: endDate, poNumber: poNumber});
        xhr.send(data);
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