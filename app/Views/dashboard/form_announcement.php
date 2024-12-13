<!DOCTYPE html>
<html lang="en">

<!------------------------------------- Announcement Page ------------------------------------->

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <link rel="icon" href="<?= base_url() ?>armada.ico" type="image/x-icon">
  <title>News</title>
  <meta name="description" content="">
  <meta name="keywords" content="">

  <link href="assets/img/favicon.png" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&display=swap" rel="stylesheet">

  <link href="<?= base_url() ?>plugins/xlsx/libs/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="<?= base_url() ?>plugins/xlsx/libs/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="<?= base_url() ?>plugins/xlsx/libs/aos/aos.css" rel="stylesheet">
  <link href="<?= base_url() ?>plugins/xlsx/libs/swiper/swiper-bundle.min.css" rel="stylesheet">
  <link href="<?= base_url() ?>plugins/xlsx/libs/glightbox/css/glightbox.min.css" rel="stylesheet">
  <link href="<?= base_url() ?>plugins/xlsx/css/main.css" rel="stylesheet">
  <link href="<?= base_url() ?>plugins/toastr/toastr.min.css" rel="stylesheet">
  <script src="<?= base_url()?>plugins/jquery/jquery.min.js"></script>
</head>

<body class="d-flex flex-column min-vh-100">
  <header id="header" class="header d-flex align-items-center sticky-top">
    <div class="container-fluid container-xl position-relative d-flex align-items-center justify-content-between">
      <a class="logo d-flex align-items-center">
        <h1 class="sitename">Announcement</h1>
      </a>
      <nav id="navmenu" class="navmenu">
        <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
      </nav>
    </div>
  </header>

  <main class="main flex-grow-1">
    <section id="upload-announcement" class="section">
      <div class="container">
        <div class="row">
          <div class="card">
            <div class="card-header">
              <h5>Form Upload Announcement</h5>
            </div>
            <div class="card-body">
              <form action="<?= base_url('dashboard/uploads-data') ?>" method="post" enctype="multipart/form-data">
                <div class="mb-3">
                  <label for="title" class="form-label">Judul Announcement</label>
                  <input type="text" id="title" name="title" class="form-control" placeholder="Masukkan judul" required>
                </div>
                <div class="mb-3">
                  <label for="content" class="form-label">Isi Announcement</label>
                  <textarea id="content" name="content" class="form-control" rows="5" placeholder="Masukkan isi announcement" required></textarea>
                </div>
                <div class="mb-3">
                  <label for="image" class="form-label">Gambar</label>
                  <input type="file" id="image" name="image" class="form-control" accept="image/*">
                </div>
                <div class="d-grid">
                  <button type="submit" class="btn btn-primary">Upload</button>
                </div>
              </form>
            </div>
          </div>

        </div>
      </div>
    </section>
  </main>


  <footer id="footer" class="footer py-3 mt-auto">
    <div class="container text-center">
      <p class="mb-0">
        <span>Copyright </span> ©<strong class="px-1 sitename">IT Department.</strong> 
        <span>PT. Mekar Armada Jaya</span>
      </p>
    </div>
  </footer>

  <script src="<?= base_url('public/') ?>plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="<?= base_url('public/') ?>plugins/toastr/toastr.min.js"></script>

  <script>
    $(document).ready(function(){
      <?php if (session()->getFlashdata('toastr_success')): ?>
        toastr.success('<?= session()->getFlashdata('toastr_success') ?>');
      <?php elseif (session()->getFlashdata('toastr_error')): ?>
        toastr.error('<?= session()->getFlashdata('toastr_error') ?>');
      <?php endif; ?>
    });
  </script>

  <script src="<?= base_url() ?>plugins/xlsx/libs/php-email-form/validate.js"></script>
  <script src="<?= base_url() ?>plugins/xlsx/libs/aos/aos.js"></script>
  <script src="<?= base_url() ?>plugins/xlsx/libs/swiper/swiper-bundle.min.js"></script>
  <script src="<?= base_url() ?>plugins/xlsx/libs/purecounter/purecounter_vanilla.js"></script>
  <script src="<?= base_url() ?>plugins/xlsx/libs/glightbox/js/glightbox.min.js"></script>
  <script src="<?= base_url() ?>plugins/xlsx/libs/imagesloaded/imagesloaded.pkgd.min.js"></script>
  <script src="<?= base_url() ?>plugins/xlsx/libs/isotope-layout/isotope.pkgd.min.js"></script>
  <script src="<?= base_url() ?>plugins/xlsx/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="<?= base_url() ?>plugins/toastr/toastr.min.js"></script>

  <script src="<?= base_url()?>plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="<?= base_url()?>dist/js/adminlte.js"></script>

  <script src="<?= base_url() ?>plugins/xlsx/js/main.js"></script>

  <script>
    $(document).ready(function(){
      <?php if (session()->getFlashdata('toastr_success')): ?>
        toastr.success('<?= session()->getFlashdata('toastr_success') ?>');
      <?php endif; ?>
    });
  </script>

</body>

</html>