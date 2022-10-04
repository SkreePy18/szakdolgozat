<?php include_once('config.php'); ?>
<?php include_once(ROOT_PATH . '/csrf.php') ?>
<?php include_once(INCLUDE_PATH . '/logic/userSignup.php'); ?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title><?php xecho(APP_NAME); ?> - Sign up</title>
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css" />
  <!-- Custom styles -->
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <?php include_once(INCLUDE_PATH . "/layouts/navbar.php") ?>

  <div class="container" style="margin-bottom: 50px;">
    <div class="row">
      <div class="col-md-4 col-md-offset-4">
        <form class="form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post" enctype="multipart/form-data">
          <h2 class="text-center">Sign up</h2>
          <hr>

          <div class="form-group <?php xecho(isset($errors['username']) ? 'has-error' : ''); ?>">
            <label class="control-label">Username</label>
            <input type="text" name="username" value="<?php xecho($username); ?>" class="form-control">
            <?php if (isset($errors['username'])): ?>
              <span class="help-block"><?php xecho($errors['username']); ?></span>
            <?php endif; ?>
          </div>
          <div class="form-group <?php xecho(isset($errors['fullname']) ? 'has-error' : ''); ?>">
            <label class="control-label">Full name</label>
            <input type="text" name="fullname" value="<?php xecho($fullname); ?>" class="form-control">
            <?php if (isset($errors['fullname'])): ?>
              <span class="help-block"><?php xecho($errors['fullname']); ?></span>
            <?php endif; ?>
          </div>
          <div class="form-group <?php xecho(isset($errors['neptuncode']) ? 'has-error' : ''); ?>">
            <label class="control-label">Neptun code</label>
            <input type="text" name="neptuncode" value="<?php xecho($neptuncode); ?>" class="form-control">
            <?php if (isset($errors['neptuncode'])): ?>
              <span class="help-block"><?php xecho($errors['neptuncode']); ?></span>
            <?php endif; ?>
          </div>
          <div class="form-group <?php xecho(isset($errors['email']) ? 'has-error' : ''); ?>">
            <label class="control-label">Email Address</label>
            <input type="email" name="email" value="<?php xecho($email); ?>" class="form-control">
            <?php if (isset($errors['email'])): ?>
              <span class="help-block"><?php xecho($errors['email']); ?></span>
            <?php endif; ?>
          </div>
          <div class="form-group <?php xecho(isset($errors['password']) ? 'has-error' : ''); ?>">
            <label class="control-label">Password</label>
            <input type="password" name="password" class="form-control">
            <?php if (isset($errors['password'])): ?>
              <span class="help-block"><?php xecho($errors['password']); ?></span>
            <?php endif; ?>
          </div>
          <div class="form-group <?php xecho(isset($errors['passwordConf']) ? 'has-error' : ''); ?>">
            <label class="control-label">Password confirmation</label>
            <input type="password" name="passwordConf" class="form-control">
            <?php if (isset($errors['passwordConf'])): ?>
              <span class="help-block"><?php xecho($errors['passwordConf']); ?></span>
            <?php endif; ?>
          </div>

          <div class="form-group">
            <?php echo(getCSRFTokenField() . "\n") ?>
            <button type="submit" name="signup_btn" class="btn btn-success btn-block">Sign up</button>
          </div>
          <p>Already have an account? <a href="login.php">Login</a></p>
        </form>
      </div>
    </div>
  </div>
<?php include_once(INCLUDE_PATH . "/layouts/footer.php") ?>
