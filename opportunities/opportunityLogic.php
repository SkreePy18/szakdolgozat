<?php include_once(INCLUDE_PATH . '/logic/validation.php') ?>
<?php
  // variable declaration. These variables will be used in the semester form
  $opportunity_id = 0;
  $opportunity = "";
  $opportunity_description = "";
  $owner_id = -1;
  $opportunity_points = "";
  $expiration_date = "";
  $points_type = "";
  $isEditing = false;
  $isDeleting = false;
  $errors = array();

  // ACTION: Save 
  if (isset($_POST['save_opportunity'])) {  // if user clicked save button ...
    saveOpportunity();
  }
  // ACTION: Update 
  if (isset($_POST['update_opportunity'])) { // if user clicked update button ...
    updateOpportunity();
  }
  // ACTION: Fetch for editing
  if (isset($_GET["edit_opportunity"])) {
    editOpportunity();
  }
  // ACTION: Delete with confirmation
  if (isset($_GET['delete_opportunity'])) {
    deleteOpportunity();
  }
  // ACTION: Force delete
  if (isset($_POST['force_delete_opportunity'])) {
    forcedeleteOpportunity();
  }

   // ACTION: Fetch data for viewing opportunity
  if (isset($_GET["view_opportunity"])) {
    viewOpportunity();
  }

  // ACTION: Remove student
  if (isset($_GET['remove_student'])) {
    deleteStudent();
  }


  



  function getOpportunities(){
    global $conn;
    $sql = "SELECT * FROM opportunities";
    $roles = getMultipleRecords($sql);
    return $roles;
  }

  // Save semester to database
  function saveOpportunity(){
    global $conn, $errors, $opportunity, $isEditing;

    // validate data
    $opportunity_data = filter_input_array(INPUT_POST, [
                       "opportunity"   => FILTER_SANITIZE_STRING,
                       "user_id"      => FILTER_SANITIZE_NUMBER_INT,
                       "description"  => FILTER_SANITIZE_STRING,
                       "points"       => FILTER_SANITIZE_NUMBER_INT,
                       "points_type"  => FILTER_SANITIZE_STRING,
                       "date"         => FILTER_SANITIZE_STRING
                     ]);

    // receive all input values from the form
    $opportunity      = $opportunity_data['opportunity'];
    $user_id          = $opportunity_data['user_id'];
    $description      = $opportunity_data['description'];
    $points           = $opportunity_data['points'];
    $expiration_date  = $opportunity_data['date'];
    $points_type      = $opportunity_data['points_type'];

    if (! hasPermissionTo('create-opportunity')) {
      $_SESSION['error_msg'] = "No permissions to create opportunity";
      header("location: " . BASE_URL . "opportunities/opportunityFilter.php");
      exit(0);
    }

    $errors = validateOpportunity($opportunity_data, ['save_opportunity']);
    if (count($errors) === 0) {
      $sql = "INSERT INTO opportunities (owner_id, opportunity, description, points, points_type, expiration_date) VALUES (?, ?, ?, ?, ?, ?)";
      $result = modifyRecord($sql, 'issiis', [$user_id, $opportunity, $description, $points, $points_type, $expiration_date]);

      if($result){
        $_SESSION['success_msg'] = "Opportunity has been successfully published";
        header("location: " . BASE_URL . "opportunities/opportunityFilter.php");
        exit(0);
      } else {
        $_SESSION['error_msg'] = "Could not create opportunity";
      }
    } else {
      $_SESSION['error_msg'] = $errors;
    }
  }

  function updateOpportunity() {
    global $conn, $errors, $opportunity_id, $opportunity, $isEditing;

    // validate data
    $opportunity_data = filter_input_array(INPUT_POST, [
                  "edit_opportunity"  => FILTER_SANITIZE_NUMBER_INT,
                  "description"       => FILTER_SANITIZE_STRING,
                  "opportunity"       => FILTER_SANITIZE_STRING,
                  "points"            => FILTER_SANITIZE_NUMBER_INT,
                  "points_type"       => FILTER_SANITIZE_STRING,
                  "date"              => FILTER_SANITIZE_STRING,
                ]);

    // receive all input values from the form
    $opportunity_id           = $opportunity_data['edit_opportunity'];
    $opportunity              = $opportunity_data['opportunity'];
    $opportunity_points       = $opportunity_data['points'];
    $opportunity_description  = $opportunity_data['description'];
    $expiration_date          = $opportunity_data['date'];
    $points_type              = $opportunity_data['points_type'];


    // check permission to update the semester data
    if (! canUpdateObjectByID('opportunity', $opportunity_id )) {
      $_SESSION['error_msg'] = "No permissions to update opportunity";
      header("location: " . BASE_URL . "opportunities/opportunityFilter.php");
      exit(0);
    }

    $errors = validateOpportunity($opportunity_data, ['edit_opportunity']);

    if (count($errors) === 0) {
      $sql = "UPDATE opportunities SET opportunity=?, description=?, points=?, points_type=?, expiration_date=? WHERE id=?";
      $result = modifyRecord($sql, 'ssiisi', [$opportunity, $opportunity_description, $opportunity_points, $points_type, $expiration_date, $opportunity_id]);
      if ($result) {
        $_SESSION['success_msg'] = "Opportunity successfully updated";
        if(hasPermissionTo('view-opportunity-list')) {
          header("location: " . BASE_URL . "opportunities/opportunityFilter.php");
        } else {
          header("location: " . BASE_URL . "index.php");
        } 
        exit(0);
      } else {
        $_SESSION['error_msg'] = "Could not update opportunity data";
        header("location: " . BASE_URL . "opportunities/opportunityFilter.php");
        exit(0);
      }
    } else {
      $_SESSION['error_msg'] = "Could not update opportunity. Validation failed!";
      header("location: " . BASE_URL . "opportunities/opportunityFilter.php");
      exit(0);
    }
    $isEditing = true;
  }

  function editOpportunity(){
    global $conn, $opportunity_id, $opportunity, $owner_id, $opportunity_description, $opportunity_points, $points_type, $expiration_date, $isEditing;

    $opportunity_id = filter_input(INPUT_GET, 'edit_opportunity', FILTER_SANITIZE_NUMBER_INT);

    if (! canUpdateObjectByID('opportunity', $opportunity_id )) {
      $_SESSION['error_msg'] = "No permissions to edit opportunity";
      header("location: " . BASE_URL . "opportunities/opportunityFilter.php");
      exit(0);
    }

    $sql = "SELECT * FROM opportunities WHERE id=?";
    $opportunity_data = getSingleRecord($sql, 'i', [$opportunity_id]);

    $opportunity              = $opportunity_data['opportunity'];
    $owner_id                 = $opportunity_data['owner_id'];
    $opportunity_description  = $opportunity_data['description'];
    $opportunity_points       = $opportunity_data['points'];
    $points_type              = $opportunity_data['points_type'];
    $expiration_date          = $opportunity_data['expiration_date'];

    $isEditing = true;
  }


  function deleteOpportunity() {
    global $conn, $opportunity_id, $opportunity, $isDeleting;

    $opportunity_id = filter_input(INPUT_GET, 'delete_opportunity', FILTER_SANITIZE_NUMBER_INT);

    if (! canDeleteOpportunityByID( $opportunity_id )) {
      // $_SESSION['error_msg'] = "No permissions to delete semester";
      header("location: " . BASE_URL . "opportunities/opportunityFilter.php");
      exit(0);
    }

    $sql = "SELECT * FROM opportunities WHERE id=?";
    $opportunity_data = getSingleRecord($sql, 'i', [$opportunity_id]);

    $opportunity = $opportunity_data['opportunity'];
    $isDeleting = true;
  }

  function forcedeleteOpportunity() {
    global $conn, $opportunity_id;

    $opportunity_id = filter_input(INPUT_POST, 'opportunity_id', FILTER_SANITIZE_NUMBER_INT);

    if (! canDeleteOpportunityByID( $opportunity_id )) {
      // $_SESSION['error_msg'] = "No permissions to delete semester";
      header("location: " . BASE_URL . "opportunities/opportunityFilter.php");
      exit(0);
    }

    $sql = "DELETE FROM opportunities WHERE id=?";
    $result = modifyRecord($sql, 'i', [$opportunity_id]);

    if ($result) {
      $_SESSION['success_msg'] = "You have successfully deleted the opportunity";
      header("location: " . BASE_URL . "opportunities/opportunityFilter.php");
      exit(0);
    } else {
      $_SESSION['error_msg'] = "Could not delete semester";
    }
  }

  function getFilterOpportunitiesBySupervisor($owner_id){
    global $conn;

    if (! hasPermissionTo('view-opportunity-list')) {
      $_SESSION['error_msg'] = "No permissions to view filtered opportunities";
      header("location: " . BASE_URL . "index.php");
      exit(0);
    }

    $sql = "SELECT * FROM opportunities WHERE owner_id=?";
    $opportunities = getMultipleRecords($sql, "i", [ $owner_id]);

    $filtered_opportunities = array();
    foreach ($opportunities as $opportunity) {
      array_push($filtered_opportunities, $opportunity);
    }

    return array($filtered_opportunities, "Filtered opportunities by supervisor");
  }

  function getFilterOpportunities(){
    global $conn;

    if (! hasPermissionTo('view-opportunity-list')) {
      $_SESSION['error_msg'] = "No permissions to view filtered opportunities";
      header("location: " . BASE_URL . "index.php");
      exit(0);
    }

    $sql = "SELECT * FROM opportunities";
    $opportunities = getMultipleRecords($sql);

    $filtered_opportunities = array();
    foreach ($opportunities as $opportunity) {
      array_push($filtered_opportunities, $opportunity);
    }

    return array($filtered_opportunities, "Opportunities");
  }

  function viewOpportunity(){
    global $conn, $opportunity_id, $opportunity, $owner_id, $opportunity_description, $opportunity_points, $expiration_date, $points_type, $isEditing;
    global $accomplised_users;

    $opportunity_id = filter_input(INPUT_GET, 'view_opportunity', FILTER_SANITIZE_NUMBER_INT);

    if (! hasPermissionTo('view-opportunity-list')) {
      $_SESSION['error_msg'] = "No permissions to view opportunities";
      header("location: " . BASE_URL . "index.php");
      exit(0);
    }

    // Check if the opportunity exists
    $sql = "SELECT * FROM opportunities WHERE id=?";
    $opportunity_data = getSingleRecord($sql, 'i', [$opportunity_id]);
    if(is_null($opportunity_data)) {
      $_SESSION['error_msg'] = "Opportunity does not exist";
      header("location: " . BASE_URL . "index.php");
      exit(0);
    }

    $opportunity_id = $opportunity_data['id'];
    $owner_id = $opportunity_data['owner_id'];
    $opportunity = $opportunity_data['opportunity'];
    $opportunity_description = $opportunity_data['description'];
    $opportunity_points = $opportunity_data['points'];
    $expiration_date = $opportunity_data['expiration_date'];
    $points_type = $opportunity_data['points_type'];

    // Get list of users who have accomplished
    $sql = "SELECT * FROM excellence_points WHERE opportunity_id = ?";
    $accomplised_users = getMultipleRecords($sql, 'i', [$opportunity_id]);
  }

  function deleteStudent() {
    global $conn, $opportunity_id, $opportunity, $isDeleting;

    $removeData = filter_input_array(INPUT_GET, [
                          'remove_student' => FILTER_SANITIZE_NUMBER_INT,
                          'view_opportunity' => FILTER_SANITIZE_NUMBER_INT,
                  ]);

    // if (! canDeleteOpportunityByID( $opportunity_id )) {
    //   $_SESSION['error_msg'] = "No permissions to delete semester";
    //   header("location: " . BASE_URL . "opportunities/opportunityFilter.php");
    //   exit(0);
    // }
    
    $student_id = $removeData['remove_student'];
    $opportunity_id = $removeData['view_opportunity'];

    $sql = "DELETE FROM excellence_points WHERE user_id=? AND opportunity_id=?";
    $delete = modifyRecord($sql, 'ii', [$student_id, $opportunity_id]);

    if($delete) {
      $_SESSION['success_msg'] = "You have successfully removed the student!";
      header("location: " . BASE_URL . "opportunities/opportunityView.php?view_opportunity=" . $opportunity_id);
      exit(0);
    }

  }