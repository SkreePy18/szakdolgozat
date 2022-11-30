<?php include_once('../config.php'); ?>
<?php include_once(ROOT_PATH . '/csrf.php') ?> 
<?php include_once(ROOT_PATH . '/excellence/excellenceLogic.php'); ?>
<?php $excellence_list = getExcellenceListTypes(); 

$excellence_list_id = $_GET['id'];

?>
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

    <div class="container" style="margin-bottom: 50px;">
      <div class="row">
        <div class="col-md-10 col-md-offset-1">
        <form class="form" action="<?php xecho(keepQueryServer()) ?>" method="post" enctype="multipart/form-data">
         <div class="form-group">
             <label class="control-label">Excellence list</label>
             <select class="form-control" name="excellence_id">
                 <?php
                     $sql = "SELECT id, name FROM excellence_lists";
                     $excellence_selector = getMultipleRecords($sql);
                 ?>
               <?php foreach ($excellence_selector as $excellence): ?>
                  <?php echo($excellence_list_id); ?>
                 <option value="<?php xecho($excellence['id']) ?>" <?php if ($excellence['id'] == $excellence_list_id) xecho("selected") ?>><?php xecho($excellence['name']) ?></option>
               <?php endforeach; ?>
             </select>
            </div>
             <div class="form-group text-center">
               <?php echo(getCSRFTokenField() . "\n") ?>
               <button type="submit" name="select_excellence_list" class="btn btn-success">Select excellence list</button>
             </div>
          <?php 
            $excellence_list = getExcellenceListOfType();
          ?>
          </form>
          <h1 class='text-center'> Excellence list </h1>
            <?php if (! empty($excellence_list)): ?>
              <table class="table table-bordered">
                <thead>
                  <tr>
                    <th width="2%"> # </th>
                    <th> Neptun </th>
                    <th colspan="3" class="text-center" width="23%"> Total points </th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($excellence_list as $key => $value): ?>
                      <tr>
                        <td><?php xecho($key + 1); ?></td>
                        <?php $url = "topic/topicView.php?view_topic="; ?>
                        <td><?php xecho($value['neptuncode']); ?> </td>
                        <td><?php xecho($value['totalPoints']); ?> </td>
                      </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            <?php else: ?>
              <h2 class="text-center">No topics</h2>
            <?php endif; ?>
        </div>
      </div>
    </div>
  <?php include_once(INCLUDE_PATH . "/layouts/footer.php") ?>



