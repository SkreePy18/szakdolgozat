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

    <div class="container" style="margin-bottom: 50px;">
      <div class="row">
        <div class="col-md-10 col-md-offset-1">
          <?php if (hasPermissionTo('view-opportunity-list')): ?>

            <?php if (hasPermissionTo('view-opportunity-list')): ?>
            <a href="../opportunities/opportunityFilter.php?filter_opportunity=all" class="btn btn-primary" style="margin-bottom: 5px;">
              <span class="glyphicon glyphicon-chevron-left"></span>
              Opportunities
            </a>
            <hr>
          <?php endif; ?>

            <?php
              $ncol = hasPermissionTo('update-opportunity') + hasPermissionTo('delete-opportunity');
              $title = "";
              $title = $opportunity;
            ?>

            <h1 class="text-center">Viewing tokens for opportunity: <?php xecho($title); ?></h1>
            <br />

            <?php if (! empty($tokens)): ?>
              <table class="table table-bordered">
                <thead>
                  <tr>
                    <th width="2%">#</th>
                    <th>Token created for</th>
                    <th width="10%">Token</th>
                    <th width="15%">QR Code</th>
                    <th width="15%">Expiration date</th>
                    <th width="15%">Redeemed</th>
                    <th colspan="3" class="text-center" width="23%">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($tokens as $key => $value): ?>
                    <?php if ( canViewOpportunityByID($value['id']) || canUpdateOpportunityByID( $value['id'] ) || canDeleteCategoryByID( $value['id'] ) ): ?>
                      <tr>
                        <td><?php xecho($key + 1); ?></td>
                        <?php $url = "opportunities/opportunityView.php?view_opportunity=" ?>
                        <td><span class='absoluteCenter'>
                            <?php 
                                $sql = "SELECT neptuncode FROM users WHERE id=? LIMIT 1";
                                $aid = getSingleRecord($sql, 'i', [ $value['user_id'] ]);
                                xecho($aid['neptuncode']);
                            ?>
                            </span>
                        </td>

                        <!-- Type of points -->

                        <td>
                          <span class="absoluteCenter"><?php xecho($value['token']); ?> </span>
                        </td>

                        <!-- Achievable points -->
                        <td>
                          <center><img class='absoluteCenter' height="32px" src='qrCodes/<?php xecho($value['token']); ?>.png'/></center>
                        </td>

                        <!-- Expiration date -->
                        <td>
                          <span class="absoluteCenter"><?php xecho($value['expiration_date']); ?> </span>
                        </td>
                        
                        <!-- Redeemed -->

                        <td>
                          <span class="absoluteCenter"><?php xecho($value['redeemed']); ?> </span>
                        </td>

                        <!-- Action buttons -->
                        <!-- <td class="text-center">
                          <a href="<?php xecho(BASE_URL); ?>tokens/tokenList.php?opportunity=<?php xecho($value['opportunity_id']); ?>&save_token=<?php 
                              xecho($value['token']);
                            
                            ?>" class="btn btn-sm <?php xecho("btn-primary"); ?>">
                            <span class="glyphicon glyphicon-save"></span>
                          </a>
                        </td> -->

                        <td class="text-center">
                            <a href="codeGenerationForm.php?edit_token=<?php xecho($value['id']); ?>" class="btn btn-sm btn-success">
                              <span class="glyphicon glyphicon-pencil"></span>
                            </a>
                          </td>
                  
                          <td class="text-center">
                            <!-- <a href="<?php xecho(BASE_URL); ?>opportunities/opportunityFilter.php?delete_opportunity=<?php xecho($value['id']); ?>" class="btn btn-sm btn-danger"> -->
                            <a href="<?php xecho(addQueryServer("delete_token", $value['token'])) ?>" class="btn btn-sm btn-danger">
                              <span class="glyphicon glyphicon-trash"></span>
                            </a>
                          </td>
                        <?php elseif(canDeleteOpportunityByID( $value['id'], false )): ?>
                          <td class="text-center">
                            <button class="btn btn-sm btn-secondary">
                              <span class="glyphicon glyphicon-trash"></span>
                            </button>
                          </td>
                      </tr>
                    <?php endif ?>
                  <?php endforeach; ?>
                </tbody>
              </table>
            <?php else: ?>
              <h2 class="text-center">No tokens have been generated for this opportunity</h2>
            <?php endif; ?>
          <?php else: ?>
            <h2 class="text-center">No permissions to view the list of tokens</h2>
          <?php endif; ?>
        </div>
      </div>
    </div>
  <?php include_once(INCLUDE_PATH . "/layouts/footer.php") ?>



