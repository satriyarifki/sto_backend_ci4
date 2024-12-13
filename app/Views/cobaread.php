<!-- app/Views/upload_excel.php -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload Excel</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2>Upload File Excel</h2>
        <form action="<?= base_url('temp/upload') ?>" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="png_file">Pilih Faktur Pajak:</label>
                <input type="file" class="form-control-file" id="png_file" name="png_file" accept=".pdf">
            </div>
            <button type="submit" class="btn btn-primary">Upload</button>
        </form>
    </div>
</body>
</html>
