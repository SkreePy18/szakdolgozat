<?php if (isset($_SESSION['success_msg'])): ?>
  <div class="alert alert-success alert-dismissible" role="alert">
    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <?php
      xecho($_SESSION['success_msg']);
      unset($_SESSION['success_msg']);
    ?>
  </div>
<?php endif; ?>

<?php if (isset($_SESSION['error_msg'])): ?>
  <div class="alert alert-danger alert-dismissible" role="alert">
    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <?php
      // We are able to print string or any other data structure
      if(is_string($_SESSION['error_msg'])) {
        xecho($_SESSION['error_msg']);
      } else {
        xecho(print_r($_SESSION['error_msg'], true));
      }
      unset($_SESSION['error_msg']);
    ?>
  </div>
<?php endif; ?>

<?php if (isset($_SESSION['warning_msg'])): ?>
  <div class="alert alert-warning alert-dismissible" role="alert">
    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <?php
      if(is_string($_SESSION['warning_msg'])) {
        xecho($_SESSION['warning_msg']);
      } else {
        xecho(print_r($_SESSION['warning_msg'], true));
      }
      unset($_SESSION['warning_msg']);
    ?>
  </div>
<?php endif; ?>
