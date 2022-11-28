<?php include_once('../../config.php'); ?>
<?php include_once(ROOT_PATH . '/csrf.php') ?>
<?php include_once(ROOT_PATH . '/admin/points/pointsLogic.php'); ?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title><?php xecho(APP_NAME); ?> - category</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css" />
    <!-- Custom styles -->
    <link rel="stylesheet" href="../../assets/css/style.css">
  </head>
  <body>
    <?php include_once(INCLUDE_PATH . "/layouts/navbar.php") ?>
    <div class="container" style="margin-bottom: 50px;">
      <div class="row">
        <div class="col-md-6 col-md-offset-3">

          <?php if (hasPermissionTo('view-point-types')): ?>
            <a href="pointsList.php" class="btn btn-primary" style="margin-bottom: 5px;">
              <span class="glyphicon glyphicon-chevron-left"></span>
              Type of points
            </a>
            <hr>
          <?php endif; ?>

          <?php if (canUpdateObjectByID('point-type', $type_id) || hasPermissionTo('create-category') ): ?>

            <?php if ($isEditing === true ): ?>
              <h2 class="text-center">Update points type</h2>
            <?php else: ?>
              <h2 class="text-center">Create points type</h2>
            <?php endif; ?>

            <form class="form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post" enctype="multipart/form-data">
              <!-- if editting category, we need that category's id -->
              <?php if ($isEditing === true): ?>
                <input type="hidden" name="type_id" value="<?php xecho($type_id); ?>">
              <?php endif; ?>
              
              <div class="form-group <?php xecho(isset($errors['type']) ? 'has-error' : ''); ?>">
                <label class="control-label">Type of points</label>
                <?php if ($isEditing === true): ?>
                  <input type="text" name="type" value="<?php xecho($type); ?>" class="form-control">
                <?php else: ?>
                  <input type="text" name="type" value="<?php xecho($type); ?>" class="form-control">
                <?php endif;?>
                <?php if (isset($errors['type'])): ?>
                  <span class="help-block"><?php xecho($errors['type']); ?></span>
                <?php endif; ?>
              </div>

              <div class="form-group">
                <?php echo(getCSRFTokenField() . "\n") ?>
                <?php if ($isEditing === true): ?>
                  <button type="submit" name="update_points_type" class="btn btn-success btn-block btn-lg">Update type</button>
                <?php else: ?>
                  <button type="submit" name="create_points_type" class="btn btn-info btn-block btn-lg">Create type</button>
                <?php endif; ?>
              </div>
            </form>
          <?php else: ?>
            <h2 class="text-center">No permissions to view category</h2>
          <?php endif; ?>
        </div>
      </div>
    </div>
  <?php include_once(INCLUDE_PATH . "/layouts/footer.php") ?>

