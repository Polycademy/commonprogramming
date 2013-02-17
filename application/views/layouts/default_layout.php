<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<? Template::partial('header', $header) ?>
<?= $yield ?>
<? Template::partial('footer', $footer) ?>