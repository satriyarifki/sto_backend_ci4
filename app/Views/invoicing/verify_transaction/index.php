<?= $this->extend('template/default') ?>

<?= $this->section('content') ?>
<section class="content">
    <div class="container-fluid">
        <div class="card">
        
        <div id="errorContainer"></div>
            <div class="card-header">
                <h3 class="card-title">Verify Transaction</h3>
            </div>
            <div class="card-body">
            <table border="0" cellspacing="5" cellpadding="5">
            <tbody>
                <tr>
                    <td><label for="monthDate">Bulan :</label></td>
                    <td><input type="month" id="monthDate" class="form-control" /></td>
                    <!-- <td><a href="#" class="btn btn-info approve-link" role="button" aria-pressed="true">Cari&nbsp;<i class="fa fa-search" aria-hidden="true"></i></a></td> -->
                </tr>
            </tbody>
            </table>
            <table id="example" class="display nowrap" style="width:100%">
                <thead>
                    <tr>
                        <th>Currency</th>
                        <th>Amount</th>
                        <th>Total Transaction</th>
                        <th>Total Verified Trx</th>
                        <th>Total Unverified Trx</th>
                        <th>Periode</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
                <tfoot>
                    <tr>
                        <th>Currency</th>
                        <th>Amount</th>
                        <th>Total Transaction</th>
                        <th>Total Verified Trx</th>
                        <th>Total Unverified Trx</th>
                        <th>Periode</th>
                        <th></th>                       
                    </tr>
                </tfoot>
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
        },
        "scrollY": "650px",
        "sScrollX": "100%",
        "scrollCollapse": true,               
    });

    document.getElementById('monthDate').addEventListener('change', function() {
        updateTableContent();
    });

    function updateTableContent() {
        var monthDate = document.getElementById('monthDate').value;
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
                    displayError(errorData.error);
                    $('#example').DataTable().processing(false);
                }
            }
        };
        var data = JSON.stringify({monthDate: monthDate});
        xhr.send(data);
    }
    function updateTable(data) {
        var table = $('#example').DataTable();
        var errorContainer = document.getElementById('errorContainer');
        errorContainer.innerHTML = '';
        table.clear().draw();
        table.row.add([
            data.currency,
            data.total_amount,
            data.total_transaction,
            data.total_verified,
            data.total_unverified,
            data.period,
            `<a href="<?= base_url('invoicing/process/') ?>${data.view}" class="btn btn-info" role="button" aria-pressed="true">view</a>`,
        ]).draw();
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

<?= $this->endSection() ?>