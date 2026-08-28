<!DOCTYPE html>
<html lang="en">

<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="icon" href="<?= base_url() ?>armada.ico" type="image/x-icon">
  <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback" rel="stylesheet">
  <link rel="stylesheet" href="<?= base_url() ?>plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="<?= base_url() ?>plugins/select2/css/select2.min.css">
  <link rel="stylesheet" href="<?= base_url() ?>plugins/datatables/DataTables-2.0.1/css/dataTables.dateTime.min.css">
  <link rel="stylesheet" href="<?= base_url() ?>dist/css/adminlte.min.css">
  <link rel="stylesheet" href="<?= base_url() ?>plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css"> 

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

    </style>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="text-center">
                <img src="<?= base_url('img/ico.png') ?>" alt="company" class="mb-3">
            </div>
            <h1 class="text-center fw-bold">STO MAJ</h1>
            <p class="text-center text-muted">Preparation of STO</p>

            <div class="card shadow-sm mt-4">
                <div class="card-body">
                    <form id="stoForm" method="post" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="total_tag" class="form-label">Total TAG</label>
                            <input 
                                type="number" 
                                class="form-control" 
                                id="totalTag" 
                                name="total_tag" 
                                min="1" 
                                placeholder="Enter total TAG to print">
                        </div>
                        <div class="mb-3">
                            <label for="area" class="form-label">Area</label>
                            <select 
                                class="form-control select2bs4" 
                                id="areaSelect" 
                                name="area">
                                <option value="">-- Select Area --</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="area" class="form-label">Part Number</label>
                            <select 
                                class="form-control select2bs4" 
                                id="partSelect" 
                                name="part_number" disabled>
                                <option value="">-- Select Part Number --</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="area" class="form-label">Job Number</label>
                            <select 
                                class="form-control select2bs4" 
                                id="jobSelect" 
                                name="job_number" disabled>
                                <option value="">-- Select Job Number --</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="area" class="form-label">Part Description</label>
                            <select 
                                class="form-control select2bs4" 
                                id="descSelect" 
                                name="part_desc" disabled>
                                <option value="">-- Select Part Description --</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="area" class="form-label">Customer</label>
                            <select 
                                class="form-control select2bs4" 
                                id="customerSelect" 
                                name="customer" disabled>
                                <option value="">-- Select Customer --</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="area" class="form-label">Type</label>
                            <select 
                                class="form-control select2bs4" 
                                id="typeSelect" 
                                name="type" disabled>
                                <option value="">-- Select Type --</option>
                            </select>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="text-center mt-3">
                <p style="font-size: 0.8rem; color: #888;">&copy; <?= date('Y') ?> IT Department</p>
            </div>
        </div>
    </div>
</div>

<div id="spinner-container">
    <button class="btn btn-primary">
        <span class="spinner-border spinner-border-sm"></span>
        Loading..
    </button>
</div>

    <script src="<?= base_url() ?>plugins/jquery/jquery.min.js"></script>
    <script src="<?= base_url() ?>plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?= base_url() ?>plugins/select2/js/select2.full.min.js"></script>
    <script src="<?= base_url() ?>dist/js/adminlte.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>

    $(document).ready(function() {
        if (localStorage.getItem("nik")) {
            $('#nik').val(localStorage.getItem("nik"));
        }

        if (localStorage.getItem("area")) {
            $('#area').val(localStorage.getItem("area"));
        }

        $('#stoForm').on('submit', function(e) {
            e.preventDefault();
            var formData = {
                area: $('#areaSelect').val(),
                customer: $('#customerSelect').val(),
                job_number: $('#jobSelect').val(),
                part_number: $('#partSelect').val(),
                part_desc: $('#descSelect').val(),
                type: $('#typeSelect').val(),
                total_tag: $('#totalTag').val()
            };

            $('#spinner-container').show();

            $.ajax({
                url: "<?= base_url('documentation/store') ?>",
                type: "POST",
                // data: formData,
                data: $.extend({}, formData, {
                    type: (formData.type || '').split(" ")[0] // only slice at posting
                }),
                dataType: "json",
                success: function(response) {
                    if (response.status === 'success') {
                        $('#spinner-container').hide();
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message
                        }).then(() => {
                            const idTags = response.id_tags.join(',');
                            window.open("<?= base_url('documentation/tag-sto') ?>?area=" + encodeURIComponent(formData.area) +
                                "&customer=" + encodeURIComponent(formData.customer) +
                                "&job_number=" + encodeURIComponent(formData.job_number) +
                                "&part_number=" + encodeURIComponent(formData.part_number) +
                                "&part_desc=" + encodeURIComponent(formData.part_desc) +
                                "&type=" + encodeURIComponent(formData.type) +
                                "&id_tags=" + encodeURIComponent(idTags),"_blank");
                            
                        });
                    } else {
                        $('#spinner-container').hide();
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: response.message
                        });
                    }
                },
                error: function() {
                    $('#spinner-container').hide();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Terjadi kesalahan saat mengirim data.'
                    });
                }
            });
        });
    });

    $(function () {
        // AREA
        $("#areaSelect").select2({
            theme: "bootstrap4",
            placeholder: 'Select an Area',
            ajax: {
                url: '<?= base_url()?>documentation/master-area',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return { search: params.term };
                },
                processResults: function (data) {
                    return { results: data.result.map(item => ({ id: item.area, text: item.area })) };
                }
            }
        }).on('select2:select', function () {
            $('#partSelect').prop('disabled', false).val(null).trigger('change');
            $('#jobSelect, #customerSelect, #descSelect, #typeSelect').prop('disabled', true).val(null).trigger('change');
        });

        // PART NUMBER
        $("#partSelect").select2({
            theme: "bootstrap4",
            placeholder: 'Select a Part Number',
            ajax: {
                url: '<?= base_url()?>documentation/master-part-number',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return { search: params.term, area: $('#areaSelect').val() };
                },
                processResults: function (data) {
                    return {
                        results: data.result.map(item => ({
                            id: item.part_number,
                            text: item.part_number - job_number,
                            description: item.material_description,
                            type: item.type,
                            customer: item.customer,
                            plant: item.plant,
                            process: item.process,
                            job_number: item.job_number
                        }))
                    };
                }
            }
        }).on('select2:select', function (e) {
            const data = e.params.data;
            let typeProcess = data.type;
            if (data.process) {
            typeProcess += ` (${data.process})`;
            }
            $('#descSelect').html(`<option value="${data.description}" selected>${data.description}</option>`).trigger('change');
            $('#typeSelect').html(`<option value="${typeProcess}" selected>${typeProcess}</option>`).trigger('change');
            $('#customerSelect').html(`<option value="${data.customer} - ${data.plant}" selected>${data.customer} - ${data.plant}</option>`).trigger('change');
            $('#jobSelect').html(`<option value="${data.job_number}" selected>${data.job_number}</option>`).trigger('change');

            $('#descSelect, #typeSelect, #customerSelect, #jobSelect').prop('disabled', true);
        });


        $('#areaSelect').on('change', function() {
            $('#partSelect').val(null).trigger('change');
            $('#jobSelect').val(null).trigger('change');
            $('#descSelect').val(null).trigger('change');
            $('#customerSelect').val(null).trigger('change');
            $('#typeSelect').val(null).trigger('change');
        });

        $('#partSelect').on('change', function() {
            $('#jobSelect').val(null).trigger('change');
            $('#customerSelect').val(null).trigger('change');
            $('#descSelect').val(null).trigger('change');
            $('#typeSelect').val(null).trigger('change');
        });
    });
    </script>
</body>

</html>