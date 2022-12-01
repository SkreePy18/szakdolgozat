<head>
	<link rel="stylesheet" href="../assets/css/style.css">
<head>

<?php if (isset($_SESSION['success_msg'])): ?>
  <div class="alert alert-success alert-dismissible" role="alert">
    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <span class="alert-glyphicon glyphicon glyphicon-ok-sign"></span>
	<span class="alert-message">
		<?php
			xecho($_SESSION['success_msg']);
			unset($_SESSION['success_msg']);
		?>
	</span>
  </div>
<?php endif; ?>

<?php if (isset($_SESSION['error_msg'])): ?>
  <div class="alert alert-danger alert-dismissible" role="alert">
    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	 <span class="alert-glyphicon glyphicon glyphicon-remove-sign"></span>
	 <span class="alert-message">
    <?php
      // We are able to print string or any other data structure
      if(is_string($_SESSION['error_msg'])) {
        xecho($_SESSION['error_msg']);
      } else {
        xecho(print_r($_SESSION['error_msg'], true));
      }
      unset($_SESSION['error_msg']);
    ?>
	</span>
  </div>
<?php endif; ?>


<?php if (isset($_SESSION['warning_msg'])): ?>
  <div class="alert alert-warning alert-dismissible" role="alert">
    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <span class="alert-glyphicon glyphicon glyphicon-exclamation-sign"></span>
    <span class="alert-message">
    <?php
      if(is_string($_SESSION['warning_msg'])) {
        xecho($_SESSION['warning_msg']);
      } else {
        xecho(print_r($_SESSION['warning_msg'], true));
      }
      unset($_SESSION['warning_msg']);
    ?>
    </span>
  </div>
<?php endif; ?>

<style>
.alert-message {
	display: inline-block !important;
	vertical-align: middle;
	margin-top: 2px;
}

.alert-glyphicon {
	vertical-align: middle !important;
	border-right: 1px solid #aaa;
	padding-right: 10px;
	margin-right: 10px;
	font-size: 25px;
}


</style>