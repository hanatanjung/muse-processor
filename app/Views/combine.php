<?= $this->extend('default') ?>

<?= $this->section('content') ?>
    <!-- CONTENT -->
    <section class="section mx-6">
        <?= form_open_multipart('combine/upload') ?>

        <div class="field">
            <?= form_label('Musescore ID*', 'muse-id', ['class' => 'label']) ?>
            <div class="control">
                <?= form_upload([
                    'id' => 'muse-id',
                    'name' => 'muse[id]',
                    'required' => true,
                    'class' => 'input'
                ]) ?>
            </div>
        </div>

        <div class="field">
            <?= form_label('Musescore JP', 'muse-jp', ['class' => 'label']) ?>
            <div class="control">
                <?= form_upload([
                    'id' => 'muse-jp',
                    'name' => 'muse[jp]',
                    'class' => 'input'
                ]) ?>
            </div>
        </div>

        <div class="field">
            <?= form_label('Musescore EN', 'muse-en', ['class' => 'label']) ?>
            <div class="control">
                <?= form_upload([
                    'id' => 'muse-en',
                    'name' => 'muse[en]',
                    'class' => 'input'
                ]) ?>
            </div>
        </div>

        <div class="field">
            <?= form_label('New File Name', 'new-file-name', ['class' => 'label']) ?>
            <div class="control">
                <?= form_input([
                    'id' => 'new-file-name',
                    'name' => 'filename',
                    'placeholder' => 'e.g. 123',
                    'class' => 'input'
                ]) ?>
            </div>
        </div>

        <div class="field">
            <?= form_submit([
                'id' => 'combine',
                'name' => 'combine',
                'class' => 'button is-danger'
            ], 'Combine') ?>
        </div>

        <?= form_close() ?>

        <?php if (isset($success)) : ?>
            <div class="notification is-success">
                <?= $success ?>
            </div>
        <?php endif; ?>

    </section>

<?= $this->endSection() ?>