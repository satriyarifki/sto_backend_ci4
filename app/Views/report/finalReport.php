<?= $this->extend('template/default') ?>
<?= $this->section('content') ?>
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Report</h5>
                    </div>
                    <div class="card-body">
                        <ul class="nav nav-tabs mb-3" id="custom-content-below-tab" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="tab-process" data-toggle="pill" href="#content-process" role="tab" aria-controls="content-home" aria-selected="true">IN PROCESS</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="tab-miro" data-toggle="pill" href="#content-miro" role="tab" aria-controls="content-profile" aria-selected="false">MIRO</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="tab-finished" data-toggle="pill" href="#content-finished" role="tab" aria-controls="content-profile" aria-selected="false">PAID</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="tab-in-finance" data-toggle="pill" href="#content-in-finance" role="tab" aria-controls="content-profile" aria-selected="false">IN FINANCE</a>
                            </li>
                        </ul>

                        <div class="form-group col-md-6">
                            <button id="searchData" class="btn btn-info">Search</button>
                        </div>
                        <div class="tab-content" id="custom-content-below-tabContent">
                            <div class="tab-pane fade show active" id="content-process" role="tabpanel" aria-labelledby="tab-process">
                                <div id="errorContainer_process"></div>
                                <div class="d-flex justify-content-end mb-2">
                                    <a href="<?= base_url()?>report/export-process" class="btn btn-info">Export Invoice Process</a>
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-md-3">
                                        <label for="dropbox">Dropbox Number</label>
                                        <input type="text" class="form-control" id="noDB_process" placeholder="Nomor Dropbox">
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label for="invoiceNumber">Invoice Number</label>
                                        <input type="text" class="form-control" id="noInvo_process" placeholder="Invoice Number">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="daterange">Dropbox Date</label>
                                        <div class="input-group daterangepicker-container">
                                            <input type="date" class="form-control" id="startDateDB_process" placeholder="Start Date">
                                            <div class="input-group-append">
                                                <span class="input-group-text">to</span>
                                            </div>
                                            <input type="date" class="form-control" id="endDateDB_process" placeholder="End Date">
                                        </div>
                                    </div>
                                </div>

                                <table id="in_process" class="display nowrap" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>No. Dropbox</th>
                                            <th>No. Invoice</th>
                                            <th>Invoicing ID</th>
                                            <th>Vendor Name</th>
                                            <th>DPP + PPN</th>
                                            <th>Dropbox Registration Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                            <div class="tab-pane fade" id="content-miro" role="tabpanel" aria-labelledby="tab-miro">
                                <div id="errorContainer_miro"></div>
                                <div class="form-row">
                                    <div class="form-group col-md-3">
                                        <label for="dropbox">Dropbox Number</label>
                                        <input type="text" class="form-control" id="noDB_miro" placeholder="Nomor Dropbox">
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label for="invoiceNumber">Invoice Number</label>
                                        <input type="text" class="form-control" id="noInvo_miro" placeholder="Invoice Number">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="daterange">Dropbox Date</label>
                                        <div class="input-group daterangepicker-container">
                                            <input type="date" class="form-control" id="startDateDB_miro" placeholder="Start Date">
                                            <div class="input-group-append">
                                                <span class="input-group-text">to</span>
                                            </div>
                                            <input type="date" class="form-control" id="endDateDB_miro" placeholder="End Date">
                                        </div>
                                    </div>
                                </div>
                                <table id="in_miro" class="display nowrap" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>No. Dropbox</th>
                                            <th>No. Invoice</th>
                                            <th>Invoicing ID</th>
                                            <th>Vendor Name</th>
                                            <th>Dropbox Registration Date</th>
                                            <th>Due Pay Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                            <div class="tab-pane fade" id="content-finished" role="tabpanel" aria-labelledby="tab-finished">
                                <div id="errorContainer_finished"></div>
                                <div class="form-row">
                                    <div class="form-group col-md-3">
                                        <label for="dropbox">Dropbox Number</label>
                                        <input type="text" class="form-control" id="noDB_finished" placeholder="Nomor Dropbox">
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label for="invoiceNumber">Invoice Number</label>
                                        <input type="text" class="form-control" id="noInvo_finished" placeholder="Invoice Number">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="daterange">Dropbox Date</label>
                                        <div class="input-group daterangepicker-container">
                                            <input type="date" class="form-control" id="startDateDB_finished" placeholder="Start Date">
                                            <div class="input-group-append">
                                                <span class="input-group-text">to</span>
                                            </div>
                                            <input type="date" class="form-control" id="endDateDB_finished" placeholder="End Date">
                                        </div>
                                    </div>
                                </div>
                                <table id="finished" class="display nowrap" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>No. Dropbox</th>
                                            <th>No. Invoice</th>
                                            <th>Invoicing ID</th>
                                            <th>Dropbox Registration Date</th>
                                            <th>Due Pay Date</th>
                                            <th>Date Paid</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                            <div class="tab-pane fade" id="content-in-finance" role="tabpanel" aria-labelledby="tab-in-finance">
                                <div id="errorContainer_in_finance"></div>
                                <div class="d-flex justify-content-end mb-2">
                                    <a href="<?= base_url()?>report/export-infinance" class="btn btn-info">Export Data</a>
                                </div>
                                <table id="in_finance" class="display nowrap" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>No. Dropbox</th>
                                            <th>No. Invoice</th>
                                            <th>No Zinver</th>
                                            <th>Vendor Name</th>
                                            <th>Date Paid</th>
                                            <th>DPP + PPN</th>
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
        </div>

    </div>
</section>

<style>
    div.dt-processing>div:last-child {
        display: none;
    }
    .daterangepicker-container {
        display: flex;
        align-items: center;
    }

    .daterangepicker-container .form-control {
        flex: 1;
    }

    .daterangepicker-container .input-group-append {
        flex: 0;
    }

</style>
<script>
    $(document).ready(function() {
        var table = $('#in_process').DataTable({
            'processing': true,
            language: {
                'loadingRecords': '&nbsp;',
                lengthMenu: '_MENU_ &nbsp Show',
                search: '<i class="fa fa-search" aria-hidden="true"></i>',
                emptyTable: '<div style="height: 120px" class="d-flex justify-content-center align-items-center"><div class="text-center"><i style="font-size:24px" class="far">&#xf07c;</i><p>No Data</p></div></div>',
                processing: '<div class="spinner-border text-primary" role="status"></div>',
                paginate: {
                    first: "<i style='font-size:18px' class='fas'>&#xf100;</i>",
                    last: "<i style='font-size:18px' class='fas'>&#xf101;</i>",
                    next: "<i style='font-size:18px' class='fas'>&#xf105;</i>",
                    previous: "<i style='font-size:18px' class='fas'>&#xf104;</i>",
                }
            },
        });

        var table = $('#in_miro').DataTable({
            'processing': true,
            language: {
                'loadingRecords': '&nbsp;',
                lengthMenu: '_MENU_ &nbsp Show',
                search: '<i class="fa fa-search" aria-hidden="true"></i>',
                emptyTable: '<div style="height: 120px" class="d-flex justify-content-center align-items-center"><div class="text-center"><i style="font-size:24px" class="far">&#xf07c;</i><p>No Data</p></div></div>',
                processing: '<div class="spinner-border text-primary" role="status"></div>',
                paginate: {
                    first: "<i style='font-size:18px' class='fas'>&#xf100;</i>",
                    last: "<i style='font-size:18px' class='fas'>&#xf101;</i>",
                    next: "<i style='font-size:18px' class='fas'>&#xf105;</i>",
                    previous: "<i style='font-size:18px' class='fas'>&#xf104;</i>",
                }
            },
        });

        var table = $('#finished').DataTable({
            'processing': true,
            language: {
                'loadingRecords': '&nbsp;',
                lengthMenu: '_MENU_ &nbsp Show',
                search: '<i class="fa fa-search" aria-hidden="true"></i>',
                emptyTable: '<div style="height: 120px" class="d-flex justify-content-center align-items-center"><div class="text-center"><i style="font-size:24px" class="far">&#xf07c;</i><p>No Data</p></div></div>',
                processing: '<div class="spinner-border text-primary" role="status"></div>',
                paginate: {
                    first: "<i style='font-size:18px' class='fas'>&#xf100;</i>",
                    last: "<i style='font-size:18px' class='fas'>&#xf101;</i>",
                    next: "<i style='font-size:18px' class='fas'>&#xf105;</i>",
                    previous: "<i style='font-size:18px' class='fas'>&#xf104;</i>",
                }
            },
        });


        var table = $('#in_finance').DataTable({
            'processing': true,
            language: {
                'loadingRecords': '&nbsp;',
                lengthMenu: '_MENU_ &nbsp Show',
                search: '<i class="fa fa-search" aria-hidden="true"></i>',
                emptyTable: '<div style="height: 120px" class="d-flex justify-content-center align-items-center"><div class="text-center"><i style="font-size:24px" class="far">&#xf07c;</i><p>No Data</p></div></div>',
                processing: '<div class="spinner-border text-primary" role="status"></div>',
                paginate: {
                    first: "<i style='font-size:18px' class='fas'>&#xf100;</i>",
                    last: "<i style='font-size:18px' class='fas'>&#xf101;</i>",
                    next: "<i style='font-size:18px' class='fas'>&#xf105;</i>",
                    previous: "<i style='font-size:18px' class='fas'>&#xf104;</i>",
                }
            },
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
            var activeTab = $('.nav-tabs .active').attr('id');
            var startDateInv, endDateInv, startDateDbp, endDateDbp, dropbox, po, invoice, gr;
            var dataToSend = {};

            if (activeTab === 'tab-process') {
                startDateDbp = document.getElementById('startDateDB_process').value;
                endDateDbp = document.getElementById('endDateDB_process').value;
                dropbox = document.getElementById('noDB_process').value;
                invoice = document.getElementById('noInvo_process').value;
            } else if (activeTab === 'tab-miro') {
                startDateDbp = document.getElementById('startDateDB_miro').value;
                endDateDbp = document.getElementById('endDateDB_miro').value;
                dropbox = document.getElementById('noDB_miro').value;
                invoice = document.getElementById('noInvo_miro').value;
            } else if (activeTab === 'tab-finished') {
                startDateDbp = document.getElementById('startDateDB_finished').value;
                endDateDbp = document.getElementById('endDateDB_finished').value;
                dropbox = document.getElementById('noDB_finished').value;
                invoice = document.getElementById('noInvo_finished').value;
            } 
            
            if (startDateDbp) dataToSend.startDateDbp = startDateDbp;
            if (endDateDbp) dataToSend.endDateDbp = endDateDbp;
            if (dropbox) dataToSend.dropbox = dropbox;
            if (invoice) dataToSend.invoice = invoice;

            if (activeTab === 'tab-finished') {
                dataToSend.paid = 'Y';
            } else if (activeTab === 'tab-miro') {
                dataToSend.miro = 'Y';
            } else if (activeTab === 'tab-in-finance') {
                dataToSend.out_pud = 'Y';
            }

            if (Object.keys(dataToSend).length > 0) {
                $('#example').DataTable().processing(true);

                var xhr = new XMLHttpRequest();
                xhr.open('POST', 'report-json', true);
                xhr.setRequestHeader('Content-Type', 'application/json');
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4) {
                        $('#example').DataTable().processing(false);
                        if (xhr.status === 200) {
                            var data = JSON.parse(xhr.responseText);
                            if (data.hasOwnProperty('result')) {
                                if (activeTab === 'tab-process') {
                                    updateTable('in_process', data.result);
                                } else if (activeTab === 'tab-miro') {
                                    updateTable('in_miro', data.result);
                                } else if (activeTab === 'tab-finished') {
                                    updateTable('finished', data.result);
                                }
                                else if (activeTab === 'tab-in-finance') {
                                    updateTable('in_finance', data.result);
                                }
                            } else if (data.hasOwnProperty('error')) {
                                var tableId;
                                if (activeTab === 'tab-process') {
                                    tableId = 'in_process';
                                } else if (activeTab === 'tab-miro') {
                                    tableId = 'in_miro';
                                } else if (activeTab === 'tab-finished') {
                                    tableId = 'finished';
                                } else if (activeTab === 'tab-in-finance') {
                                    tableId = 'in_finance';
                                }

                                displayError(tableId, data.error);
                            }
                        } else {
                            var errorData = JSON.parse(xhr.responseText);
                            if (errorData.hasOwnProperty('error')) {
                                displayError(errorData.error);
                            }
                        }
                    }
                };
                var data = JSON.stringify(dataToSend);
                xhr.send(data);
            } else {
                toastr.info('Masukan setidaknya satu input untuk melakukan pencarian');
            }
        }

        function updateTable(tableId, data) {
            if ($.fn.DataTable.isDataTable('#' + tableId)) {
                $('#' + tableId).DataTable().clear().destroy();
            }

            var errorContainerId = 'errorContainer_' + tableId.split('_')[1]; 
            var errorContainer = document.getElementById(errorContainerId);
            
            if (errorContainer) {
                errorContainer.innerHTML = '';
            }

            $('#' + tableId).DataTable({
                data: data,
                columns: getColumns(tableId),
                responsive: true,
                'processing': true,
                language: {
                    'loadingRecords': '&nbsp;',
                    lengthMenu: '_MENU_ &nbsp Show',
                    search: '<i class="fa fa-search" aria-hidden="true"></i>',
                    emptyTable: '<div style="height: 120px" class="d-flex justify-content-center align-items-center"><div class="text-center"><i style="font-size:24px" class="far">&#xf07c;</i><p>No Data</p></div></div>',
                    processing: '<div class="spinner-border text-primary" role="status"></div>',
                    paginate: {
                        first: "<i style='font-size:18px' class='fas'>&#xf100;</i>",
                        last: "<i style='font-size:18px' class='fas'>&#xf101;</i>",
                        next: "<i style='font-size:18px' class='fas'>&#xf105;</i>",
                        previous: "<i style='font-size:18px' class='fas'>&#xf104;</i>",
                    }
                },
            });
        }

        function getColumns(tableId) {
            var columns;
            if (tableId === 'in_process') {
                columns = [
                    { data: 'dropbox_id', title: 'No. Dropbox' },
                    { data: 'no_invoice', title: 'No. Invoice' },
                    { data: 'invoicing_id', title: 'Invoicing ID' },
                    { data: 'company_name', title: 'Vendor Name' },
                    { 
                        data: 'total_payment', 
                        title: 'DPP + PPN',
                        render: function(data, type, row) {
                            return type === 'display' ? formatCurrency(data) : data;
                        }
                    },
                    { data: 'generated_date', title: 'Dropbox Registration Date' },
                ];
            } else if (tableId === 'in_miro') {
                columns = [
                    { data: 'dropbox_id', title: 'No. Dropbox' },
                    { data: 'no_invoice', title: 'No. Invoice' },
                    { data: 'invoicing_id', title: 'Invoicing ID' },
                    { data: 'company_name', title: 'Vendor Name' },
                    { data: 'generated_date', title: 'Dropbox Registration Date' },
                    { data: 'deadline', title: 'Due Pay Date' }
                ];
            } else if (tableId === 'finished') {
                columns = [
                    { data: 'dropbox_id', title: 'No. Dropbox' },
                    { data: 'no_invoice', title: 'No. Invoice' },
                    { data: 'invoicing_id', title: 'Invoicing ID' },
                    { data: 'generated_date', title: 'Dropbox Registration Date' },
                    { data: 'deadline', title: 'Due Pay Date' },
                    { data: 'date_paid', title: 'Date Paid' }
                ];
            } else if (tableId === 'in_finance') {
                columns = [
                    { data: 'dropbox_id', title: 'No. Dropbox' },
                    { data: 'no_invoice', title: 'No. Invoice' },
                    { 
                        data: 'no_zinver', 
                        title: 'No. Zinver',
                        render: function(data, type, row) {
                            return parseInt(data, 10);
                        }
                    },
                    { data: 'company_name', title: 'Vendor Name' },
                    { data: 'deadline', title: 'Due Pay Date' },
                    { 
                        data: 'total_payment', 
                        title: 'DPP + PPN',
                        render: function(data, type, row) {
                            return type === 'display' ? formatCurrency(data) : data;
                        }
                    },
                ];
            }
            return columns;
        }

        function formatCurrency(value) {
            if (value === null || value === undefined) {
                return '';
            }
            return 'Rp ' + parseFloat(value).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function displayError(tableId, error) {
            var errorMap = {
                'in_process': 'errorContainer_process',
                'in_miro': 'errorContainer_miro',
                'finished': 'errorContainer_finished',
                'in_finance': 'errorContainer_in_finance'
            };
            
            var errorContainerId = errorMap[tableId];
            if (!errorContainerId) {
                console.error('Error container not found for tableId:', tableId);
                return;
            }

            var table = $('#' + tableId).DataTable();
            var errorContainer = document.getElementById(errorContainerId);
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
    })
</script>
<?= $this->endSection() ?>