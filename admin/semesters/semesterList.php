<?php include_once('../../config.php') ?>
<?php include_once(ROOT_PATH . '/csrf.php') ?> 
<?php include_once(ROOT_PATH . '/admin/semesters/semesterLogic.php') ?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title><?php xecho(APP_NAME); ?> - Semester management</title>
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
        <input type="hidden" name="semester_id" value="<?php xecho($semester_id); ?>">
        <p class="text-center">Do you really want to delete semester: '<?php xecho($semester); ?>'?</p>
        <div class="form-group text-center">
          <?php echo(getCSRFTokenField() . "\n") ?>
          <button type="submit" name="force_delete_semester" class="btn btn-success btn-lg">Delete</button>
          <button type="submit" name="cancel_delete_semester" class="btn btn-danger btn-lg">Cancel</button>
        </div>
      </form>
    </div>
  <?php endif; ?>

  <div class="container" style="margin-bottom: 50px;">
    <div class="row">
      <div class="col-md-8 col-md-offset-2">

        <?php if (canViewDashboard()): ?>
          <a href="../dashboard.php" class="btn btn-primary">
            <span class="glyphicon glyphicon-chevron-left"></span>
            Dashboard
          </a>
        <?php endif; ?>

        <?php if (canCreateSemester()): ?>
          <a href="semesterForm.php" class="btn btn-success">
            <span class="glyphicon glyphicon-plus"></span>
            Create new semester
          </a>
          <hr>
        <?php endif ?>

        <?php if (canViewSemesterList()): ?>
          <?php
            $allSemesters = getAllSemesters();
            $ncol = canUpdateSemester() + canDeleteSemester(-1);
          ?>
          <h1 class="text-center">Semester management</h1>
          <br />
          <?php if (!empty($allSemesters)): ?>
            <table class="table table-bordered">
              <thead>
                <tr>
                  <th width="5%">#</th>
                  <th width="80%">Semester name</th>
                  <?php if ($ncol > 0): ?>
                    <th colspan="<?php xecho($ncol); ?>" class="text-center" width="20%">Action</th>
                  <?php endif ?>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($allSemesters as $key => $value): ?>
                  <tr>
                    <td><?php xecho($key + 1); ?></td>
                    <td><?php xecho($value['semester']); ?></td>

                    <?php if ($ncol > 0): ?>
                      <?php if (canUpdateSemester() && ($value['id'] > 1)): ?>
                        <td class="text-center">
                          <a href="<?php xecho(BASE_URL); ?>admin/semesters/semesterForm.php?edit_semester=<?php xecho($value['id']); ?>" class="btn btn-sm btn-success">
                            <span class="glyphicon glyphicon-pencil"></span>
                          </a>
                        </td>
                      <?php endif ?>

                      <?php if (canDeleteSemester() && ($value['id'] > 1)): ?>
                        <td class="text-center">
                          <a href="<?php xecho(BASE_URL); ?>admin/semesters/semesterlist.php?delete_semester=<?php xecho($value['id']); ?>" class="btn btn-sm btn-danger">
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
            <h2 class="text-center">No semesters in database</h2>
          <?php endif; ?>
        <?php else: ?>
          <h2 class="text-center">No permissions to view semester list</h2>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <?php include_once(INCLUDE_PATH . "/layouts/footer.php") ?>
</body>
</html>
