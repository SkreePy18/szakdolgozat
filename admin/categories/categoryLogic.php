<?php include_once(INCLUDE_PATH . '/logic/validation.php') ?>
<?php
  // variable declaration. These variables will be used in the category form
  $category_id = 0;
  $category = "";
  $isEditing = false;
  $isDeleting = false;
  $errors = array();

  // ACTION: Save 
  if (isset($_POST['save_category'])) {  // if user clicked save button ...
      saveCategory();
  }
  // ACTION: Update 
  if (isset($_POST['update_category'])) { // if user clicked update button ...
      updateCategory();
  }
  // ACTION: Fetch for editing
  if (isset($_GET["edit_category"])) {
    editCategory();
  }
  // ACTION: Delete with confirmation
  if (isset($_GET['delete_category'])) {
    deleteCategory();
  }
  // ACTION: Force delete
  if (isset($_POST['force_delete_category'])) {
    forcedeleteCategory();
  }

  function getCategories(){
    global $conn;
    $sql = "SELECT * FROM categories";
    $roles = getMultipleRecords($sql);
    return $roles;
  }

  // Save category to database
  function saveCategory(){
    global $conn, $errors, $category, $isEditing;

    // validate data
    $category_data = filter_input_array(INPUT_POST, [
                       "category" => FILTER_SANITIZE_STRING,
                     ]);

    // receive all input values from the form
    $category = $category_data['category'];

    if (! canCreatecategory( )) {
      $_SESSION['error_msg'] = "No permissions to create category";
      header("location: " . BASE_URL . "admin/categories/categoryList.php");
      exit(0);
    }

    $errors = validateCategory($category_data, ['save_category']);
    if (count($errors) === 0) {
      $sql = "INSERT INTO categories SET name=?";
      $result = modifyRecord($sql, 's', [$category]);

      if($result){
        $_SESSION['success_msg'] = "category created successfully";
        header("location: " . BASE_URL . "admin/categories/categoryList.php");
        exit(0);
      } else {
        $_SESSION['error_msg'] = "Could not create category data";
      }
    } else {
      $_SESSION['error_msg'] = $errors;
    }
  }

  function updateCategory() {
    global $conn, $errors, $category_id, $category, $isEditing;

    // validate data
    $category_data = filter_input_array(INPUT_POST, [
                  "category_id" => FILTER_SANITIZE_NUMBER_INT,
                  "category" => FILTER_SANITIZE_STRING
                 ]);

    // receive all input values from the form
    $category_id = $category_data['category_id'];
    $category = $category_data['category'];

    // check permission to update the category data
    if (! canUpdateCategoryByID( $category_id )) {
      $_SESSION['error_msg'] = "No permissions to update category";
      header("location: " . BASE_URL . "admin/categories/categoryList.php");
      exit(0);
    }

    $errors = validatecategory($category_data, ['update_category']);

    if (count($errors) === 0) {
      $sql = "UPDATE categories SET name=? WHERE id=?";
      $result = modifyRecord($sql, 'si', [$category, $category_id]);
      if ($result) {
        $_SESSION['success_msg'] = "category successfully updated";
        if(canViewcategoryList()) {
          header("location: " . BASE_URL . "admin/categories/categoryList.php");
        } else {
          header("location: " . BASE_URL . "index.php");
        } 
        exit(0);
      } else {
        $_SESSION['error_msg'] = "Could not update category data";
      }
    } else {
      $_SESSION['error_msg'] = "Could not update category";
    }
    $isEditing = true;
  }

  function editCategory(){
    global $conn, $category_id, $category, $isEditing;

    $category_id = filter_input(INPUT_GET, 'edit_category', FILTER_SANITIZE_NUMBER_INT);
    
    if (! canUpdateCategoryByID( $category_id )) {
      $_SESSION['error_msg'] = "No permissions to edit category";
      header("location: " . BASE_URL . "admin/categories/categoryList.php");
      exit(0);
    }


    $sql = "SELECT * FROM categories WHERE id=?";
    $category_data = getSingleRecord($sql, 'i', [$category_id]);

    $category = $category_data['name'];
    $isEditing = true;
  }


  function deleteCategory() {
    global $conn, $category_id, $category, $isDeleting;

    $category_id = filter_input(INPUT_GET, 'delete_category', FILTER_SANITIZE_NUMBER_INT);

    if (! canDeletecategoryByID( $category_id )) {
      header("location: " . BASE_URL . "admin/categories/categoryList.php");
      exit(0);
    }

    $sql = "SELECT * FROM categories WHERE id=?";
    $category_data = getSingleRecord($sql, 'i', [$category_id]);

    $category = $category_data['name'];
    $isDeleting = true;
  }

  function forcedeleteCategory() {
    global $conn, $category_id;

    $category_id = filter_input(INPUT_POST, 'category_id', FILTER_SANITIZE_NUMBER_INT);
    if (! canDeletecategoryByID( $category_id )) {
      header("location: " . BASE_URL . "admin/categories/categoryList.php");
      exit(0);
    }

    $sql = "DELETE FROM categories WHERE id=?";
    $result = modifyRecord($sql, 'i', [$category_id]);

    if ($result) {
      $_SESSION['success_msg'] = "Category has been deleted!!";
      header("location: " . BASE_URL . "admin/categories/categoryList.php");
      exit(0);
    } else {
      $_SESSION['error_msg'] = "Could not delete this category";
    }
  }

