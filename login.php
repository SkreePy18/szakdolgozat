<?php include_once('config.php'); ?>
<?php include_once(ROOT_PATH . '/csrf.php') ?>
<?php include_once(INCLUDE_PATH . '/logic/userSignup.php'); ?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title><?php xecho(APP_NAME) ?> - Login</title>
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css" />
  <!-- Custome styles -->
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <?php include_once(INCLUDE_PATH . "/layouts/navbar.php") ?>
  <div class="container" style="margin-bottom: 50px;">
    <div class="row">
      <div class="col-md-4 col-md-offset-4">
        <form class="form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
          <h2 class="text-center">Login</h2>
          <hr>
          <!-- display form error messages  -->
          <?php include_once(INCLUDE_PATH . "/layouts/messages.php") ?>
          <div class="form-group <?php xecho(isset($errors['username']) ? 'has-error' : ''); ?>">
            <label class="control-label">Username or Email</label>
            <input type="text" name="username" id="password" value="<?php xecho($username); ?>" class="form-control">
            <?php if (isset($errors['username'])): ?>
              <span class="help-block"><?php xecho($errors['username']) ?></span>
            <?php endif; ?>
          </div>
          <div class="form-group <?php xecho(isset($errors['password']) ? 'has-error' : ''); ?>">
            <label class="control-label">Password</label>
            <input type="password" name="password" id="password" class="form-control">
            <?php if (isset($errors['password'])): ?>
              <span class="help-block"><?php xecho($errors['password']); ?></span>
            <?php endif; ?>
          </div>
          <div class="form-group">
            <?php echo(getCSRFTokenField() . "\n") ?>
            <button type="submit" name="login_btn" class="btn btn-success">Login</button>
          </div>
          <p>Don't have an account? <a href="signup.php">Sign up</a></p>
        </form>
      </div>
    </div>
  </div>
<?php include_once(INCLUDE_PATH . "/layouts/footer.php") ?>
