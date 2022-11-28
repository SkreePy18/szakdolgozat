<?php include_once(INCLUDE_PATH . '/logic/validation.php') ?>
<?php
  // variable declaration. These variables will be used in the category form
  $name = "";
  $excellence_id = -1;
  $isEditing = false;
  $isDeleting = false;
  $errors = array();

  // ACTION: Save 
  if (isset($_POST['create_excellence_list'])) {  // if user clicked save button ...
    createExcellence();
  }
  // ACTION: Update 
  if (isset($_POST['update_excellence_list'])) { // if user clicked update button ...
    updateExcellence();
  }
  // ACTION: Fetch for editing
  if (isset($_GET["edit_excellence_list"])) {
    editExcellence();
  }
  // ACTION: Delete with confirmation
  if (isset($_GET['delete_excellence_list'])) {
    deleteExcellence();
  }
  // ACTION: Force delete
  if (isset($_POST['force_delete_excellence_list'])) {
    forceDeleteExcellence();
  }

  function getExcellenceList(){
    global $conn;
    $sql = "SELECT * FROM excellence_lists";
    $roles = getMultipleRecords($sql);
    return $roles;
  }

  // Save category to database
  function createExcellence(){
    global $conn, $errors, $name, $isEditing;

    // validate data
    $type_data = filter_input_array(INPUT_POST, [
                       "name"           => FILTER_SANITIZE_STRING,
                       "choose_points"  => FILTER_SANITIZE_STRING,
                      //  "point_types"    => FILTER_SANITIZE_STRING,
                       "choose_users"   => FILTER_SANITIZE_STRING,
                      //  "students"       => FILTER_SANITIZE_STRING,
                       "created_by"     => FILTER_SANITIZE_NUMBER_INT
                     ]);

    // receive all input values from the form
    $name             = $type_data['name'];
    $bPointsSelected  = $type_data['choose_points'];
    // $point_types      = $type_data['point_types'];
    $bUserSelected    = $type_data['choose_users'];
    // $users            = $type_data['students'];
    $created_by       = $type_data['created_by'];

    //Get array values and encode in json
    if(isset($_POST['point_types'])) {
      $point_types = json_encode($_POST['point_types']);
    } else {
      $point_types = "all";
    }

    if(isset($_POST['students'])) {
      $users = json_encode($_POST['students']);
    } else {
      $users = "all";
    }

    if (! hasPermissionTo('manage-excellence-list')) {
      $_SESSION['error_msg'] = "No permissions to create new point type";
      header("location: " . BASE_URL . "admin/points/pointsList.php");
      exit(0);
    }


    $sql = "INSERT INTO `excellence_lists` (name, users, points_type, created_by) VALUES (?, ?, ?, ?)";
    $result = modifyRecord($sql, 'sssi', [$name, $users, $point_types, $created_by]);

     if($result){
        $_SESSION['success_msg'] = "Point type created successfully";
        header("location: " . BASE_URL . "admin/excellence/excellenceList.php");
        exit(0);
      } else {
        $_SESSION['error_msg'] = "Could not create category data";
      }


    // $errors = validateCategory($category_data, ['save_category']);
    // if (count($errors) === 0) {
      

      // // if($result){
      //   $_SESSION['success_msg'] = "Point type created successfully";
      //   header("location: " . BASE_URL . "admin/excellence/excellenceList.php");
      //   exit(0);
      // // } else {
      // //   $_SESSION['error_msg'] = "Could not create category data";
      // // }
    // } else {
      // $_SESSION['error_msg'] = $errors;
    // }
  }

  function updateExcellence() {
    global $conn, $errors, $excellence_id, $name, $isEditing;

    // validate data
    $category_data = filter_input_array(INPUT_POST, [
                  "excellence_id" => FILTER_SANITIZE_NUMBER_INT,
                  "name" => FILTER_SANITIZE_STRING
                 ]);

    // receive all input values from the form
    $excellence_id = $category_data['excellence_id'];
    $type = $category_data['type'];

    // check permission to update the category data
    if (! canUpdateObjectByID('excellence-list', $excellence_id )) {
      $_SESSION['error_msg'] = "No permissions to update excellence list!";
      header("location: " . BASE_URL . "admin/points/pointsList.php");
      exit(0);
    }

    // $errors = validatecategory($category_data, ['update_category']);

    // if (count($errors) === 0) {
      $sql = "UPDATE excellence_lists SET name=? WHERE id=?";
      $result = modifyRecord($sql, 'si', [$type, $excellence_id]);
      if ($result) {
        $_SESSION['success_msg'] = "Excellence list has been successfully updated!";
        if(hasPermissionTo('view-category-list')) {
          header("location: " . BASE_URL . "admin/excellence/excellenceList.php");
        } else {
          header("location: " . BASE_URL . "index.php");
        } 
        exit(0);
      } else {
        $_SESSION['error_msg'] = "Could not update excellence list";
      }
    // } else {
      // $_SESSION['error_msg'] = "Could not update category";
    // }
    $isEditing = true;
  }

  function editExcellence(){
    global $conn, $excellence_id, $name, $isEditing;

    $excellence_id = filter_input(INPUT_GET, 'edit_excellence_list', FILTER_SANITIZE_NUMBER_INT);
    
    if (! canUpdateObjectByID('excellence-list', $excellence_id)) {
      $_SESSION['error_msg'] = "No permissions to edit excellence list!";
      header("location: " . BASE_URL . "admin/excellence/excellenceList.php");
      exit(0);
    }


    $sql = "SELECT * FROM excellence_lists WHERE id=?";
    $type_data = getSingleRecord($sql, 'i', [$excellence_id]);

    $excellence_id = $type_data['id'];
    $name = $type_data['name'];
    $isEditing = true;
  }


  function deleteExcellence() {
    global $conn, $excellence_id, $name, $isDeleting;

    $excellence_id = filter_input(INPUT_GET, 'delete_type', FILTER_SANITIZE_NUMBER_INT);

    if (! canDeleteTypeByID( $excellence_id )) {
      header("location: " . BASE_URL . "admin/excellence/excellenceList.php");
      exit(0);
    }

    $sql = "SELECT * FROM excellence_lists WHERE id=?";
    $type_data = getSingleRecord($sql, 'i', [$excellence_id]);

    $name = $type_data['name'];
    $isDeleting = true;
  }

  function forceDeleteExcellence() {
    global $conn, $excellence_id;

    $excellence_id = filter_input(INPUT_POST, 'excellence_id', FILTER_SANITIZE_NUMBER_INT);
    if (! canDeleteTypeByID( $excellence_id )) {
      header("location: " . BASE_URL . "admin/excellence/excellenceList.php");
      exit(0);
    }

    $sql = "DELETE FROM excellence_lists WHERE id=?";
    $result = modifyRecord($sql, 'i', [$excellence_id]);

    if ($result) {
      $_SESSION['success_msg'] = "Excellence list has been deleted!!";
      header("location: " . BASE_URL . "admin/excellence/excellenceList.php");
      exit(0);
    } else {
      $_SESSION['error_msg'] = "Could not delete this excellence list";
    }
  }

