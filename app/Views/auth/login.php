<!DOCTYPE html>
<html lang="en">

<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="icon" href="<?= base_url()?>armada.ico" type="image/x-icon">
  <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback" rel="stylesheet">
  <link rel="stylesheet" href="<?= base_url() ?>plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="<?= base_url() ?>dist/css/adminlte.min.css">

  <title>Login</title>
  <style>
    body {
      background: url('<?= base_url() ?>dist/img/cool-background_2.png') no-repeat center center fixed;
      background-size: cover;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
    }

    .main-content {
      width: 100%;
      max-width: 500px;
      border-radius: 10px;
      box-shadow: 0 5px 5px rgba(0, 0, 0, .4);
      padding: 2em;
      background-color: rgba(255, 255, 255, 0.85);
    }

    .title {
      color: #0b479b;
      font-weight: 700;
      letter-spacing: 5px;
      text-align: center;
    }

    .para {
      color: #0b479b;
      font-weight: 500;
      letter-spacing: 2px;
      font-size: 16px;
      text-align: center;
    }

    .form-check-label {
      color: #0b479b;
      font-weight: 500;
    }

    form {
      padding: 0 1em;
    }

    img {
      width: 100px;
      height: auto;
      display: block;
      margin: 0 auto;
    }

    .form__input {
      width: 100%;
      height: 100%;
      padding-top: 20px;
      border: none;
      outline: none;
      background-color: transparent;
      color: #000;
    }

    .btn {
      transition: all .5s ease;
      width: 100%;
      border-radius: 30px;
      color: #0b479b;
      font-weight: 600;
      background-color: #fff;
      border: 1px solid #0b479b;
      margin-top: 1.5em;
      margin-bottom: 1em;
    }

    .btn:hover,
    .btn:focus {
      background-color: #0b479b;
      color: #fff;
    }

    .form {
      width: 100%;
      position: relative;
      height: 60px;
      overflow: hidden;
      margin-bottom: 1.5em;
    }

    .form input:focus {
      outline: none;
    }

    .form__input:focus+.label-name .content-name,
    .form__input:valid+.label-name .content-name {
      transform: translateY(-100%);
      font-size: 14px;
      left: 0px;
      color: #0b479b;
    }

    .form__input:focus+.label-name::after,
    .form__input:valid+.label-name::after {
      transform: translateX(0%);
    }

    .form label {
      position: absolute;
      bottom: 0;
      left: 0;
      width: 100%;
      height: 100%;
      pointer-events: none;
      border-bottom: 1px solid #0b479b;
      color: #0b479b;
      font-weight: normal;
    }

    .form label::after {
      content: "";
      position: absolute;
      bottom: -1px;
      left: 0;
      width: 100%;
      height: 100%;
      border-bottom: 3px solid #0b479b;
      transform: translateX(-100%);
      transition: all 0.3s ease;
    }

    .content-name {
      position: absolute;
      bottom: 0;
      left: 0;
      padding-bottom: 5px;
      transition: all 0.3s ease;
    }

    input:-webkit-autofill,
    input:-webkit-autofill:hover,
    input:-webkit-autofill:focus,
    input:-webkit-autofill:active {
      transition: background-color 5000s ease-in-out 0s;
      -webkit-text-fill-color: #000 !important;
    }

    input:-webkit-autofill+.label-name .content-name {
      transform: translateY(-100%);
      font-size: 14px;
      left: 0px;
      color: #0b479b;
    }

    input:-webkit-autofill+.label-name::after {
      transform: translateX(0%);
    }
  </style>
</head>

<body>
  <div class="login-box">
    <div class="main-content ">
      <div class="text-center">
        <img src="<?= base_url('img/ico.png') ?>" alt="company">
      </div>
      <h1 class="title">MSPIN</h1>
      <p class="para">MAJ Supplier Portal Integration Network</p>
      <div class="text-center mt-2">
        <form action="<?= base_url('auth/login') ?>" method="post">
          <div class="form">
            <input type="email" name="identity" id="identity" class="form__input" autocomplete="off" required>
            <label for="identity" class="label-name">
              <span class="content-name">Username</span>
            </label>
          </div>
          <div class="form">
            <input type="password" name="password" id="password" class="form__input" autocomplete="off" required>
            <label for="password" class="label-name">
              <span class="content-name">Password</span>
            </label>
          </div>
          <div id="errorContainer"></div>
          <div class="form-check d-flex justify-content-start align-items-center gap-2">
            <input type="checkbox" name="show" id="show" onclick="myFunction()" class="form-check-input">
            <label class="form-check-label" for="show">Show Password</label>
          </div>
          <div class="form-check d-flex justify-content-start align-items-center gap-2">
            <input type="checkbox" name="remember" id="remember" class="form-check-input">
            <label class="form-check-label" for="remember">Remember Me</label>
          </div>
          <input type="submit" value="<?= lang('Auth.login_submit_btn') ?>" class="btn btn-primary">
        </form>
      </div>
      <div class="text-center">
        <p style="font-weight: 500;">
          <a href="https://www.termsfeed.com/live/59d82317-1041-4303-8bbd-40e8f545eaba" rel="privacy-policy">Privacy Policy</a>
        </p>
      </div>
      <div class="text-center mt-3">
          <p style="font-size: 0.8rem; color: #888;">&copy; <?= date('Y') ?> IT Department</p>
      </div>
    </div>
  </div>
  <script>
    function myFunction() {
      var x = document.getElementById("password");
      if (x.type === "password") {
        x.type = "text";
      } else {
        x.type = "password";
      }
    }
  </script>
  <script src="<?= base_url() ?>plugins/jquery/jquery.min.js"></script>
  <script src="<?= base_url() ?>plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="<?= base_url() ?>dist/js/adminlte.min.js"></script>
  <script>
    $(document).ready(function(){
      var errorContainer = document.getElementById('errorContainer');
      errorContainer.innerHTML = '';
      <?php if (session()->getFlashdata('toastr_failed')): ?>
        errorContainer.innerHTML += '<div class="alert alert-danger" style="text-align: center;">Username dan Password Salah</div>';
        setTimeout(function(){
          $('#errorContainer .alert').fadeOut('slow', function(){
            $(this).remove();
          });
        }, 3000);
      <?php endif; ?>
    });
  </script>
</body>

</html>