<?= $this->extend('template/default') ?>

<?= $this->section('content') ?>
<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Data Process</h3>
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
            </div>
            <div id="errorContainer"></div>
            <table id="example" class="display nowrap" style="width:100%">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>No. Item</th>
                        <th>Status</th>
                        <th>No. Invoice</th>
                        <th>No. PO</th>
                        <th>No. GR</th>
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
            xhr.open('POST', 'track_real', true);
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
            var tanggal = new Date(value.create_date);
            var namaBulan = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
            var hari = tanggal.getDate();
            var bulan = namaBulan[tanggal.getMonth()];
            var tahun = tanggal.getFullYear();
            var tanggalAkhir = `${hari} ${bulan} ${tahun}`;

            $.ajax({
                url: '<?= base_url()?>invoicing/getreceive',
                method: 'POST',
                data: {
                    belnr: value.no_gr,
                    ebelp: value.no_item,
                    ebeln: value.no_po
                },
                dataType: 'json',
                success: function(response) {
                    if(response.result != null){
                        if(response.result.status_receive == "Y"){
                            if (response.result.dok_ok == "Y") {
                                if (response.result.delete_pud == "Y"){
                                    approve = 'Delete';
                                    approveClass = 'btn-danger';
                                } else {
                                    if (response.result.status_miro == "Y"){
                                        approve = 'Ready to Paid';
                                        approveClass = 'btn-dark';
                                    } else {
                                        approve = 'Verifiying';
                                        approveClass = 'btn-primary';
                                    }
                                }
                            } else if (response.result.onhold == "Y") {
                                if (response.result.delete_onhold == "Y"){
                                    approve = 'Delete';
                                    approveClass = 'btn-danger';
                                } else {
                                    approve = 'On Hold';
                                    approveClass = 'btn-danger';
                                }
                            } else {
                                approve = 'Received';
                                approveClass = 'btn-secondary';
                            }
                        } else {
                            approve = 'Delivery';
                            approveClass = 'btn-warning';  
                        }
                    } else {
                        approve = 'Invoiced';
                        approveClass = 'btn-success';  
                    }
                    table.row.add([
                        $no++,
                        value.no_item,
                        '<button class="btn ' + approveClass + '">' + approve + '</button>',
                        value.no_invoice,
                        value.no_po,
                        value.no_gr,
                        tanggalAkhir,
                    ]).draw();
                },
                error: function(xhr, status, error) {
                    console.error(error);
                }
            });
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