<?= $this->extend('template/default') ?>

<?= $this->section('content') ?>

<section class="content">
    <div class="container-fluid">
        <div class="card card-primary">
            <div class="card-header">
            <h3 class="card-title">Quick Example</h3>
            </div>
            <!-- /.card-header -->
            <!-- form start -->
            <form action="<?= base_url('invoicing/approve/') . $gr_data['id'] ?>" method="POST">
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-6">
                    <div class="form-group">
                        <label for="exampleInputEmail1">ID</label>
                        <input type="email" class="form-control" id="id" name="name" value="<?= $gr_data['id']?>" readOnly>
                    </div>
                    </div>
                    <div class="col-sm-6">
                    <div class="form-group">
                        <label for="exampleInputEmail1">GR Number</label>
                        <input type="email" class="form-control" id="gr_number" name="gr_number" value="<?= $gr_data['gr_number']?>" readOnly>
                    </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6">
                    <div class="form-group">
                        <label for="exampleInputEmail1">Delivery Date Note</label>
                        <input type="email" class="form-control" id="date" name="date" value="<?= $gr_data['del_note_date']?>" readOnly>
                    </div>
                    </div>
                    <div class="col-sm-6">
                    <div class="form-group">
                        <label for="exampleInputEmail1">Delivery Number</label>
                        <input type="email" class="form-control" id="delivery_number" name="delivery_number" value="<?= $gr_data['delivery_number']?>" readOnly>
                    </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6">
                    <div class="form-group">
                        <label for="exampleInputEmail1">PO Number</label>
                        <input type="email" class="form-control" id="po_number" name="po_number" value="<?= $gr_data['po_number']?>" readOnly>
                    </div>
                    </div>
                    <div class="col-sm-6">
                    <div class="form-group">
                        <label for="exampleInputEmail1">Currency</label>
                        <input type="email" class="form-control" id="currency" name="currency" value="<?= $gr_data['currency']?>" readOnly>
                    </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6">
                    <div class="form-group">
                        <label for="exampleInputEmail1">Vendor Names</label>
                        <input type="email" class="form-control" id="vendor_name" name="vendor_name" value="<?= $gr_data['vendor_names']?>" readOnly>
                    </div>
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-between">
                <a href="<?= base_url()?>invoicing/gr" class="btn btn-secondary" role="button" aria-pressed="true">Back</a>
                <div class="ml-auto">
                    <button type="submit" name="action" value="unverified" class="btn btn-danger">Unverified</button>
                    <button type="submit" name="action" value="verified"  class="btn btn-success ml-2">Verified</button>
                </div>
            </div>
            </form>
        </div>
    </div>
</section>


<?= $this->endSection() ?>