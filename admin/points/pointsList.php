<?php include_once('../../config.php') ?>
<?php include_once(ROOT_PATH . '/csrf.php') ?> 
<?php include_once(ROOT_PATH . '/admin/points/excellenceLogic.php'); ?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title><?php xecho(APP_NAME); ?> - Category management</title>
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css" />
  <!-- Custome styles -->
  <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
  <?php include_once(INCLUDE_PATH . "/layouts/navbar.php") ?>

  <?php if ($isDeleting === true): ?>
    <div class="col-md-6 col-md-offset-3">
      <form class="form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post" enctype="multipart/form-data">
        <input type="hidden" name="type_id" value="<?php xecho($type_id); ?>">
        <p class="text-center">Do you really want to delete point type: '<?php xecho($type); ?>'?</p>
        <div class="form-group text-center">
          <?php echo(getCSRFTokenField() . "\n") ?>
          <button type="submit" name="force_delete_type" class="btn btn-success btn-lg">Delete</button>
          <button type="submit" name="cancel_delete_type" class="btn btn-danger btn-lg">Cancel</button>
        </div>
      </form>
    </div>
  <?php endif; ?>

  <div class="container" style="margin-bottom: 50px;">
    <div class="row">
      <div class="col-md-8 col-md-offset-2">

        <?php if (hasPermissionTo('view-dashboard')): ?>
          <a href="../dashboard.php" class="btn btn-primary">
            <span class="glyphicon glyphicon-chevron-left"></span>
            Dashboard
          </a>
        <?php endif; ?>

        <?php if (hasPermissionTo('create-point-type')): ?>
          <a href="pointsForm.php" class="btn btn-success">
            <span class="glyphicon glyphicon-plus"></span>
            Create new point type
          </a>
          <hr>
        <?php endif ?>

        <?php if (hasPermissionTo('view-point-types')): ?>
          <?php
            $categories = getTypesOfPoints();
            $ncol = hasPermissionTo('update-point-type') + hasPermissionTo('delete-point-type');
          ?>
          <h1 class="text-center">Excellence management</h1>
          <br />
          <?php if (!empty($categories)): ?>
            <table class="table table-bordered">
              <thead>
                <tr>
                  <th width="5%">#</th>
                  <th width="80%">Name of type</th>
                  <?php if ($ncol > 0): ?>
                    <th colspan="<?php xecho($ncol); ?>" class="text-center" width="20%">Action</th>
                  <?php endif ?>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($categories as $key => $value): ?>
                  <tr>
                    <td><?php xecho($key + 1); ?></td>
                    <td><?php xecho($value['name']); ?></td>

                    <?php if ($ncol > 0): ?>
                      <?php if (hasPermissionTo('update-category') && ($value['id'] > 0)): ?>
                        <td class="text-center">
                          <a href="<?php xecho(BASE_URL); ?>admin/points/pointsForm.php?edit_type=<?php xecho($value['id']); ?>" class="btn btn-sm btn-success">
                            <span class="glyphicon glyphicon-pencil"></span>
                          </a>
                        </td>
                      <?php endif ?>

                      <?php if (hasPermissionTo('delete-category') && ($value['id'] > 0)): ?>
                        <td class="text-center">
                          <a href="<?php xecho(BASE_URL); ?>admin/points/pointsList.php?delete_type=<?php xecho($value['id']); ?>" class="btn btn-sm btn-danger">
                            <span class="glyphicon glyphicon-trash"></span>
                          </a>
                        </td>
                      <?php endif ?>
                    <?php endif ?>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          <?php else: ?>
            <h2 class="text-center">No categories in the database</h2>
          <?php endif; ?>
        <?php else: ?>
          <h2 class="text-center">No permissions to view list of categories</h2>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <?php include_once(INCLUDE_PATH . "/layouts/footer.php") ?>
</body>
</html>
