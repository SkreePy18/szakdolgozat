<?php include_once(INCLUDE_PATH . '/logic/validation.php') ?>
<?php
  // variable declaration. These variables will be used in the user form
  $user_id = 0;
  $role_id = 0;
  $username = "";
  $fullname = "";
  $neptuncode = "";
  $email = "";
  $password = "";
  $passwordConf = "";
  $isEditing = false;
  $isDeleting = false;
  $users = array();
  $errors = array();

  // ACTION: Save User
  if (isset($_POST['save_user'])) {  // if user clicked save_user button ...
      saveUser();
  }
  // ACTION: update user
  if (isset($_POST['update_user'])) { // if user clicked update_user button ...
      updateUser();
  }
  // ACTION: fetch user for editing
  if (isset($_GET["edit_user"])) {
    editUser();
  }
  // ACTION: Delete user with confirmation
  if (isset($_GET['delete_user'])) {
    deleteUser();
  }
  // ACTION: Delete user with confirmation
  if (isset($_POST['force_delete_user'])) {
    forcedeleteUser();
  }

  function getAllRoles(){
    global $conn;
    $sql = "SELECT id, name FROM roles";
    $roles = getMultipleRecords($sql);
    return $roles;
  }

  // retrieve all users except the user itself who is executing this
  function getAllUsers(){
    global $conn;
    // for every user, select a user role name from roles table, and then id, role_id and username from user table
    // where the role_id on user table matches the id on roles table
    $sql = "SELECT r.name as role, u.id, u.role_id, u.username, u.fullname, u.neptuncode
            FROM users u
            LEFT JOIN roles r ON u.role_id=r.id
            WHERE role_id IS NOT NULL AND u.id != ?";

    $users = getMultipleRecords($sql, 'i', [$_SESSION['user']['id']]);
    return $users;
  }

  // Save user to database
  function saveUser(){
    global $conn, $errors, $username, $fullname, $neptuncode, $role_id, $email, $isEditing;

    // validate data
    $user_data = filter_input_array(INPUT_POST, [
                  "username" => FILTER_SANITIZE_STRING,
                  "fullname" => FILTER_SANITIZE_STRING,
                  "neptuncode" => FILTER_SANITIZE_STRING,
                  "email" => FILTER_SANITIZE_STRING,
                  "password" => FILTER_SANITIZE_STRING, 
                  "passwordConf" => FILTER_SANITIZE_STRING
                 ]);

    // receive all input values from the form
    $username = $user_data['username'];
    $fullname = $user_data['fullname'];
    $neptuncode = $user_data['neptuncode'];
    $email = $user_data['email'];
    if (isset($_POST['role_id'])) {
      $role_id = filter_input(INPUT_POST, 'role_id', FILTER_SANITIZE_NUMBER_INT);
    }

    if (! canCreateUser( )) {
      $_SESSION['error_msg'] = "No permissions to create user";
      header("location: " . BASE_URL . "admin/users/userList.php");
      exit(0);
    }

    $errors = validateUser($user_data, ['save_user']);
    if (count($errors) === 0) {
      $password = password_hash($user_data['password'], PASSWORD_DEFAULT); //encrypt the password before saving in the database

      $sql = "INSERT INTO users SET username=?, fullname=?, neptuncode=?, role_id=?, email=?, password=?";
      $result = modifyRecord($sql, 'sssiss', [$username, $fullname, $neptuncode, $role_id, $email, $password]);

      if($result){
        $_SESSION['success_msg'] = "User account created successfully";
        header("location: " . BASE_URL . "admin/users/userList.php");
        exit(0);
      } else {
        $_SESSION['error_msg'] = "Could not create user data";
      }
    } else {
      $_SESSION['error_msg'] = $errors;
    }
  }

  function updateUser() {
    global $conn, $errors, $user_id, $username, $fullname, $neptuncode, $role_id, $email, $isEditing;

    // validate data
    $user_data = filter_input_array(INPUT_POST, [
                  "user_id" => FILTER_SANITIZE_NUMBER_INT,
                  "username" => FILTER_SANITIZE_STRING,
                  "fullname" => FILTER_SANITIZE_STRING,
                  "neptuncode" => FILTER_SANITIZE_STRING,
                  "email" => FILTER_SANITIZE_STRING
                 ]);

    // receive all input values from the form
    $user_id = $user_data['user_id'];
    $username = $user_data['username'];
    $fullname = $user_data['fullname'];
    $neptuncode = $user_data['neptuncode'];
    $email = $user_data['email'];
    if (isset($_POST['role_id'])) {
      $role_id = filter_input(INPUT_POST, 'role_id', FILTER_SANITIZE_NUMBER_INT);
    }

    // check permission to update the user data
    if (! canUpdateUserByID( $user_id )) {
      $_SESSION['error_msg'] = "No permissions to update user";
      header("location: " . BASE_URL . "admin/users/userList.php");
      exit(0);
    }

    // if any non-zero password is specified, signal that we are changing password
    $change_password = false;
    if(   (isset($_POST['passwordOld']) && strlen($_POST['passwordOld']) > 0)
       || (isset($_POST['password']) && strlen($_POST['password']) > 0) 
       || (isset($_POST['passwordConf']) && strlen($_POST['passwordConf']) > 0) ) {
      $password_data = filter_input_array(INPUT_POST, [
                        "passwordOld" => FILTER_SANITIZE_STRING,
                        "password" => FILTER_SANITIZE_STRING,
                        "passwordConf" => FILTER_SANITIZE_STRING
                       ]);
      $user_data['passwordOld']  = $password_data['passwordOld'];
      $user_data['password']     = $password_data['password'];
      $user_data['passwordConf'] = $password_data['passwordConf'];
      $change_password = true;
    }

    if ($change_password) {
      // the admin does not have to specify the old password
      if(isAdmin($_SESSION['user']['id'])) {
        $ignored = ['update_user', 'passwordOld'];
      } else {
        $ignored = ['update_user'];
      }
    } else {
      $ignored = ['update_user', 'passwordOld', 'password', 'passwordConf'];
    }
    $errors = validateUser($user_data, $ignored);

    if (count($errors) === 0) {
      if ($change_password) {
        $password = password_hash($user_data['password'], PASSWORD_DEFAULT); //encrypt the password before saving in the database
        if (canAssignUserRole()) {
          $sql = "UPDATE users SET username=?, fullname=?, neptuncode=?, role_id=?, email=?, password=? WHERE id=?";
          $result = modifyRecord($sql, 'sssissi', [$username, $fullname, $neptuncode, $role_id, $email, $password, $user_id]);
        } else {
          $sql = "UPDATE users SET username=?, fullname=?, neptuncode=?, email=?, password=? WHERE id=?";
          $result = modifyRecord($sql, 'sssssi', [$username, $fullname, $neptuncode, $email, $password, $user_id]);
        }
      } else {
        if (canAssignUserRole()) {
          $sql = "UPDATE users SET username=?, fullname=?, neptuncode=?, role_id=?, email=? WHERE id=?";
          $result = modifyRecord($sql, 'sssisi', [$username, $fullname, $neptuncode, $role_id, $email, $user_id]);
        } else {
          $sql = "UPDATE users SET username=?, fullname=?, neptuncode=?, email=? WHERE id=?";
          $result = modifyRecord($sql, 'ssssi', [$username, $fullname, $neptuncode, $email, $user_id]);
        }
      }
      if ($result) {
        $_SESSION['success_msg'] = "User account successfully updated";
        if(canViewUserList()) {
          header("location: " . BASE_URL . "admin/users/userList.php");
        } else {
          header("location: " . BASE_URL . "index.php");
        } 
        exit(0);
      } else {
        $_SESSION['error_msg'] = "Could not update user data";
      }
    } else {
      $_SESSION['error_msg'] = "Could not update user";
    }
    $isEditing = true;
  }

  function editUser(){
    global $conn, $user_id, $role_id, $username, $fullname, $neptuncode, $email, $isEditing;

    $user_id = filter_input(INPUT_GET, 'edit_user', FILTER_SANITIZE_NUMBER_INT);

    if (! canUpdateUserByID( $user_id )) {
      $_SESSION['error_msg'] = "No permissions to edit user";
      header("location: " . BASE_URL . "admin/users/userList.php");
      exit(0);
    }

    $sql = "SELECT * FROM users WHERE id=?";
    $user = getSingleRecord($sql, 'i', [$user_id]);

    $role_id = $user['role_id'];
    $username = $user['username'];
    $fullname = $user['fullname'];
    $neptuncode = $user['neptuncode'];
    $email = $user['email'];
    $isEditing = true;
  }


  function deleteUser() {
    global $conn, $user_id, $username, $isDeleting;

    $user_id = filter_input(INPUT_GET, 'delete_user', FILTER_SANITIZE_NUMBER_INT);

    if (! canDeleteUserByID( $user_id )) {
      // $_SESSION['error_msg'] = "No permissions to delete role";
      header("location: " . BASE_URL . "admin/users/userList.php");
      exit(0);
    }

    $sql = "SELECT * FROM users WHERE id=?";
    $user = getSingleRecord($sql, 'i', [$user_id]);

    $role_id = $user['role_id'];
    $username = $user['username'];
    $fullname = $user['fullname'];
    $neptuncode = $user['neptuncode'];
    $email = $user['email'];
    $isDeleting = true;
  }


  function forcedeleteUser() {
    global $conn, $user_id;

    $user_id = filter_input(INPUT_POST, 'user_id', FILTER_SANITIZE_NUMBER_INT);

    if (! canDeleteUserByID( $user_id )) {
      // $_SESSION['error_msg'] = "No permissions to delete user";
      header("location: " . BASE_URL . "admin/users/userList.php");
      exit(0);
    }

    $sql = "DELETE FROM users WHERE id=?";
    $result1 = modifyRecord($sql, 'i', [$user_id]);

    if ($result1 && $result2) {
      $_SESSION['success_msg'] = "User have been deleted!";
      header("location: " . BASE_URL . "admin/users/userList.php");
      exit(0);
    } else {
      $_SESSION['error_msg'] = "Could not delete user";
    }
  }

