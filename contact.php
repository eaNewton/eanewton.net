<?php
    require_once($_SERVER["DOCUMENT_ROOT"] . "/resources/config.php");

    require_once($_SERVER["DOCUMENT_ROOT"] . "/resources/templates/header.php");
?>
<div class="container">
    <div class="content">
      <?php
      ini_set('display_errors', 1);
      error_reporting(E_ALL|E_STRICT);

      include($_SERVER["DOCUMENT_ROOT"] . "/resources/library/contact-form.php");
      ?>
    </div>
</div>
<?php
    require_once($_SERVER["DOCUMENT_ROOT"] . "/resources/templates/footer.php");
?>
