<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="icon" href="<?= base_url() ?>armada.ico" type="image/x-icon">
  <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback" rel="stylesheet">
  <link rel="stylesheet" href="<?= base_url() ?>plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="<?= base_url() ?>dist/css/adminlte.min.css">
  <script src="<?= base_url()?>plugins/jquery/jquery.min.js"></script>

  <title>STO</title>
    <style>
        body {
        background: #dcdcdc;
        background-size: cover;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        margin: 0;
        }

        img {
        width: 100px;
        height: auto;
        display: block;
        margin: 0 auto;
        }


        input[type="file"]::file-selector-button {
            border-radius: 30px;
            padding: 0 16px;
            height: 40px;
            cursor: pointer;
            background-color: white;
            border: 0.1px solid rgba(0, 0, 0, 0);
            box-shadow: 0px 1px 0px rgba(0, 0, 0, 0.05);
            margin-top: -10px;
            transition: background-color 200ms;
        }

        input[type="file"]::file-selector-button:hover {
            background-color: #f3f4f6;
        }

        input[type="file"]::file-selector-button:active {
            background-color: #e5e7eb;
        }

        #spinner-container {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1000;
            display: none;
        }

        .image-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            visibility: hidden;
            opacity: 0;
            transition: opacity 0.3s ease-in-out;
        }

        .image-overlay img {
            width: auto;
            height: auto;
            max-width: 95vw; 
            max-height: 95vh; 
            border-radius: 10px;
            transition: transform 0.3s ease-in-out;
        }

        .image-overlay.show {
            visibility: visible;
            opacity: 1;
        }



    </style>

    <script src="<?= base_url()?>plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <link href="<?= base_url() ?>plugins/datatables/DataTables-2.0.1/css/dataTables.dataTables.css" rel="stylesheet">
    <link href="<?= base_url() ?>plugins/datatables/DataTables-2.0.1/css/dataTables.dateTime.min.css" rel="stylesheet">
    <link href="<?= base_url() ?>plugins/datatables/DataTables-2.0.1/css/dataTables.dataTables.css" rel="stylesheet">
    <link href="<?= base_url() ?>plugins/datatables/DataTables-2.0.1/css/dataTables.dataTables.css" rel="stylesheet">
    <link href="<?= base_url() ?>plugins/datatables/DataTables-2.0.1/css/dataTables.dataTables.css" rel="stylesheet">
    <link href="<?= base_url() ?>plugins/toastr/toastr.min.css" rel="stylesheet">
</head>

<body>
<div class="container-fluid mt-2">
    <div class="row">
        <div class="col-md-12">
        <div class="card">
            <div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog" aria-labelledby="confirmationModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="confirmationModalLabel"></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-ban" aria-hidden="true"></i></button>
                        <button type="button" class="btn btn-primary" id="btnContinue"><i class="fa fa-check-circle" aria-hidden="true"></i></button>
                    </div>
                    </div>
                </div>
            </div>
            <div class="card-header">
                <h5 class="card-title">Report</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-auto">
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-append">
                                    <span class="input-group-text">Nomor TAG</span>
                                </div>
                                <input type="text" id="notag" name="notag" class="form-control" placeholder="Masukkan Nomor TAG" style="width: 50%;">
                            </div>
                            <small class="text-muted">Jika ingin mengambil semua datanya, inputkan <strong>'ALL'</strong> pada Nomor TAG.</small>
                        </div>
                    </div>
                    <div class="col-auto">
                        <button id="loadDataBtn" class="btn btn-info">Search</button>
                    </div>
                    <div class="col-auto ml-auto">
                        <button id="exportData" class="btn btn-success" disabled>Export to Excel</button>
                    </div>
                </div>
                <div id="errorContainer"></div>
                <table id="example" class="display" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>Area</th>
                            <th>NIK</th>
                            <th>Nomor TAG</th>
                            <th>Timestamp</th>
                            <th>Image</th>
                            <th></th>
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

<div class="image-overlay" id="imageOverlay">
    <img id="overlayImage" src="" alt="Preview Image">
</div>

<div id="spinner-container">
    <button class="btn btn-primary">
        <span class="spinner-border spinner-border-sm"></span>
        Loading..
    </button>
</div>


<script src="<?= base_url() ?>plugins/jquery/jquery.min.js"></script>
<script src="<?= base_url() ?>plugins/toastr/toastr.min.js"></script>
<script src="<?= base_url() ?>plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?= base_url() ?>plugins/datatables/jquery.dataTables.min.js"></script>
<script src="<?= base_url() ?>plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="<?= base_url() ?>plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="<?= base_url() ?>plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<script src="<?= base_url() ?>plugins/datatables/DataTables-2.0.1/js/dataTables.js"></script>
<script src="<?= base_url() ?>plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?= base_url() ?>plugins/xlsx/xlsx.full.min.js"></script>
<script src="<?= base_url() ?>dist/js/adminlte.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>

$(document).ready(function() {

    function openImageModal(imageSrc) {
        document.getElementById('modalImage').src = imageSrc;
        $('#imageModal').modal('show');
    }

    var table = $('#example').DataTable({
        'processing': true,
        language: {
            'loadingRecords': '&nbsp;',
            lengthMenu: '_MENU_ &nbsp Show',
            search: '<i class="fa fa-search" aria-hidden="true"></i>',
            emptyTable: '<div style="height: 120px" class="d-flex justify-content-center align-items-center"><div class="text-center"><i style="font-size:24px" class="far">&#xf07c;</i><p>No Data</p></div></div>',
        },
        layout: {
            topStart: false,
            topEnd: 'search',
            bottomStart: 'pageLength',
            bottom2Start: 'info',
        },
        "scrollY": "650px",
        "sScrollX": "100%",
        "scrollCollapse": true,
        "ajax": null,
        'columns': [
            { 'data': 'area' },
            { 'data': 'nik' },
            { 'data': 'no_tag' },
            { 'data': 'create_date' },
            {
                'data': 'path_file',
                'render': function (data, type, row) {
                    if (data) {
                        return `<img src="<?= base_url('uploads/evidence/') ?>${data}" 
                                    alt="Evidence" class="image-zoom" 
                                    data-src="<?= base_url('uploads/evidence/') ?>${data}"
                                    style="width: 120px; height: 120px; object-fit: cover; border-radius: 5px; cursor: pointer;">`;
                    } else {
                        return 'No Image';
                    }
                }
            },
            {
                'data': 'path_file',
                'render': function(data, type, row) {
                    if (data) {
                        let imageUrl = `<?= base_url('uploads/evidence/') ?>${data}`;
                        return `
                            <div class="d-flex align-items-center">
                                <a href="${imageUrl}" target="_blank" class="btn btn-secondary">
                                    <i class="fa fa-eye" aria-hidden="true"> <span>OPEN</span></i>
                                </a>
                            </div>
                        `;
                    } else {
                        return 'No Image';
                    }
                }
            }
        ]
    });

    $(document).on('click', '.image-zoom', function () {
        var imageSrc = $(this).data('src'); 
        $('#overlayImage').attr('src', imageSrc);
        $('#imageOverlay').addClass('show');
    });

    $('#imageOverlay').on('click', function (e) {
        if (e.target !== $('#overlayImage')[0]) {
            $(this).removeClass('show');
        }
    });

    $('#loadDataBtn').on('click', function() {
        var notag = document.getElementById('notag').value;

        if (notag) {
            $('#example').DataTable().processing(true);
            $.ajax({
                url: '<?= base_url()?>documentation/report-query',
                type: 'POST',
                data: { no_tag: notag },

                success: function(response) {
                    $('#example').DataTable().processing(false);
                    if (response.data.status === false) {
                        displayError(response.data.error);
                        document.getElementById('exportData').disabled = true;
                    } else {
                        var errorContainer = document.getElementById('errorContainer');
                        errorContainer.innerHTML = '';
                        table.clear().rows.add(response.data).draw();
                        document.getElementById('exportData').disabled = false;
                    }
                },
                error: function() {
                    alert('Terjadi kesalahan pada server.');
                }
            });
        } else {
            toastr.info('Masukan Nomor Tag terlebih dahulu')
        }
    });

    $('#exportData').on('click', function() {
        var notag = document.getElementById('notag').value;

        $.ajax({
            url: '<?= base_url()?>documentation/report-query',
            type: 'POST',
            data: { no_tag: notag },
            beforeSend: function() {
                $('#exportData').prop('disabled', true).text('Exporting...');
            },
            success: function(response) {
                $('#exportData').prop('disabled', false).text('Export Excel');

                if (response.data) {
                    var headers = ["Area.", "No. NIK", "No. Tag", "Timestamp", "Gambar"];
                    var tableData = [headers];

                    var no = 1;
                    response.data.forEach(function(value) {
                        var imageUrl = "<?= base_url('public/uploads/evidence/') ?>" + value.path_file;
                        tableData.push([
                            value.area,
                            value.nik,
                            value.no_tag,
                            value.create_date,
                            imageUrl
                        ]);
                    });

                    var wb = XLSX.utils.book_new();
                    var ws = XLSX.utils.aoa_to_sheet(tableData);
                    XLSX.utils.book_append_sheet(wb, ws, 'Data Evidence STO');
                    XLSX.writeFile(wb, 'data_sto.xlsx');
                } else {
                    toastr.warning('Data SAP tidak tersedia.');
                }
            },
            error: function() {
                $('#exportData').prop('disabled', false).text('Export Excel');
                toastr.error('Terjadi kesalahan saat mengambil data.');
            }
        });
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

</script>
</body>

</html>