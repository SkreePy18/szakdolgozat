<?php include_once(INCLUDE_PATH . '/logic/validation.php') ?>
<?php
  // variable declaration. These variables will be used in the category form
  $type = "";
  $type_id = -1;
  $isEditing = false;
  $isDeleting = false;
  $errors = array();

  // ACTION: Save 
  if (isset($_POST['create_points_type'])) {  // if user clicked save button ...
      createType();
  }
  // ACTION: Update 
  if (isset($_POST['update_points_type'])) { // if user clicked update button ...
    updateType();
  }
  // ACTION: Fetch for editing
  if (isset($_GET["edit_type"])) {
    editType();
  }
  // ACTION: Delete with confirmation
  if (isset($_GET['delete_type'])) {
    deleteType();
  }
  // ACTION: Force delete
  if (isset($_POST['force_delete_type'])) {
    forceDeleteType();
  }

  function getTypesOfPoints(){
    global $conn;
    $sql = "SELECT * FROM opportunity_points_type";
    $roles = getMultipleRecords($sql);
    return $roles;
  }

  // Save category to database
  function createType(){
    global $conn, $errors, $type, $isEditing;

    // validate data
    $type_data = filter_input_array(INPUT_POST, [
                       "type" => FILTER_SANITIZE_STRING,
                     ]);

    // receive all input values from the form
    $type = $type_data['type'];

    if (! hasPermissionTo('create-point-type')) {
      $_SESSION['error_msg'] = "No permissions to create new point type";
      header("location: " . BASE_URL . "admin/points/pointsList.php");
      exit(0);
    }

    // $errors = validateCategory($category_data, ['save_category']);
    // if (count($errors) === 0) {
      $sql = "INSERT INTO opportunity_points_type SET name=?";
      $result = modifyRecord($sql, 's', [$type]);

      if($result){
        $_SESSION['success_msg'] = "Point type created successfully";
        header("location: " . BASE_URL . "admin/points/pointsList.php");
        exit(0);
      } else {
        $_SESSION['error_msg'] = "Could not create category data";
      }
    // } else {
      // $_SESSION['error_msg'] = $errors;
    // }
  }

  function updateType() {
    global $conn, $errors, $type_id, $type, $isEditing;

    // validate data
    $category_data = filter_input_array(INPUT_POST, [
                  "type_id" => FILTER_SANITIZE_NUMBER_INT,
                  "type" => FILTER_SANITIZE_STRING
                 ]);

    // receive all input values from the form
    $type_id = $category_data['type_id'];
    $type = $category_data['type'];

    // check permission to update the category data
    if (! canUpdateObjectByID('point-type', $type_id )) {
      $_SESSION['error_msg'] = "No permissions to update point type!";
      header("location: " . BASE_URL . "admin/points/pointsList.php");
      exit(0);
    }

    // $errors = validatecategory($category_data, ['update_category']);

    // if (count($errors) === 0) {
      $sql = "UPDATE opportunity_points_type SET name=? WHERE id=?";
      $result = modifyRecord($sql, 'si', [$type, $type_id]);
      if ($result) {
        $_SESSION['success_msg'] = "Point type has been successfully updated!";
        if(hasPermissionTo('view-category-list')) {
          header("location: " . BASE_URL . "admin/points/pointsList.php");
        } else {
          header("location: " . BASE_URL . "index.php");
        } 
        exit(0);
      } else {
        $_SESSION['error_msg'] = "Could not update category data";
      }
    // } else {
      // $_SESSION['error_msg'] = "Could not update category";
    // }
    $isEditing = true;
  }

  function editType(){
    global $conn, $type_id, $type, $isEditing;

    $type_id = filter_input(INPUT_GET, 'edit_type', FILTER_SANITIZE_NUMBER_INT);
    
    if (! canUpdateObjectByID('point-type', $type_id)) {
      $_SESSION['error_msg'] = "No permissions to edit point type!";
      header("location: " . BASE_URL . "admin/points/pointsList.php");
      exit(0);
    }


    $sql = "SELECT * FROM opportunity_points_type WHERE id=?";
    $type_data = getSingleRecord($sql, 'i', [$type_id]);

    $type_id = $type_data['id'];
    $isEditing = true;
  }


  function deleteType() {
    global $conn, $type_id, $type, $isDeleting;

    $type_id = filter_input(INPUT_GET, 'delete_type', FILTER_SANITIZE_NUMBER_INT);

    if (! canDeleteTypeByID( $type_id )) {
      header("location: " . BASE_URL . "admin/points/pointsList.php");
      exit(0);
    }

    $sql = "SELECT * FROM opportunity_points_type WHERE id=?";
    $type_data = getSingleRecord($sql, 'i', [$type_id]);

    $type = $type_data['name'];
    $isDeleting = true;
  }

  function forceDeleteType() {
    global $conn, $type_id;

    $type_id = filter_input(INPUT_POST, 'type_id', FILTER_SANITIZE_NUMBER_INT);
    if (! canDeleteTypeByID( $type_id )) {
      header("location: " . BASE_URL . "admin/points/pointsList.php");
      exit(0);
    }

    $sql = "DELETE FROM opportunity_points_type WHERE id=?";
    $result = modifyRecord($sql, 'i', [$type_id]);

    if ($result) {
      $_SESSION['success_msg'] = "Category has been deleted!!";
      header("location: " . BASE_URL . "admin/points/pointsList.php");
      exit(0);
    } else {
      $_SESSION['error_msg'] = "Could not delete this category";
    }
  }

