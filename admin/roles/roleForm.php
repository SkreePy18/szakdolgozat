<?php include_once('../../config.php') ?>
<?php include_once(ROOT_PATH . '/csrf.php') ?>
<?php include_once(ROOT_PATH . '/admin/roles/roleLogic.php') ?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title><?php xecho(APP_NAME) ?> - User role </title>
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

          <?php if (hasPermissionTo('view-role-list')): ?>
            <a href="roleList.php" class="btn btn-primary">
              <span class="glyphicon glyphicon-chevron-left"></span>
              Roles
            </a>
            <hr>
          <?php endif; ?>

          <?php if (canUpdateObjectByID('role', $role_id) || hasPermissionTo('create-role') ): ?>
            <?php if ($isEditting === true): ?>
              <h1 class="text-center">Update Role</h1>
            <?php else: ?>
              <h1 class="text-center">Create Role</h1>
            <?php endif; ?>
            <br />
            <form class="form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
              <?php if ($isEditting === true): ?>
                <input type="hidden" name="role_id" value="<?php xecho($role_id) ?>">
              <?php endif; ?>
              <div class="form-group <?php xecho(isset($errors['name']) ? 'has-error': ''); ?>">
                <label class="control-label">Role name</label>
                <input type="text" name="name" value="<?php xecho($name); ?>" class="form-control">
                <?php if (isset($errors['name'])): ?>
                  <span class="help-block"><?php xecho($errors['name']); ?></span>
                <?php endif; ?>
              </div>
              <div class="form-group <?php xecho(isset($errors['description']) ? 'has-error': ''); ?>">
                <label class="control-label">Description</label>
                <textarea name="description" rows="3" cols="10" class="form-control"><?php xecho($description); ?></textarea>
                <?php if (isset($errors['description'])): ?>
                  <span class="help-block"><?php xecho($errors['description']); ?></span>
                <?php endif; ?>
              </div>
              <div class="form-group">
                <?php echo(getCSRFTokenField() . "\n") ?>
                <?php if ($isEditting === true): ?>
                  <button type="submit" name="update_role" class="btn btn-success btn-block btn-lg">Update Role</button>
                <?php else: ?>
                  <button type="submit" name="save_role" class="btn btn-success btn-block btn-lg">Save Role</button>
                <?php endif; ?>
              </div>
            </form>
          <?php else: ?>
            <h2 class="text-center">No permissions to view role</h2>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <?php include_once(INCLUDE_PATH . "/layouts/footer.php") ?>
  </body>
</html>
