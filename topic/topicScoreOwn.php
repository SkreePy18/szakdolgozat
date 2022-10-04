<?php include_once('../config.php'); ?>
<?php include_once(ROOT_PATH . '/csrf.php') ?> 
<?php include_once(ROOT_PATH . '/topic/topicLogic.php'); ?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title><?php xecho(APP_NAME) ?> - View topic scores</title>
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

          <?php if (hasPermissionTo('view-own-topic-score')): ?>
            <?php
              $topics = getFilterTopicsScore($_SESSION['user']['id']);
            ?>
            <h1 class="text-center">Edit the score of topics</h1>
            <br />

            <?php if (! empty($topics)): ?>
              <table class="table table-bordered">
                <thead>
                  <tr>
                    <th width="2%">#</th>
                    <th>Title</th>
                    <th>Your score</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($topics as $key => $value): ?>
                    <tr>
                      <td><?php xecho($key + 1); ?></td>
                      <td><?php xecho($value['title']) ?></td>

                      <td><?php if( is_null($value['score']) ) { xecho("Not set"); } else { xecho($value['score']['value']); } ?></td>

                      <td class="text-center">
                        <?php if (hasPermissionTo('assign-topic-score')): ?>
                          <a href="<?php xecho(BASE_URL); ?>topic/topicScoreEdit.php?view_topic=<?php xecho($value['id']);?>" 
                              class="btn btn-primary">
                              <span class="glyphicon glyphicon-thumbs-up"></span>
                          </a>
                        <?php else: ?>
                          <span class="btn btn-sm glyphicon glyphicon-ban-circle"></span>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            <?php else: ?>
              <h2 class="text-center">No topics</h2>
            <?php endif; ?>
          <?php else: ?>
            <h2 class="text-center">No permissions to view topic list</h2>
          <?php endif; ?>
        </div>
      </div>
    </div>
  <?php include_once(INCLUDE_PATH . "/layouts/footer.php") ?>



