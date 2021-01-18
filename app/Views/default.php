<!DOCTYPE html><html lang="en"><head>    <meta charset="UTF-8">    <title>Muse Processor by IREC Tokyo</title>    <meta name="description" content="Combine multilanguage lyrics into single score and convert score into PPT">    <meta name="viewport" content="width=device-width, initial-scale=1">    <link rel="shortcut icon" type="image/png" href="https://irec.tokyo/app/uploads/2020/01/cropped-grii-logo-icon-copy-32x32.png"/>    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.1/css/bulma.min.css" />    <link rel="stylesheet" href="<?= base_url('dist/ppt.css') ?>" />    <script defer src="https://use.fontawesome.com/releases/v5.14.0/js/all.js"></script></head><body><!-- NAVBAR --><nav class="navbar is-danger" role="navigation" aria-label="main navigation" style="height: 120px;">    <div class="navbar-brand">        <a class="navbar-item" href="<?= base_url() ?>">            <img src="https://irec.tokyo/app/uploads/2019/12/logo_pri-e1576422876724.png" width="58" height="80" style="max-height: none;">        </a>        <a role="button" class="navbar-burger" aria-label="menu" aria-expanded="false" data-target="main-nav">            <span aria-hidden="true"></span>            <span aria-hidden="true"></span>            <span aria-hidden="true"></span>        </a>    </div>    <div id="main-nav" class="navbar-menu">        <div class="navbar-start">            <a class="navbar-item" href="<?= base_url('combine') ?>">Combine Muse-Scores</a>            <a class="navbar-item" href="<?= base_url('ppt') ?>">Create PPT</a>        </div>    </div></nav><div class="container">    <?= $this->renderSection('content') ?></div><footer></footer></body></html>