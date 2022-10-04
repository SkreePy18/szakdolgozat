<?php include_once('../../config.php') ?>
<?php include_once(ROOT_PATH . '/csrf.php') ?>
<?php include_once(ROOT_PATH . '/admin/roles/roleLogic.php') ?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title><?php xecho(APP_NAME); ?> - Assign permissions</title>
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css" />
  <!-- Custome styles -->
  <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
  <?php include_once(INCLUDE_PATH . "/layouts/navbar.php") ?>
  <div class="container" style="margin-bottom: 50px;">
    <div class="row">
      <div class="col-md-4 col-md-offset-4">
        <?php if (canViewRoleList()): ?>
          <a href="roleList.php" class="btn btn-primary">
            <span class="glyphicon glyphicon-chevron-left"></span>
            Roles
          </a>
          <hr>
        <?php endif ?>

        <?php if ( canAssignRolePermissionsByID($role_id) ): ?>
          <?php
            $sql = "SELECT name FROM roles WHERE id=? LIMIT 1";
            $role = getSingleRecord($sql, 'i', [$role_id]); 
          ?>
          <h1 class="text-center">Assign permissions for <?php xecho($role['name']); ?></h1>
          <br />
          <?php
            // Getting all permissions
            $permissions = getAllPermissions();

            // Getting all permissions belonging to role
            $role_permissions = getRoleAllPermissions($role_id);

            // array of permission ids belonging to the role
            $r_permissions_id = array_column($role_permissions, "id");
          ?>
          <?php if (count($permissions) > 0): ?>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
              <table class="table table-bordered">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Role name</th>
                    <th class="text-center">Status</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($permissions as $key => $value): ?>
                    <tr class="text-center">
                      <td><?php xecho($key + 1); ?></td>
                      <td><?php xecho($value['description']); ?></td>
                      <td>
                        <div class="form-check">
                          <input type="hidden" name="role_id" value="<?php xecho($role_id); ?>">
                          <!-- if current permission id is inside role's ids, then check it as already belonging to role -->
                          <?php if (in_array($value['id'], $r_permissions_id)): ?>
                            <input type="checkbox" name="permission[]" value="<?php xecho($value['id']); ?>" checked>
                          <?php else: ?>
                            <input type="checkbox" name="permission[]" value="<?php xecho($value['id']); ?>" >
                          <?php endif; ?>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                  <tr>
                    <td colspan="3">
                      <?php echo(getCSRFTokenField() . "\n") ?>
                      <button type="submit" name="assign_permissions" class="btn btn-block btn-success btn-lg">Assign permissions</button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </form>
          <?php else: ?>
            <h2 class="text-center">No permissions in database</h2>
          <?php endif; ?>
        <?php else: ?>
          <h2 class="text-center">No permissions to view permissions</h2>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <?php include_once(INCLUDE_PATH . "/layouts/footer.php") ?>
</body>
</html>
