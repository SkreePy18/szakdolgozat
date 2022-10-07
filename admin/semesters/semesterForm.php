<?php include_once('../../config.php'); ?>
<?php include_once(ROOT_PATH . '/csrf.php') ?>
<?php include_once(ROOT_PATH . '/admin/semesters/semesterLogic.php'); ?>

<?php 
  $roles = getAllSemesters(); 
?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title><?php xecho(APP_NAME); ?> - Semester</title>
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

          <?php if (hasPermissionTo('view-semester')): ?>
            <a href="semesterList.php" class="btn btn-primary" style="margin-bottom: 5px;">
              <span class="glyphicon glyphicon-chevron-left"></span>
              Semesters
            </a>
            <hr>
          <?php endif; ?>

          <?php if (canUpdateSemesterByID($semester_id) || hasPermissionTo('delete-semester') ): ?>

            <?php if ($isEditing === true ): ?>
              <h2 class="text-center">Update Semester</h2>
            <?php else: ?>
              <h2 class="text-center">Create Semester</h2>
            <?php endif; ?>

            <form class="form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post" enctype="multipart/form-data">
              <!-- if editting semester, we need that semester's id -->
              <?php if ($isEditing === true): ?>
                <input type="hidden" name="semester_id" value="<?php xecho($semester_id); ?>">
              <?php endif; ?>
              
              <div class="form-group <?php xecho(isset($errors['semester']) ? 'has-error' : ''); ?>">
                <label class="control-label">Semester</label>
                <input type="text" name="semester" value="<?php xecho($semester); ?>" class="form-control">
                <?php if (isset($errors['semester'])): ?>
                  <span class="help-block"><?php xecho($errors['semester']); ?></span>
                <?php endif; ?>
              </div>

              <div class="form-group">
                <?php echo(getCSRFTokenField() . "\n") ?>
                <?php if ($isEditing === true): ?>
                  <button type="submit" name="update_semester" class="btn btn-success btn-block btn-lg">Update semester</button>
                <?php else: ?>
                  <button type="submit" name="save_semester" class="btn btn-danger btn-block btn-lg">Save semester</button>
                <?php endif; ?>
              </div>
            </form>
          <?php else: ?>
            <h2 class="text-center">No permissions to view semester</h2>
          <?php endif; ?>
        </div>
      </div>
    </div>
  <?php include_once(INCLUDE_PATH . "/layouts/footer.php") ?>

