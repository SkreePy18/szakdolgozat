<?php include_once('../config.php'); ?>
<?php include_once(ROOT_PATH . '/csrf.php') ?>
<?php include_once(ROOT_PATH . '/topic/topicLogic.php'); ?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title><?php xecho(APP_NAME); ?> - Create topic</title>
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

          <?php if (canViewTopicList()): ?>
            <a href="topicFilter.php?filter_topic=all" class="btn btn-primary" style="margin-bottom: 5px;">
              <span class="glyphicon glyphicon-chevron-left"></span>
              Topics
            </a>
            <hr>
          <?php endif; ?>

          <?php if (canUpdateTopicByID( $topic_id ) || canCreateTopic() ): ?>
            <?php if ($isEditing === true ): ?>
              <h2 class="text-center">Update topic</h2>
            <?php else: ?>
              <h2 class="text-center">Create topic</h2>
            <?php endif; ?>

            <form class="form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post" enctype="multipart/form-data">
              <!-- if editting topic, we need that topic's id -->
              <?php if ($isEditing === true): ?>
                <input type="hidden" name="topic_id" value="<?php xecho($topic_id); ?>">
              <?php endif; ?>

              <?php if (canAssignTopicSemester() ): ?>
                <?php
                    $sql = "SELECT id, semester FROM semesters";
                    $semesterlist = getMultipleRecords($sql);
                ?>
                <div class="form-group">
                    <label class="control-label">Semester</label>
                    <select class="form-control" name="semester_id">
                      <?php foreach ($semesterlist as $sem): ?>
                        <option value="<?php xecho($sem['id']) ?>" <?php if ($sem['id'] == $semester_id) xecho("selected") ?>><?php xecho($sem['semester']) ?></option>
                      <?php endforeach; ?>
                    </select>
                </div>
              <?php else: ?>
                <?php if ($isEditing === true ): ?>
                  <div class="form-group" >
                    <input type="hidden" name="semester_id" value="<?php xecho($semester_id) ?>">
                  </div>
                <?php endif; ?>
              <?php endif; ?>

              <?php if ( canOwnTopicUser() ): ?>     
                <!-- With this permission user can select the supervisor for the topic -->
                <?php 
                  $sql = "SELECT id, fullname, neptuncode FROM users WHERE role_id=?";
                  $supervisors = getMultipleRecords($sql, "i", [ getSupervisorRoleID() ] );
                ?>              
                <div class="form-group <?php xecho(isset($errors['topic_user_id']) ? 'has-error' : ''); ?>">
                  <label class="control-label">Supervisor</label>
                  <select class="form-control" name="topic_user_id">
                    <?php foreach ($supervisors as $super): ?>
                      <option value="<?php xecho($super['id']) ?>" <?php if ($super['id'] == $topic_user_id) xecho("selected") ?>><?php xecho($super['fullname']) ?></option>
                    <?php endforeach; ?>
                  </select>
                  <?php if (isset($errors['topic_user_id'])): ?>
                    <span class="help-block"><?php xecho($errors['topic_user_id']); ?></span>
                  <?php endif; ?>
                </div>
              <?php else: ?>
                <!-- no supervisor selection permission, then display the supervisor when updating -->
                <?php if ($isEditing === true ): ?>
                  <?php 
                    $sql = "SELECT fullname FROM users WHERE id=?";
                    $topic_user = getSingleRecord($sql, 'i', [$topic_user_id]);
                  ?>
                  <div class="form-group" >
                    <label class="control-label">Supervisor</label>
                    <input type="hidden" name="topic_user_id" value="<?php xecho($topic_user_id) ?>">
                    <input type="text" name="topic_user" value="<?php if(isset($topic_user)) { xecho($topic_user['fullname']); } ?>" class="form-control" disabled>
                  </div>
                <?php endif; ?>
              <?php endif; ?>

              <div class="form-group <?php xecho(isset($errors['title']) ? 'has-error' : '') ?>">
                <label class="control-label">Title</label>
                <input type="text" name="title" value="<?php xecho($title); ?>" class="form-control">
                <?php if (isset($errors['title'])): ?>
                  <span class="help-block"><?php xecho($errors['title']); ?></span>
                <?php endif; ?>
              </div>
              <div class="form-group <?php xecho(isset($errors['description']) ? 'has-error' : '') ?>">
                <label class="control-label">Description</label>
                <textarea type="text" name="description" cols="30" rows="10" class="form-control"><?php xecho($description); ?></textarea>
                <?php if (isset($errors['description'])): ?>
                  <span class="help-block"><?php xecho($errors['description']); ?></span>
                <?php endif; ?>
              </div>
              <div class="form-group <?php xecho(isset($errors['requirement']) ? 'has-error' : '') ?>">
                <label class="control-label">Requirements</label>
                <textarea type="text" name="requirement" cols="30" rows="3" class="form-control"><?php xecho($requirement); ?></textarea>
                <?php if (isset($errors['requirement'])): ?>
                  <span class="help-block"><?php xecho($errors['requirement']); ?></span>
                <?php endif; ?>
              </div>


              <?php 
                $categories = getAllCategories(); 
              ?>
              <div class="form-group <?php xecho(isset($errors['category_id']) ? 'has-error' : '') ?>">
                <label class="control-label">Category</label>
                <select class="form-control" name="category_id">
                  <option value="" ></option>
                  <?php foreach ($categories as $cat): ?>
                      <option value="<?php xecho($cat['id']) ?>" <?php if ($cat['id'] == $category_id) xecho("selected"); ?>><?php xecho($cat['name']) ?></option>
                  <?php endforeach; ?>
                </select>
                <?php if (isset($errors['category_id'])): ?>
                  <span class="help-block"><?php xecho($errors['category_id']); ?></span>
                <?php endif; ?>
              </div>

              <?php if ( canPublishTopic() ): ?>
                <?php if ($published == true): ?>
                  <label for="published">
                    Publish
                    <input type="checkbox" name="published" checked onclick="return confirm('Are you sure?\nThis will hide the topic and delete all registrations and the approved user for this topic!')">
                  </label>
                <?php else: ?>
                  <label for="published">
                    Publish
                    <input type="checkbox" name="published">
                  </label>
                <?php endif ?>
              <?php endif ?>

              <div class="form-group">
                <?php echo(getCSRFTokenField() . "\n") ?>
                <?php if ($isEditing === true): ?>
                  <button type="submit" name="update_topic" class="btn btn-success btn-block btn-lg">Update topic</button>
                <?php else: ?>
                  <button type="submit" name="save_topic" class="btn btn-success btn-block btn-lg">Save topic</button>
                <?php endif; ?>
              </div>
            </form>
          <?php else: ?>
            <h2 class="text-center">No permissions to update or create topic</h2>
          <?php endif ?>
        </div>
      </div>
    </div>
  <?php include_once(INCLUDE_PATH . "/layouts/footer.php") ?>



