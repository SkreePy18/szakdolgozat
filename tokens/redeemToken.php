<?php include_once('../config.php'); ?>
<?php include_once(ROOT_PATH . '/csrf.php') ?>
<?php include_once(ROOT_PATH . '/tokens/tokenLogic.php'); ?>
<?php
  $token = "";
  if(isset($_GET['token'])) {
    $token = $_GET['token'];
  }
?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title><?php xecho(APP_NAME); ?> - Redeem token</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css" />
    <!-- Custome styles -->
    <link rel="stylesheet" href="../assets/css/style.css">
  </head>
  <body>
    <?php include_once(INCLUDE_PATH . "/layouts/navbar.php") ?>

    <div class="container" style="margin-bottom: 50px;">
      <div class="row">
        <div class="col-md-8 col-md-offset-2">

          <?php if (hasPermissionTo('view-opportunity-list')): ?>
            <a href="../opportunities/opportunityFilter.php?filter_opportunity=all" class="btn btn-primary" style="margin-bottom: 5px;">
              <span class="glyphicon glyphicon-chevron-left"></span>
              Opportunities
            </a>
            <hr>
          <?php endif; ?>

          <?php if (hasPermissionTo('view-opportunity-list')): ?>
            <h2 class="text-center">Redeem token</h2>

            <form class="form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post" enctype="multipart/form-data">
            <input type="hidden" id="user_id" name="user_id" value="<?php xecho($_SESSION['user']['id']); ?>">
              <div class="form-group <?php xecho(isset($errors['token']) ? 'has-error' : '') ?>">
                <label class="control-label">Token</label>
                <input type="text" name="token" value="<?php xecho($token); ?>" class="form-control">
                <?php if (isset($errors['token'])): ?>
                  <span class="help-block"><?php xecho($errors['token']); ?></span>
                <?php endif; ?>
              </div>
             
              <div class="form-group">
                <?php echo(getCSRFTokenField() . "\n") ?>
                  <button type="submit" name="redeem" class="btn btn-success btn-block btn-lg">Redeem</button>
              </div>
            </form>
          <?php else: ?>
            <h2 class="text-center">No permissions to redeem a token</h2>
          <?php endif ?>
        </div>
      </div>
    </div>
  <?php include_once(INCLUDE_PATH . "/layouts/footer.php") ?>



