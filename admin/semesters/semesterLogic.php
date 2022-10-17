<?php include_once(INCLUDE_PATH . '/logic/validation.php') ?>
<?php
  // variable declaration. These variables will be used in the semester form
  $semester_id = 0;
  $semester = "";
  $isEditing = false;
  $isDeleting = false;
  $errors = array();

  // ACTION: Save 
  if (isset($_POST['save_semester'])) {  // if user clicked save button ...
      saveSemester();
  }
  // ACTION: Update 
  if (isset($_POST['update_semester'])) { // if user clicked update button ...
      updateSemester();
  }
  // ACTION: Fetch for editing
  if (isset($_GET["edit_semester"])) {
    editSemester();
  }
  // ACTION: Delete with confirmation
  if (isset($_GET['delete_semester'])) {
    deleteSemester();
  }
  // ACTION: Force delete
  if (isset($_POST['force_delete_semester'])) {
    forcedeleteSemester();
  }

  function getAllSemesters(){
    global $conn;
    $sql = "SELECT id, semester FROM semesters";
    $roles = getMultipleRecords($sql);
    return $roles;
  }

  // Save semester to database
  function saveSemester(){
    global $conn, $errors, $semester, $isEditing;

    // validate data
    $semester_data = filter_input_array(INPUT_POST, [
                       "semester" => FILTER_SANITIZE_STRING,
                     ]);

    // receive all input values from the form
    $semester = $semester_data['semester'];

    if (! hasPermissionTo('create-semester')) {
      $_SESSION['error_msg'] = "No permissions to create semester";
      header("location: " . BASE_URL . "admin/semesters/semesterList.php");
      exit(0);
    }

    $errors = validateSemester($semester_data, ['save_semester']);
    if (count($errors) === 0) {
      $sql = "INSERT INTO semesters SET semester=?";
      $result = modifyRecord($sql, 's', [$semester]);

      if($result){
        $_SESSION['success_msg'] = "Semester created successfully";
        header("location: " . BASE_URL . "admin/semesters/semesterList.php");
        exit(0);
      } else {
        $_SESSION['error_msg'] = "Could not create semester data";
      }
    } else {
      $_SESSION['error_msg'] = $errors;
    }
  }

  function updateSemester() {
    global $conn, $errors, $semester_id, $semester, $isEditing;

    // validate data
    $semester_data = filter_input_array(INPUT_POST, [
                  "semester_id" => FILTER_SANITIZE_NUMBER_INT,
                  "semester" => FILTER_SANITIZE_STRING
                 ]);

    // receive all input values from the form
    $semester_id = $semester_data['semester_id'];
    $semester = $semester_data['semester'];

    // check permission to update the semester data
    if (! canUpdateObjectByID('semester', $semester_id )) {
      $_SESSION['error_msg'] = "No permissions to update semester";
      header("location: " . BASE_URL . "admin/semesters/semesterList.php");
      exit(0);
    }

    $errors = validateSemester($semester_data, ['update_semester']);

    if (count($errors) === 0) {
      $sql = "UPDATE semesters SET semester=? WHERE id=?";
      $result = modifyRecord($sql, 'si', [$semester, $semester_id]);
      if ($result) {
        $_SESSION['success_msg'] = "Semester successfully updated";
        if(hasPermissionTo('view-semester-list')) {
          header("location: " . BASE_URL . "admin/semesters/semesterList.php");
        } else {
          header("location: " . BASE_URL . "index.php");
        } 
        exit(0);
      } else {
        $_SESSION['error_msg'] = "Could not update semester data";
      }
    } else {
      $_SESSION['error_msg'] = "Could not update semester";
    }
    $isEditing = true;
  }

  function editSemester(){
    global $conn, $semester_id, $semester, $isEditing;

    $semester_id = filter_input(INPUT_GET, 'edit_semester', FILTER_SANITIZE_NUMBER_INT);

    if (! canUpdateObjectByID('semester', $semester_id)) {
      $_SESSION['error_msg'] = "No permissions to edit semester";
      header("location: " . BASE_URL . "admin/semesters/semesterList.php");
      exit(0);
    }

    $sql = "SELECT * FROM semesters WHERE id=?";
    $semester_data = getSingleRecord($sql, 'i', [$semester_id]);

    $semester = $semester_data['semester'];
    $isEditing = true;
  }


  function deleteSemester() {
    global $conn, $semester_id, $semester, $isDeleting;

    $semester_id = filter_input(INPUT_GET, 'delete_semester', FILTER_SANITIZE_NUMBER_INT);

    if (! canDeleteSemesterByID( $semester_id )) {
      // $_SESSION['error_msg'] = "No permissions to delete semester";
      header("location: " . BASE_URL . "admin/semesters/semesterList.php");
      exit(0);
    }

    $sql = "SELECT * FROM semesters WHERE id=?";
    $semester_data = getSingleRecord($sql, 'i', [$semester_id]);

    $semester = $semester_data['semester'];
    $isDeleting = true;
  }

  function forcedeleteSemester() {
    global $conn, $semester_id;

    $semester_id = filter_input(INPUT_POST, 'semester_id', FILTER_SANITIZE_NUMBER_INT);

    if (! canDeleteSemesterByID( $semester_id )) {
      // $_SESSION['error_msg'] = "No permissions to delete semester";
      header("location: " . BASE_URL . "admin/semesters/semesterList.php");
      exit(0);
    }

    $sql = "DELETE FROM semesters WHERE id=?";
    $result = modifyRecord($sql, 'i', [$semester_id]);

    if ($result) {
      $_SESSION['success_msg'] = "Semester have been deleted!!";
      header("location: " . BASE_URL . "admin/semesters/semesterList.php");
      exit(0);
    } else {
      $_SESSION['error_msg'] = "Could not delete semester";
    }
  }

