<?= $this->extend('template/default') ?>

<?= $this->section('content') ?>
<section class="content">
    <div class="container-fluid">
        <div class="card">
        
        <div id="errorContainer"></div>
            <div class="card-header">
                <h3 class="card-title">Pre Order</h3>
            </div>
            <div class="card-body">
            <table border="0" cellspacing="5" cellpadding="5">
            <tbody>
                <tr>
                    <td><label for="startDate">Tanggal Awal :</label></td>
                    <td><input type="date" id="startDate" class="form-control" /></td>
                    <td><label for="endDate">Tanggal Akhir :</label></td>
                    <td><input type="date" id="endDate" class="form-control" /></td>
                    <td><label for="ponumber">Nomor PO :</label></td>
                    <td><input type="text" id="ponumber" class="form-control" /></td>
                </tr>
            </tbody>
            </table>
            <table id="example" class="display nowrap" style="width:100%">
                <thead>
                    <tr>
                        <th>Status</th>
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
</section>


<script>
$(document).ready(function(){
    $('#successModal').modal('show');

    var table = $('#example').DataTable({
        'processing': true,
        'language': {
            'loadingRecords': '&nbsp;',
            'processing': '<div class="spinner"></div>'
        }                
    });

    document.getElementById('startDate').addEventListener('change', function() {
        updateTableContent();
    });
    document.getElementById('endDate').addEventListener('change', function() {
        updateTableContent();
    });
    document.getElementById('ponumber').addEventListener('change', function() {
        updateTableContent();
    });

    function updateTableContent() {
        var startDate = document.getElementById('startDate').value;
        var endDate = document.getElementById('endDate').value;
        var poNumber = document.getElementById('ponumber').value;
        $('#example').DataTable().processing(true);

        // Kirim permintaan AJAX ke server
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'verify', true);
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                // Tangani respon dari server
                var data = JSON.parse(xhr.responseText);
                if (data.hasOwnProperty('data_sap')) {
                    updateTable(data.data_sap);
                    $('#example').DataTable().processing(false);
                }
            } else {
                var errorData = JSON.parse(xhr.responseText);
                if (errorData.hasOwnProperty('error')) {
                    // Tampilkan pesan kesalahan pada halaman
                    displayError(errorData.error);
                    $('#example').DataTable().processing(false);
                }
            }
        };
        var data = JSON.stringify({startDate: startDate, endDate: endDate, poNumber: poNumber});
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
                `<a href="<?= base_url('invoicing/approve/') ?>${value.BELNR}/${value.EBELP}" class="btn btn-info approve-link" role="button" aria-pressed="true">Approve SAP</a>`,
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
    
    // $('input[name="start_date"]').daterangepicker({
    //     singleDatePicker: true,
    //     showDropdowns: true,
    //     minYear: 1901,
    //     maxYear: parseInt(moment().format('YYYY'), 10),
    //     startDate: moment().startOf('month')
    // });

    // $('input[name="end_date"]').daterangepicker({
    //     singleDatePicker: true,
    //     showDropdowns: true,
    //     minYear: 1901,
    //     maxYear: parseInt(moment().format('YYYY'), 10),
    //     endDate: moment().endOf('month')
    // });

    // var today = new Date(new Date().getFullYear(), new Date().getMonth(), new Date().getDate());
    //     $('#startDate').datepicker({
    //         uiLibrary: 'bootstrap4',
    //         iconsLibrary: 'fontawesome',
    //         minDate: today,
    //         maxDate: function () {
    //             return $('#endDate').val();
    //         }
    //     });
    //     $('#endDate').datepicker({
    //         uiLibrary: 'bootstrap4',
    //         iconsLibrary: 'fontawesome',
    //         minDate: function () {
    //             return $('#startDate').val();
    //         }
    // });
    
    // Menangani perubahan pada input tanggal

});
</script>

<script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
<link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
<script src="https://unpkg.com/gijgo@1.9.14/js/gijgo.min.js" type="text/javascript"></script>
<link href="https://unpkg.com/gijgo@1.9.14/css/gijgo.min.css" rel="stylesheet" type="text/css" />

<?= $this->endSection() ?>