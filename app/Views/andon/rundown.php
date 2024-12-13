<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" href="<?= base_url() ?>armada.ico" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url() ?>plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="<?= base_url() ?>dist/css/adminlte.min.css">    
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <title><?= $title ?></title>
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

        .priority-table th,
        .priority-table td {
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

	@keyframes blink-background-safe {
            0% {
                background-color: #ACE2E1;
            }

            50% {
                background-color: transparent;
            }

            100% {
                background-color: #ACE2E1;
            }
        }

        .safe-blink {
            background-color: #ACE2E1;
            animation: blink-background-safe 3s infinite;
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

        tr,
        th {
            background-color: black;
            color: white;
        }

        .custom-header th {
            background-color: white;
            color: black;
            border: 1px solid black;
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
            font-size: 20px;
            font-weight: bold;
            color: white;
            padding: 10px;
            height: auto;
        }

        #fixed-div {
            position: sticky;
            z-index: 1;
            top: 0;
        }

	.white{
	    background-color: white;
	}
    </style>

</head>

<div class="table-container">
    <table>
        <thead id="fixed-div">
            <tr>
                <th class="white">
                    <img src="<?= base_url('img/MAJ-LOGO-3.png') ?>" alt="Logo Perusahaan" class="logo" style="width: 300px;">
                </th>
                <th colspan="6" rowspan="3" style="color: white;">
                    <h1>DROPBOX INVOICE ACHIEVEMENT</h1>
                    <h2>PT Mekar Armada Jaya Tambun Plant</h2>
                </th>
                <th colspan="6" rowspan="3">
                    <table class="priority-table" style="font-size: 12px;">
                        <tr>
                            <th>LOW PRIORITY</th>
                            <td style="background-color: green;"></td>
                        </tr>
                        <tr>
                            <th>MEDIUM PRIORITY</th>
                            <td style="background-color: yellow;"></td>
                        </tr>
                        <tr>
                            <th>HIGH PRIORITY</th>
                            <td style="background-color: red;"></td>
                        </tr>
			<tr>
                            <th>CURRENT PROCESS</th>
                            <td style="background-color: #ACE2E1;"></td>
                        </tr>
                    </table>
                </th>
            </tr>
            <tr>
                <th>
                    <input type="text" id="date_filter" name="date_filter" class="form-control" value="<?= date('Y-m-d') ?>">
                </th>
            </tr>
            <tr>
                <th id="clock"></th>
            </tr>
            <tr class="custom-header">
                <th colspan="3">INVOICE</th>
                <th>SECURITY</th>
                <th colspan="3">PURCHASING</th>
                <th colspan="1">FINANCE</th>
                <th rowspan="2">Total Work Day</th>
            </tr>
            <tr class="custom-header">
                <th>VENDOR</th>
                <th>DROPBOX NUMBER</th>
                <th>INVOICE NUMBER</th>
                <th>RECEIVED</th>
                <th>VERIFYING</th>
                <th>ZINVER IN</th>
                <th>ZINVER OUT</th>
                <th>MIRO</th>
            </tr>
        </thead>
        <tbody id="invoice-table-body">
        </tbody>
    </table>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>

<script>
    $(function() {
        $("#date_filter").datepicker({
            dateFormat: "yy-mm-dd",
            changeMonth: true,
            changeYear: true,
            showAnim: "slideDown",
            onSelect: function() {
                performSearch();
            },
        });

        function updateClock() {
            var now = new Date();
            var hours = now.getHours();
            var minutes = now.getMinutes();
            var seconds = now.getSeconds();

            hours = ('0' + hours).slice(-2);
            minutes = ('0' + minutes).slice(-2);
            seconds = ('0' + seconds).slice(-2);

            var monthNames = [
                'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
            ];

            var month = monthNames[now.getMonth()];
            var day = ('0' + now.getDate()).slice(-2);
            var year = now.getFullYear();
            var clockElement = document.getElementById('clock');
            clockElement.textContent = day + ' ' + month + ' ' + year + ' ' + hours + ':' + minutes + ':' + seconds;
            setTimeout(updateClock, 1000);
        }
        updateClock();

        var allData = [];

        function fetchAndRenderInvoices() {
            $.ajax({
                url: '<?= base_url("rundown/getinvoice") ?>',
                method: 'GET',
                dataType: 'json',
                success: function(data) {
                    allData = data;
                    data.forEach(function(item) {
                        var generatedDate = new Date(item.date_approve);
                        if (isNaN(generatedDate.getTime())) {
                            console.error(`Invalid date_approve for item: ${item.company_name}, date_approve: ${item.date_approve}`);
                            return;
                        }
                        var today = new Date();
                        var differenceInTime = today - generatedDate;
                        var differenceInDays = Math.floor(differenceInTime / (1000 * 60 * 60 * 24));
                        item.differenceInDays = differenceInDays;

                    });
                    renderInvoices(data);
                },
                error: function(xhr, status, error) {
                    console.error("Gagal mengambil data:", error);
                }
            });
        }

        function formatDate(dateString) {
            if (!dateString) return "-";
            var date = new Date(dateString);
            var day = ('0' + date.getDate()).slice(-2);
            var month = ('0' + (date.getMonth() + 1)).slice(-2);
            var year = date.getFullYear();
            return `${day}-${month}-${year}`;
        }

        function hitungHariKerja(startDate, endDate) {
            var count = 0;
            var currentDate = new Date(startDate);
            var end = new Date(endDate);
            var hariLibur = [
                "2024-11-27"
            ];

            while (currentDate <= end) {
                var day = currentDate.getDay();
                var currentDateStr = currentDate.toISOString().split('T')[0];

                if (day !== 0 && day !== 6 && !hariLibur.includes(currentDateStr)) {
                    count++;
                }
                currentDate.setDate(currentDate.getDate() + 1);
            }
            return count;
        }


	function determineBlinkClass(item) {
            var blinkClass = 'green-blink';
            var hariKerja = hitungHariKerja(item.date_approve, new Date());

            if (item.date_approve && item.date_dok_ok == null) {

                if (hariKerja < 2) {
                    blinkClass = 'green-blink';
                } else if (hariKerja === 2) {
                    blinkClass = 'yellow-blink';
                } else if (hariKerja > 2) {
                    blinkClass = 'red-blink';
                }

            }
            if (item.date_dok_ok && item.date_out_pud == null) {

                if (hariKerja < 4) {
                    blinkClass = 'green-blink';
                } else if (hariKerja === 4) {
                    blinkClass = 'yellow-blink';
                } else if (hariKerja > 4) {
                    blinkClass = 'red-blink';
                }

            }
            if (item.date_out_pud && item.date_miro == null) {

                if (hariKerja < 6) {
                    blinkClass = 'green-blink';
                } else if (hariKerja === 6) {
                    blinkClass = 'yellow-blink';
                } else if (hariKerja > 6) {
                    blinkClass = 'red-blink';
                }
            }
            return blinkClass;
        }

        function renderInvoices(data) {
            data.sort(function(a, b) {
		var blinkA = determineBlinkClass(a);
                var blinkB = determineBlinkClass(b);

                var colorPriority = {
                    'red-blink': 1,
                    'yellow-blink': 2,
                    'green-blink': 3
                };

                if (colorPriority[blinkA] < colorPriority[blinkB]) return -1;
                if (colorPriority[blinkA] > colorPriority[blinkB]) return 1;

                var dateA = new Date(a.date_approve);
                var dateB = new Date(b.date_approve);

                if (dateA < dateB) return -1;
                if (dateA > dateB) return 1;

                return 0;
            });

            var tbody = $('#invoice-table-body');
            tbody.empty();

            var groupedData = {};

            data.forEach(function(item) {
                var key = item.dropbox_id + '-' + item.company_name;

                if (!groupedData[key]) {
                    groupedData[key] = [];
                }
                groupedData[key].push(item);
            });

            for (var key in groupedData) {
                var group = groupedData[key];
                var rowspan = group.length;

                group.forEach(function(value, index) {
                    var blinkClass = '';
		    var blinkCurrent = 'safe-blink';
		    var hariKerja = hitungHariKerja(value.date_approve, new Date());
                    if (value.date_approve && value.date_dok_ok == null) {

                        if (hariKerja < 2) {
                            blinkClass = 'green-blink';
                        } else if (hariKerja === 2) {
                            blinkClass = 'yellow-blink';
                        } else if (hariKerja > 2) {
                            blinkClass = 'red-blink';
                        }

                    }
                    if (value.date_dok_ok && value.date_out_pud == null) {

                        if (hariKerja < 4) {
                            blinkClass = 'green-blink';
                        } else if (hariKerja === 4) {
                            blinkClass = 'yellow-blink';
                        } else if (hariKerja > 4) {
                            blinkClass = 'red-blink';
                        }

                    }
                    if (value.date_out_pud && value.date_miro == null) {

                        if (hariKerja < 6) {
                            blinkClass = 'green-blink';
                        } else if (hariKerja === 6) {
                            blinkClass = 'yellow-blink';
                        } else if (hariKerja > 6) {
                            blinkClass = 'red-blink';
                        }
                    }
                    var row = `<tr>`;

                    if (index === 0) {
                        row += `<td rowspan="${rowspan}">${value.company_name !== null ? value.company_name : "-"}</td>`;
                    }
                    if (index === 0) {
                        row += `<td class="${blinkClass}" rowspan="${rowspan}">${value.dropbox_id !== null ? value.dropbox_id : "-"}</td>`;
                    }
                    row += `<td>${value.no_invoice !== null ? value.no_invoice : "-"}</td>
                            <td class="${!value.date_archieve_pud && value.date_approve ? blinkCurrent : ''}">
                                ${value.date_approve !== null ? formatDate(value.date_approve) : "-"}
                            </td>
                            <td class="${value.date_archieve_pud && !value.date_dok_ok ? blinkCurrent : ''}">
                                ${value.date_dok_ok !== null ? formatDate(value.date_dok_ok) : "-"}
                            </td>
                            <td class="${value.date_dok_ok && !value.date_in_pud ? blinkCurrent : ''}">
                                ${value.date_in_pud !== null ? formatDate(value.date_in_pud) : "-"}
                            </td>
                            <td class="${value.date_in_pud && !value.date_out_pud || value.date_in_pud && value.date_out_pud && !value.date_archieve_finance ? blinkCurrent : ''}">
                                ${value.date_out_pud !== null ? formatDate(value.date_out_pud) : "-"}
                            </td>
                            <td class="${value.date_out_pud && !value.date_miro && value.date_archieve_finance ? blinkCurrent : ''}">
                                ${value.date_miro !== null ? formatDate(value.date_miro) : "-"}
                            </td>
                            <td>${hariKerja}</td>
                        </tr>`;
                    tbody.append(row);
                });
            }
        }


        function performSearch() {
            var searchDate = $("#date_filter").val()

            if (!searchDate) {
                renderInvoices(allData)
                return;
            }

            var filteredData = allData.filter(function(item) {
                var itemDate = new Date(item.date_approve);
                return itemDate.toISOString().split('T')[0] === searchDate;
            });
	    if (filteredData == [] || filteredData.length == 0) {
                var tbody = $('#invoice-table-body');
                tbody.empty();
                var noDataRow = `<tr><td colspan="10" style="text-align: center; font-weight: bold;">No data available</td></tr>`;
                tbody.append(noDataRow);
                return;
            }
            renderInvoices(filteredData);
        }

        function pageScroll() {
            var scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            var windowHeight = window.innerHeight;
            var documentHeight = document.documentElement.scrollHeight;

            if (!$('#date_filter').is(':focus')) {
                if (scrollTop + windowHeight >= documentHeight) {
                    window.scrollTo(0, 0);
                } else {
                    window.scrollBy(0, 1);
                }
            }

            scrolldelay = setTimeout(pageScroll, 60);
        }
        pageScroll()
        fetchAndRenderInvoices()
        setInterval(fetchAndRenderInvoices, 60000);

    });
</script>

</html>