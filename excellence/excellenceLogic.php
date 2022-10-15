<?php include_once(INCLUDE_PATH . '/logic/validation.php') ?>
<?php 

  // variable declaration. These variables will be used in the user form
  $topic_id = -1;
  $topic_user_id = -1;
  $title = "";
  $description = "";
  $requirement = "";
  $semester_id = 1;
  $approved_user_id = -1;
  $registered_user_id = -1;
  $reason = '';
  $category_id = NULL;
  $published = false;
  $isEditing = false;
  $isDeleting = false;
  $filter_type = "";

  function getExcellenceList(){
    global $conn;
    $user_id = filter_input(INPUT_GET, "user_id", FILTER_SANITIZE_NUMBER_INT);
    $sql = "SELECT *, SUM(points) AS totalPoints FROM excellence_points 
            INNER JOIN opportunities ON excellence_points.opportunity_id = opportunities.id 
            INNER JOIN users ON excellence_points.user_id = users.id
          GROUP BY excellence_points.user_id ORDER BY totalPoints DESC";
    $result = getMultipleRecords($sql);
    return $result;
  }

?>

