<?php include_once('../../config.php'); ?>
<?php include_once(ROOT_PATH . '/csrf.php') ?>
<?php include_once(ROOT_PATH . '/admin/categories/categoryLogic.php'); ?>

<?php 
  $roles = getCategories(); 
?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title><?php xecho(APP_NAME); ?> - category</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css" />
    <!-- Custom styles -->
    <link rel="stylesheet" href="../../assets/css/style.css">
  </head>
  <body>
    <?php include_once(INCLUDE_PATH . "/layouts/navbar.php") ?>
    <div class="container" style="margin-bottom: 50px;">
      <div class="row">
        <div class="col-md-6 col-md-offset-3">

          <?php if (hasPermissionTo('view-category-list')): ?>
            <a href="categoryList.php" class="btn btn-primary" style="margin-bottom: 5px;">
              <span class="glyphicon glyphicon-chevron-left"></span>
              categorys
            </a>
            <hr>
          <?php endif; ?>

          <?php if (canUpdateObjectByID('category', $category_id) || hasPermissionTo('create-category') ): ?>

            <?php if ($isEditing === true ): ?>
              <h2 class="text-center">Update category</h2>
            <?php else: ?>
              <h2 class="text-center">Create category</h2>
            <?php endif; ?>

            <form class="form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post" enctype="multipart/form-data">
              <!-- if editting category, we need that category's id -->
              <?php if ($isEditing === true): ?>
                <input type="hidden" name="category_id" value="<?php xecho($category_id); ?>">
              <?php endif; ?>
              
              <div class="form-group <?php xecho(isset($errors['category']) ? 'has-error' : ''); ?>">
                <label class="control-label">category</label>
                <input type="text" name="category" value="<?php xecho($category); ?>" class="form-control">
                <?php if (isset($errors['category'])): ?>
                  <span class="help-block"><?php xecho($errors['category']); ?></span>
                <?php endif; ?>
              </div>

              <div class="form-group">
                <?php echo(getCSRFTokenField() . "\n") ?>
                <?php if ($isEditing === true): ?>
                  <button type="submit" name="update_category" class="btn btn-success btn-block btn-lg">Update category</button>
                <?php else: ?>
                  <button type="submit" name="save_category" class="btn btn-danger btn-block btn-lg">Save category</button>
                <?php endif; ?>
              </div>
            </form>
          <?php else: ?>
            <h2 class="text-center">No permissions to view category</h2>
          <?php endif; ?>
        </div>
      </div>
    </div>
  <?php include_once(INCLUDE_PATH . "/layouts/footer.php") ?>

