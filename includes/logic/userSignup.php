<?php include_once(INCLUDE_PATH . '/logic/validation.php') ?>
<?php

  // variable declaration
  $username = "";
  $fullname = "";
  $neptuncode = "";
  $email  = "";
  $errors  = [];


  function loginById($user_id) {
    global $conn;
    $sql = "SELECT u.id, u.role_id, u.username, r.name as role FROM users u LEFT JOIN roles r ON u.role_id=r.id WHERE u.id=? LIMIT 1";
    $user = getSingleRecord($sql, 'i', [$user_id]);

    if (!empty($user)) {
      // put logged in user into session array
      $_SESSION['user'] = $user;
      $_SESSION['success_msg'] = "You are now logged in";
      // determine permissions for the user
      $permissionsSql = "SELECT p.name as permission_name FROM permissions as p
                          JOIN permission_role as pr ON p.id=pr.permission_id
                          WHERE pr.role_id=?";
      $userPermissions = getMultipleRecords($permissionsSql, "i", [$user['role_id']]);
      $_SESSION['userPermissions'] = $userPermissions;
      // redirect to homepage
      header('location: ' . BASE_URL . 'index.php');
      exit(0);
    }
  }

  // SIGN UP USER
  if (isset($_POST['signup_btn'])) {
    $user_data = filter_input_array(INPUT_POST, [
                  "username" => FILTER_SANITIZE_STRING,
                  "fullname" => FILTER_SANITIZE_STRING,
                  "neptuncode" => FILTER_SANITIZE_STRING,
                  "email" => FILTER_SANITIZE_STRING,
                  "password" => FILTER_SANITIZE_STRING,
                  "passwordConf" => FILTER_SANITIZE_STRING
                 ]);
    // receive all input values from the form. No need to escape... bind_param takes care of escaping
    $username = $user_data['username'];
    $fullname = $user_data['fullname'];
    $neptuncode = $user_data['neptuncode'];
    $email = $user_data['email'];

    // validate form values
    $errors = validateUser($user_data, ['signup_btn']);

    $password = password_hash($user_data['password'], PASSWORD_DEFAULT); //encrypt the password before saving in the database

    // if no errors, proceed with signup
    if (count($errors) === 0) {
      // insert user into database
      $query = "INSERT INTO users SET role_id=2, username=?, fullname=?, neptuncode=?, email=?, password=?";
      $stmt = $conn->prepare($query);
      $stmt->bind_param('sssss', $username, $fullname, $neptuncode, $email, $password);
      $result = $stmt->execute();
      if ($result) {
        $user_id = $stmt->insert_id;
        $stmt->close();
        loginById($user_id); // log user in
       } else {
         $_SESSION['error_msg'] = "Could not register user data";
      }
    } else {
      $_SESSION['error_msg'] = "Could not register user";
    }
  }


  // USER LOGIN
  if (isset($_POST['login_btn'])) {
    $user_data = filter_input_array(INPUT_POST, [
                  "username" => FILTER_SANITIZE_STRING,
                  "password" => FILTER_SANITIZE_STRING,
                 ]);
    $username = $user_data['username'];
    $password = $user_data['password']; // don't escape passwords.
    // validate form values
    $errors = validateUser($user_data, ['login_btn']);

    if (empty($errors)) {
      $sql = "SELECT * FROM users WHERE username=? OR email=? LIMIT 1";
      $user = getSingleRecord($sql, 'ss', [$username, $username]);
      if (!empty($user)) { // if user was found
        if (password_verify($password, $user['password'])) { // if password matches
          // log user in
          loginById($user['id']);
        } else { // if password does not match
          $_SESSION['error_msg'] = "Wrong credentials";
        }
      } else { // if no user found
        $_SESSION['error_msg'] = "Wrong credentials";
      }
    }
  }
