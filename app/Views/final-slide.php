<?= $this->extend('default') ?>

<?= $this->section('content') ?>

    <!-- CONTENT -->
    <section class="section mx-6">

        <?= form_open_multipart('final-slide/generate', ['id' => 'ppt-generator']) ?>

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
            <?= form_label('Slides', 'slides', ['class' => 'label']) ?>
            <div class="control">
            </div>
        </div>

        <div class="field">
            <?= form_submit([
                'id' => 'generate',
                'name' => 'generate',
                'class' => 'button is-danger'
            ], 'Generate') ?>
        </div>

        <?= form_close() ?>

        <?php if (isset($success)) : ?>
            <div class="notification is-success">
                <?= $success ?>
            </div>
        <?php endif; ?>

    </section>

    <section class="columns">
        <div id="scores-display" class="column is-flex is-align-items-center is-flex-direction-row is-flex-wrap-wrap is-three-quarter">

        </div>
        <div class="column is-one-quarter">
            <button id="create-ppt" class="button is-info mb-2" disabled>Create PPT</button>
            <div class="notification is-warning is-light">
                <pre id="crop-values-reader" class="is-size-7">{}</pre>
            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/gh/gitbrent/pptxgenjs@3.4.0/dist/pptxgen.bundle.js"></script>
<?php $assets = json_decode(file_get_contents(__DIR__ . '/../../public/manifest.json')) ?>
    <script src="<?= base_url($assets->ppt->js) ?>"></script>

<?= $this->endSection() ?>