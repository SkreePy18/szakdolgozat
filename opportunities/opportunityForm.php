<?php include_once('../config.php'); ?>
<?php include_once(ROOT_PATH . '/csrf.php') ?>
<?php include_once(ROOT_PATH . '/opportunities/opportunityLogic.php'); ?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title><?php xecho(APP_NAME); ?> - Create opportunity</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css" />
    <!-- Custome styles -->
    <link rel="stylesheet" href="../assets/css/style.css">
  </head>
  <body>
    <?php include_once(INCLUDE_PATH . "/layouts/navbar.php") ?>

    <div class="container" style="margin-bottom: 50px;">
      <div class="row">
        <div class="col-md-8 col-md-offset-2">

          <?php if (hasPermissionTo('view-opportunity-list')): ?>
            <a href="opportunityFilter.php?filter_opportunity=all" class="btn btn-primary" style="margin-bottom: 5px;">
              <span class="glyphicon glyphicon-chevron-left"></span>
              Opportunities
            </a>
            <hr>
          <?php endif; ?>

          <?php if (canUpdateObjectByID('opportunity', $opportunity_id ) || hasPermissionTo('create-opportunity') ): ?>
            <?php if ($isEditing === true ): ?>
              <h2 class="text-center">Update opportunity</h2>
            <?php else: ?>
              <h2 class="text-center">Create opportunity</h2>
            <?php endif; ?>

            <form class="form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post" enctype="multipart/form-data">
            <input type="hidden" id="user_id" name="user_id" value="<?php xecho($_SESSION['user']['id']); ?>">
              <?php if ($isEditing === true): ?>
                <input type="hidden" name="edit_opportunity" value="<?php xecho($opportunity_id); ?>">
              <?php endif; ?>

                <?php if ($isEditing === true ): ?>
                  <div class="form-group" >
                    <input type="hidden" name="opportunity_id" value="<?php xecho($opportunity_id) ?>">
                  </div>
                  <div class="form-group <?php xecho(isset($errors['opportunity']) ? 'has-error' : '') ?>">
                    <label class="control-label">Supervisor</label>
                    <?php 
                      $sql = "SELECT fullname FROM users WHERE id=? LIMIT 1";
                      $user = getSingleRecord($sql, 'i', [$owner_id]);
                      ?>
                    <input type="text" name="owner" value="<?php xecho($user['fullname']); ?>" class="form-control" disabled>
                  </div>
                <?php endif; ?>

              <div class="form-group <?php xecho(isset($errors['opportunity']) ? 'has-error' : '') ?>">
                <label class="control-label">Opportunity</label>
                <input type="text" name="opportunity" value="<?php xecho($opportunity); ?>" class="form-control" required>
                <?php if (isset($errors['opportunity'])): ?>
                  <span class="help-block"><?php xecho($errors['opportunity']); ?></span>
                <?php endif; ?>
              </div>
              <div class="form-group <?php xecho(isset($errors['description']) ? 'has-error' : '') ?>">
                <label class="control-label">Description</label>
                <textarea required type="text" name="description" cols="30" rows="10" class="form-control"><?php xecho($opportunity_description); ?></textarea>
                <?php if (isset($errors['description'])): ?>
                  <span class="help-block"><?php xecho($errors['description']); ?></span>
                <?php endif; ?>
              </div>
              <div class="form-group <?php xecho(isset($errors['points_type']) ? 'has-error' : '') ?>">
                <label class="control-label">Type of points</label><br>
                <select name="points_type" required>
                  <option value="" disabled selected hidden>Choose the type of points</option>
                  <?php 
                  // Get the types of points 
                  $sql = "SELECT name FROM `opportunity_points_type`";
                  $result = getMultipleRecords($sql);
                  if($result){
                    foreach($result as $key => $point_type) {
                    echo("<option value=" . $point_type['name'] . ">". ucfirst($point_type['name']) . " point</option>");
                    }
                  }
                  ?>
                </select>
                <?php if (isset($errors['points_type'])): ?>
                  <span class="help-block"><?php xecho($errors['points_type']); ?></span>
                <?php endif; ?>
              </div>
              <div class="form-group <?php xecho(isset($errors['description']) ? 'has-error' : '') ?>">
                <label class="control-label">Expiration date</label>
                <input type="date" name="date" class="form-control" value="<?php xecho($expiration_date); ?>" required></input>
                <?php if (isset($errors['date'])): ?>
                  <span class="help-block"><?php xecho($errors['date']); ?></span>
                <?php endif; ?>
              </div>
              <div class="form-group <?php xecho(isset($errors['points']) ? 'has-error' : '') ?>">
                <label class="control-label">Points</label>
                <input type="number" name="points" value="<?php xecho($opportunity_points); ?>" class="form-control" required>
                <?php if (isset($errors['points'])): ?>
                  <span class="help-block"><?php xecho($errors['points']); ?></span>
                <?php endif; ?>
              </div>


              <div class="form-group">
                <?php echo(getCSRFTokenField() . "\n") ?>
                <?php if ($isEditing === true): ?>
                  <button type="submit" name="update_opportunity" class="btn btn-success btn-block btn-lg">Update opportunity</button>
                <?php else: ?>
                  <button type="submit" name="save_opportunity" class="btn btn-success btn-block btn-lg">Save opportunity</button>
                <?php endif; ?>
              </div>
            </form>
          <?php else: ?>
            <h2 class="text-center">No permissions to update or create opportunity</h2>
          <?php endif ?>
        </div>
      </div>
    </div>
  <?php include_once(INCLUDE_PATH . "/layouts/footer.php") ?>



