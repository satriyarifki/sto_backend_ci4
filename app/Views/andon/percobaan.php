<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="icon" href="<?= base_url('public/') ?>armada.ico" type="image/x-icon">
  <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback" rel="stylesheet">
  <link rel="stylesheet" href="<?= base_url('public/') ?>plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="<?= base_url('public/') ?>dist/css/adminlte.min.css">
  <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
  <title><?= $title ?></title>

  <?php if(isset($css['header'])): ?>
      <?php foreach($css['header'] as $cssFile): ?>
          <link rel="stylesheet" href="<?= base_url('public/plugins/' . $cssFile . '.css') ?>">
      <?php endforeach; ?>
  <?php endif; ?>
  <style>
    body {
        background-color: black;
        color: white;
        font-family: Arial, sans-serif;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }    

    .priority-table {
        width: 100%;
    }

    .priority-table th, .priority-table td {
        width: 50%;
        padding: 10px;
        text-align: left;
        border: 1px solid #ddd;
    }

    @keyframes blink-background {
        50% {
            background-color: transparent;
            color: white;
        }
        100% {
            background-color: green;
            color: black; 
        }
    }

    .green-blink {
        background-color: green;
        animation: blink-background 3s infinite;
        color: white;
    }

    @keyframes blink-background-yellow {
        50% {
            background-color: transparent;
            color: white;
        }
        100% {
            background-color: yellow;
            color: black;
        }
    }

    .yellow-blink {
        background-color: yellow;
        animation: blink-background-yellow 3s infinite;
        color: black;
    }

    @keyframes blink-background-red {
        50% {
            background-color: transparent;
            color: white;
        }
        100% {
            background-color: red;
            color: black;
        }
    }

    .red-blink {
        background-color: red;
        animation: blink-background-red 3s infinite;
        color: white;
    }

    table,
    th,
    td {
        border: 1px solid white;
    }

    th,
    td {
        padding: 10px;
        text-align: center;
        vertical-align: middle;
        font-weight: bold;
    }

    th {
        background-color: black;
        color: white;
    }

    .custom-header th {
        background-color: white;
        color: black;
        border-color: black; 
    }

    .good {
        background-color: green;
        color: white;
    }

    .fair {
        background-color: yellow;
        color: black;
    }

    .poor {
        background-color: red;
        color: white;
    }

    thead th {
        color: white;
        padding: 10px;
    }

    tbody td {
        padding: 8px; 
    }

    .logo {
        max-width: 300px;
        height: auto;
        display: block; 
        margin: 0 auto;
    }
    
    #clock {
        font-size: 24px;
        font-weight: bold; 
        color: white; 
        padding: 10px;
        height: auto;
    }

    .white {
        background-color: white;
    }
</style>

</head>

<div class="table-container">
  <table>
    <thead>
        <tr>
            <th class="white">
                <img src="<?= base_url('public/img/MAJ-LOGO-3.png') ?>" alt="Logo Perusahaan" class="logo" style="width: 300px;">
            </th>
            <th colspan="6" rowspan="2">
                <h1>DROPBOX INVOICE ACHIEVEMENT</h1>
                <h2>PT Mekar Armada Jaya Tambun Plant</h2>
            </th>
            <th colspan="4" rowspan="2">
                <table class="priority-table">
                    <tr>
                        <th>LOW PRIORITY</th>
                        <td class="good"></td>
                    </tr>
                    <tr>
                        <th>MEDIUM PRIORITY</th>
                        <td class="fair"></td>
                    </tr>
                    <tr>
                        <th>HIGH PRIORITY</th>
                        <td class="poor"></td>
                    </tr>
                </table>
            </th>
        </tr>
        <tr>
            <th>
                <input type="text" id="date_filter" name="date_filter" class="form-control" value="<?= date('Y-m-d') ?>">
            </th>
        </tr>
        <tr class="custom-header">
            <th colspan="3">INVOICE</th>
            <th>SECURITY</th>
            <th colspan="4">PURCHASING</th>
            <th colspan="3">FINANCE</th>
        </tr>
        <tr class="custom-header">
            <th>VENDOR</th>
            <th>DROPBOX NUMBER</th>
            <th>INVOICE NUMBER</th>
            <th>RECEIVED</th>
            <th>ON HOLD</th>
            <th>VERIFYING</th>
            <th>ZINVER IN</th>
            <th>ZINVER OUT</th>
            <th>ON HOLD</th>
            <th>MIRO</th>
            <th>Total Per Day</th>
        </tr>
    </thead>
    <tbody id="invoice-table-body">
    </tbody>
  </table>
</div>

</html>
