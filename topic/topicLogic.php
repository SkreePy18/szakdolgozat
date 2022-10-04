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

  // ACTION: Update topic
  if (isset($_POST['update_topic'])) { // if user clicked update_topic button ...
      updateTopic();
  }
  // ACTION: Save topic
  if (isset($_POST['save_topic'])) {  // if user clicked save_topic button ...
      saveTopic();
  }
  // ACTION: fetch topic for editing
  if (isset($_GET["edit_topic"])) {
    editTopic();
  }
  // ACTION: Delete topic with confirmation
  if (isset($_GET['delete_topic'])) {
    deleteTopic();
  }
  // ACTION: Delete topic
  if (isset($_POST['force_delete_topic'])) {
    forcedeleteTopic();
  }
  // ACTION: fetch topic for viewing
  if (isset($_GET["view_topic"])) {
    viewTopic();
  }

  // ACTION: register for topic
  if (isset($_POST["register_topic_user"])) {
    $reg_data = filter_input_array(INPUT_POST, [
                  "topic_id" => FILTER_SANITIZE_NUMBER_INT,
                  "reason" => FILTER_SANITIZE_STRING
                ]);
    $topic_id = $reg_data['topic_id'];
    $reason = $reg_data['reason'];
    registerTopicUser($topic_id, $_SESSION['user']['id'], $reason);
  }
  // ACTION: unregister for topic
  if (isset($_POST["unregister_topic_user"])) {
    $topic_id = filter_input(INPUT_POST, 'topic_id', FILTER_SANITIZE_NUMBER_INT);
    unregisterTopicUser($topic_id, $_SESSION['user']['id']);
  }

  // ACTION: approve user of topic
  if (isset($_POST["approve_topic"])) {
    $topic_id = filter_input(INPUT_POST, 'topic_id', FILTER_SANITIZE_NUMBER_INT);
    $reg_user_id = filter_input(INPUT_POST, 'registered_topic_user', FILTER_SANITIZE_NUMBER_INT);
    approveTopic($topic_id, $reg_user_id);
  }
  // ACTION: unapprove user for topic
  if (isset($_POST["unapprove_topic"])) {
    $topic_id = filter_input(INPUT_POST, 'topic_id', FILTER_SANITIZE_NUMBER_INT);
    unapproveTopic($topic_id);
  }

  // ACTION: Select semester
  if (isset($_POST['select_semester'])) {
    selectSemester();
  }

  // ACTION: Select score
  if (isset($_POST['select_score'])) {
    selectScore();
  }


  function getAllCategories(){
    global $conn;
    $sql = "SELECT id, name FROM categories";
    $categories = getMultipleRecords($sql);
    return $categories;
  }

  /* * * * * * * * * * * * * * *
  * Receives a topic id and
  * Returns category of the topic
  * * * * * * * * * * * * * * */
  function getTopicCategory($topic_id){
    global $conn;
    $sql = "SELECT * FROM categories WHERE id= ( SELECT category_id FROM topic_category WHERE topic_id=? ) LIMIT 1";
    $category = getSingleRecord($sql, 'i', [$topic_id]);
    return $category;
  }

  function validateTopic($topic, $ignoreFields) {
    $errors = [];

    if ( strlen($topic['title']) < 10 ) {
      $errors['title'] = "The title must be longer than 10 character";
    }
    if ( strlen($topic['title']) > 1000 ) {
      $errors['title'] = "The title must be shorter than 1000 character";
    }

    if ( strlen($topic['description']) < 50 ) {
      $errors['description'] = "The description must be longer than 50 character";
    }
    if ( strlen($topic['description']) > 3000 ) {
      $errors['description'] = "The description must be shorter than 3000 character";
    }
    
    if ( strlen($topic['requirement']) > 1000 ) {
      $errors['requirement'] = "The requirement must be shorter than 1000 character";
    }
    
    // required validation
    foreach ($topic as $key => $value) {
      if (in_array($key, $ignoreFields)) {
        continue;
      }
      if (empty($topic[$key])) {
        $errors[$key] = "This field is required";
      }
    }
    return $errors;
  }

  function updateTopic() {
    global $conn, $errors, $topic_id, $title, $description, $requirement, $category_id, $published, $topic_user_id, $semester_id, $isEditing;

    $topic_data = filter_input_array(INPUT_POST, [
                    "topic_id" => FILTER_SANITIZE_NUMBER_INT,
                    "semester_id" => FILTER_SANITIZE_NUMBER_INT,
                    "title" => FILTER_SANITIZE_STRING,
                    "description" => FILTER_SANITIZE_STRING,
                    "requirement" => FILTER_SANITIZE_STRING,
                    "category_id" => FILTER_SANITIZE_NUMBER_INT
                  ]);

    // receive all input values from the form
    $topic_id = $topic_data['topic_id'];
    $title = $topic_data['title'];
    $description = $topic_data['description'];
    $requirement = $topic_data['requirement'];
    $category_id = $topic_data['category_id'];
    $semester_id = $topic_data['semester_id'];
    // when checkbox is unchecked it is not getting posted, 
    // so when there is a post, it should be true
    if(isset($_POST['published'])) {
      $published = true;
    } else {
      $published = false;
    }
    if (isset($_POST['topic_user_id'])) {
      $topic_user_id = filter_input(INPUT_POST, 'topic_user_id', FILTER_SANITIZE_NUMBER_INT);
    }

    if (! canUpdateTopicByID( $topic_id )) {
      $_SESSION['error_msg'] = "No permissions to update topic";
      header("location: " . BASE_URL . "index.php");
      exit(0);
    }

    $errors = validateTopic($topic_data, ['update_topic', 'category_id']);

    if (count($errors) === 0) {

      // start the transaction
      mysqli_autocommit ($conn, false);
      mysqli_begin_transaction($conn);
      
      try {

        // update the topic basic data
        $sql1 = "UPDATE topics SET title=?, description=?, requirement=?, published=?, semester_id=? WHERE id=?";
        $result1 = modifyRecord($sql1, 'sssiii', [$title, $description, $requirement, $published, $semester_id, $topic_id]);

        // update the owner of the topic, if new user was set
        if(isset($_POST['topic_user_id'])) {
          $sql2 = "UPDATE topics SET user_id=? WHERE id=?";
          $result2 = modifyRecord($sql2, 'ii', [$topic_user_id, $topic_id]);
        }

        // if we are unpublishing the topic, then remove approved user 
        if(!$published) {
          $sql3 = "UPDATE topics SET approved_user_id=-1, published=0 WHERE id=?";
          $result3 = modifyRecord($sql3, 'i', [$topic_id]);

          // and remove all registrations
          $sql4 = "DELETE FROM topic_user WHERE topic_id=?";
          $result4 = modifyRecord($sql4, 'i', [$topic_id]);
        }

        // update category
        // check whether a category exists for the topic
        $category_id_old = getTopicCategory($topic_id);
        // if the newly specified category is not empty, then
        if ($category_id != '') {
          // if there is no category, then insert
          if(is_null($category_id_old)) {
            $sql5 = "INSERT INTO topic_category SET topic_id=?, category_id=?";
            $result5 = modifyRecord($sql5, 'ii', [$topic_id, $category_id]);
          // if there is already a category, then update
          } else {
            $sql5 = "UPDATE topic_category SET category_id=? where topic_id=?";
            $result5 = modifyRecord($sql5, 'ii', [$category_id, $topic_id]);
          }
        // if the newly specified category is empty, 
        } else {
          // if there is already a category then delete the category for the topic
          if(! is_null($category_id_old)) {
            $sql5 = "DELETE FROM topic_category where topic_id=?";
            $result5 = modifyRecord($sql5, 'i', [$topic_id]);
          }
        }

        mysqli_commit($conn);
        mysqli_autocommit ($conn, true);
        $_SESSION['success_msg'] = "Diploma topic successfully updated";

      } catch(EXCEPTION $e){

        mysqli_rollback($conn);
        mysqli_autocommit ($conn, true);
        $_SESSION['error_msg'] = "Could not update topic data";
        throw $e;
      }
    } else {
      $_SESSION['error_msg'] = "Could not update topic";
    }
    $isEditing = true;
  }

  // Save a new topic to database
  function saveTopic(){
    global $conn, $errors, $title, $description, $requirement, $category_id, $published, $topic_user_id, $isEditing;

    $topic_data = filter_input_array(INPUT_POST, [
                    "title" => FILTER_SANITIZE_STRING,
                    "description" => FILTER_SANITIZE_STRING,
                    "requirement" => FILTER_SANITIZE_STRING,
                    "category_id" => FILTER_SANITIZE_NUMBER_INT
                  ]);

    // receive all input values from the form
    $title = $topic_data['title'];
    $description = $topic_data['description'];
    $requirement = $topic_data['requirement'];
    $category_id = $topic_data['category_id'];
    // when checkbox is unchecked it is not getting posted, 
    // so when there is a post, it should be true
    if(isset($_POST['published'])) {
      $published = true;
    } else {
      $published = false;
    }
    if (isset($_POST['topic_user_id'])) {
      $topic_user_id = filter_input(INPUT_POST, 'topic_user_id', FILTER_SANITIZE_NUMBER_INT);
    } else {
      $topic_user_id = $_SESSION['user']['id'];
    }

    if (! hasPermissionTo('create-topic')) {
      $_SESSION['error_msg'] = "No permissions to create topic";
      header("location: " . BASE_URL . "index.php");
      exit(0);
    }

    $errors = validateTopic($topic_data, ['save_topic', 'category_id']);

    if (count($errors) === 0) {

      // start a transaction
      mysqli_autocommit($conn, false);
      mysqli_begin_transaction($conn);

      try {
        $sql = "INSERT INTO topics SET user_id=?, title=?, description=?, requirement=?, published=?";
        $result1 = modifyRecord($sql, 'isssi', [$topic_user_id, $title, $description, $requirement, $published]);

        if ($category_id != '') {
          $sql = "INSERT INTO topic_category SET topic_id=LAST_INSERT_ID(), category_id=?";
          $result2 = modifyRecord($sql, 'i', [$category_id]);
        }

        mysqli_commit($conn);
        mysqli_autocommit ($conn, true);

        $_SESSION['success_msg'] = "Diploma topic is successfully created";
        header("location: " . BASE_URL . "topic/topicFilter.php?filter_topic=all");
        exit(0);

      } catch(EXCEPTION $e){

        mysqli_rollback($conn);
        mysqli_autocommit ($conn, true);
        $_SESSION['error_msg'] = "Could not create topic";
        throw $e;
      }
    } else {
      $_SESSION['error_msg'] = "Could not save topic";
    }
  }


  function editTopic( ){
    global $conn, $topic_id, $topic_user_id, $category_id, $title, $description, $requirement, $published, $semester_id, $isEditing;

    $topic_spec_id = filter_input(INPUT_GET, 'edit_topic', FILTER_SANITIZE_NUMBER_INT);

    if (! canUpdateTopicByID( $topic_spec_id )) {
      $_SESSION['error_msg'] = "No permissions to edit topic";
      header("location: " . BASE_URL . "index.php");
      exit(0);
    }

    $sql = "SELECT * FROM topics WHERE id=?";
    $topic = getSingleRecord($sql, 'i', [$topic_spec_id]);

    $topic_id = $topic['id'];
    $topic_user_id = $topic['user_id'];
    $title = $topic['title'];
    $description = $topic['description'];
    $requirement = $topic['requirement'];
    $published = $topic['published'];
    $semester_id = $topic['semester_id'];

    $cat = getTopicCategory($topic_id);
    if(! is_null($cat)) {
      $category_id = $cat['id'];
    }

    $isEditing = true;
  }


  function deleteTopic() {
    global $conn, $topic_id, $title, $isDeleting;

    $topic_id = filter_input(INPUT_GET, 'delete_topic', FILTER_SANITIZE_NUMBER_INT);

    if (! canDeleteTopicByID( $topic_id )) {
      $_SESSION['error_msg'] = "No permissions to delete topic";
      header("location: " . BASE_URL . "topic/topicFilter.php?filter_topic=all");
      exit(0);
    }

    $sql = "SELECT * FROM topics WHERE id=?";
    $topic = getSingleRecord($sql, 'i', [$topic_id]);

    $title = $topic['title'];
    $description = $topic['description'];
    $requirement = $topic['requirement'];
    $published = $topic['published'];
    $isDeleting = true;
  }


  function forcedeleteTopic() {
    global $conn, $topic_id;

    $topic_id = filter_input(INPUT_POST, 'topic_id', FILTER_SANITIZE_NUMBER_INT);

    if (! canDeleteTopicByID( $topic_id )) {
      $_SESSION['error_msg'] = "No permissions to delete topic";
      header("location: " . BASE_URL . "topic/topicFilter.php?filter_topic=all");
      exit(0);
    }

    $sql = "DELETE FROM topics WHERE id=?";
    $result = modifyRecord($sql, 'i', [$topic_id]);

    if ($result) {
      $_SESSION['success_msg'] = "Topic have been deleted!!";
      header("location: " . BASE_URL . "topic/topicFilter.php?filter_topic=all");
      exit(0);
    } else {
      $_SESSION['error_msg'] = "Could not delete topic";
    }
  }

  function selectSemester() {
    global $conn, $semester_id;

    if (! hasPermissionTo('view-semester-selector')) {
      $_SESSION['error_msg'] = "No permissions to view semesters";
      header("location: " . BASE_URL . "topic/topicFilter.php?filter_topic=all");
      exit(0);
    }

    $semester_id = filter_input(INPUT_POST, 'semester_id', FILTER_SANITIZE_NUMBER_INT);
  }

  function viewTopic(){
    global $conn, $topic_id, $topic_user_id, $category_id, $title, $description, $requirement, $approved_user_id, $registered_user_id, $published, $isEditing;

    $topic_spec_id = filter_input(INPUT_GET, 'view_topic', FILTER_SANITIZE_NUMBER_INT);

    if (! hasPermissionTo('view-topic-list')) {
      $_SESSION['error_msg'] = "No permissions to view topic(s)";
      header("location: " . BASE_URL . "index.php");
      exit(0);
    }

    // check whether topic exists at all
    $sql = "SELECT * FROM topics WHERE id=?";
    $topic = getSingleRecord($sql, 'i', [$topic_spec_id]);
    if(is_null($topic)) {
      $_SESSION['error_msg'] = "Topic does not exist";
      header("location: " . BASE_URL . "index.php");
      exit(0);
    }

    $topic_id = $topic['id'];
    $topic_user_id = $topic['user_id'];
    $title = $topic['title'];
    $description = $topic['description'];
    $requirement = $topic['requirement'];
    $approved_user_id = $topic['approved_user_id'];
    $published = $topic['published'];

    if (isset($_GET["view_registered_user"])) {
      $registered_user_id = filter_input(INPUT_GET, 'view_registered_user', FILTER_SANITIZE_NUMBER_INT);
    }

    $cat = getTopicCategory($topic_id);
    if(! is_null($cat)) {
      $category_id = $cat['id'];
    }
  }

  function selectScore(){
    global $conn;

    if (! hasPermissionTo('assing-topic-score')) {
      $_SESSION['error_msg'] = "No permissions to set topic score";
      header("location: " . BASE_URL . "index.php");
      exit(0);
    }

    $score_data = filter_input_array(INPUT_POST, [
      "topic_id" => FILTER_SANITIZE_NUMBER_INT,
      "user_id" => FILTER_SANITIZE_NUMBER_INT,
      "score_id" => FILTER_SANITIZE_NUMBER_INT
    ]);
    $topic_id = $score_data['topic_id'];
    if(isAdmin($_SESSION['user']['id'])) {
      $user_id  = $score_data['user_id'];
    } else {
      $user_id  = $_SESSION['user']['id'];
    }
    $score_id = $score_data['score_id'];

    // check whether topic exists at all
    $sql = "SELECT * FROM topics WHERE id=?";
    $topic = getSingleRecord($sql, 'i', [$topic_id]);
    if(is_null($topic)) {
      $_SESSION['error_msg'] = "Topic does not exist";
      header("location: " . BASE_URL . "index.php");
      exit(0);
    }

    if($score_id == -1) {
      $sql = "DELETE FROM topic_score WHERE topic_id=? AND user_id=?";
      $result = modifyRecord($sql, 'ii', [ $topic_id, $user_id ]);
    } else {
      $sql = "INSERT INTO topic_score (`topic_id`, `user_id`, `score_id`) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE `score_id`=?";
      $result = modifyRecord($sql, 'iiii', [ $topic_id, $user_id, $score_id, $score_id ]);
    }

    if ($result) {
      $_SESSION['success_msg'] = "Topic score have been updated!";
      header("location: " . BASE_URL . "topic/topicScoreEdit.php?view_topic=".$topic_id);
      exit(0);
    } else {
      $_SESSION['error_msg'] = "Could not update score";
    }
  }

  // --------------------------------------------------------------------------
  // Filtering
  // --------------------------------------------------------------------------
  // collect topics that belong to a supervisor, published and no approved user for it, and in the current semester
  function getFilterTopicsBySupervisor($owner_id, $semester_id){
    global $conn;

    if (! hasPermissionTo('view-topic-list')) {
      $_SESSION['error_msg'] = "No permissions to view filtered topic(s)";
      header("location: " . BASE_URL . "index.php");
      exit(0);
    }

    // for admin show everything, unpublished and approved as well
    if(isAdmin($_SESSION['user']['id'])) {
      if($semester_id == -1) {
        $sql = "SELECT t.id, t.title, t.published, t.user_id, t.approved_user_id, t.semester_id, u.fullname FROM topics t INNER JOIN users u ON t.user_id=u.id WHERE t.user_id=? ORDER BY t.id";
        $topics = getMultipleRecords($sql, "i", [ $owner_id ]);
      } else {
        $sql = "SELECT t.id, t.title, t.published, t.user_id, t.approved_user_id, t.semester_id, u.fullname FROM topics t INNER JOIN users u ON t.user_id=u.id WHERE t.user_id=? AND semester_id=? ORDER BY t.id";
        $topics = getMultipleRecords($sql, "ii", [ $owner_id, $semester_id ]);
      }
    } else {
      // for non-admin users show only published and non-approved topics
      if($semester_id == -1) {
        $sql = "SELECT t.id, t.title, t.published, t.user_id, t.approved_user_id, t.semester_id, u.fullname FROM topics t INNER JOIN users u ON t.user_id=u.id WHERE t.approved_user_id = -1 AND t.published=true AND t.user_id=? ORDER BY t.id";
        $topics = getMultipleRecords($sql, "i", [ $owner_id ]);
      } else {
        $sql = "SELECT t.id, t.title, t.published, t.user_id, t.approved_user_id, t.semester_id, u.fullname FROM topics t INNER JOIN users u ON t.user_id=u.id WHERE t.approved_user_id = -1 AND t.published=true AND t.user_id=? AND semester_id=? ORDER BY t.id";
        $topics = getMultipleRecords($sql, "ii", [ $owner_id, $semester_id ]);
      }
    }

    $final_topics = array();
    foreach ($topics as $topic) {
      $topic['category'] = getTopicCategory($topic['id']); 
      array_push($final_topics, $topic);
    }

    return array($final_topics, "Filtered topics by supervisor");
  }


  // collect topics that belong to a category, published and there is no approved user for it
  function getFilterTopicsByCategory($category_id, $semester_id){
    global $conn;

    if (! hasPermissionTo('view-topic-list')) {
      $_SESSION['error_msg'] = "No permissions to view filtered topic(s)";
      header("location: " . BASE_URL . "index.php");
      exit(0);
    }

    // for admin show everything, unpublished and approved as well
    if(isAdmin($_SESSION['user']['id'])) {
      if($semester_id == -1) {
        $sql = "SELECT t.id, t.title, t.published, t.user_id, t.approved_user_id, t.semester_id, u.fullname FROM topics t INNER JOIN topic_category tc ON tc.topic_id=t.id INNER JOIN users u ON t.user_id=u.id WHERE category_id=? ORDER BY t.id";
        $topics = getMultipleRecords($sql, "i", [ $category_id ]);
      } else {
        $sql = "SELECT t.id, t.title, t.published, t.user_id, t.approved_user_id, t.semester_id, u.fullname FROM topics t INNER JOIN topic_category tc ON tc.topic_id=t.id INNER JOIN users u ON t.user_id=u.id WHERE category_id=? AND semester_id=? ORDER BY t.id";
        $topics = getMultipleRecords($sql, "ii", [ $category_id, $semester_id ]);
      }
    } else {
      if($semester_id == -1) {
        $sql = "SELECT t.id, t.title, t.published, t.user_id, t.approved_user_id, t.semester_id, u.fullname FROM topics t INNER JOIN topic_category tc ON tc.topic_id=t.id INNER JOIN users u ON t.user_id=u.id WHERE t.approved_user_id = -1 AND t.published=true AND category_id=? ORDER BY t.id";
        $topics = getMultipleRecords($sql, "i", [ $category_id ]);
      } else {
        $sql = "SELECT t.id, t.title, t.published, t.user_id, t.approved_user_id, t.semester_id, u.fullname FROM topics t INNER JOIN topic_category tc ON tc.topic_id=t.id INNER JOIN users u ON t.user_id=u.id WHERE t.approved_user_id = -1 AND t.published=true AND category_id=? AND semester_id=? ORDER BY t.id";
        $topics = getMultipleRecords($sql, "ii", [ $category_id, $semester_id ]);
      }
    }
    $final_topics = array();
    foreach ($topics as $topic) {
      $topic['category'] = getTopicCategory($topic['id']); 
      array_push($final_topics, $topic);
    }

    return array($final_topics, "Filtered topics by category");
  }


  // the main filtering function
  // if semester_id == -1 then all topics of all semesters are listed
  function getFilterTopics($filter_type, $semester_id){
    global $conn;

    if (! hasPermissionTo('view-topic-list')) {
      $_SESSION['error_msg'] = "No permissions to view filtered topic(s)";
      header("location: " . BASE_URL . "index.php");
      exit(0);
    }

    // topics owned/created by user
    if($filter_type == "owned") {
      if($semester_id == -1) {
        $sql = "SELECT t.id, t.title, t.published, t.user_id, t.approved_user_id, t.semester_id, u.fullname FROM topics t INNER JOIN users u ON t.user_id=u.id WHERE user_id=? ORDER BY t.id";
        $topics = getMultipleRecords($sql, "i", [ $_SESSION['user']['id'] ]);
      } else {
        $sql = "SELECT t.id, t.title, t.published, t.user_id, t.approved_user_id, t.semester_id, u.fullname FROM topics t INNER JOIN users u ON t.user_id=u.id WHERE user_id=? AND t.semester_id=? ORDER BY t.id";
        $topics = getMultipleRecords($sql, "ii", [ $_SESSION['user']['id'], $semester_id ]);
      }

      $final_topics = array();
      foreach ($topics as $topic) {
        $topic['category'] = getTopicCategory($topic['id']); 
        array_push($final_topics, $topic);
      }
      $title="My created topics";
    // collect topics where the current user is approved
    } else if($filter_type == "approved") {
      $sql = "SELECT t.id, t.title, t.published, t.user_id, t.approved_user_id, t.semester_id, u.fullname FROM topics t INNER JOIN users u ON t.user_id=u.id WHERE approved_user_id=? ORDER BY t.id";
      $topics = getMultipleRecords($sql, "i", [ $_SESSION['user']['id'] ]);

      $final_topics = array();
      foreach ($topics as $topic) {
        $topic['category'] = getTopicCategory($topic['id']); 
        array_push($final_topics, $topic);
      }
      $title="My approved topic";
    // collect topics where the current user is registered
    } else if($filter_type == "registered") {
      if($semester_id == -1) {
        $sql = "SELECT t.id, t.title, t.published, t.user_id, t.approved_user_id, t.semester_id, u.fullname FROM topics t INNER JOIN users u ON t.user_id=u.id INNER JOIN topic_user tu ON t.id=tu.topic_id WHERE tu.user_id=? ORDER BY t.id";
        $topics = getMultipleRecords($sql, "i", [ $_SESSION['user']['id'] ]);
      } else {
        $sql = "SELECT t.id, t.title, t.published, t.user_id, t.approved_user_id, t.semester_id, u.fullname FROM topics t INNER JOIN users u ON t.user_id=u.id INNER JOIN topic_user tu ON t.id=tu.topic_id WHERE tu.user_id=? AND t.semester_id=? ORDER BY t.id";
        $topics = getMultipleRecords($sql, "ii", [ $_SESSION['user']['id'], $semester_id ]);
      }

      $final_topics = array();
      foreach ($topics as $topic) {
        $topic['category'] = getTopicCategory($topic['id']); 
        array_push($final_topics, $topic);
      }
      $title="My registered topics";
    // collect topics for which students are registered
    } else if($filter_type == "registered-user") {
      if(hasPermissionTo('view-topic-summary')) {
        if($semester_id == -1) {
          $sql = "SELECT t.id, t.title, t.published, t.user_id, t.approved_user_id, t.semester_id, tu.user_id as student_id FROM topics t INNER JOIN topic_user tu ON t.id=tu.topic_id ORDER BY t.id";
          $topics = getMultipleRecords($sql);
        } else {
          $sql = "SELECT t.id, t.title, t.published, t.user_id, t.approved_user_id, t.semester_id, tu.user_id as student_id FROM topics t INNER JOIN topic_user tu ON t.id=tu.topic_id WHERE t.semester_id=? ORDER BY t.id";
          $topics = getMultipleRecords($sql, "i", [ $semester_id ]);
        }
        $title="All topics with registered student";
      } else {
        if($semester_id == -1) {
          $sql = "SELECT t.id, t.title, t.published, t.user_id, t.approved_user_id, t.semester_id, tu.user_id as student_id FROM topics t INNER JOIN topic_user tu ON t.id=tu.topic_id WHERE t.user_id=? ORDER BY t.id";
          $topics = getMultipleRecords($sql, "i", [ $_SESSION['user']['id'] ]);
        } else {
          $sql = "SELECT t.id, t.title, t.published, t.user_id, t.approved_user_id, t.semester_id, tu.user_id as student_id FROM topics t INNER JOIN topic_user tu ON t.id=tu.topic_id WHERE t.user_id=? AND t.semester_id=? ORDER BY t.id";
          $topics = getMultipleRecords($sql, "ii", [ $_SESSION['user']['id'], $semester_id ]);
        }
        $title="My topics with registered student";
      }

      $final_topics = array();
      foreach ($topics as $topic) {
        $topic['category'] = getTopicCategory($topic['id']); 
        array_push($final_topics, $topic);
      }
    // collect topics for which students are approved
    } else if($filter_type == "approved-user") {
      if(hasPermissionTo('view-topic-summary')) {
        if($semester_id == -1) {
          $sql = "SELECT t.id, t.title, t.published, t.user_id, t.approved_user_id, t.semester_id, u.fullname FROM topics t INNER JOIN users u ON t.user_id=u.id WHERE t.approved_user_id <> -1 ORDER BY t.id";
          $topics = getMultipleRecords($sql);
        } else {
          $sql = "SELECT t.id, t.title, t.published, t.user_id, t.approved_user_id, t.semester_id, u.fullname FROM topics t INNER JOIN users u ON t.user_id=u.id WHERE t.approved_user_id <> -1 AND t.semester_id=? ORDER BY t.id";
          $topics = getMultipleRecords($sql, "i", [ $semester_id ]);
        }
        $title="All topics with approved student";
      } else {
        if($semester_id == -1) {
          $sql = "SELECT t.id, t.title, t.published, t.user_id, t.approved_user_id, t.semester_id, u.fullname FROM topics t INNER JOIN users u ON t.user_id=u.id WHERE t.approved_user_id <> -1 AND t.user_id=? ORDER BY t.id";
          $topics = getMultipleRecords($sql, "i", [ $_SESSION['user']['id'] ]);
        } else {
          $sql = "SELECT t.id, t.title, t.published, t.user_id, t.approved_user_id, t.semester_id, u.fullname FROM topics t INNER JOIN users u ON t.user_id=u.id WHERE t.approved_user_id <> -1 AND t.user_id=? AND t.semester_id=? ORDER BY t.id";
          $topics = getMultipleRecords($sql, "ii", [ $_SESSION['user']['id'], $semester_id ]);
        }
        $title="My topics with approved student";
      }

      $final_topics = array();
      foreach ($topics as $topic) {
        $topic['category'] = getTopicCategory($topic['id']); 
        array_push($final_topics, $topic);
      }
    // in all other cases list all published
    } else {
      // if we can publish topics, then really everything is visible
      if(hasPermissionTo('publish-topic')) {
        if($semester_id == -1) {
          $sql = "SELECT t.id, t.title, t.published, t.user_id, t.approved_user_id, t.semester_id, u.fullname FROM topics t INNER JOIN users u ON t.user_id=u.id ORDER BY t.id";
          $topics = getMultipleRecords($sql);
        } else {
          $sql = "SELECT t.id, t.title, t.published, t.user_id, t.approved_user_id, t.semester_id, u.fullname FROM topics t INNER JOIN users u ON t.user_id=u.id WHERE t.semester_id=? ORDER BY t.id";
          $topics = getMultipleRecords($sql, "i", [ $semester_id ]);
        }
        $final_topics = array();
        foreach ($topics as $topic) {
          $topic['category'] = getTopicCategory($topic['id']); 
          array_push($final_topics, $topic);
        }
        $title="All topics";
      } else {
        // only the published and non-approved topics are listed
        if($semester_id == -1) {
          $sql = "SELECT t.id, t.title, t.published, t.user_id, t.approved_user_id, t.semester_id, u.fullname FROM topics t INNER JOIN users u ON t.user_id=u.id WHERE published=true AND t.approved_user_id=-1 ORDER BY t.id";
          $topics = getMultipleRecords($sql);
        } else {
          $sql = "SELECT t.id, t.title, t.published, t.user_id, t.approved_user_id, t.semester_id, u.fullname FROM topics t INNER JOIN users u ON t.user_id=u.id WHERE published=true AND t.approved_user_id=-1 AND t.semester_id=? ORDER BY t.id";
          $topics = getMultipleRecords($sql, "i", [ $semester_id ]);
        }
        $final_topics = array();
        foreach ($topics as $topic) {
          $topic['category'] = getTopicCategory($topic['id']); 
          array_push($final_topics, $topic);
        }
        $title="All published topics";
      }
    }

    return array($final_topics, $title);
  }


  function getFilterTopicsScore($user_id){
    global $conn;

    if (! hasPermissionTo('view-own-score')) {
      $_SESSION['error_msg'] = "No permissions to view topic(s) to score";
      header("location: " . BASE_URL . "index.php");
      exit(0);
    }

    // published and in current semester
    $sql = "SELECT t.id, t.title, t.views FROM topics t WHERE t.published=true AND t.semester_id=1 ORDER BY t.id";
    $topics = getMultipleRecords($sql);

    $final_topics = array();
    foreach ($topics as $topic) {
      $sql ="SELECT s.value FROM topic_score t LEFT JOIN scores s ON t.score_id=s.id WHERE t.topic_id=? AND t.user_id=? LIMIT 1";
      $topic['score'] = getSingleRecord($sql, 'ii', [ $topic['id'], $user_id ]);
      array_push($final_topics, $topic);
    }

    return $final_topics;
  }

  // --------------------------------------------------------------------------
  // Register 
  // --------------------------------------------------------------------------
  // determines whether the user is already registered for the topic
  function isUserRegisteredTopic($topic_id, $user_id) {
    global $conn;

    $sql = "SELECT id FROM topic_user WHERE topic_id=? AND user_id=?";
    $topic_user = getSingleRecord($sql, 'ii', [$topic_id, $user_id]);
    if(is_null($topic_user)) {
      return false;
    } else {
      return true;
    }
  }

  // register the user for the topic
  function registerTopicUser($topic_id, $user_id, $reg_reason) {
    global $conn;

    // check whether topic exists at all
    $sql = "SELECT * FROM topics WHERE id=?";
    $topic = getSingleRecord($sql, 'i', [$topic_id]);
    if(is_null($topic)) {
      $_SESSION['error_msg'] = "Topic does not exist";
      header("location: " . BASE_URL . "index.php");
      exit(0);
    }

    // check whether user exists at all
    $sql = "SELECT * FROM users WHERE id=?";
    $topic = getSingleRecord($sql, 'i', [$user_id]);
    if(is_null($topic)) {
      $_SESSION['error_msg'] = "User does not exist";
      header("location: " . BASE_URL . "index.php");
      exit(0);
    }

    if (! canRegisterTopicUserByID( $topic_id )) {
      $_SESSION['error_msg'] = "No permissions to register for topic";
      header("location: " . BASE_URL . "index.php");
      exit(0);
    }

    if(isUserRegisteredTopic($topic_id, $user_id)) {
      $_SESSION['error_msg'] = "You are already registered for the topic";
      header("location: " . BASE_URL . "topic/topicView.php?view_topic=" . $topic_id);
      exit(0);
    }

    if (strlen($reg_reason) < 1 ) {
      $_SESSION['error_msg'] = "Reason or answer section cannot be empty";
      header("location: " . BASE_URL . "topic/topicView.php?view_topic=" . $topic_id);
      exit(0);
    }

    if (strlen($reg_reason) > 300 ) {
      $_SESSION['error_msg'] = "Reason cannot longer than 300 characters";
      header("location: " . BASE_URL . "topic/topicView.php?view_topic=" . $topic_id);
      exit(0);
    }

    $sql = "INSERT INTO topic_user SET topic_id=?, user_id=?, reason=?";
    $result = modifyRecord($sql, 'iis', [$topic_id, $user_id, $reg_reason]);
    if ($result) {
      $_SESSION['success_msg'] = "You have successfully registered for the topic!";
      header("location: " . BASE_URL . "topic/topicView.php?view_topic=" . $topic_id);
      exit(0);
    } else {
      $_SESSION['error_msg'] = "Could not execute registration for topic";
    }

  }

  // unregister the user from the topic
  function unregisterTopicUser($topic_id, $user_id) {
    global $conn;

    // check whether topic exists at all
    $sql = "SELECT * FROM topics WHERE id=?";
    $topic = getSingleRecord($sql, 'i', [$topic_id]);
    if(is_null($topic)) {
      $_SESSION['error_msg'] = "Topic does not exist";
      header("location: " . BASE_URL . "index.php");
      exit(0);
    }

    // check whether user exists at all
    $sql = "SELECT * FROM users WHERE id=?";
    $topic = getSingleRecord($sql, 'i', [$user_id]);
    if(is_null($topic)) {
      $_SESSION['error_msg'] = "User does not exist";
      header("location: " . BASE_URL . "index.php");
      exit(0);
    }

    if (! canRegisterTopicUserByID( $topic_id )) {
      $_SESSION['error_msg'] = "No permissions to unregister for topic";
      header("location: " . BASE_URL . "index.php");
      exit(0);
    }

    if(! isUserRegisteredTopic($topic_id, $user_id)) {
      $_SESSION['error_msg'] = "You are not registered for the topic";
      header("location: " . BASE_URL . "topic/topicView.php?view_topic=" . $topic_id);
      exit(0);
    }

    $sql = "DELETE FROM topic_user WHERE topic_id=? AND user_id=?";
    $result = modifyRecord($sql, 'ii', [$topic_id, $user_id]);
    if ($result) {
      $_SESSION['success_msg'] = "You have successfully unregistered from the topic!";
      header("location: " . BASE_URL . "topic/topicView.php?view_topic=" . $topic_id);
      exit(0);
    } else {
      $_SESSION['error_msg'] = "Could not execute unregistration for topic";
    }
  }

  // --------------------------------------------------------------------------
  // Approve 
  // --------------------------------------------------------------------------
  // approve the users selection of the topic
  function approveTopic($topic_id, $user_id) {
    global $conn;

    // check whether topic exists at all
    $sql = "SELECT * FROM topics WHERE id=?";
    $topic = getSingleRecord($sql, 'i', [$topic_id]);
    if(is_null($topic)) {
      $_SESSION['error_msg'] = "Topic does not exist";
      header("location: " . BASE_URL . "index.php");
      exit(0);
    }

    // check whether user exists at all
    $sql = "SELECT * FROM users WHERE id=?";
    $topic = getSingleRecord($sql, 'i', [$user_id]);
    if(is_null($topic)) {
      $_SESSION['error_msg'] = "User does not exist";
      header("location: " . BASE_URL . "index.php");
      exit(0);
    }

    if (! canApproveTopicUser( $topic_id )) {
      $_SESSION['error_msg'] = "No permissions to approve a user for a topic";
      header("location: " . BASE_URL . "index.php");
      exit(0);
    }

    // start transaction
    mysqli_autocommit($conn, false);
    mysqli_begin_transaction($conn);

    try {
      $sql = "UPDATE topics SET approved_user_id=? WHERE id=?";
      $result1 = modifyRecord($sql, 'ii', [$user_id, $topic_id]);

      $sql = "DELETE FROM topic_user WHERE topic_id=?";
      $result2 = modifyRecord($sql, 'i', [$topic_id]);

      $sql = "DELETE FROM topic_user WHERE user_id=?";
      $result3 = modifyRecord($sql, 'i', [$user_id]);

      mysqli_commit($conn);
      mysqli_autocommit ($conn, true);

      $_SESSION['success_msg'] = "Diploma topic is successfully approved for student";
      header("location: " . BASE_URL . "topic/topicView.php?view_topic=" . $topic_id);
      exit(0);

    } catch(EXCEPTION $e){

      mysqli_rollback($conn);
      mysqli_autocommit ($conn, true);
      $_SESSION['error_msg'] = "Could not approve user for topic";
      throw $e;
    }
  }

  // unapprove the user from the topic
  function unapproveTopic($topic_id) {
    global $conn;

    if (! canApproveTopicUser( $topic_id )) {
      $_SESSION['error_msg'] = "No permissions to unapprove the user for a topic";
      header("location: " . BASE_URL . "index.php");
      exit(0);
    }

    // check whether topic exists at all
    $sql = "SELECT * FROM topics WHERE id=?";
    $topic = getSingleRecord($sql, 'i', [$topic_id]);
    if(is_null($topic)) {
      $_SESSION['error_msg'] = "Topic does not exist";
      return;
    }

    $sql = "UPDATE topics SET approved_user_id=-1 WHERE id=?";
    $result = modifyRecord($sql, 'i', [$topic_id]);
    if ($result) {
      $_SESSION['success_msg'] = "Diploma topic is successfully updated and now it can be selected for students";
      header("location: " . BASE_URL . "topic/topicView.php?view_topic=" . $topic_id);
      exit(0);
    } else {
      $_SESSION['error_msg'] = "Could not delete approved user of topic";
    }
  }


?>

