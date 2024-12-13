<?= $this->extend('template/default') ?>

<?= $this->section('content') ?>
<section>
    <div class="row">
        <div class="col-md-5">
            <div class="p-3 py-5">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="text-right">Basic Information</h4>
                </div>
                <div class="row mt-3">
                    <div class="col-md-12 text-center">
                        <img src="<?= base_url('public/') ?>img/customer/<?= $current_user->logo_attachment ?>" alt="LOGO" style="width: 35%; height: auto;">
                    </div>
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
                    <div class="col-md-6">
                        <label class="labels">Abbrevieted Name</label>
                        <input type="text" class="form-control" placeholder="Abbrevieted Name" value="<?= $current_user->abbreviated_name?>" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="labels">Abbrevieted (Supplier)</label>
                        <input type="text" class="form-control" placeholder="Abbrevieted (Supplier)" value="<?= $current_user->abbreviated_supplier?>" readonly>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-12">
                        <label class="labels">Company Website</label>
                        <input type="text" class="form-control" placeholder="Company Website" value="<?= $current_user->company_website?>" readonly>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-6">
                        <label class="labels">Established Date</label>
                        <input type="text" class="form-control" placeholder="Established Date" value="<?= $current_user->established_date?>" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="labels">Supplier Category</label>
                        <input type="text" class="form-control" value="<?= $current_user->supplier_category?>" placeholder="Supplier Category" readonly>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-6">
                        <label class="labels">Vendor Code</label>
                        <input type="text" class="form-control" placeholder="Vendor Code" value="<?= $current_user->vendor_code?>" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="labels">Join Date with MAJ</label>
                        <input type="text" class="form-control" value="<?= $current_user->join_date?>" placeholder="Join Date with MAJ" readonly>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-6">
                        <label class="labels">Official Letter Attachment</label>
                        <input type="text" class="form-control" placeholder="Official Letter Attachment" value="<?= $current_user->official_letter_attachment?>" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="labels">Supplier Group</label>
                        <input type="text" class="form-control" placeholder="Supplier Group" value="<?= $current_user->supplier_group?>" readonly>
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
                        <input type="text" class="form-control" placeholder="Enter contact person's email 1" value="<?= $current_user->cp_email1?>" readonly>
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
                    <div class="col-md-6">
                        <label class="labels">City</label>
                        <input type="text" class="form-control" placeholder="Enter city" value="<?= $current_user->city?>" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="labels">Zip Code</label>
                        <input type="text" class="form-control" placeholder="Enter zip code" value="<?= $current_user->zip_code?>" readonly>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-6">
                        <label class="labels">Company Phone Number</label>
                        <input type="text" class="form-control" placeholder="Enter company phone number" value="<?= $current_user->phone?>" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="labels">Company Fax Number</label>
                        <input type="text" class="form-control" placeholder="Enter company fax number" value="<?= $current_user->company_fax_number?>" readonly>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center row mt-5">
                    <h4 class="text-right">Official Information</h4>
                </div>
                <div class="row mt-3">
                    <div class="col-md-12">
                        <label class="labels">Company Classification</label>
                        <input type="text" class="form-control" name="company_classification" placeholder="Enter company classification" value="<?= $current_user->company_clasification?>" readonly>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-6">
                        <label class="labels">Capital</label>
                        <input type="text" class="form-control" placeholder="Enter capital" value="<?= $current_user->capital?>" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="labels">Asset Value</label>
                        <input type="text" class="form-control" placeholder="Enter asset value" value="<?= $current_user->asset_value?>" readonly>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-6">
                        <label class="labels">Supplier Affiliation</label>
                        <input type="text" class="form-control" placeholder="Enter supplier affiliation" value="<?= $current_user->supplier_affiliation?>" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="labels">Technical Assistant</label>
                        <input type="text" class="form-control" placeholder="Enter technical assistant" value="<?= $current_user->technical_assistant?>" readonly>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-6">
                        <label class="labels">Start Operation Date</label>
                        <input type="text" class="form-control" placeholder="Enter start operation date" value="<?= $current_user->start_operation_date?>" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="labels">Currency</label>
                        <input type="text" class="form-control" placeholder="Enter currency" value="<?= $current_user->currency?>" readonly>
                    </div>
                </div>
                <div class="row text-right mt-5">
                    <div class="col-md-12">
                        <a href="<?= base_url('users/update') ?>" class="btn btn-secondary" role="button" aria-pressed="true">Edit</a>
                        <a onclick="history.back(-1)" role="button" aria-pressed="true" class="more-link btn btn-danger">Back</a>
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
