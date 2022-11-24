<!-- closing container div can be found in the footer -->

<div class="container">
  <?php if (isset($_SESSION['user']) && hasPermissionTo('view-dashboard')): ?>
  <nav class="navbar navbar-inverse">
  <?php else: ?>
  <nav class="navbar navbar-default">
  <?php endif; ?>
    <div class="container-fluid">
      <div class="navbar-header">
        <a class="navbar-brand" href="<?php xecho(BASE_URL . 'index.php'); ?>"><?php xecho(APP_NAME); ?></a>        
      </div>
      <ul class="nav navbar-nav navbar-right">
        <?php if (isset($_SESSION['user'])): ?>
          <li><label id="mytimer" class="navbar-text">-00:00:00</label></li>
          <?php echo('<script src="'.BASE_URL.'assets/js/timer.js" type="text/javascript"></script>'); ?>
          <script type="text/javascript">
            topic_start_timer(<?php echo(-$timeout) ?>, 'You have been logged out due to inactivity!', "<?php echo(BASE_URL.'logout.php') ?>" );
          </script>

          <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
              <?php xecho($_SESSION['user']['username'] . ' (' . $_SESSION['user']['role'] . ')'); ?> <span class="caret"></span></a>

              <ul class="dropdown-menu">
                <?php if (hasPermissionTo('view-profile')): ?>
                  <li><a href="<?php xecho(BASE_URL . 'admin/users/userForm.php?edit_user=' . $_SESSION['user']['id']); ?>">Profile</a></li>
                <?php endif; ?>
                <?php if (hasPermissionTo('view-dashboard')): ?>
                  <li><a href="<?php xecho(BASE_URL . 'admin/dashboard.php'); ?>" style="color: red;">Dashboard</a></li>
                <?php endif; ?>
                <li role="separator" class="divider"></li>
                <li><a href="<?php xecho(BASE_URL . 'excellence/excellenceFilter.php?id=1'); ?>" >Excellence list</a></li>
                <?php if (hasPermissionTo('view-topic-list')): ?>
                  <li><a href="<?php xecho(BASE_URL . 'topic/topicFilter.php?filter_topic=all'); ?>" >All topics</a></li>
                  <?php if (hasPermissionTo('view-topic-summary')): ?>
                      <li><a href="<?php xecho(BASE_URL . 'topic/topicFilter.php?filter_topic=registered-user'); ?>" >All topics with registered user</a></li>
                      <li><a href="<?php xecho(BASE_URL . 'topic/topicFilter.php?filter_topic=approved-user'); ?>" >All topics with approved user</a></li>
                  <?php else: ?>
                    <?php if (hasPermissionTo('create-topic')): ?>
                      <li><a href="<?php xecho(BASE_URL . 'topic/topicFilter.php?filter_topic=owned'); ?>" >My created topics</a></li>
                      <li><a href="<?php xecho(BASE_URL . 'topic/topicFilter.php?filter_topic=registered-user'); ?>" >My topics with registered user</a></li>
                      <li><a href="<?php xecho(BASE_URL . 'topic/topicFilter.php?filter_topic=approved-user'); ?>" >My topics with approved user</a></li>
                    <?php endif; ?>
                  <?php endif; ?>
                  <?php if (hasPermissionTo('register-topic')): ?>
                    <?php if ( hasUserApprovedTopic($_SESSION['user']['id']) ): ?>
                      <li><a href="<?php xecho(BASE_URL . 'topic/topicFilter.php?filter_topic=approved'); ?>" >My approved topic</a></li>
                    <?php else: ?>
                      <li><a href="<?php xecho(BASE_URL . 'topic/topicFilter.php?filter_topic=registered'); ?>" >My registered topics</a></li>
                    <?php endif; ?>
                  <?php endif; ?>

                  <?php if (hasPermissionTo('view-own-topic-score')): ?>
                    <li><a href="<?php xecho(BASE_URL . 'topic/topicScoreOwn.php'); ?>" >My scores for topics</a></li>
                  <?php endif; ?>
                  <?php if (hasPermissionTo('view-all-topic-score')): ?>
                    <li><a href="<?php xecho(BASE_URL . 'topic/topicScoreAll.php'); ?>" >All scores for all topics</a></li>
                  <?php endif; ?>

                <?php endif; ?>

                <!-- Opportunities -->
                <?php if (hasPermissionTo('view-opportunity-list')): ?>
                  <li><a href="<?php xecho(BASE_URL . 'opportunities/opportunityFilter.php'); ?>" >Opportunities</a></li>
                <?php endif; ?>

                <!-- Redeem token -->

                <?php if (hasPermissionTo('view-opportunity-list')): ?>
                  <li><a href="<?php xecho(BASE_URL . 'tokens/redeemToken.php'); ?>" >Redeem token</a></li>
                <?php endif; ?>

                <!-- Logout -->
                <li><a href="<?php xecho(BASE_URL . 'logout.php'); ?>" style="color: red;">Logout</a></li>
              </ul>
          </li>
        <?php else: ?>
          <li><a href="<?php xecho(BASE_URL . 'signup.php') ?>"><span class="glyphicon glyphicon-user"></span> Sign Up</a></li>
          <li><a href="<?php xecho(BASE_URL . 'login.php') ?>"><span class="glyphicon glyphicon-log-in"></span> Login</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </nav>
  <?php include_once(INCLUDE_PATH . "/layouts/messages.php") ?>

