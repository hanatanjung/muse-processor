<?= $this->extend('default') ?>

<?= $this->section('content') ?>

    <section>
        <p>Drive API Quickstart</p>

        <!--Add buttons to initiate auth sequence and sign out-->
        <button id="authorize_button" onclick="handleAuthClick()">Authorize</button>
        <button id="signout_button" onclick="handleSignoutClick()">Sign Out</button>

        <pre id="content" style="white-space: pre-wrap;"></pre>
    </section>

    <!-- CONTENT -->
    <section class="section">

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
            <div id="gdrive-list" class="box">
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

    <script src="https://cdn.jsdelivr.net/gh/gitbrent/pptxgenjs@3.4.0/dist/pptxgen.bundle.js"></script>
<?php $assets = json_decode(file_get_contents(__DIR__ . '/../../public/manifest.json')) ?>
    <script src="<?= base_url($assets->gdrive->js) ?>"></script>
    <script async defer src="https://apis.google.com/js/api.js"></script>
    <script async defer src="https://accounts.google.com/gsi/client"></script>

<?= $this->endSection() ?>