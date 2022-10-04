<?php include_once('../config.php'); ?>
<?php include_once(ROOT_PATH . '/csrf.php') ?>
<?php include_once(ROOT_PATH . '/topic/topicLogic.php'); ?>

<?php
  $sql = "SELECT name FROM categories WHERE id=?";
  $category_name = getSingleRecord($sql, 'i', [$category_id]);

  $sql = "SELECT fullname FROM users WHERE id=?";
  $topic_user = getSingleRecord($sql, 'i', [$topic_user_id]);

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

            <?php if (hasPermissionTo('view-own-topic-score')): ?>
              <a href="topicScoreOwn.php" class="btn btn-primary" style="margin-bottom: 5px;">
                <span class="glyphicon glyphicon-chevron-left"></span>
                Topics
              </a>
              <hr>

              <h2 class="text-center">Topic information</h2>

              <?php if (hasPermissionTo('assign-topic-score')): ?>
                <?php
                  $sql = "SELECT s.id, s.value FROM scores s";
                  $score_list= getMultipleRecords($sql);

                  $sql = "SELECT u.id, u.fullname FROM users u ORDER BY u.fullname";
                  $user_list= getMultipleRecords($sql);

                  $sql ="SELECT s.id, s.value FROM topic_score t LEFT JOIN scores s ON t.score_id=s.id WHERE t.topic_id=? AND t.user_id=? LIMIT 1";
                  $score = getSingleRecord($sql, 'ii', [ $topic_id, $_SESSION['user']['id'] ]);
                ?>
                <form class="form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post" enctype="multipart/form-data">
                  <input type="hidden" name="topic_id" value="<?php xecho($topic_id); ?>">
                  <?php if ( isAdmin( $_SESSION['user']['id']) ): ?>
                    <div class="form-group">
                      <label class="control-label">User</label>
                      <select class="form-control" name="user_id">
                        <?php foreach ($user_list as $user): ?>
                          <option value="<?php xecho($user['id']) ?>" <?php if ($user['id'] == $_SESSION['user']['id']) xecho("selected") ?>><?php xecho($user['fullname']) ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                  <?php else: ?>
                    <input type="hidden" name="user_id" value="<?php xecho($_SESSION['user']['id']); ?>">
                  <?php endif; ?>
                  <div class="form-group">
                      <label class="control-label">Difficulty score</label>
                      <select class="form-control" name="score_id">
                        <option value="-1" <?php if (is_null($score)) { xecho("selected"); } ?>>Not set</option>
                        <?php foreach ($score_list as $sc): ?>
                          <option value="<?php xecho($sc['id']) ?>" <?php if ((!is_null($score)) && ($sc['id'] == $score['id'])) xecho("selected") ?>><?php xecho($sc['value']) ?></option>
                        <?php endforeach; ?>
                      </select>
                  </div>
                  <div class="form-group text-center">
                    <?php echo(getCSRFTokenField() . "\n") ?>
                    <button type="submit" name="select_score" class="btn btn-success">Select score</button>
                  </div>
                </form>
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



