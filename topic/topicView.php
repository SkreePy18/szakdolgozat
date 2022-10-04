<?php include_once('../config.php'); ?>
<?php include_once(ROOT_PATH . '/csrf.php') ?>
<?php include_once(ROOT_PATH . '/topic/topicLogic.php'); ?>

<?php
  $sql = "SELECT name FROM categories WHERE id=?";
  $category_name = getSingleRecord($sql, 'i', [$category_id]);

  $sql = "SELECT fullname FROM users WHERE id=?";
  $topic_user = getSingleRecord($sql, 'i', [$topic_user_id]);


  $sql = "SELECT id FROM topics WHERE id=? AND published=1";
  $topic_viewable = getSingleRecord($sql, 'i', [$topic_id]);

?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title><?php xecho(APP_NAME); ?> - View topic</title>
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

            <?php if ( canViewTopicByID($topic_id) ): ?>
              <?php
                $sql = "UPDATE topics SET views = views + 1 WHERE id=?";
                $result = modifyRecord($sql, 'i', [ $topic_id ]);
              ?>
              <a href="topicFilter.php?filter_topic=all" class="btn btn-primary" style="margin-bottom: 5px;">
                <span class="glyphicon glyphicon-chevron-left"></span>
                Topics
              </a>
              <hr>

              <h2 class="text-center">Topic information</h2>

              <!-- user registers/unregisters for topic -->
              <!-- if user has permissions to register AND there is no approved user for this topic AND the user has no approved topic -->
              <?php if(    canRegisterTopicUserByID( $topic_id )
                        && ($approved_user_id == -1) 
                        && (! hasUserApprovedTopic($_SESSION['user']['id']))): ?>
                <?php if (isUserRegisteredTopic( $topic_id, $_SESSION['user']['id'] )): ?>
                  <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="topic_id" value="<?php xecho($topic_id); ?>">
                    <div class="form-group">
                      <?php echo(getCSRFTokenField() . "\n") ?>
                      <button type="submit" name="unregister_topic_user" class="btn btn-warning btn-block btn-lg">Unregister from topic</button>
                    </div>
                  </form>
                <?php else: ?>
                  <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="topic_id" value="<?php xecho($topic_id); ?>">
                    
                    <div class="form-group <?php xecho(isset($errors['reason']) ? 'has-error' : '') ?>">
                      <label class="control-label">Reasons and competences to register for the topic or your answer to the supervisor</label>
                      <textarea type="text" name="reason" cols="30" rows="3" class="form-control"><?php xecho($reason); ?></textarea>
                      <?php if (isset($errors['reason'])): ?>
                        <span class="help-block"><?php xecho($errors['reason']) ?></span>
                      <?php endif; ?>
                    </div>

                    <div class="form-group">
                      <?php echo(getCSRFTokenField() . "\n") ?>
                      <button type="submit" name="register_topic_user" class="btn btn-success btn-block btn-lg">Register for topic</button>
                    </div>
                  </form>
                <?php endif; ?>
              <?php endif; ?>

              <!-- if there is no approval -->
              <?php if ($approved_user_id == -1): ?>
                <!-- user can approve the registration of a user for the topic -->
                <?php if (canApproveTopicUser( $topic_id )): ?>
                  <?php 
                    $sql = "SELECT tu.user_id, u.fullname, u.neptuncode, u.email, tu.reason FROM topic_user tu INNER JOIN users u ON tu.user_id=u.id WHERE tu.topic_id=? AND tu.user_id=?";
                    $registered_topic_user = getSingleRecord($sql, 'ii', [$topic_id, $registered_user_id]);
                  ?>
                  <!-- if there is a registered users for the topic, we can approve -->
                  <?php if (! (is_null($registered_topic_user) || empty($registered_topic_user) ) ): ?>
                    <form class="form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post" enctype="multipart/form-data">
                      <input type="hidden" name="topic_id" value="<?php xecho($topic_id); ?>">
                      <input type="hidden" name="registered_topic_user" value="<?php xecho($registered_user_id); ?>">
                      <div class="form-group" >
                        <label class="control-label">Requesting student:</label>
                        <input type="text" value="<?php xecho($registered_topic_user['fullname'] . ' (' . $registered_topic_user['neptuncode'] . ') - ' . $registered_topic_user['email']); ?>" class="form-control" style="background-color: #f0ad4e; color: #FFFFFF;" disabled>
                      </div>
                      <div class="form-group" >
                        <label class="control-label">Reasons and competences to register for the topic or answer to supervisor</label>
                        <textarea class="form-control" rows=3 disabled><?php xecho($registered_topic_user['reason']); ?></textarea>
                      </div>

                      <div class="form-group">
                        <?php echo(getCSRFTokenField() . "\n") ?>
                        <button type="submit" name="approve_topic" class="btn btn-success btn-block btn-lg">Approve</button>
                      </div>
                    </form>
                  <?php endif; ?>
                <?php endif; ?>
              <!-- there is an approved user for the topic, show it -->
              <?php else: ?>
                <?php 
                  $sql = "SELECT fullname, neptuncode, email FROM users WHERE id=?";
                  $approved_user = getSingleRecord($sql, 'i', [$approved_user_id]);
                ?>
                <div class="form-group" >
                  <label class="control-label">Assigned to:</label>
                  <input type="text" value="<?php xecho($approved_user['fullname'] . ' (' . $approved_user['neptuncode'] . ') - '. $approved_user['email']); ?>" class="form-control" style="background-color: #2E7D32; color: #FFFFFF;" disabled>
                </div>
                <?php if (canApproveTopicUser( $topic_id )): ?>
                  <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="topic_id" value="<?php xecho($topic_id); ?>">
                    <div class="form-group">
                      <?php echo(getCSRFTokenField() . "\n") ?>
                      <button type="submit" name="unapprove_topic" class="btn btn-warning btn-block btn-lg">Withdraw approval</button>
                    </div>
                  </form>
                <?php endif; ?>
              <?php endif; ?>

              <div class="form-group" >
                <label class="control-label">Supervisor</label>
                <input type="text" value="<?php xecho($topic_user['fullname']); ?>" class="form-control" disabled>
              </div>
              <div class="form-group" >
                <label class="control-label">Title</label>
                <textarea class="form-control" rows=2 disabled><?php xecho($title); ?></textarea>
              </div>
              <div class="form-group" >
                <label class="control-label">Description</label>
                <textarea class="form-control" rows=10 disabled><?php xecho($description); ?></textarea>
              </div>
              <div class="form-group" >
                <label class="control-label">Requirements</label>
                <textarea class="form-control" rows=3 disabled><?php xecho($requirement); ?></textarea>
              </div>

              <div class="form-group">
                <label class="control-label">Category</label>
                <?php if (is_null($category_id)): ?>
                  <input type="text" value="<?php xecho('No category'); ?>" class="form-control" disabled>
                <?php else: ?>
                  <input type="text" value="<?php xecho($category_name['name']); ?>" class="form-control" disabled>
                <?php endif ?>
              </div>
            <?php else: ?>
              <h2 class="text-center">No permissions to view topic</h2>
            <?php endif ?>

          </form>
        </div>
      </div>
    </div>
  <?php include_once(INCLUDE_PATH . "/layouts/footer.php") ?>



