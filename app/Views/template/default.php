
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="<?= base_url()?>armada.ico" type="image/x-icon">
  <title><?= $title; ?></title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome Icons -->
  <link rel="stylesheet" href="<?= base_url()?>plugins/fontawesome-free/css/all.min.css">
  <!-- overlayScrollbars -->
  <link rel="stylesheet" href="<?= base_url()?>plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="<?= base_url()?>dist/css/adminlte.min.css">
  <!-- khusus preloader -->
  <link rel="stylesheet" href="<?= base_url()?>dist/css/preloader.css">
  <link rel="privacy-policy" href="https://www.termsfeed.com/live/59d82317-1041-4303-8bbd-40e8f545eaba" />

  <script src="<?= base_url()?>plugins/jquery/jquery.min.js"></script>

  <?php
  if (isset($css['header'])) {
      echo view('partials/css', ['css' => $css['header']]);
  }
  if (isset($js['header'])) {
      echo view('partials/js', ['js' => $js['header']]);
  }
  ?>
  
</head>
<body class="sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
<div class="wrapper">

  <!-- Preloader -->
  <!-- <div class="preloader flex-column justify-content-center align-items-center">
    <img class="animation__wobble" src="tambahkan base_url/dist/img/ico.png" alt="AdminLTELogo">
  </div> -->

  <!-- <div class="preloader flex-column justify-content-center align-items-center">
    <div class="loading">Loading</div>
  </div> -->

  <!-- <div class="preloader flex-column justify-content-center align-items-center">
    <div class="overlay dark"><i class="fas fa-3x fa-sync-alt fa-spin"></i><div class="text-bold pt-2">Loading...</div></div>
  </div> -->

  <nav class="main-header navbar navbar-expand navbar-dark">
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
      </li>
      <li class="nav-item">
        <a href="<?= base_url('users/profile');?>" class="nav-link">Profile</a>
      </li>
      <?php if($ionAuth->inGroup('admin')||$ionAuth->user()->row()->email === 'farah.amalia06@gmail.com') :?>
        <li class="nav-item d-none d-sm-inline-block">
          <a href="<?= base_url('users/upload-user');?>" class="nav-link">Upload User</a>
        </li>
      <?php endif; ?>
      <?php if($ionAuth->inGroup('vendor') || $ionAuth->inGroup('maj')) :?>
        <li class="nav-item d-none d-sm-inline-block">
          <a href="<?= base_url('auth/change-password');?>" class="nav-link">Change Password</a>
        </li>
      <?php endif; ?>
    </ul>

    <ul class="navbar-nav ml-auto">

      <li class="nav-item d-none d-sm-inline-block">
        <a class="nav-link" id="clock"></a>
      </li>
      
      <li class="nav-item d-none d-sm-inline-block">
        <a href="<?= base_url('dashboard/news');?>" class="nav-link"><i class="fa fa-newspaper"></i></a>
      </li>

      <?php if($ionAuth->inGroup('vendor')) :?>
        <li class="nav-item dropdown">
            <a class="nav-link" data-toggle="dropdown" href="#">
                <i class="far fa-bell"></i>
                <span class="badge badge-warning navbar-badge" id="notification-count"><?= $notificationCount ?></span>
            </a>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right" id="notification-dropdown">
                <span class="dropdown-item dropdown-header"><?= $notificationCount ?> Notifications</span>
                <div class="dropdown-divider"></div>
                <?php foreach ($notifications as $notification): ?>
                    <div class="dropdown-item" onclick="markNotificationAsRead(<?= $notification['id'] ?>, this)">
                        <i class="fas fa-envelope mr-2"></i>
                        <a href='https://mail.google.com/' target="_blank">
                            <strong><?= $notification['header'] ?></strong><br>
                            <?= $notification['message'] ?>
                        </a>  
                        <span class="float-right text-muted text-sm"><?= $notification['created_at'] ?></span>
                    </div>
                    <div class="dropdown-divider"></div>
                <?php endforeach; ?>
                <button type="button" class="dropdown-item dropdown-footer">See All Notifications</button>
            </div>
        </li>
      <?php endif; ?>

      <li class="nav-item">
        <a href="<?= base_url('auth/logout');?>" class="nav-link">Logout</a>
      </li>

      <li class="nav-item d-none d-sm-inline-block">
        <a class="nav-link" data-widget="fullscreen" href="#" role="button">
          <i class="fas fa-expand-arrows-alt"></i>
        </a>
      </li>
    </ul>
  </nav>
  
  <script>
      function markNotificationAsRead(notificationId, element) {
          var xhr = new XMLHttpRequest();
          xhr.open('POST', `<?= base_url('notification/markAsRead') ?>`, true);
          xhr.setRequestHeader('Content-Type', 'application/json');
          xhr.onreadystatechange = function () {
              if (xhr.readyState === 4 && xhr.status === 200) {
                  var data = JSON.parse(xhr.responseText);
                  document.getElementById('notification-count').textContent = data.notificationCount;
                  
                  var parent = element.parentElement;
                  var divider = element.nextElementSibling;
                  if (divider && divider.classList.contains('dropdown-divider')) {
                      parent.removeChild(divider);
                  }
                  parent.removeChild(element);
              }
          };
          xhr.send(JSON.stringify({ notificationId: notificationId }));
      }
  </script>

  <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="index3.html" class="brand-link">
      <img src="<?= base_url()?>dist/img/ico.png" alt="AdminLTE Logo" class="brand-image" style="opacity: .8">
      <span class="brand-text font-weight-light">Portal Supplier MAJ</span>
    </a>

    <!-- Sidebar -->
    <?= $this->include('template/menu')?>
    <!-- /.sidebar -->

  </aside>

  <div class="content-wrapper">
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0"><?= $title; ?></h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active"><?= $title; ?></li>
            </ol>
          </div>
        </div>
      </div>
    </div>

    <?= $this->renderSection('content') ?>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->

  <!-- Control Sidebar -->
  <!-- <aside class="control-sidebar control-sidebar-dark"> -->
    <!-- Control sidebar content goes here -->
  <!-- </aside> -->
  <!-- /.control-sidebar -->

  <!-- Main Footer -->
  <footer class="main-footer">
      <strong>Copyright &copy; 2024 <a href="https://newarmada.biz/">IT Department</a>.</strong>
      PT. Mekar Armada Jaya | <a href="https://www.termsfeed.com/live/59d82317-1041-4303-8bbd-40e8f545eaba" rel="privacy-policy">Privacy Policy</a>
      <div class="float-right d-none d-sm-inline-block">
        <b>Version</b> 1.0.0
      </div>
  </footer>
</div>
<!-- ./wrapper -->

<!-- REQUIRED SCRIPTS -->
<!-- jQuery -->
<!-- Bootstrap -->
<script src="<?= base_url()?>plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?= base_url()?>plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
<script src="<?= base_url()?>dist/js/adminlte.js"></script>

<!-- PAGE PLUGINS -->
<!-- jQuery Mapael -->
<script src="<?= base_url()?>plugins/jquery-mousewheel/jquery.mousewheel.js"></script>
<script src="<?= base_url()?>plugins/raphael/raphael.min.js"></script>
<script src="<?= base_url()?>plugins/jquery-mapael/jquery.mapael.min.js"></script>
<script src="<?= base_url()?>plugins/jquery-mapael/maps/usa_states.min.js"></script>

<script src="<?= base_url()?>plugins/popper/umd/popper.min.js"></script>
<!-- ChartJS -->
<script src="<?= base_url()?>plugins/chart.js/Chart.min.js"></script>

<?php 
  if (isset($js['footer'])) {
      echo view('partials/js', ['js' => $js['footer']]);
  }
?>

<script>
  function updateClock() {
    var now = new Date();
    var hours = now.getHours();
    var minutes = now.getMinutes();
    var seconds = now.getSeconds();

    // Formatting time to add leading zero if necessary
    hours = ('0' + hours).slice(-2);
    minutes = ('0' + minutes).slice(-2);
    seconds = ('0' + seconds).slice(-2);

    // Array of month names
    var monthNames = [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];

    var month = monthNames[now.getMonth()];

    // Formatting date
    var day = ('0' + now.getDate()).slice(-2);
    var year = now.getFullYear();

    var clockElement = document.getElementById('clock');
    clockElement.textContent = day + ' ' + month + ' ' + year + ' ' + hours + ':' + minutes + ':' + seconds;

    setTimeout(updateClock, 1000);
  }
  updateClock();
</script>
</body>
</html>
