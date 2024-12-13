<?php if (isset($validation)): ?>
    <div class="alert alert-danger">
        <?= \Config\Services::validation()->listErrors(); ?>
    </div>
<?php endif; ?>