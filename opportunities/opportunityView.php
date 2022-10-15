<?php include_once('../config.php'); ?>
<?php include_once(ROOT_PATH . '/csrf.php') ?>
<?php include_once(ROOT_PATH . '/opportunities/opportunityLogic.php'); ?>

<?php
  $sql = "SELECT fullname FROM users WHERE id=?";
  $opportunity_owner = getSingleRecord($sql, 'i', [$owner_id]);

?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title><?php xecho(APP_NAME); ?> - View opportunities</title>
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

            <?php if ( canViewOpportunityByID($opportunity_id) ): ?>
              <a href="opportunityFilter.php?filter_opportunity=all" class="btn btn-primary" style="margin-bottom: 5px;">
                <span class="glyphicon glyphicon-chevron-left"></span>
                Opportunities
              </a>
              <hr>

              <h2 class="text-center">Opportunities</h2>
            
              <div class="form-group" >
                <label class="control-label">Supervisor</label>
                <input type="text" value="<?php xecho($opportunity_owner['fullname']); ?>" class="form-control" disabled>
              </div>
              <div class="form-group" >
                <label class="control-label">Opportunity</label>
                <textarea class="form-control" rows=2 disabled><?php xecho($opportunity); ?></textarea>
              </div>
              <div class="form-group" >
                <label class="control-label">Description</label>
                <textarea class="form-control" rows=10 disabled><?php xecho($opportunity_description); ?></textarea>
              </div>
              <div class="form-group" >
                <label class="control-label">Type of points</label>
                <input type="text" class="form-control" rows=1 value="<?php xecho($points_type); ?>" disabled></input>
              </div>
              <div class="form-group" >
                <label class="control-label">Points</label>
                <input type="number" class="form-control" rows=1 value="<?php xecho($opportunity_points); ?>" disabled></input>
              </div>
              <div class="form-group" >
                <label class="control-label">Expiration date</label>
                <input type="date" class="form-control" rows=1 value="<?php xecho($expiration_date); ?>" disabled></input>
              </div>
            <?php else: ?>
              <h2 class="text-center">No permissions to view opportunity</h2>
            <?php endif ?>

          </form>
        </div>
      </div>
    </div>
  <?php include_once(INCLUDE_PATH . "/layouts/footer.php") ?>



