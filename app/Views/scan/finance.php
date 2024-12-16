<?= $this->extend('template/default') ?>
<?= $this->section('content') ?>

<section class="content">
</section>

<style>
    section {
        display: flex;
        justify-content: center;
        align-items: center;
        background-image: url('<?= base_url() ?>img/framefinance.png');
        background-size: 65%;
        background-position: center;
        background-repeat: no-repeat;
        height: 70vh;
    }
</style>

<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/sweetalert2@9.17.2/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@9.17.2/dist/sweetalert2.all.min.js"></script>


<script>
    let scannedData = '';
    let scanTimeout;

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && scannedData.length > 0) {
            $.ajax({
                url: '<?= base_url() ?>transaction/process-scanned-finance',
                type: 'POST',
                contentType: 'application/json',
                processData: false,
                data: JSON.stringify({ barcode: scannedData }), 
                dataType: 'json',
                success: function(response) {
                    if (response.message == true) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.response,
                        });
                    } else if (response.message == false) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Failed',
                            text: response.response,
                        });
                    }
                    scannedData = '';
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Terjadi kesalahan dalam mengirim data.',
                    });
                    console.error('Error:', error);
                    scannedData = '';
                }
            });
        } else if (e.key !== 'Shift' && e.key !== 'Control') {
            scannedData += e.key;
        }
    });
</script>

<?= $this->endSection() ?>