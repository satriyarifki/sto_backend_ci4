<?= $this->extend('template/default') ?>

<?= $this->section('content') ?>

<section>
    <form action="<?= base_url('users/stored/' . $current_user->id); ?>" method="post" enctype="multipart/form-data">
        <div class="row">
            <div class="col-md-5">
                <div class="p-3 py-5">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="text-right">Basic Information</h4>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <label class="labels">Company Name</label>
                            <input type="text" class="form-control <?= ($validation->hasError('company_name')) ? 'is-invalid' : '';?>" name="company_name" placeholder="Company Name" value="<?= $current_user->company_name?>">
                            <div class="invalid-feedback">
                                <?= $validation->getError('company_name'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label class="labels">Company Title</label>
                            <input type="text" class="form-control <?= ($validation->hasError('company_title')) ? 'is-invalid' : '';?>" name="company_title" placeholder="Company Title" value="<?= $current_user->company_title?>">
                            <div class="invalid-feedback">
                                <?= $validation->getError('company_title'); ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="labels">NPWP Number</label>
                            <input type="text" class="form-control <?= ($validation->hasError('npwp_number')) ? 'is-invalid' : '';?>" name="npwp_number" placeholder="NPWP Number" value="<?= $current_user->npwp_number?>">
                            <div class="invalid-feedback">
                                <?= $validation->getError('npwp_number'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label class="labels">Abbrevieted Name</label>
                            <input type="text" class="form-control <?= ($validation->hasError('abbreviated_name')) ? 'is-invalid' : '';?>" name="abbreviated_name" placeholder="Abbrevieted Name" value="<?= $current_user->abbreviated_name?>">
                            <div class="invalid-feedback">
                                <?= $validation->getError('abbreviated_name'); ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="labels">Abbrevieted (Supplier)</label>
                            <input type="text" class="form-control <?= ($validation->hasError('supplier_abbreviated')) ? 'is-invalid' : '';?>" name="supplier_abbreviated" placeholder="Abbrevieted (Supplier)" value="<?= $current_user->abbreviated_supplier?>">
                            <div class="invalid-feedback">
                                <?= $validation->getError('supplier_abbreviated'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <label class="labels">Company Website</label>
                            <input type="text" class="form-control <?= ($validation->hasError('company_website')) ? 'is-invalid' : '';?>" name="company_website" placeholder="Company Website" value="<?= $current_user->company_website?>">
                            <div class="invalid-feedback">
                                <?= $validation->getError('company_website'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label class="labels">Established Date</label>
                            <input type="date" class="form-control <?= ($validation->hasError('established_date')) ? 'is-invalid' : '';?>" name="established_date" placeholder="dd-mm-yyyy" value="<?= $current_user->established_date?>" min="1800-01-01" max="2050-12-31"> 
                            <div class="invalid-feedback">
                                <?= $validation->getError('established_date'); ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="labels">Supplier Category</label>
                            <input type="text" class="form-control <?= ($validation->hasError('supplier_category')) ? 'is-invalid' : '';?>" name="supplier_category" placeholder="Supplier Category" value="<?= $current_user->supplier_category?>">
                            <div class="invalid-feedback">
                                <?= $validation->getError('supplier_category'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label class="labels">Vendor Code</label>
                            <input type="text" class="form-control <?= ($validation->hasError('vendor_code')) ? 'is-invalid' : '';?>" name="vendor_code" placeholder="Vendor Code" value="<?= $current_user->vendor_code?>">
                            <div class="invalid-feedback">
                                <?= $validation->getError('vendor_code'); ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="labels">Join Date with MAJ</label>
                            <input type="date" class="form-control <?= ($validation->hasError('join_date_maj')) ? 'is-invalid' : '';?>" name="join_date_maj" placeholder="dd-mm-yyyy" value="<?= $current_user->join_date?>" min="1800-01-01" max="2050-12-31"> 
                            <div class="invalid-feedback">
                                <?= $validation->getError('join_date_maj'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label class="labels">Official Letter Attachment</label>
                            <input type="text" class="form-control <?= ($validation->hasError('official_letter_attachment')) ? 'is-invalid' : '';?>" name="official_letter_attachment" placeholder="Official Letter Attachment" value="<?= $current_user->official_letter_attachment?>">
                            <div class="invalid-feedback">
                                <?= $validation->getError('official_letter_attachment'); ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="labels">Supplier Group</label>
                            <input type="text" class="form-control <?= ($validation->hasError('supplier_group')) ? 'is-invalid' : '';?>" name="supplier_group" placeholder="Supplier Group" value="<?= $current_user->supplier_group?>">
                            <div class="invalid-feedback">
                                <?= $validation->getError('supplier_group'); ?>
                            </div>
                        </div>
                    </div>


                    <div class="d-flex justify-content-between align-items-center row mt-5">
                        <div class="col-md-12">
                            <h4>Contact Person</h4>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label class="labels">Contact Person Name</label>
                            <input type="text" class="form-control <?= ($validation->hasError('contact_person_name')) ? 'is-invalid' : '';?>" name="contact_person_name" placeholder="Enter contact person's name" value="<?= $current_user->cp_name?>">
                            <div class="invalid-feedback">
                                <?= $validation->getError('contact_person_name'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label class="labels">Contact Number</label>
                            <input type="text" class="form-control <?= ($validation->hasError('contact_number')) ? 'is-invalid' : '';?>" name="contact_number" placeholder="Enter contact number" value="<?= $current_user->cp_number?>">
                            <div class="invalid-feedback">
                                <?= $validation->getError('contact_number'); ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="labels">Contact Person Title</label>
                            <input type="text" class="form-control <?= ($validation->hasError('contact_person_title')) ? 'is-invalid' : '';?>" name="contact_person_title" placeholder="Enter contact person's title" value="<?= $current_user->cp_title?>">
                            <div class="invalid-feedback">
                                <?= $validation->getError('contact_person_title'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label class="labels">Contact Person Email</label>
                            <input type="text" class="form-control <?= ($validation->hasError('contact_person_email_1')) ? 'is-invalid' : '';?>" name="contact_person_email_1" placeholder="Enter contact person's email 1" value="<?= $current_user->cp_email1?>">
                            <div class="invalid-feedback">
                                <?= $validation->getError('contact_person_email_1'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <label class="labels">Address</label>
                            <input type="text" class="form-control <?= ($validation->hasError('address')) ? 'is-invalid' : '';?>" name="address" placeholder="Enter your address" value="<?= $current_user->address?>">
                            <div class="invalid-feedback">
                                <?= $validation->getError('address'); ?>
                            </div>
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
                            <input type="text" class="form-control <?= ($validation->hasError('country')) ? 'is-invalid' : '';?>" name="country" placeholder="Enter country" value="<?= $current_user->country?>">
                            <div class="invalid-feedback">
                                <?= $validation->getError('country'); ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="labels">Province</label>
                            <input type="text" class="form-control <?= ($validation->hasError('province')) ? 'is-invalid' : '';?>" name="province" placeholder="Enter province" value="<?= $current_user->provience?>">
                            <div class="invalid-feedback">
                                <?= $validation->getError('province'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label class="labels">City</label>
                            <input type="text" class="form-control <?= ($validation->hasError('city')) ? 'is-invalid' : '';?>" name="city" placeholder="Enter city" value="<?= $current_user->city?>">
                            <div class="invalid-feedback">
                                <?= $validation->getError('city'); ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="labels">Zip Code</label>
                            <input type="text" class="form-control <?= ($validation->hasError('zip_code')) ? 'is-invalid' : '';?>" name="zip_code" placeholder="Enter zip code" value="<?= $current_user->zip_code?>">
                            <div class="invalid-feedback">
                                <?= $validation->getError('zip_code'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label class="labels">Company Phone Number</label>
                            <input type="text" class="form-control <?= ($validation->hasError('company_phone_number')) ? 'is-invalid' : '';?>" name="company_phone_number" placeholder="Enter company phone number" value="<?= $current_user->company_phone_number?>">
                            <div class="invalid-feedback">
                                <?= $validation->getError('company_phone_number'); ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="labels">Company Fax Number</label>
                            <input type="text" class="form-control <?= ($validation->hasError('company_fax_number')) ? 'is-invalid' : '';?>" name="company_fax_number" placeholder="Enter company fax number" value="<?= $current_user->company_fax_number?>">
                            <div class="invalid-feedback">
                                <?= $validation->getError('company_fax_number'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center row mt-5">
                        <div class="col-md-12">
                            <h4>Official Information</h4>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <label class="labels">Logo Attachment</label>
                            <input class="form-control-sm <?= ($validation->hasError('logo_attachment')) ? 'is-invalid' : '';?>" id="formFileLg" type="file" name="logo_attachment">
                            <div class="invalid-feedback">
                                <?= $validation->getError('logo_attachment'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <label class="labels">Company Classification</label>
                            <input type="text" class="form-control <?= ($validation->hasError('company_clasification')) ? 'is-invalid' : '';?>" name="company_clasification" placeholder="Enter company classification" value="<?= $current_user->company_clasification?>">
                            <div class="invalid-feedback">
                                <?= $validation->getError('company_clasification'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label class="labels">Capital</label>
                            <input type="text" class="form-control <?= ($validation->hasError('capital')) ? 'is-invalid' : '';?>" name="capital" placeholder="Enter capital" value="<?= $current_user->capital?>">
                            <div class="invalid-feedback">
                                <?= $validation->getError('capital'); ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="labels">Asset Value</label>
                            <input type="text" class="form-control <?= ($validation->hasError('asset_value')) ? 'is-invalid' : '';?>" name="asset_value" placeholder="Enter asset value" value="<?= $current_user->asset_value?>">
                            <div class="invalid-feedback">
                                <?= $validation->getError('asset_value'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label class="labels">Supplier Affiliation</label>
                            <input type="text" class="form-control <?= ($validation->hasError('supplier_affiliation')) ? 'is-invalid' : '';?>" name="supplier_affiliation" placeholder="Enter supplier affiliation" value="<?= $current_user->supplier_affiliation?>">
                            <div class="invalid-feedback">
                                <?= $validation->getError('supplier_affiliation'); ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="labels">Technical Assistant</label>
                            <input type="text" class="form-control <?= ($validation->hasError('technical_assistant')) ? 'is-invalid' : '';?>" name="technical_assistant" placeholder="Enter technical assistant" value="<?= $current_user->technical_assistant?>">
                            <div class="invalid-feedback">
                                <?= $validation->getError('technical_assistant'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label class="labels">Start Operation Date</label>
                            <input type="date" class="form-control <?= ($validation->hasError('start_operation_date')) ? 'is-invalid' : '';?>" name="start_operation_date" placeholder="dd-mm-yyyy" value="<?= $current_user->start_operation_date?>" min="1800-01-01" max="2050-12-31"> 
                            <div class="invalid-feedback">
                                <?= $validation->getError('start_operation_date'); ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="labels">Currency</label>
                            <input type="text" class="form-control <?= ($validation->hasError('currency')) ? 'is-invalid' : '';?>" name="currency" placeholder="Enter currency" value="<?= $current_user->currency?>">
                            <div class="invalid-feedback">
                                <?= $validation->getError('currency'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="row text-right mt-5">
                        <div class="col-md-12">
                            <button class="btn btn-primary profile-button" type="submit">Save Profile</button>
                            <a href="<?= base_url('users') ?>" class="btn btn-secondary" role="button" aria-pressed="true">Cancel</a>
                        </div>
                    </div>
                </div>  
            </div>
        </div>
    </form>
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
