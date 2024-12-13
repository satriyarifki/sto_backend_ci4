<?= $this->extend('template/default') ?>

<?= $this->section('content') ?>
<section>
    <div class="row">
        <div class="col-md-6 offset-md-3">
            <div class="card">
                <div class="card-header">
                    <h4 class="text-left"><?php echo lang('Auth.change_password_heading'); ?></h4>
                </div>
                <div class="card-body">
                    <div id="infoMessage"><?php echo $message; ?></div>

                    <?php echo form_open('auth/change-password'); ?>
                    <div class="row mt-3">

                        <div class="col-md-12 mt-3">
                            <?php echo form_label(lang('Auth.change_password_old_password_label'), 'old_password', ['class' => 'labels']); ?>
                            <?php echo form_input($old_password, '', ['class' => 'form-control']); ?>
                            <span class="text-danger"><?= \Config\Services::validation()->getError('old_password') ?></span>
                        </div>

                        <div class="col-md-12 mt-3">
                            <?php echo form_label(sprintf(lang('Auth.change_password_new_password_label'), $minPasswordLength), 'new_password', ['class' => 'labels']); ?>
                            <?php echo form_input($new_password, '', ['class' => 'form-control']); ?>
                            <span class="text-danger"><?= \Config\Services::validation()->getError('new_password') ?></span>
                        </div>

                        <div class="col-md-12 mt-3">
                            <?php echo form_label(lang('Auth.change_password_new_password_confirm_label'), 'new_password_confirm', ['class' => 'labels']); ?>
                            <?php echo form_input($new_password_confirm, '', ['class' => 'form-control']); ?>
                            <span class="text-danger"><?= \Config\Services::validation()->getError('new_password_confirm') ?></span>
                        </div>

                        <?php echo form_input($user_id); ?>

                        <div class="col-md-12 mt-3 text-right">
                            <?php echo form_submit('submit', lang('Auth.change_password_submit_btn'), ['class' => 'btn btn-primary profile-button']); ?>
                        </div>
                    </div>
                    <?php echo form_close(); ?>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    .form-control:focus {
        box-shadow: none;
        border-color: #BA68C8;
    }

    .profile-button {
        background: rgb(99, 39, 120);
        box-shadow: none;
        border: none;
    }

    .profile-button:hover {
        background: #682773;
    }

    .profile-button:focus {
        background: #682773;
        box-shadow: none;
    }

    .profile-button:active {
        background: #682773;
        box-shadow: none;
    }

    .back:hover {
        color: #682773;
        cursor: pointer;
    }

    .labels {
        font-size: 15px;
    }

    .add-experience:hover {
        background: #BA68C8;
        color: #fff;
        cursor: pointer;
        border: solid 1px #BA68C8;
    }
</style>

<?= $this->endSection() ?>
