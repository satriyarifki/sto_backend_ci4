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

  <title>Forgot Password</title>
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
      <h1 class="title">Forgot Password</h1>
      <p class="para"><?php echo sprintf(lang('Auth.forgot_password_subheading'), $identity_label); ?></p>

      <div id="infoMessage"><?php echo $message; ?></div>

      <?php echo form_open('auth/forgot_password'); ?>

      <div class="form">
        <input type="text" name="identity" id="identity" class="form__input" autocomplete="off" required>
        <label for="identity" class="label-name">
          <span class="content-name"><?php echo (($type === 'email') ? sprintf(lang('Auth.forgot_password_email_label'), $identity_label) : sprintf(lang('Auth.forgot_password_identity_label'), $identity_label)); ?></span>
        </label>
      </div>

      <input type="submit" value="<?php echo lang('Auth.forgot_password_submit_btn'); ?>" class="btn btn-primary">

      <?php echo form_close(); ?>
    </div>
  </div>

  <script src="<?= base_url() ?>plugins/jquery/jquery.min.js"></script>
  <script src="<?= base_url() ?>plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="<?= base_url() ?>dist/js/adminlte.min.js"></script>
  <script>
    $(document).ready(function(){
      var errorContainer = document.getElementById('errorContainer');
      errorContainer.innerHTML = '';
      <?php if (session()->getFlashdata('toastr_failed')): ?>
        errorContainer.innerHTML += '<div class="alert alert-danger" style="text-align: center;">Username atau Email Salah</div>';
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
