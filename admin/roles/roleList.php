<?php include_once('../../config.php') ?>
<?php include_once(ROOT_PATH . '/csrf.php') ?> 
<?php include_once(ROOT_PATH . '/admin/roles/roleLogic.php') ?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title><?php xecho(APP_NAME); ?> - Role management</title>
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
        <input type="hidden" name="role_id" value="<?php xecho($role_id); ?>">
        <p class="text-center">Do you really want to delete role: '<?php xecho($name); ?>'?</p>
        <div class="form-group text-center">
          <?php echo(getCSRFTokenField() . "\n") ?>
          <button type="submit" name="force_delete_role" class="btn btn-success btn-lg">Delete</button>
          <button type="submit" name="cancel_delete_role" class="btn btn-danger btn-lg">Cancel</button>
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

        <?php if (hasPermissionTo('create-role')): ?>
          <a href="roleForm.php" class="btn btn-success">
            <span class="glyphicon glyphicon-plus"></span>
            Create new role
          </a>
          <hr>
        <?php endif ?>

        <?php if (hasPermissionTo('view-role-list')): ?>
          <?php
            $roles = getAllRoles();
            $ncol = hasPermissionTo('assign-role-permissions') + hasPermissionTo('update-role') + hasPermissionTo('delete-role');
          ?>
          <h1 class="text-center">Role management</h1>
          <br />
          <?php if (!empty($roles)): ?>
            <table class="table table-bordered">
              <thead>
                <tr>
                  <th width="5%">#</th>
                  <th width="55%">Role name</th>
                  <?php if ($ncol > 0): ?>
                    <th colspan="<?php xecho($ncol); ?>" class="text-center" width="40%">Action</th>
                  <?php endif ?>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($roles as $key => $value): ?>
                  <tr>
                    <td><?php xecho($key + 1); ?></td>
                    <td><?php xecho($value['name']) ?></td>

                    <?php if ($ncol > 0): ?>
                      <?php if (hasPermissionTo('assign-role-permissions')): ?>
                        <td class="text-center">
                          <a href="<?php xecho(BASE_URL) ?>admin/roles/assignPermissions.php?assign_permissions=<?php xecho($value['id']); ?>" class="btn btn-sm btn-info">
                            permissions
                          </a>
                        </td>
                      <?php endif ?>
                      <?php if (hasPermissionTo('update-role')): ?>
                        <td class="text-center">
                          <a href="<?php xecho(BASE_URL); ?>admin/roles/roleForm.php?edit_role=<?php xecho($value['id']); ?>" class="btn btn-sm btn-success">
                            <span class="glyphicon glyphicon-pencil"></span>
                          </a>
                        </td>
                      <?php endif ?>
                      <?php if (hasPermissionTo('delete-role')): ?>
                        <td class="text-center">
                          <a href="<?php xecho(BASE_URL); ?>admin/roles/roleList.php?delete_role=<?php xecho($value['id']); ?>" class="btn btn-sm btn-danger">
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
            <h2 class="text-center">No roles in database</h2>
          <?php endif; ?>
        <?php else: ?>
          <h2 class="text-center">No permissions to view roles</h2>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <?php include_once(INCLUDE_PATH . "/layouts/footer.php") ?>
</body>
</html>
