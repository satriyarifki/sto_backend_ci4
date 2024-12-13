<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="<?= base_url('public/') ?>armada.ico" type="image/x-icon">
  <title><?= $title; ?></title>

  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <link rel="stylesheet" href="<?= base_url()?>plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="<?= base_url()?>dist/css/adminlte.min.css">
  <script src="<?= base_url()?>plugins/jquery/jquery.min.js"></script>
  
</head>

<body class="sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
    <div class="wrapper">
        <section class="content">
            <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                <!-- Main content -->
                <div class="invoice p-3 mb-3">
                    <!-- title row -->
                    <div class="row">
                    <div class="col-12">
                        <h4>
                        Goods Received [GR].
                        <small class="float-right">Date: 2/10/2014</small>
                        </h4>
                    </div>
                    <!-- /.col -->
                    </div>
                    <!-- info row -->
                    <div class="row invoice-info">
                    <div class="col-sm-6 invoice-col">
                        <dl>
                            <dt>No. PO:</dt>
                            <dd>Value PO</dd>

                            <dt>No. GR:</dt>
                            <dd>Value GR</dd>

                            <dt>Doc. Date:</dt>
                            <dd>Value Doc. Date</dd>

                            <dt>Storage Loc.:</dt>
                            <dd>Value Storage Loc.</dd>
                        </dl>
                    </div>
                    <!-- /.col -->
                    <div class="col-sm-4 invoice-col">
                        <dl>
                            <dt>Movement Type:</dt>
                            <dd>Value Movement Type</dd>

                            <dt>Posting Date:</dt>
                            <dd>Value Posting Date</dd>

                            <dt>Header Text:</dt>
                            <dd>Value Header Text</dd>

                            <dt>Created By:</dt>
                            <dd>Value Created By</dd>

                            <dt>Vendor:</dt>
                            <dd>Value Vendor</dd>
                        </dl>
                    </div>
                    <!-- /.col -->
                    </div>
                    <!-- /.row -->

                    <!-- Table row -->
                    <div class="row">
                    <div class="col-12 table-responsive">
                        <table class="table table-striped">
                        <thead>
                        <tr>
                            <th>No.</th>
                            <th>Material</th>
                            <th>Material Description</th>
                            <th>Batch</th>
                            <th>Quantity</th>
                            <th>Unit</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>1</td>
                            <td>MMKM019202</td>
                            <td>SLFG59SF-SS440</td>
                            <td>10MKM-0</td>
                            <td>12</td>
                            <td>Unit</td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>MMKM019202</td>
                            <td>247-925-726</td>
                            <td>10MKMLLSD-0</td>
                            <td>56</td>
                            <td>Unit</td>
                        </tr>
                        </tbody>
                        </table>
                    </div>
                    <!-- /.col -->
                    </div>
                    <!-- /.row -->

                    <div class="row mt-4">
                        <!--signature column-->
                        <div class="col-2">
                            <div style="margin-top: 80px; text-align: center;">
                                <?php echo $styleRound ?>
                            </div>
                        </div>
                        <div class="col-1"></div>
                        <div class="col-3">
                            <div style="margin-top: 80px; border: 1px solid #000; width: 230px; height: 150px; text-align: center;">
                                <!-- Tempatkan konten tanda tangan di sini -->
                                Received
                            </div>
                        </div>
                        <div class="col-3">
                            <div style="margin-top: 80px; border: 1px solid #000; width: 230px; height: 150px; text-align: center;">
                                <!-- Tempatkan konten tanda tangan di sini -->
                                Werehouse
                            </div>
                        </div>
                        <div class="col-3">
                            <div style="margin-top: 80px; border: 1px solid #000; width: 230px; height: 150px; text-align: center;">
                                <!-- Tempatkan konten tanda tangan di sini -->
                                Procurement
                            </div>
                        </div>
                        <!-- /.col -->
                    </div>
                    <!-- /.row -->

                    <!-- this row will not appear when printing -->
                    <div class="row no-print">
                    <div class="col-12">
                    </div>
                    </div>
                </div>
                <!-- /.invoice -->
                </div><!-- /.col -->
            </div><!-- /.row -->
            </div><!-- /.container-fluid -->
        </section>
    </div>

<script>
    // Cek jika window di-load
    window.addEventListener("load", function() {
    // Cek apakah ada opsi pencetakan
    if (window.matchMedia) {
        // Periksa jika pengguna membatalkan pencetakan
        var mediaQueryList = window.matchMedia('print');
        mediaQueryList.addListener(function(mql) {
            if (!mql.matches) {
                // Pengguna membatalkan pencetakan, tambahkan penanganan di sini
                console.log("Pencetakan dibatalkan");
                // Contoh: Kembali ke halaman web sebelumnya
                window.history.back();
            }
        });
    }

    // Pencetakan otomatis saat window di-load
    window.print();
});
</script>

<style>
    .signature-container {
        position: relative;
        height: 100px; /* Atur tinggi container sesuai kebutuhan */
    }

    .signature-line {
        border-bottom: 1px solid #000; /* Garis tanda tangan */
        width: 80%; /* Panjang garis sesuai kebutuhan */
        margin-bottom: 10px; /* Jarak antara garis dan label */
    }

    .signature-label {
        position: absolute;
        bottom: 0;
        left: 0;
        font-size: 12px;
        color: #555;
    }
</style>

</body>