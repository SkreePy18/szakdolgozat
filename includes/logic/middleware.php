<?php

  // except for these pages, check for logged in user
  if(! in_array(basename($_SERVER['PHP_SELF']), ['login.php', 'signup.php'])) {
    // if user is NOT logged in, redirect them to login page
    if (!isset($_SESSION['user'])) {
      header("location: " . BASE_URL . "login.php");
      exit(0);
    }
  }

  // if user is logged in and the role is empty, redirect them to landing page
  if (isset($_SESSION['user']) && is_null($_SESSION['user']['role'])) {
    header("location: " . BASE_URL);
    exit(0);
  }


  // from _SERVER information assemble the URL + Query string
  function keepQueryServer() {
    if (array_key_exists('QUERY_STRING', $_SERVER)) {
      $query = $_SERVER['QUERY_STRING'];
    } else {
      $query = '';
    }
    parse_str($query, $query_array);
    $url = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
    if(count($query_array) == 0) {
      return ($url);
    } else {
      $new_query=http_build_query($query_array);

      return (htmlspecialchars($url)."?".$new_query);
    }
  }

  // add a new query string to the URL
  function addQueryServer($key, $value) {
    global $_SERVER;

    if (array_key_exists('QUERY_STRING', $_SERVER)) {
      $query = $_SERVER['QUERY_STRING'];
    } else {
      $query = '';
    }
    parse_str($query, $query_array);
    $query_array[$key] = $value;

    $new_query=http_build_query($query_array);
    $url = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

    return (htmlspecialchars($url)."?".$new_query);
  }

  // delete a query string from the URL
  function removeQueryServer($key) {
    global $_SERVER;
    
    if (array_key_exists('QUERY_STRING', $_SERVER)) {
      $query = $_SERVER['QUERY_STRING'];
    } else {
      $query = '';
    }
    parse_str($query, $query_array);
    unset($query_array[$key]);
    $url = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
    if(count($query_array) == 0) {
      return ($url);
    } else {
      $new_query=http_build_query($query_array);

      return (htmlspecialchars($url)."?".$new_query);
    }
  }

  // Accept a user ID and returns true if user is admin and false otherwise
  function isAdmin($user_id) {
    global $conn;
    $sql = "SELECT * FROM users WHERE id=? AND role_id=1 LIMIT 1";
    $user = getSingleRecord($sql, 'i', [$user_id]); // get single user from database
    if (!empty($user)) {
      return true;
    } else {
      return false;
    }
  }

  function getSupervisorRoleID() {
    return 4;
  }

  function getStudentRoleID() {
    return 3;
  }

  function getGuestRoleID() {
    return 2;
  }

  function hasPermissionTo($permisson_code){
    if(in_array(['permission_name' => $permisson_code], $_SESSION['userPermissions'])) {
      return true;
    } else {
      return false;
    }
  }

  
  // Update object by ID

  function canUpdateObjectByID($object_type, $object_id = null) {
    global $conn, $database_by_object;
    $permission = 'update-' . $object_type;
    
    // Special checks
    if($object_type == "user") {
      if ($object_id == $_SESSION['user']['id'] ) {
        return true;
      }
    } elseif($object_type == "semester") {
      if($object_id == 1) {
        return false;
      }
    }

    // Global, applies on every update
    if(in_array(['permission_name' => $permission], $_SESSION['userPermissions']) || isAdmin($_SESSION['user']['id'])) {
      if(array_key_exists($object_type, $database_by_object)) {
        $table = $database_by_object[$object_type];
        if(!$table) {
          return false;
        }
        $sql = "SELECT id FROM $table WHERE id = ?";
        $cat_results = getSingleRecord($sql, 'i', [$object_id]);
        if(is_null($cat_results)) {
          return false;
        } else {
          return true;
        }
      } else {
        return false;
      }
    } else {
      return false;
    }
  }

  function canDeleteUserByID( $user_id ) {
    if(in_array(['permission_name' => 'delete-user'], $_SESSION['userPermissions'])){

      // if current user is equal to the user to delete, do not allow it
      if ($user_id == $_SESSION['user']['id'] ) {
        $_SESSION['error_msg'] = "You cannot delete yourself"; 
        return false;
      }

      // check whether user exists at all
      $sql = "SELECT * FROM users WHERE id=?";
      $user_result = getSingleRecord($sql, 'i', [$user_id]);
      if(is_null($user_result)) {
        $_SESSION['error_msg'] = "User does not exist to delete it"; 
        return false;
      }

      // check whether the user has topic
      $sql = "SELECT id FROM topics WHERE user_id=?";
      $user_result = getMultipleRecords($sql, 'i', [$user_id]);
      if(count($user_result) > 0) {
        $_SESSION['error_msg'] = "Cannot delete user, it owns topics"; 
        return false;
      }

      // check whether the user belong to an approved topic
      $sql = "SELECT id FROM topics WHERE approved_user_id=?";
      $user_result = getMultipleRecords($sql, 'i', [$user_id]);
      if(count($user_result) > 0) {
        $_SESSION['error_msg'] = "Cannot delete user, it belongs to an approved topic"; 
        return false;
      }

      // check whether the user is registered for a topic
      $sql = "SELECT id FROM topic_user WHERE user_id=?";
      $user_result = getMultipleRecords($sql, 'i', [$user_id]);
      if(count($user_result) > 0) {
        $_SESSION['error_msg'] = "Cannot delete user, it belongs to a registered topic"; 
        return false;
      }

      return true;
    } else {
      $_SESSION['error_msg'] = "No permissions to delete role";
      return false;
    }
  }


  function canDeleteRoleByID($role_id = NULL) {
    if(in_array(['permission_name' => 'delete-role'], $_SESSION['userPermissions'])){
      // check whether role exists at all
      $sql = "SELECT * FROM roles WHERE id=?";
      $role_result = getSingleRecord($sql, 'i', [$role_id]);
      if(is_null($role_result)) {
        $_SESSION['error_msg'] = "Role does not exist to delete it";
        return false;
      }

      // check whether role is assigned to any user
      $sql = "SELECT id FROM users WHERE role_id=?";
      $role_result = getMultipleRecords($sql, 'i', [$role_id]);
      if(count($role_result) > 0) {
        $_SESSION['error_msg'] = "Cannot delete role, a user belongs to it"; 
        return false;
      }

      return true;
    } else {
      $_SESSION['error_msg'] = "No permissions to delete role";
      return false;
    }
  }


  function canAssignRolePermissionsByID($role_id = NULL) {
    if(in_array(['permission_name' => 'assign-role-permission'], $_SESSION['userPermissions'])){
      // check whether role exists at all
      $sql = "SELECT * FROM roles WHERE id=?";
      $role_result = getSingleRecord($sql, 'i', [$role_id]);
      if(is_null($role_result)) {
        return false;
      }

      return true;
    } else {
      return false;
    }
  }

  // ---------------------------------- Topic ---------------------------------

  function canViewTopicByID($topic_id) {
    if(in_array(['permission_name' => 'view-topic-list'], $_SESSION['userPermissions'])){

      // admin role can view everything
      if(isAdmin($_SESSION['user']['id'])) {
        return true;
      }

      $sql = "SELECT id, role_id from users WHERE id=?";
      $role = getSingleRecord($sql, 'i', [ $_SESSION['user']['id'] ]);

      $sql = "SELECT * from topics WHERE id=?";
      $topic = getSingleRecord($sql, 'i', [ $topic_id ]);

      // echo "<pre>"; print_r($topic['approved_user_id']); echo "</pre>";
      // echo "<pre>"; print_r($_SESSION['user']['id']); echo "</pre>";

      if($role['role_id'] == getStudentRoleID()) {  // if user is a student
        if(   ($topic['published'] == true)             // if topic is publshed
           && (   (($topic['semester_id'] == 1) && ($topic['approved_user_id'] == -1)) // if topic is in current semester and has no approved user
               || ($topic['approved_user_id'] == $_SESSION['user']['id']) ) // if is mine
          ) {
          return true;
        } else {
          return false;
        }
      }

      if(   ($role['role_id'] != getGuestRoleID())                      // if user is not a guest
         && ( ($topic['semester_id'] == 1) || canViewSemesterList() )   // if topic is in current semester or user can view semesters
        ) {
        return true;
      }

      return false;
    } else {
      return false;
    }
  }



  // checks if logged in user can update topic
  function canUpdateTopicByID($topic_id = null, $semester_matter = true){
    global $conn;

    if(in_array(['permission_name' => 'update-topic'], $_SESSION['userPermissions'])){
      // check whether topic exists at all and get owner id
      $sql = "SELECT user_id, approved_user_id,semester_id FROM topics WHERE id=?";
      $topic_result = getSingleRecord($sql, 'i', [$topic_id]);
      if(is_null($topic_result)) {
        return false;
      }

      // admin role can update anyway
      if(isAdmin($_SESSION['user']['id'])) {
        return true;
      }

      // if there is an approved user for the topic, we cannot edit it any more
      if ($topic_result['approved_user_id'] != -1) {
        return false;
      }

      // if the semester matters and the topic is not in the current semester, then users cannot edit it
      if($semester_matter && $topic_result['semester_id'] != 1) {
        return false;
      }

      // if current user is the author of the topic, then they can update the topic
      if ($topic_result['user_id'] == $_SESSION['user']['id'] ) {
        return true;
      } else { // if topic is not created by this author
        return false;
      }

    } else {
      return false;
    }
  }

  function canDeleteTopicByID($topic_id = null, $semester_matter = true) {
    global $conn;

    if(in_array(['permission_name' => 'delete-topic'], $_SESSION['userPermissions'])){
      // check whether topic exists at all and get owner id
      $sql = "SELECT user_id, approved_user_id,semester_id FROM topics WHERE id=?";
      $topic_result = getSingleRecord($sql, 'i', [$topic_id]);
      if(is_null($topic_result)) {
        return false;
      }

      // admin role can delete anyway
      if(isAdmin($_SESSION['user']['id'])) {
        return true;
      }

      // if there is an approved user for the topic, we cannot delete it any more
      if ($topic_result['approved_user_id'] != -1) {
        return false;
      }

      // if the semester matters and the topic is not in the current semester, then users cannot delete it
      if($semester_matter && $topic_result['semester_id'] != 1) {
        return false;
      }

      // if current user is the author of the topic, then they can delete the topic
      if ($topic_result['user_id'] == $_SESSION['user']['id'] ) {
        return true;
      } else { // if topic is not created by this author
        return false;
      }
    } else {
      return false;
    }

  }

  
  // this checks whether the user can register for a _specific_ topic
  function canRegisterTopicUserByID($topic_id = null) {
    global $conn;
    if(in_array(['permission_name' => 'register-topic-user'], $_SESSION['userPermissions'])){
      // check whether topic exists at all and get owner id
      $sql = "SELECT user_id FROM topics WHERE id=?";
      $topic_result = getSingleRecord($sql, 'i', [$topic_id]);
      if(is_null($topic_result)) {
        return(false);
      }
      $topic_user_id = $topic_result['user_id'];

      // if the author of the topic is the current user, then he cannot register for the topic
      if ($topic_user_id == $_SESSION['user']['id']) {
        return false;
      } else {
        return true;
      }

    } else {
      return false;
    }
  }

  // check whether the user has an already accepted/approved topic
  function hasUserApprovedTopic($user_id) {
    global $conn;

    $sql = "SELECT id FROM topics WHERE approved_user_id=?";
    $approved_topic = getSingleRecord($sql, 'i', [$user_id]);
    if(is_null($approved_topic)) {
      return false;
    } else {
      return true;
    }
  }

  


  function canApproveTopicUser($topic_id = null) {
    global $conn;
    if(in_array(['permission_name' => 'approve-topic-user'], $_SESSION['userPermissions'])){
      // admin role can approve anyway
      if(isAdmin($_SESSION['user']['id'])) {
        return true;
      }

      // user can approve user for topic that they themselves created
      $sql = "SELECT user_id FROM topics WHERE id=?";
      $topic_result = getSingleRecord($sql, 'i', [$topic_id]);
      if(is_null($topic_result)) {
        return(false);
      }
      $topic_user_id = $topic_result['user_id'];

      // if the author of the topic is the current user, then he cannot approve for the topic
      if ($topic_user_id == $_SESSION['user']['id']) {
        return true;
      } else {
        return false;
      }

    } else {
      return false;
    }
  }


  
  // ---------------------------------- Semester ------------------------------


  function canDeleteSemesterByID($semester_id = null) {
    global $conn;

    if(in_array(['permission_name' => 'delete-semester'], $_SESSION['userPermissions'])){
      // check whether semester exists at all
      $sql = "SELECT id FROM semesters WHERE id=?";
      $semester_result = getSingleRecord($sql, 'i', [$semester_id]);
      if(is_null($semester_result)) {
        $_SESSION['error_msg'] = "Semester does not exist to delete it";
        return false;
      }

      // we are not allowed to delete the current semester (id == 1)
      if($semester_id == 1) {
        $_SESSION['error_msg'] = "Cannot delete current semester";
        return false;
      }

      // we are not allowed to delete a semester with topics
      $sql = "SELECT id FROM topics WHERE semester_id=?";
      $semester_result = getMultipleRecords($sql, 'i', [$semester_id]);
      if(count($semester_result) > 0) {
        $_SESSION['error_msg'] = "Cannot delete semester with topics";
        return false;
      }

      return true;
    } else {
      $_SESSION['error_msg'] = "No permissions to delete semester";
      return false;
    }

  }


  function canDeleteCategoryByID($category_id = null) {
    global $conn;
    if(in_array(['permission_name' => 'delete-category'], $_SESSION['userPermissions'])){
      // check whether category exists at all
      $sql = "SELECT id FROM `categories` WHERE id = ?";
      $result = getSingleRecord($sql, 'i', [$category_id]);
      if(is_null($result)) {
        $_SESSION['error_msg'] = "Category does not exist to delete it. ID: '" . $category_id . "'";
        return false;
      }

      // we are not allowed to delete a semester with topics
      $sql = "SELECT id FROM topic_category WHERE category_id=?";
      $result = getMultipleRecords($sql, 'i', [$category_id]);
      if(count($result) > 0) {
        $_SESSION['error_msg'] = "Cannot delete category with topics";
        return false;
      }

      return true;
    } else {
      $_SESSION['error_msg'] = "No permissions to delete category";
      return false;
    }

  }


  
  // Opportunity related
  
  function canDeleteOpportunityByID($opportunity_id = null) {
    global $conn;
    if(in_array(['permission_name' => 'delete-category'], $_SESSION['userPermissions'])){
      // Check if opportunity exists
      $sql = "SELECT id FROM `opportunities` WHERE id = ?";
      $result = getSingleRecord($sql, 'i', [$opportunity_id]);
      if(is_null($result)) {
        $_SESSION['error_msg'] = "Opportunity does not exist to delete it. ID: '" . $opportunity_id . "'";
        return false;
      }

      return true;
    } else {
      // $_SESSION['error_msg'] = "No permissions to delete the opportunity";
      return false;
    }
  }

  function canViewOpportunityByID($opportunity_id) {
    if(in_array(['permission_name' => 'view-opportunity-list'], $_SESSION['userPermissions'])){

      // Check if opportunity exists
      $sql = "SELECT * from opportunities WHERE id=?";
      $opportunities = getSingleRecord($sql, 'i', [ $opportunity_id ]);

      if(is_null($opportunities)) {
        return false;
      }
      // admin role can view everything
      if(isAdmin($_SESSION['user']['id'])) {
        return true;
      }

      return true;
    } else {
      return false;
    }
  }

  function canGenerateCodeByID($opportunity_id, $user_id = NULL) {
    if(in_array(['permission_name' => 'generate-code'], $_SESSION['userPermissions'])){
      // Check if opportunity exists
      $sql = "SELECT * from opportunities WHERE id=?";
      $opportunities = getSingleRecord($sql, 'i', [ $opportunity_id ]);

      if(is_null($opportunities)) {
        return false;
      }

      if($user_id != NULL) {
        // Check whether the user has a token already generated or achieved the point
        $sql = "SELECT id FROM tokens WHERE user_id = ? AND opportunity_id = ?";
        $tokenResults = getSingleRecord($sql, 'ii', [$user_id, $opportunity_id]);
        if(!is_null($tokenResults)) {
          return false;
        }

        $sql = "SELECT id FROM excellence_points WHERE user_id = ? AND opportunity_id = ?";
        $pointResults = getSingleRecord($sql, 'ii', [$user_id, $opportunity_id]);
        if(!is_null($pointResults)) {
          return false;
        }
      }

      return true;
    } else {
      return false;
    }
  }

  function canImportPointsForOpportunityByID($opportunity_id, $user_id) {
    if(in_array(['permission_name' => 'generate-code'], $_SESSION['userPermissions'])) {
      $sql = "SELECT id FROM `excellence_points` WHERE user_id = ? AND opportunity_id = ?";
      $result = getSingleRecord($sql, 'ii', [$user_id, $opportunity_id]);
      if(!is_null($result)) {
        return false;
      }

      return true;
    }  else {
      return false;
    }
  }

  function canUserRedeemToken($user_id, $token) {
    if(in_array(['permission_name' => 'view-opportunity-list'], $_SESSION['userPermissions'])){

      // Check if opportunity exists
      $sql = "SELECT * from tokens WHERE token=? AND user_id = ?";
      $tokenInsance = getSingleRecord($sql, 'si', [ $token, $user_id]);

      if(is_null($tokenInsance)) {
        return false;
      }

      if($tokenInsance["redeemed"] == "yes") {
        return false;
      }
      return true;
    } else {
      return false;
    }
  }

  // Type of points
  // checks if logged in user can update semester
  function canViewTypeByID($type_id = null){
    global $conn;

    if(in_array(['permission_name' => 'update-semester'], $_SESSION['userPermissions'])){
      // check whether semester exists at all
      $sql = "SELECT id FROM semesters WHERE id=?";
      $semester_result = getSingleRecord($sql, 'i', [$type_id]);
      if(is_null($semester_result)) {
        return false;
      }

      // we are not allowed to update the current semester (id == 1)
      if($type_id > 1) {
        return true;
      }
      else {
        return false;
      }
    } else {
      return false;
    }
  }


  function canDeleteTypeByID($semester_id = null) {
    global $conn;

    if(in_array(['permission_name' => 'delete-semester'], $_SESSION['userPermissions'])){
      // check whether semester exists at all
      $sql = "SELECT id FROM opportunity_points_type WHERE id=?";
      $semester_result = getSingleRecord($sql, 'i', [$semester_id]);
      if(is_null($semester_result)) {
        $_SESSION['error_msg'] = "Semester does not exist to delete it";
        return false;
      }

      return true;
    } else {
      $_SESSION['error_msg'] = "No permissions to delete semester";
      return false;
    }
  }

  function canUpdateOpportunityByID($object_id) {
    global $conn;

    if(in_array(['permission_name' => 'update-opportunity'], $_SESSION['userPermissions'])){
      $sql = "SELECT id, owner_id FROM `opportunities` WHERE id = ?";
      $result = getSingleRecord($sql, 'i', [$object_id]);

      if(is_null($result)) {
        return false;
      }

      if(isAdmin($_SESSION['user']['id'])) {
        return true;
      }

      if($_SESSION['user']['id'] != $result['owner_id']) {
        return false;
      }

      return true;
    }
  }