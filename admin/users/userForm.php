<?php include_once('../../config.php'); ?>
<?php include_once(ROOT_PATH . '/csrf.php') ?>
<?php include_once(ROOT_PATH . '/admin/users/userLogic.php'); ?>

<?php 
  $roles = getAllRoles(); 
?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title><?php xecho(APP_NAME); ?> - User account</title>
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

          <?php if (hasPermissionTo('view-user-list')): ?>
            <a href="userList.php" class="btn btn-primary" style="margin-bottom: 5px;">
              <span class="glyphicon glyphicon-chevron-left"></span>
              Users
            </a>
            <hr>
          <?php endif; ?>

          <?php if (canUpdateObjectByID('user', $user_id) || hasPermissionTo('create-user') ): ?>

            <?php if ($isEditing === true ): ?>
              <h2 class="text-center">Update user</h2>
            <?php else: ?>
              <h2 class="text-center">Create user</h2>
            <?php endif; ?>

            <form class="form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post" enctype="multipart/form-data">
              <!-- if editting user, we need that user's id -->
              <?php if ($isEditing === true): ?>
                <input type="hidden" name="user_id" value="<?php xecho($user_id); ?>">
              <?php endif; ?>
              <?php if(isAdmin($_SESSION['user']['id']) && $user_id != $_SESSION['user']['id']): ?>
                <div class="form-group <?php xecho(isset($errors['username']) ? 'has-error' : ''); ?>">
                  <label class="control-label">Username</label>
                  <input type="text" name="username" value="<?php xecho($username); ?>" class="form-control">
                  <?php if (isset($errors['username'])): ?>
                    <span class="help-block"><?php xecho($errors['username']); ?></span>
                  <?php endif; ?>
                </div>
              <?php else: ?>
                <div class="form-group" >
                  <label class="control-label">Username</label>
                  <input type="text" value="<?php xecho($username); ?>" class="form-control" disabled>
                </div>
                <input type="hidden" name="username" value="<?php xecho($username); ?>">
              <?php endif; ?>
              <div class="form-group <?php xecho(isset($errors['fullname']) ? 'has-error' : ''); ?>">
                <label class="control-label">Full name</label>
                <input type="text" name="fullname" value="<?php xecho($fullname); ?>" class="form-control">
                <?php if (isset($errors['fullname'])): ?>
                  <span class="help-block"><?php xecho($errors['fullname']); ?></span>
                <?php endif; ?>
              </div>
              <div class="form-group <?php xecho(isset($errors['neptuncode']) ? 'has-error' : ''); ?>">
                <label class="control-label">Neptun code</label>
                <input type="text" name="neptuncode" value="<?php xecho($neptuncode); ?>" class="form-control">
                <?php if (isset($errors['neptuncode'])): ?>
                  <span class="help-block"><?php xecho($errors['neptuncode']); ?></span>
                <?php endif; ?>
              </div>
              <div class="form-group <?php xecho(isset($errors['email']) ? 'has-error' : ''); ?>">
                <label class="control-label">Email Address</label>
                <input type="email" name="email" value="<?php xecho($email); ?>" class="form-control">
                <?php if (isset($errors['email'])): ?>
                  <span class="help-block"><?php xecho($errors['email']); ?></span>
                <?php endif; ?>
              </div>
              <?php if ($isEditing === true ): ?>
                <?php if( ! isAdmin($_SESSION['user']['id']) ): ?>
                  <div class="form-group <?php xecho(isset($errors['passwordOld']) ? 'has-error' : '') ?>">
                    <label class="control-label">Old Password</label>
                    <input type="password" name="passwordOld" class="form-control">
                    <?php if (isset($errors['passwordOld'])): ?>
                      <span class="help-block"><?php xecho($errors['passwordOld']) ?></span>
                    <?php endif; ?>
                  </div>
                <?php else: ?>
                  <div class="form-group" >
                    <label class="control-label">Old Password</label>
                    <input type="text" value="" class="form-control" disabled>
                  </div>
                <?php endif; ?>
              <?php endif; ?>
              <div class="form-group <?php xecho(isset($errors['password']) ? 'has-error' : '') ?>">
                <label class="control-label">Password</label>
                <input type="password" name="password" class="form-control">
                <?php if (isset($errors['password'])): ?>
                  <span class="help-block"><?php xecho($errors['password']); ?></span>
                <?php endif; ?>
              </div>
              <div class="form-group <?php xecho(isset($errors['passwordConf']) ? 'has-error' : ''); ?>">
                <label class="control-label">Password confirmation</label>
                <input type="password" name="passwordConf" class="form-control">
                <?php if (isset($errors['passwordConf'])): ?>
                  <span class="help-block"><?php xecho($errors['passwordConf']); ?></span>
                <?php endif; ?>
              </div>

              <?php if (hasPermissionTo('assign-user-role')): ?>
                <div class="form-group <?php xecho(isset($errors['role_id']) ? 'has-error' : ''); ?>">
                  <label class="control-label">User Role</label>
                  <select class="form-control" name="role_id">
                    <?php $roles = getAllRoles(); ?>
                    <?php foreach ($roles as $role): ?>
                      <option value="<?php xecho($role['id']); ?>" <?php 
                          if ($role['id'] == $role_id) { xecho("selected"); }
                          if ($role_id == 0 && $role['id'] == 2) { xecho("selected"); } ?>><?php xecho($role['name']) ?></option>
                    <?php endforeach; ?>
                  </select>
                  <?php if (isset($errors['role_id'])): ?>
                    <span class="help-block"><?php xecho($errors['role_id']); ?></span>
                  <?php endif; ?>
                </div>
              <?php endif; ?>
              <div class="form-group">
                <?php echo(getCSRFTokenField() . "\n") ?>
                <?php if ($isEditing === true): ?>
                  <button type="submit" name="update_user" class="btn btn-success btn-block btn-lg">Update user</button>
                <?php else: ?>
                  <button type="submit" name="save_user" class="btn btn-success btn-block btn-lg">Save user</button>
                <?php endif; ?>
              </div>
            </form>
          <?php else: ?>
            <h2 class="text-center">No permissions to view user</h2>
          <?php endif; ?>
        </div>
      </div>
    </div>
  <?php include_once(INCLUDE_PATH . "/layouts/footer.php") ?>

