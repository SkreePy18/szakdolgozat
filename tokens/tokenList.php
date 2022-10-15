<?php include_once('../config.php'); ?>
<?php include_once(ROOT_PATH . '/csrf.php') ?> 
<?php include_once(ROOT_PATH . '/tokens/tokenLogic.php'); ?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title><?php xecho(APP_NAME) ?> - View tokens</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css" />
    <!-- Custome styles -->
    <link rel="stylesheet" href="../assets/css/style.css">
  </head>
  <body>
    <?php include_once(INCLUDE_PATH . "/layouts/navbar.php") ?>

    <?php if ($isDeleting === true): ?>
      <div class="col-md-6 col-md-offset-3">
        <form class="form" action="<?php xecho(removeQueryServer("delete_opportunity"));?>" method="post" enctype="multipart/form-data">
          <input type="hidden" name="opportunity_id" value="<?php xecho($opportunity_id); ?>">
          <p class="text-center">Do you really want to delete Opportunity: '<?php xecho($opportunity); ?>'?</p>
          <div class="form-group text-center">
            <?php echo(getCSRFTokenField() . "\n") ?>
            <button type="submit" name="force_delete_opportunity" class="btn btn-success btn-lg">Delete</button>
            <button type="submit" name="cancel_delete_opportunity" class="btn btn-danger btn-lg">Cancel</button>
          </div>
        </form>
      </div>
    <?php endif; ?>

    <div class="container" style="margin-bottom: 50px;">
      <div class="row">
        <div class="col-md-10 col-md-offset-1">

          <!-- Opportunity creation -->
          <?php if (hasPermissionTo('create-opportunity')): ?>
            <a href="opportunityForm.php" class="btn btn-success">
              <span class="glyphicon glyphicon-plus"></span>
              Create an opportunity
            </a>
            <hr>
          <?php endif ?>

          <?php if (hasPermissionTo('view-opportunity-list')): ?>

            <?php
              $ncol = hasPermissionTo('update-opportunity') + hasPermissionTo('delete-opportunity');
              $title = "";
              
            ?>

            <h1 class="text-center"><?php xecho($title); ?></h1>
            <br />

            <?php if (! empty($opportunities)): ?>
              <table class="table table-bordered">
                <thead>
                  <tr>
                    <th width="2%">#</th>
                    <th>Opportunity</th>
                    <th width="10%">Creator</th>
                    <th width="15%">NEPTUN</th>
                    <th width="15%">Achievable points</th>
                    <th colspan="3" class="text-center" width="23%">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($opportunities as $key => $value): ?>
                    <?php if ( canViewOpportunityByID($value['id']) || canUpdateOpportunityByID( $value['id'] ) || canDeleteCategoryByID( $value['id'] ) ): ?>
                      <tr>
                        <td><?php xecho($key + 1); ?></td>
                        <?php $url = "opportunities/opportunityView.php?view_opportunity=" ?>
                        <td><a href=<?php echo(BASE_URL . $url . $value['id']); ?> > <?php xecho($value['opportunity']); ?></a></td>
                        <td>
                          <a href="<?php xecho(BASE_URL . 'opportunities/opportunityFilter.php?filter_supervisor=' . $value['owner_id']); ?>" class="btn absoluteCenter">
                            <?php 
                                $sql = "SELECT fullname FROM users WHERE id=? LIMIT 1";
                                $aid = getSingleRecord($sql, 'i', [ $value['owner_id'] ]);
                                xecho($aid['fullname']);
                            ?>
                          </a>
                        </td>

                        <!-- Type of points -->

                        <td>
                          <span class="absoluteCenter"><?php xecho($value['points_type']); ?> </span>
                        </td>

                        <!-- Achievable points -->
                        <td>
                          <span class="absoluteCenter"><?php xecho($value['points']); ?> </span>
                        </td>
                        <!-- Action buttons -->
                        <td class="text-center">
                          <a href="<?php xecho(BASE_URL); ?>opportunities/opportunityView.php?view_opportunity=<?php 
                              xecho($value['id']);
                            
                            ?>" class="btn btn-sm <?php xecho("btn-primary"); ?>">
                            <span class="glyphicon glyphicon-info-sign"></span>
                          </a>
                        </td>
                        <!-- Generate QR code / hexadecimal number -->
                        <?php if (canGenerateCodeByID( $value['id'] )): ?>
                          <td class="text-center">
                            <a href="<?php xecho(BASE_URL); ?>tokens/codeGenerationForm.php?generate_code=<?php xecho($value['id']); ?>" class="btn btn-sm btn-success">
                              <span class="glyphicon glyphicon-qrcode"></span>
                            </a>
                          </td>
                        <?php elseif(canUpdateOpportunityByID( $value['id'], false )): ?>
                          <td class="text-center">
                            <button class="btn btn-sm btn-secondary">
                              <span class="glyphicon glyphicon-qrcode"></span>
                            </button>
                          </td>
                        <?php else: ?>
                          <td class="text-center"><span class="btn btn-sm glyphicon glyphicon-ban-circle"></span></td>
                        <?php endif ?>

                        <?php if (canUpdateOpportunityByID( $value['id'] )): ?>
                          <td class="text-center">
                            <a href="<?php xecho(BASE_URL); ?>opportunities/opportunityForm.php?edit_opportunity=<?php xecho($value['id']); ?>" class="btn btn-sm btn-success">
                              <span class="glyphicon glyphicon-pencil"></span>
                            </a>
                          </td>
                        <?php elseif(canUpdateOpportunityByID( $value['id'], false )): ?>
                          <td class="text-center">
                            <button class="btn btn-sm btn-secondary">
                              <span class="glyphicon glyphicon-pencil"></span>
                            </button>
                          </td>
                        <?php else: ?>
                          <td class="text-center"><span class="btn btn-sm glyphicon glyphicon-ban-circle"></span></td>
                        <?php endif ?>

                        <?php if (canDeleteOpportunityByID( $value['id'] )): ?>
                          <td class="text-center">
                            <!-- <a href="<?php xecho(BASE_URL); ?>opportunities/opportunityFilter.php?delete_opportunity=<?php xecho($value['id']); ?>" class="btn btn-sm btn-danger"> -->
                            <a href="<?php xecho(addQueryServer("delete_opportunity", $value['id'])) ?>" class="btn btn-sm btn-danger">
                              <span class="glyphicon glyphicon-trash"></span>
                            </a>
                          </td>
                        <?php elseif(canDeleteOpportunityByID( $value['id'], false )): ?>
                          <td class="text-center">
                            <button class="btn btn-sm btn-secondary">
                              <span class="glyphicon glyphicon-trash"></span>
                            </button>
                          </td>
                        <?php else: ?>
                          <td class="text-center"><span class="btn btn-sm glyphicon glyphicon-ban-circle"></span></td>
                        <?php endif ?>
                      </tr>
                    <?php endif ?>
                  <?php endforeach; ?>
                </tbody>
              </table>
            <?php else: ?>
              <h2 class="text-center">No opportunities</h2>
            <?php endif; ?>
          <?php else: ?>
            <h2 class="text-center">No permissions to view opportunity list</h2>
          <?php endif; ?>
        </div>
      </div>
    </div>
  <?php include_once(INCLUDE_PATH . "/layouts/footer.php") ?>


