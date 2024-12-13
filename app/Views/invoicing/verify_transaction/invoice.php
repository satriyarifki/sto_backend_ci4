<?= $this->extend('template/default') ?>

<?= $this->section('content') ?>
<section class="content">
    <div class="container-fluid">
        <div class="card">
        
            <div id="errorContainer"></div>
            <div class="card-header">
                <h3 class="card-title">Invoice</h3>
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
                        <th>No. PO</th>
                        <th>No. Surat Jalan</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
                <tfoot>
                    <tr>
                        <th>No. PO</th>
                        <th>No. Surat Jalan</th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
            </div>
        </div>
        <div class="card printinvoice"></div>
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
        xhr.open('POST', 'invoice-json', true);
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
        var data = JSON.stringify({startDate: startDate, endDate: endDate, poNumber: poNumber});
        xhr.send(data);
    }
    function updateTable(data) {
        var startDate = document.getElementById('startDate').value;
        var endDate = document.getElementById('endDate').value;
        var table = $('#example').DataTable();
        var errorContainer = document.getElementById('errorContainer');
        errorContainer.innerHTML = '';
        table.clear().draw();
        data.forEach(function(value) {
            var itemsArray = value.items.map(item => `${item.EBELN}:${item.BKTXT}`).join(',');
            table.row.add([
                value.EBELN,
                value.BKTXT,
                `<a href="<?= base_url()?> invoicing/pdf?EBELN=${value.EBELN}&BKTXT=${value.BKTXT}&startDate=${startDate}&endDate=${endDate}" class="btn btn-info" role="button" aria-pressed="true">Print</a>`,
            ]).draw(false);
        });
    }

    // $('#example').on('click', '.invoice-view', function(e) {
    //     e.preventDefault();
    //     var startDate = document.getElementById('startDate').value;
    //     var endDate = document.getElementById('endDate').value;
    //     var itemsArray = $(this).data('items').split(','); // Pisahkan array dari data-items
    //     var EBELN = $(this).closest('tr').find('td:eq(0)').text(); // Kolom pertama (EBELN)
    //     var BKTXT = $(this).closest('tr').find('td:eq(1)').text(); // Kolom kedua (BKTXT)

    //     window.location.href = `pdf?EBELN=${EBELN}&BKTXT=${BKTXT}&startDate=${startDate}&endDate=${endDate}`;
    // });

    $(document).on('click', '.approve-link', function(e) {
        e.preventDefault();
        var url = $(this).attr('href');

        $.ajax({
            url: url,
            type: 'GET',
            success: function(response) {
                alert('GR Telah di Proses');
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