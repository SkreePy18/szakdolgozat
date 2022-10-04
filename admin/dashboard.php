<?php include_once('../config.php') ?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title><?php xecho(APP_NAME) ?> - Admin</title>
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css" />
  <!-- Custome styles -->
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
  <?php include_once(INCLUDE_PATH . "/layouts/navbar.php") ?>
  <div class="container" style="margin-bottom: 50px;">
    <div class="row">
      <div class="col-md-4 col-md-offset-4">
        <h1 class="text-center">Administration</h1>
        <br />
        <?php if (canViewDashboard()): ?>
          <ul class="list-group">
            <?php if (canViewUserList()): ?>
              <a href="<?php xecho(BASE_URL . 'admin/users/userList.php'); ?>" class="list-group-item">Manage users</a>
            <?php endif ?>
            <?php if (canViewRoleList()): ?>
              <a href="<?php xecho(BASE_URL . 'admin/roles/roleList.php'); ?>" class="list-group-item">Manage roles and permissions</a>
            <?php endif ?>
            <?php if (canViewSemesterList()): ?>
              <a href="<?php xecho(BASE_URL . 'admin/semesters/semesterList.php'); ?>" class="list-group-item">Manage semesters</a>
            <?php endif ?>
          </ul>
        <?php else: ?>
          <h2 class="text-center">No permissions to view dashboard</h2>
        <?php endif ?>
      </div>
    </div>
  </div>

  <?php include_once(INCLUDE_PATH . "/layouts/footer.php") ?>
</body>
</html>

