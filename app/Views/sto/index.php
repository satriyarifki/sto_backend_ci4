<!DOCTYPE html>
<html lang="en">

<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="icon" href="<?= base_url() ?>armada.ico" type="image/x-icon">
  <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback" rel="stylesheet">
  <link rel="stylesheet" href="<?= base_url() ?>plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="<?= base_url() ?>dist/css/adminlte.min.css">

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
            <p class="text-center text-muted">Documentation of STO</p>

            <div class="card shadow-sm mt-4">
                <div class="card-body">
                    <form id="stoForm" method="post" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="area" class="form-label">Area</label>
                            <select name="area" id="area" class="form-select" required>
                                <option value="" disabled selected>Pilih Area</option>
                                <option value="IRM">IRM</option>
                                <option value="Docking IRM">Docking IRM</option>
                                <option value="PRESS">PRESS</option>
                                <option value="Koridor Welding">Koridor Welding</option>
                                <option value="Welding">Welding</option>
                                <option value="IFP & IFP Baru">IFP & IFP Baru</option>
                                <option value="Docking IFP">Docking IFP</option>
                                <option value="Subcon">Subcon</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="nik" class="form-label">NIK</label>
                            <input type="text" name="nik" id="nik" class="form-control" autocomplete="off" required>
                        </div>

                        <div class="mb-3">
                            <label for="no_tag" class="form-label">Nomor Tag</label>
                            <input type="text" name="no_tag" id="no_tag" class="form-control" autocomplete="off" 
                                required pattern="[0-9]+" title="Hanya boleh angka">
                        </div>

                        <div class="mb-3">
                            <label for="evidence" class="form-label">Evidence</label>
                            <input type="file" class="form-control" id="evidence" name="evidence" accept="image/*" capture="environment">
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

            var formData = new FormData(this);
            var nikValue = $('#nik').val();
            var areaValue = $('#area').val();

            $('#spinner-container').show();

            $.ajax({
                url: "<?= base_url('documentation/store') ?>",
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,
                dataType: "json",
                success: function(response) {
                    if (response.status === 'success') {
                        $('#spinner-container').hide();
                        localStorage.setItem("nik", nikValue);
                        localStorage.setItem("area", areaValue);
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message
                        }).then(() => {
                            window.location.href = "<?= base_url('documentation') ?>";
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
    </script>
</body>

</html>