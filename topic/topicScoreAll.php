<?php include_once('../config.php'); ?>
<?php include_once(ROOT_PATH . '/csrf.php') ?> 
<?php include_once(ROOT_PATH . '/topic/topicLogic.php'); ?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title><?php xecho(APP_NAME) ?> - View scores for topics</title>
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

          <?php if (hasPermissionTo('view-all-topic-score')): ?>
            <?php
              $topics = getFilterTopicsScore($_SESSION['user']['id']);
            ?>
            <h1 class="text-center">View scores for topics</h1>
            <br />

            <?php if (! empty($topics)): ?>
              <table class="table table-bordered">
                <thead>
                  <tr>
                    <th width="2%">#</th>
                    <th>Title</th>
                    <th>Views</th>
                    <th width="40%">Scores</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($topics as $key => $value): ?>
                    <tr>
                      <td><?php xecho($key + 1); ?></td>
                      <td><?php xecho($value['title']) ?></td>
                      <td><?php xecho($value['views']) ?></td>
                      <td>
                        <table class="table table-bordered">
                          <tbody>
                            <?php
                                $sql = "SELECT u.fullname, s.value FROM topic_score ts INNER JOIN scores s ON ts.score_id=s.id INNER JOIN users u ON ts.user_id=u.id WHERE topic_id=?";
                                $score_list=getMultipleRecords($sql, "i", [ $value['id'] ]);
                                $score_num=count($score_list);
                                $score_min_value=9999;
                                $score_min_users=array();
                                $score_max_value=-9999;
                                $score_max_users=array();
                                $score_all_users=array();
                                foreach($score_list as $sc) {
                                  array_push($score_all_users, $sc['fullname']);
                                  if( $score_min_value == $sc['value'] ) {
                                    array_push($score_min_users, $sc['fullname']);
                                  }
                                  if( $score_min_value > $sc['value'] ) {
                                    $score_min_value = $sc['value'];
                                    $score_min_users=array();
                                    array_push($score_min_users, $sc['fullname']);
                                  }
                                  if( $score_max_value == $sc['value'] ) {
                                    array_push($score_max_users, $sc['fullname']);
                                  }
                                  if( $score_max_value < $sc['value'] ) {
                                    $score_max_value = $sc['value'];
                                    $score_max_users=array();
                                    array_push($score_max_users, $sc['fullname']);
                                  }
                                }
                            ?>
                            <tr><td>min</td><td><?php xecho($score_min_value) ?></td><td><?php echo(implode("<br>", $score_min_users)); ?></td></tr>
                            <tr><td>#</td><td><?php xecho($score_num) ?></td><td></td></tr>
                            <!-- <tr><td>#</td><td><?php xecho($score_num) ?></td><td><?php echo(implode("<br>", $score_all_users)); ?></td></tr> -->
                            <tr><td>max</td><td><?php xecho($score_max_value) ?></td><td><?php echo(implode("<br>", $score_max_users)); ?></td></tr>
                          </tbody>
                        </table>
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



