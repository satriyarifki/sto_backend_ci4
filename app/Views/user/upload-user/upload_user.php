<?= $this->extend('template/default') ?>

<?= $this->section('content') ?>

<section class="content">
    <div class="container-fluid">
        <button class="btn btn-info" type="button" data-toggle="collapse" data-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample">
            Upload
        </button>
        <div class="card-body">
            <div class="row table-responsive">
            <div class="collapse" id="collapseExample">
            <div class="card card-info">
                <div class="card-header">
                    <h4 class="card-title">Data Upload</h4>
                </div>
                <div class="card-body">
                <a href="<?= base_url()?>users/template" class="btn btn-secondary" role="button" aria-pressed="true" style="margin: 10px 0px;">Download Template Upload</a>
                <form action="<?= base_url('users/stored-user') ?>" enctype="multipart/form-data" method="POST" role="form">
                    <div class="row">
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                        <div class="form-group">
                        <label for="fileUpload">File input</label>
                        <div class="input-group">
                        <div class="custom-file">
                            <input type="file" class="form-control-file" id="fileUpload" name="fileUpload">
                        </div>
                        </div>
                    </div>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                        <div class="col-sm-12">
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </div>
                    </div>
                </form>
                </div>
            </div>
        </div>
            </div>
        </div>
    </div>
</section>

<style>
    .form-group {
        border: 1px solid #ccc;
        border-radius: 5px;
        padding: 10px;
        margin-bottom: 15px;
    }
</style>

<?= $this->endSection() ?>