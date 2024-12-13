<?= $this->extend('template/default') ?>
<?= $this->section('content') ?>
<section class="content">
    <div class="container-fluid">
    <!-- Info boxes -->
    <div class="row">
        <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box total-gr-unverified" data-indikasi="UNVERIFIED">
            <span class="info-box-icon bg-info elevation-1"><i class="fa fa-book"></i></span>

            <div class="info-box-content">
            <span class="info-box-text">Total GR Unverified</span>
            <span class="info-box-number" id="gruniverified_val"></span>
            </div>
            <!-- /.info-box-content -->
        </div>
        <!-- /.info-box -->
        </div>
        <!-- /.col -->
        <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box total-process mb-3" data-indikasi="MORE">
            <span class="info-box-icon bg-danger elevation-1"><i class="fa fa-spinner"></i></span>

            <div class="info-box-content">
            <span class="info-box-text">Total in Process</span>
            <span class="info-box-number" id="processtotal_val"></span>
            </div>
            <!-- /.info-box-content -->
        </div>
        <!-- /.info-box -->
        </div>
        <!-- /.col -->

        <!-- fix for small devices only -->
        <div class="clearfix hidden-md-up"></div>
        <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box total-received mb-3" data-indikasi="RECEIVED">
            <span class="info-box-icon bg-success elevation-1"><i class="fa fa-bookmark"></i></span>

            <div class="info-box-content">
            <span class="info-box-text">Total Received</span>
            <span class="info-box-number" id="receivedtotal_val"></span>
            </div>
            <!-- /.info-box-content -->
        </div>
        <!-- /.info-box -->
        </div>
        <!-- /.col -->
        <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box total-doc mb-3" data-indikasi="MORE">
            <span class="info-box-icon bg-warning elevation-1"><i class="fa fa-folder"></i></span>

            <div class="info-box-content">
            <span class="info-box-text">Total All Document</span>
            <span class="info-box-number" id="all_val"></span>
            </div>
            <!-- /.info-box-content -->
        </div>
        <!-- /.info-box -->
        </div>
        <!-- /.col -->
    </div>
    <!-- /.row -->

    <div class="row">
        <div class="col-md-12">
        <div class="card">
        <div id="errorContainer"></div>
            <div class="card-header">
                <h5 class="card-title"></h5>
            </div>
            <div class="card-body">
            <table border="0" cellspacing="10" cellpadding="">
            <tbody>
                <tr>
                    <td class="text-right"><input type="text" name="daterange" value="Periode Tanggal" /></td>
                </tr>
            </tbody>
            </table>
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
                <tfoot>
                    <tr>
                        <th>No. PO</th>
                        <th>No. GR</th>
                        <th>No. Item</th>
                        <th>Part Number</th>
                        <th>Part Name</th>
                        <th>Qty</th>
                        <th>Tanggal GR</th>
                    </tr>
                </tfoot>
            </table>
            </div>
        </div>
        <!-- /.card -->
        </div>
        <!-- /.col -->
    </div>
    </div><!--/. container-fluid -->
</section>


<script>
$(document).ready(function(){
    $('#successModal').modal('show');

    let minDate, maxDate;

    DataTable.ext.search.push(function (settings, data, dataIndex) {
        let date = new Date(data[6]);
        if (
            (minDate === undefined || minDate === null) && 
            (maxDate === undefined || maxDate === null)
        ) {
            return true;
        }
        if (
            (minDate === undefined || minDate === null || date >= minDate) && 
            (maxDate === undefined || maxDate === null || date <= maxDate)
        ) {
            return true;
        }
        return false;
    });
    
    // Create date inputs
    // minDate = new DateTime('#min', {
    //     format: 'DD-MM-YYYY'
    // });
    // maxDate = new DateTime('#max', {
    //     format: 'DD-MM-YYYY'
    // });

    $('input[name="daterange"]').daterangepicker({
        opens: 'right'
    }, function(start, end, label) {
        minDate = start.format('YYYY-MM-DD');
        maxDate = end.format('YYYY-MM-DD');
        updateTableContent();
    });

    var table = $('#example').DataTable({
        'processing': true,
        'language': {
            'loadingRecords': '&nbsp;',
            'processing': '<div class="spinner"></div>'
        }                
    });
    
    var totalDocData = document.querySelector('.total-doc');
    updateTableContent(totalDocData);
    updateValueContent(totalDocData);

    document.querySelectorAll('.info-box').forEach(function(box) {
        box.addEventListener('click', function() {
            updateTableContent(this);
        });
    });

    function updateTableContent(element) {
        var indikasi;
        if (element !== undefined) {
            indikasi = element.dataset.indikasi;
        }

        $('#example').DataTable().processing(true);

        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'dashboard/real', true);
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                var data = JSON.parse(xhr.responseText);
                if (data.hasOwnProperty('table_header')) {
                    document.querySelector('.card-title').textContent = data.table_header;
                }
                if (data.hasOwnProperty('data_sap')) {
                    updateTable(data.data_sap);
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
        var data;
        if (indikasi !== undefined) {
            data = JSON.stringify({
                indikasi: indikasi
            });
        } else {
            data = JSON.stringify({
                minDate: minDate,
                maxDate: maxDate
            });
        }
        xhr.send(data);
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
            // Membuat tanggal dalam format yang diinginkan
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

        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'dashboard/value', true);
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                var data = JSON.parse(xhr.responseText);
                if (data.hasOwnProperty('jumlah_ALL')) {
                    document.getElementById('all_val').innerHTML = data.jumlah_ALL;
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
                }
            }
        };
        var data = JSON.stringify({indikasi: indikasi});
        xhr.send(data);
    }

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
    
    // DataTables initialisation
    let table = new DataTable('#example');

});
</script>

<script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
<link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
<script src="https://unpkg.com/gijgo@1.9.14/js/gijgo.min.js" type="text/javascript"></script>
<link href="https://unpkg.com/gijgo@1.9.14/css/gijgo.min.css" rel="stylesheet" type="text/css" />
<?= $this->endSection() ?>