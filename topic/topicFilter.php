<?php include_once('../config.php'); ?>
<?php include_once(ROOT_PATH . '/csrf.php') ?> 
<?php include_once(ROOT_PATH . '/topic/topicLogic.php'); ?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title><?php xecho(APP_NAME) ?> - View topic</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css" />
    <!-- Custome styles -->
    <link rel="stylesheet" href="../assets/css/style.css">
  </head>
  <body>
    <?php include_once(INCLUDE_PATH . "/layouts/navbar.php") ?>

    <?php if ($isDeleting === true): ?>
      <div class="col-md-6 col-md-offset-3">
        <form class="form" action="<?php xecho(removeQueryServer("delete_topic"));?>" method="post" enctype="multipart/form-data">
          <input type="hidden" name="topic_id" value="<?php xecho($topic_id); ?>">
          <p class="text-center">Do you really want to delete topic: '<?php xecho($title); ?>'?</p>
          <div class="form-group text-center">
            <?php echo(getCSRFTokenField() . "\n") ?>
            <button type="submit" name="force_delete_topic" class="btn btn-success btn-lg">Delete</button>
            <button type="submit" name="cancel_delete_topic" class="btn btn-danger btn-lg">Cancel</button>
          </div>
        </form>
      </div>
    <?php endif; ?>

    <div class="container" style="margin-bottom: 50px;">
      <div class="row">
        <div class="col-md-10 col-md-offset-1">

          <!-- Topic creation if allowed -->
          <?php if (hasPermissionTo('create-topic')): ?>
            <a href="topicForm.php" class="btn btn-success">
              <span class="glyphicon glyphicon-plus"></span>
              Create new topic
            </a>
            <hr>
          <?php endif ?>

          <?php if (hasPermissionTo('view-topic-list')): ?>
            
            <?php if (hasPermissionTo('view-semester-selector') ): ?>
              <form class="form" action="<?php xecho(keepQueryServer()) ?>" method="post" enctype="multipart/form-data">
                <?php
                    $sql = "SELECT id, semester FROM semesters";
                    $semesterlist = getMultipleRecords($sql);
                ?>
                <div class="form-group">
                    <label class="control-label">Semester</label>
                    <select class="form-control" name="semester_id">
                      <option value="-1" <?php if ($semester_id == -1) xecho("selected") ?>>All semesters</option>
                      <?php foreach ($semesterlist as $sem): ?>
                        <option value="<?php xecho($sem['id']) ?>" <?php if ($sem['id'] == $semester_id) xecho("selected") ?>><?php xecho($sem['semester']) ?></option>
                      <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group text-center">
                  <?php echo(getCSRFTokenField() . "\n") ?>
                  <button type="submit" name="select_semester" class="btn btn-success">Select semester</button>
                </div>
              </form>
            <?php endif; ?>

            <?php
              $ncol = hasPermissionTo('update-topic') + hasPermissionTo('delete-topic');
              $title = "";
              if (isset($_GET["filter_supervisor"])) {
                $owner_id = filter_input(INPUT_GET, 'filter_supervisor', FILTER_SANITIZE_NUMBER_INT);
                list($topics, $title) = getFilterTopicsBySupervisor($owner_id, $semester_id);
              } else if (isset($_GET["filter_category"])) {
                $category_id = filter_input(INPUT_GET, 'filter_category', FILTER_SANITIZE_NUMBER_INT);
                list($topics, $title) = getFilterTopicsByCategory($category_id, $semester_id);
              } else {
                if (isset($_GET["filter_topic"])) {
                  $filter_type = filter_input(INPUT_GET, 'filter_topic', FILTER_SANITIZE_STRING);
                } else {
                  $filter_type = "all";
                }
                list($topics, $title) = getFilterTopics($filter_type, $semester_id);
              }
            ?>

            <h1 class="text-center"><?php xecho($title); ?></h1>
            <br />

            <?php if (! empty($topics)): ?>
              <table class="table table-bordered">
                <thead>
                  <tr>
                    <th width="2%">#</th>
                    <th>Title</th>
                    <?php if(   (isset($filter_type)) 
                             && (($filter_type == 'approved-user') || ($filter_type == 'registered-user'))): ?>
                      <th width="20%">Student</th>
                    <?php else: ?>
                      <th width="20%">Supervisor</th>
                    <?php endif ?>
                    <?php if(   (isset($filter_type)) 
                             && (($filter_type != 'approved-user') && ($filter_type != 'registered-user'))): ?>
                      <th width="20%">Category</th>
                    <?php endif ?>
                    <th colspan="3" class="text-center" width="23%">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($topics as $key => $value): ?>
                    <?php if ( canViewTopicByID($value['id']) || canUpdateTopicByID( $value['id'] ) || canDeleteTopicByID( $value['id'] ) ): ?>
                      <tr>
                        <td><?php xecho($key + 1); ?></td>
                        <td><?php xecho($value['title']) ?><?php if($value['semester_id'] != 1) {echo(" <font color='ff0000'>(Archived)</font>");} ?></td>
                        <!-- if we are listing approved users -->
                        <?php if( (isset($filter_type)) && ($filter_type == 'approved-user') ): ?>
                          <td>
                            <?php 
                              $sql = "SELECT fullname FROM users WHERE id=? LIMIT 1";
                              $aid = getSingleRecord($sql, 'i', [ $value['approved_user_id'] ]);
                              xecho($aid['fullname']);
                             ?>
                          </td>
                        <!-- if we are listing registered users -->
                        <?php elseif( (isset($filter_type)) && ($filter_type == 'registered-user') ): ?>
                          <td>
                            <?php 
                              $sql = "SELECT fullname FROM users WHERE id=? LIMIT 1";
                              $rid = getSingleRecord($sql, 'i', [ $value['student_id'] ]);
                              xecho($rid['fullname']);
                             ?>
                          </td>
                        <!-- in all other cases we list topics with supervisors -->
                        <?php else : ?>
                          <td>
                              <a 
                                href="<?php xecho(BASE_URL . 'topic/topicFilter.php?filter_supervisor=' . $value['user_id']); ?>"
                                class="btn category">
                                <?php xecho($value['fullname']); ?>
                              </a>
                          </td>
                        <?php endif ?>
                        <!--  -->
                        <?php if(($filter_type != 'approved-user') && ($filter_type != 'registered-user')): ?>
                          <td>
                            <?php if (isset($value['category']['name'])): ?>
                              <a
                                href="<?php xecho(BASE_URL . 'topic/topicFilter.php?filter_category=' . $value['category']['id']); ?>"
                                class="btn category" style="white-space: normal;">
                                <?php xecho($value['category']['name']); ?>
                              </a>
                            <?php endif ?>
                          </td>
                        <?php endif ?>

                        <?php if ( $value['published'] ): ?>
                          <td class="text-center">
                            <a href="<?php xecho(BASE_URL); ?>topic/topicView.php?view_topic=<?php 
                                xecho($value['id']);
                                if( (isset($filter_type)) && ($filter_type == 'registered-user') ) {
                                  xecho("&view_registered_user=" . $value['student_id']);
                                }
                              ?>" class="btn btn-sm <?php if($value['approved_user_id'] != -1) { xecho("btn-primary"); } else { xecho("btn-success"); } ?>">
                              <span class="glyphicon glyphicon-info-sign"></span>
                            </a>
                          </td>
                        <?php else: ?>
                          <td class="text-center"><span class="btn btn-sm glyphicon glyphicon-ban-circle"></span></td>
                        <?php endif ?>

                        <?php if (canUpdateTopicByID( $value['id'] )): ?>
                          <td class="text-center">
                            <a href="<?php xecho(BASE_URL); ?>topic/topicForm.php?edit_topic=<?php xecho($value['id']); ?>" class="btn btn-sm btn-success">
                              <span class="glyphicon glyphicon-pencil"></span>
                            </a>
                          </td>
                        <?php elseif(canUpdateTopicByID( $value['id'], false )): ?>
                          <td class="text-center">
                            <button class="btn btn-sm btn-secondary">
                              <span class="glyphicon glyphicon-pencil"></span>
                            </button>
                          </td>
                        <?php else: ?>
                          <td class="text-center"><span class="btn btn-sm glyphicon glyphicon-ban-circle"></span></td>
                        <?php endif ?>

                        <?php if (canDeleteTopicByID( $value['id'] )): ?>
                          <td class="text-center">
                            <!-- <a href="<?php xecho(BASE_URL); ?>topic/topicFilter.php?delete_topic=<?php xecho($value['id']); ?>" class="btn btn-sm btn-danger"> -->
                            <a href="<?php xecho(addQueryServer("delete_topic", $value['id'])) ?>" class="btn btn-sm btn-danger">
                              <span class="glyphicon glyphicon-trash"></span>
                            </a>
                          </td>
                        <?php elseif(canDeleteTopicByID( $value['id'], false )): ?>
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
              <h2 class="text-center">No topics</h2>
            <?php endif; ?>
          <?php else: ?>
            <h2 class="text-center">No permissions to view topic list</h2>
          <?php endif; ?>
        </div>
      </div>
    </div>
  <?php include_once(INCLUDE_PATH . "/layouts/footer.php") ?>



