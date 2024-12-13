<?= $this->extend('template/default') ?>

<?= $this->section('content') ?>
<section class="content">
    <div class="container-fluid">
        <div class="card">
        <div id="errorContainer"></div>
            <div class="modal fade" id="myModal">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">Detail Dropbox</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <table class="table" id="detailTable">
                                <thead>
                                    <tr>
                                        <th>No Invoice</th>
                                        <th>No Verifikasi</th>
                                        <th>Nomor PO</th>
                                        <th>Nomor Item</th>
                                        <th>Nomor GR</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-header">
                <h3 class="card-title">Reprint Dropbox</h3>
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
                    <button id="searchData" class="btn btn-info">Search</button>
                </div>
            </div>
            <table id="example" class="display nowrap" style="width:100%">
                <thead>
                    <tr>
                        <th></th>
                        <th>Dropbox Code</th>
                        <th>Generate Date</th>
                        <th>Generate Time</th>
                        <th>User Generate</th>
                        <th>Vendor Name</th>
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

        if (startDate || endDate){
            $('#example').DataTable().processing(true);
    
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'mgl-reprint-json', true);
            xhr.setRequestHeader('Content-Type', 'application/json');
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    var data = JSON.parse(xhr.responseText);
                    if (data.hasOwnProperty('result')) {
                        updateTable(data.result);
                        $('#example').DataTable().processing(false);
                    }
                    console.log(xhr.responseText);
                } else {
                    var errorData = JSON.parse(xhr.responseText);
                    if (errorData.hasOwnProperty('error')) {
                        displayError(errorData.error);
                        $('#example').DataTable().processing(false);
                    }
                    console.log(xhr.responseText);
                }
            };
            var data = JSON.stringify({startDate: startDate, endDate: endDate});
            xhr.send(data);
        } else {
            toastr.info('Masukan input tanggal terlebih dahulu')
        }
        // var poNumber = document.getElementById('ponumber').value;
    }
    
    function updateTable(data) {
        var table = $('#example').DataTable();
        var errorContainer = document.getElementById('errorContainer');
        errorContainer.innerHTML = '';
        table.clear().draw();
        data.forEach(function(value) {
            table.row.add([
                `<button class="btn btn-info btn-detail" data-dropbox-id="${value['dropbox_id']}">+</button>`,
                value['dropbox_id'],
                value['generated_date'],
                value['generated_time'],
                value['company_name'],
                value['user_generate'],
                `<a href="<?= base_url()?>dropbox/pdfmgl?dropbox_id=${value['dropbox_id']}" class="btn btn-info" role="button" aria-pressed="true">Print <i class="fa fa-print" aria-hidden="true"></i></a>`,
            ]).draw();
        });

        $('.btn-detail').click(function() {
            var dropboxId = $(this).data('dropbox-id');
            $.ajax({
            url: "<?= base_url()?>dropbox/mgl-dropbox-detail",
            method: "GET",
            dataType : 'json',
            data: { dropbox_id: dropboxId },
                success: function(response) {
                    var detailData = response.detail;
                    console.log(detailData);
                    $('#myModal #detailTable tbody').empty();
                    detailData.forEach(function(item) {
                        var totalPaymentFormatted = Number(item.total_payment).toLocaleString('id-ID', { style: 'currency', currency: 'IDR' });
                        $('#myModal #detailTable tbody').append(
                            `<tr>
                                <td>${item.no_invoice}</td>
                                <td>${item.invoicing_id}</td>
                                <td>${item.no_po}</td>
                                <td>${item.no_item}</td>
                                <td>${item.no_gr}</td>
                            </tr>`
                        );
                    });
                    $('#myModal').modal('show');
                },
                error: function(xhr, status, error) {
                    console.error(xhr.responseText);
                    errorContainer.innerHTML = 'Failed to retrieve data. Please try again.';
                }
            });
        });

        // Close modal when clicking on the close button
        $('.close').click(function() {
            $('#myModal').css('display', 'none');
        });
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