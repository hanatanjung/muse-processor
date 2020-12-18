<?= $this->renderSection('header') ?>

<!-- CONTENT -->
<section>
    <?php
    echo form_open_multipart('combine/upload');

    echo form_label('Musescore ID', 'muse-id');
    echo form_upload(['id' => 'muse-id', 'name' => 'muse[id]']);

    echo form_label('Musescore JP', 'muse-jp');
    echo form_upload(['id' => 'muse-jp', 'name' => 'muse[jp]']);

    echo form_label('Musescore EN', 'muse-en');
    echo form_upload(['id' => 'muse-en', 'name' => 'muse[en]']);

    echo form_label('New File Name', 'new-file-name');
    echo form_input(['id' => 'new-file-name', 'name' => 'filename']);

    echo form_submit('combine', 'Combine');

    echo form_close();
    ?>
</section>

<?= $this->renderSection('footer') ?>