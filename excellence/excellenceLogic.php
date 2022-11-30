<?php include_once(INCLUDE_PATH . '/logic/validation.php') ?>
<?php 

  // variable declaration. These variables will be used in the user form
  $topic_id = -1;
  $topic_user_id = -1;
  $title = "";
  $description = "";
  $requirement = "";
  $excellence_list_id = 1;
  $approved_user_id = -1;
  $registered_user_id = -1;
  $reason = '';
  $category_id = NULL;
  $published = false;
  $isEditing = false;
  $isDeleting = false;
  $filter_type = "";

  if(isset($_POST['select_excellence_list'])) {
    filterExcellenceList();
  }

  function getExcellenceListTypes(){
    global $conn;
    $sql = "SELECT * FROM excellence_lists";
    $result = getMultipleRecords($sql);
    return $result;
  }

  function getExcellenceListOfType() {
    global $conn;
    $whereNeeded = false;
    $studentsString = "";
    $studentsWhereString = "LIKE '%'";
    $pointsString = "";
    $pointsWhereString = "LIKE '%'";
    $excellence_list_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
    // First get the filtered excellence list
    $sql = "SELECT * FROM excellence_lists WHERE id = ? LIMIT 1";
    $excellence_list = getSingleRecord($sql, 'i', [$excellence_list_id]);
    if(is_null($excellence_list)) {
      $_SESSION['error_msg'] = "No permissions to create new point type";
      header("location: " . BASE_URL . "index.php");
      exit(0);
    }

    $students = $excellence_list['users'];
    $point_types = $excellence_list['points_type'];

    // Decode students & points from JSON if != all
    if($students != 'all') {
      $students = json_decode($students);
      // After decode make a , separated list for SQL WHERE clause
      foreach ($students as $row) {
        if($studentsString == "") {
          $studentsString = "'" . $row . "'";
        } else {
          $studentsString = $studentsString . ", " . "'" . $row . "'";
        }
      }
      $studentsWhereString = "IN (" . $studentsString . ") ";
    }

    if($point_types != 'all') {
      $point_types = json_decode($point_types);
      // After decode make a , separated list for SQL WHERE clause
      foreach ($point_types as $row) {
        if($pointsString == "") {
          $pointsString = "'" . $row . "'";
        } else {
          $pointsString = $pointsString . ", " . "'" . $row . "'";
        }
      }
      $pointsWhereString = "IN (" . $pointsString . ") ";
    }

    // Because stmt->bind doesn't really accepts IN(...) statement with parameter binding - as a workaround solution, we use predefined strings in WHERE clause
    $sql = "SELECT *, SUM(points) AS totalPoints FROM excellence_points 
              INNER JOIN opportunities ON excellence_points.opportunity_id = opportunities.id 
              INNER JOIN users ON excellence_points.user_id = users.id
            WHERE users.neptuncode " . $studentsWhereString . " AND opportunities.points_type " . $pointsWhereString . "
            GROUP BY excellence_points.user_id 
            ORDER BY totalPoints DESC";



    $result = getMultipleRecords($sql);

    return $result;
  }

  function filterExcellenceList() {
    global $excellence_list_id;
    $excellence_list_id = filter_input(INPUT_POST, 'excellence_id', FILTER_SANITIZE_NUMBER_INT);
    header("location: " . BASE_URL . "excellence/excellenceFilter.php?id=" . $excellence_list_id);
    exit(0);
  }
?>

