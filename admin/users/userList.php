<?php include_once('../../config.php') ?>
<?php include_once(ROOT_PATH . '/csrf.php') ?> 
<?php include_once(ROOT_PATH . '/admin/users/userLogic.php') ?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title><?php xecho(APP_NAME); ?> - User management</title>
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
        <input type="hidden" name="user_id" value="<?php xecho($user_id); ?>">
        <p class="text-center">Do you really want to delete user: '<?php xecho($username); ?>'?</p>
        <div class="form-group text-center">
          <?php echo(getCSRFTokenField() . "\n") ?>
          <button type="submit" name="force_delete_user" class="btn btn-success btn-lg">Delete</button>
          <button type="submit" name="cancel_delete_user" class="btn btn-danger btn-lg">Cancel</button>
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

        <?php if (hasPermissionTo('create-user')): ?>
          <a href="userForm.php" class="btn btn-success">
            <span class="glyphicon glyphicon-plus"></span>
            Create new user
          </a>
          <hr>
        <?php endif ?>

        <?php if (hasPermissionTo('view-user-list')): ?>
          <?php
            $allUsers = getAllUsers();
            $ncol = hasPermissionTo('update-user') + hasPermissionTo('delete-user');
          ?>
          <h1 class="text-center">User management</h1>
          <br />
          <?php if (!empty($allUsers)): ?>
            <table class="table table-bordered">
              <thead>
                <tr>
                  <th width="5%">#</th>
                  <th width="10%">Username</th>
                  <th width="35%">Fullname</th>
                  <th width="20%">Neptun code</th>
                  <th width="10%">Role</th>
                  <?php if ($ncol > 0): ?>
                    <th colspan="<?php xecho($ncol); ?>" class="text-center" width="20%">Action</th>
                  <?php endif ?>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($allUsers as $key => $value): ?>
                  <tr>
                    <td><?php xecho($key + 1); ?></td>
                    <td><?php xecho($value['username']); ?></td>
                    <td><?php xecho($value['fullname']); ?></td>
                    <td><?php xecho($value['neptuncode']); ?></td>
                    <td><?php xecho($value['role']); ?></td>

                    <?php if ($ncol > 0): ?>
                      <?php if (hasPermissionTo('update-user')): ?>
                        <td class="text-center">
                          <a href="<?php xecho(BASE_URL); ?>admin/users/userForm.php?edit_user=<?php xecho($value['id']); ?>" class="btn btn-sm btn-success">
                            <span class="glyphicon glyphicon-pencil"></span>
                          </a>
                        </td>
                      <?php endif ?>

                      <?php if (hasPermissionTo('delete-user')): ?>
                        <td class="text-center">
                          <a href="<?php xecho(BASE_URL); ?>admin/users/userList.php?delete_user=<?php xecho($value['id']); ?>" class="btn btn-sm btn-danger">
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
            <h2 class="text-center">No users in database</h2>
          <?php endif; ?>
        <?php else: ?>
          <h2 class="text-center">No permissions to view user list</h2>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <?php include_once(INCLUDE_PATH . "/layouts/footer.php") ?>
</body>
</html>
