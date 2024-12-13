<!DOCTYPE html>
<html lang="en">

<!------------------------------------- Announcement Page ------------------------------------->

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <link rel="icon" href="<?= base_url('public/') ?>armada.ico" type="image/x-icon">
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
    <section id="announcement-detail" class="about section">
      <div class="container">
          <div class="row align-items-center justify-content-between">
              <div class="col-lg-7 mb-5 mb-lg-0 order-lg-2" data-aos="fade-up" data-aos-delay="400">
                  <div class="swiper init-swiper">
                      <script type="application/json" class="swiper-config">
                          {
                              "loop": true,
                              "speed": 600,
                              "autoplay": {
                                  "delay": 5000
                              },
                              "slidesPerView": "auto",
                              "pagination": {
                                  "el": ".swiper-pagination",
                                  "type": "bullets",
                                  "clickable": true
                              },
                              "breakpoints": {
                                  "320": {
                                      "slidesPerView": 1,
                                      "spaceBetween": 40
                                  },
                                  "1200": {
                                      "slidesPerView": 1,
                                      "spaceBetween": 1
                                  }
                              }
                          }
                      </script>
                      <div class="swiper-wrapper">
                          <div class="swiper-slide">
                              <img src="<?= base_url('uploads/news/' . $announcement['path_image']) ?>" alt="<?= $announcement['title'] ?>" class="img-fluid">
                          </div>
                      </div>
                      <div class="swiper-pagination"></div>
                  </div>
              </div>
              <div class="col-lg-4 order-lg-1">
                  <h1 class="mb-4" data-aos="fade-up">
                      <?= esc($announcement['title']) ?>
                  </h1>
                  <p data-aos="fade-up">
                      <?= esc($announcement['content']) ?>
                  </p>
                  <p class="mt-5" data-aos="fade-up">
                      <a href="<?= base_url('dashboard/news') ?>" class="btn btn-get-started">Kembali ke Berita</a>
                  </p>
              </div>
          </div>
      </div>
    </section>
  </main>

  <footer id="footer" class="footer light-background py-3 mt-auto">
    <div class="container">
      <div class="row align-items-center justify-content-between">
        <div class="col-md-4 text-center text-md-start">
          <p class="mb-0">
            <span>Copyright </span> ©<strong class="px-1 sitename">IT Department.</strong> 
            <span>PT. Mekar Armada Jaya</span>
          </p>
        </div>
        
        <div class="col-md-4 text-center">
            <a href="<?= base_url($redirect_url) ?>" class="btn btn-get-continue">Continue</a>
        </div>

        <div class="col-md-4 text-center text-md-end">
          <img src="<?= base_url('public/')?>img/MAJ-LOGO-3.png" alt="armada.ico" height="50">
        </div>
      </div>
    </div>
  </footer>


  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>
  <div id="preloader"></div>

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