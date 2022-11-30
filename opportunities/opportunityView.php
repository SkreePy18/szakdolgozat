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
     <!-- Bootstrap tooltip -->
     <script src="../assets/js/tooltip.js"></script>
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
              <?php if (canUpdateOpportunityByID($opportunity_id)): ?>
              <form method="post" action="importStudents.php" enctype="multipart/form-data" style="display: inline">
              <?php echo(getCSRFTokenField() . "\n") ?>
                <input type="hidden" name="opportunity_id" value="<?php xecho($opportunity_id); ?>"> </input>
                <input type="file" name="fileToUpload" id="fileToUpload" class="btn" style="margin-bottom: 5px; display: inline-block;float: right"></input>
                <input type="submit" class='btn btn-info' name="import" id="import" value="Import list" style="margin-bottom: 5px; float: right"> </input>
              </form>
              <?php endif ?>
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
                <?php 
                  $sql = "SELECT name FROM `opportunity_points_type` WHERE id = ?";
                  $result = getSingleRecord($sql, 'i', [$points_type]);
                ?>
                <input type="text" class="form-control" rows=1 value="<?php xecho($result['name']); ?>" disabled></input>
              </div>
              <div class="form-group" >
                <label class="control-label">Points</label>
                <input type="number" class="form-control" rows=1 value="<?php xecho($opportunity_points); ?>" disabled></input>
              </div>
              <div class="form-group" >
                <label class="control-label">Expiration date</label>
                <input type="date" class="form-control" rows=1 value="<?php xecho($expiration_date); ?>" disabled></input>
              </div>

              <!-- View students who has accomplished the opportunity -->
              <?php if (canUpdateOpportunityByID($opportunity_id)): ?>
                <br><h2 class="text-center">Student(s) who have accomplished the opportunity</h2><br>

                <?php if(! empty($accomplised_users)): ?>
                  <table class="table table-bordered">
                  <thead>
                    <tr>
                      <th width="2%">#</th>
                      <th width="10%">Student</th>
                      <th width="15%">Date achieved</th>
                      <th colspan="4" class="text-center" width="23%">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($accomplised_users as $key => $user): ?>
                        <tr>
                          <!-- Index -->

                          <td class="text-center"> <?php xecho($key + 1); ?> </td>

                          <!-- Neptun code -->

                          <td>

                            <?php 
                              // echo($user['user_id']);
                                $sql = "SELECT neptuncode FROM users WHERE id=? LIMIT 1";
                                $aid = getSingleRecord($sql, 'i', [ $user['user_id'] ]);
                                xecho($aid['neptuncode']);
                            ?>
                          </td>

                          <!-- Date -->

                          <td class="text-center"> <?php xecho(date("F j, Y", strtotime($user['date']))); ?> </td>

                          <!-- Remove -->

                          <td class="text-center">
                            <a data-toggle="tooltip" title="Remove student" href="<?php xecho(BASE_URL); ?>opportunities/opportunityView.php?view_opportunity=<?php xecho($opportunity_id); ?>&remove_student=<?php 
                                xecho($user['user_id']);

                              ?>" class="btn btn-sm <?php xecho("btn-danger"); ?>">
                              <span class="glyphicon glyphicon-trash"></span>
                            </a>
                          </td>

                        </tr>
                    <?php endforeach; ?>
                  </tbody>
                 </table>
                 <?php else: ?>
                  <h3 class="text-center"> Noone has completed this opportunity yet </h3>
                  <?php endif ?>
              <?php endif; ?>
            <?php else: ?>
              <h2 class="text-center">No permissions to view opportunity</h2>
            <?php endif ?>
          </form>
        </div>
      </div>
    </div>
  <?php include_once(INCLUDE_PATH . "/layouts/footer.php") ?>

