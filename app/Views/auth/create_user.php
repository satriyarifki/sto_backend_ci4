<?= $this->extend('template/default') ?>

<?= $this->section('content') ?>
<section class="content">
      <div class="container-fluid">
            <div class="card">
                  <div class="card-header">
                        <h1><?php echo lang('Auth.create_user_heading');?></h1>
                        <p><?php echo lang('Auth.create_user_subheading');?></p>
                  </div>
                  <div class="card-body">
                        <!-- <div id="infoMessage" class="alert alert-warning"></div> -->
                        <?php echo form_open('auth/create_user', ['class' => 'form-horizontal']);?>

                        <div class="row mt-3">
                              <div class="col-md-4">
                                    <div class="form-group">
                                          <?php echo form_label('Masukan nama pengguna', 'cp_name', ['class' => 'form-label']);?> 
                                          <?php echo form_input($cp_name, '', ['class' => 'form-control']);?>
                                    </div>
                              </div>
                              <div class="col-md-4">
                                    <div class="form-group">
                                          <?php echo form_label('Masukan nomor telepon pengguna', 'cp_number', ['class' => 'form-label']);?> 
                                          <?php echo form_input($cp_number, '', ['class' => 'form-control']);?>
                                    </div>
                              </div>
                              <div class="col-md-4">
                                    <div class="form-group">
                                          <?php echo form_label('Masukan nama perusahaan', 'company_name', ['class' => 'form-label']);?> 
                                          <?php echo form_input($company_name, '', ['class' => 'form-control']);?>
                                    </div>
                              </div>
                        </div>
                  
                        <?php if ($identity_column !== 'email'): ?>
                              <div class="form-group">
                              <?php echo form_label(lang('Auth.create_user_identity_label'), 'identity', ['class' => 'form-label']);?>
                              <?php echo \Config\Services::validation()->getError('identity'); ?>
                              <?php echo form_input($identity, '', ['class' => 'form-control']);?>
                              </div>
                        <?php endif; ?>
                        
                        <div class="row mt-3">
                              <div class="col-md-4">
                                    <div class="form-group">
                                          <?php echo form_label(lang('Auth.create_user_email_label'), 'email', ['class' => 'form-label']);?> 
                                          <?php echo form_input($email, '', ['class' => 'form-control']);?>
                                    </div>
                              </div>
                              <div class="col-md-4">
                                    <div class="form-group">
                                          <?php echo form_label(lang('Auth.create_user_password_label'), 'password', ['class' => 'form-label']);?> 
                                          <?php echo form_input($password, '', ['class' => 'form-control']);?>
                                    </div>
                              </div>
                              <div class="col-md-4">
                                    <div class="form-group">
                                          <?php echo form_label(lang('Auth.create_user_password_confirm_label'), 'password_confirm', ['class' => 'form-label']);?> 
                                          <?php echo form_input($password_confirm, '', ['class' => 'form-control']);?>
                                    </div>
                              </div>
                        </div>

                        <div class="form-group">
                              <?php echo form_label(lang('Auth.create_user_phone_label'), 'phone', ['class' => 'form-label']);?> 
                              <?php echo form_input($phone, '', ['class' => 'form-control']);?>
                        </div>

                        <div class="form-group">
                              <?php echo form_submit('submit', lang('Auth.create_user_submit_btn'), ['class' => 'btn btn-primary']);?>
                        </div>

                        <?php echo form_close();?>
                  </div>
            </div>
      </div>
</section>

<style>

</style>

<?= $this->endSection() ?>
