<?= $this->extend('template/default') ?>

<?= $this->section('content') ?>
<section>
    <div class="card">
        <div class="row">
            <div class="col-md-5">
                <div class="p-3 py-5">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="text-right">Basic Information</h4>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <label class="labels">Company Name</label>
                            <input type="text" class="form-control" placeholder="Company Name" value="<?= $current_user->company_name?>" readonly>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label class="labels">Company Title</label>
                            <input type="text" class="form-control" placeholder="Company Title" value="<?= $current_user->company_title?>" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="labels">NPWP Number</label>
                            <input type="text" class="form-control" value="<?= $current_user->npwp_number?>" placeholder="NPWP Number" readonly>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <label class="labels">Email Company</label>
                            <input type="text" class="form-control" placeholder="Vendor Code" value="<?= $current_user->email?>" readonly>
                        </div>
                    </div>
    
                    <div class="d-flex justify-content-between align-items-center row mt-5">
                        <h4 class="text-right">Contact Person</h4>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <label class="labels">Contact Person Name</label>
                            <input type="text" class="form-control" placeholder="Enter your username" value="<?= $current_user->cp_name?>" readonly>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label class="labels">Contact Number</label>
                            <input type="text" class="form-control" placeholder="Enter contact number" value="<?= $current_user->cp_number?>" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="labels">Contact Person Title</label>
                            <input type="text" class="form-control" placeholder="Enter contact person's title" value="<?= $current_user->cp_title?>" readonly>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <label class="labels">Contact Person Email</label>
                            <input type="text" class="form-control" placeholder="Enter contact person's email" value="<?= $current_user->cp_email1?>" readonly>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-5">
                <div class="p-3 py-5">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="text-right">Official Address</h4>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label class="labels">Country</label>
                            <input type="text" class="form-control" placeholder="Enter country" value="<?= $current_user->country?>" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="labels">Province</label>
                            <input type="text" class="form-control" placeholder="Enter province" value="<?= $current_user->provience?>" readonly>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <label class="labels">Address</label>
                            <input type="text" class="form-control" placeholder="Enter company address" value="<?= $current_user->address?>" readonly>
                        </div>
                    </div>
                </div>  
            </div>
        </div>
    </div>
</section>

<style>
    .form-control:focus {
        box-shadow: none;
        border-color: #BA68C8
    }

    .profile-button {
        background: rgb(99, 39, 120);
        box-shadow: none;
        border: none
    }

    .profile-button:hover {
        background: #682773
    }

    .profile-button:focus {
        background: #682773;
        box-shadow: none
    }

    .profile-button:active {
        background: #682773;
        box-shadow: none
    }

    .back:hover {
        color: #682773;
        cursor: pointer
    }

    .labels {
        font-size: 11px
    }

    .add-experience:hover {
        background: #BA68C8;
        color: #fff;
        cursor: pointer;
        border: solid 1px #BA68C8
    }
</style>

<?= $this->endSection() ?>
