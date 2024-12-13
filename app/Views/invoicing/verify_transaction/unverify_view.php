<?= $this->extend('template/default') ?>

<?= $this->section('content') ?>
<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="p-3 py-5">  
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <label class="labels">Amount</label>
                                    <input type="text" class="form-control" value="" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="labels">PPn</label>
                                    <input type="text" class="form-control" value="" readonly>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <label class="labels">Total</label>
                                    <input type="text" class="form-control" value="" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 py-5">
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <label class="labels">DDP PPh23</label>
                                    <input type="text" class="form-control" value="" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="labels">PPh23</label>
                                    <input type="text" class="form-control" value="" readonly>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <label class="labels">Transaction</label>
                                    <input type="text" class="form-control" value="" readonly>
                                </div>
                            </div>
                        </div>  
                    </div>
                </div>
            </div>
            <div class="card-body">
            <table border="0" cellspacing="5" cellpadding="5">
            <tbody>
            <a href="#" class="btn btn-info" role="button" aria-pressed="true">
                <i class="fa fa-barcode" aria-hidden="true"></i>
                <span class="ml-2">Scan</span>
            </a>
            </tbody>
            </table>
            <table id="example" class="display nowrap" style="width:100%">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Status</th>
                        <th>GR Number</th>
                        <th>Del Note Date</th>
                        <th>Manifest No</th>
                        <th>Delivery Note</th>
                        <th>PO Number</th>
                        <th>PO Date</th>
                        <th>Currency</th>
                        <th>Tax Code PO</th>
                        <th>PGr</th>
                        <th>Item Category</th>
                        <th>Amount PO</th>
                        <th>Invoice Number</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
                <tfoot>
                    <tr>
                        <th>No</th>
                        <th>Status</th>
                        <th>GR Number</th>
                        <th>Del Note Date</th>
                        <th>Manifest No</th>
                        <th>Delivery Note</th>
                        <th>PO Number</th>
                        <th>PO Date</th>
                        <th>Currency</th>
                        <th>Tax Code PO</th>
                        <th>PGr</th>
                        <th>Item Category</th>
                        <th>Amount PO</th>
                        <th>Invoice Number</th>                      
                    </tr>
                </tfoot>
            </table>
            </div>
        </div>
    </div>
</section>


<script>
$(function () {
    let minDate, maxDate;
    
    // Custom filtering function which will search data in column four between two values
    DataTable.ext.search.push(function (settings, data, dataIndex) {
        let min = minDate.val();
        let max = maxDate.val();
        let date = new Date(data[4]);
    
        if (
            (min === null && max === null) ||
            (min === null && date <= max) ||
            (min <= date && max === null) ||
            (min <= date && date <= max)
        ) {
            return true;
        }
        return false;
    });
    
    // Create date inputs
    minDate = new DateTime('#min', {
        format: 'DD MMMM YYYY'
    });
    maxDate = new DateTime('#max', {
        format: 'DD MMMM YYYY'
    });
    
    // DataTables initialisation
    let table = new DataTable('#example');
    
    // Refilter the table
    document.querySelectorAll('#min, #max').forEach((el) => {
        el.addEventListener('change', () => table.draw());
    });
    
    // $('input[name="start_date"]').daterangepicker({
    //     singleDatePicker: true,
    //     showDropdowns: true,
    //     minYear: 1901,
    //     maxYear: parseInt(moment().format('YYYY'), 10),
    //     startDate: moment().startOf('month')
    // });

    // $('input[name="end_date"]').daterangepicker({
    //     singleDatePicker: true,
    //     showDropdowns: true,
    //     minYear: 1901,
    //     maxYear: parseInt(moment().format('YYYY'), 10),
    //     endDate: moment().endOf('month')
    // });

    // var today = new Date(new Date().getFullYear(), new Date().getMonth(), new Date().getDate());
    //     $('#startDate').datepicker({
    //         uiLibrary: 'bootstrap4',
    //         iconsLibrary: 'fontawesome',
    //         minDate: today,
    //         maxDate: function () {
    //             return $('#endDate').val();
    //         }
    //     });
    //     $('#endDate').datepicker({
    //         uiLibrary: 'bootstrap4',
    //         iconsLibrary: 'fontawesome',
    //         minDate: function () {
    //             return $('#startDate').val();
    //         }
    // });
    
    // Menangani perubahan pada input tanggal

});
</script>

<?= $this->endSection() ?>