<?php


// Accept a user object, validates user and return an array with the error messages
  function validateUser($user, $ignoreFields) {
    global $conn;
    $errors = [];

    if ( isset($user['username']) && strlen($user['username']) < 6 ) {
      $errors['username'] = "The username must be longer than 6 character";
    }
    if ( isset($user['username']) &&  strlen($user['username']) > 255 ) {
      $errors['username'] = "The username must be shorter than 255 character";
    }

    if ( isset($user['fullname']) && strlen($user['fullname']) < 8 ) {
      $errors['fullname'] = "The Fullname must be longer than 8 character";
    }
    if ( isset($user['fullname']) && strlen($user['fullname']) > 255 ) {
      $errors['fullname'] = "The Fullname must be shorter than 255 character";
    }

    if ( isset($user['neptuncode']) && strlen($user['neptuncode']) < 6 ) {
      $errors['neptuncode'] = "The Neptun code must be longer than 6 character";
    }
    if ( isset($user['neptuncode']) && strlen($user['neptuncode']) > 255 ) {
      $errors['neptuncode'] = "The Neptun code must be shorter than 255 character";
    }

    // except on the login page, we check the "quality" of the password
    if ( (!in_array('login_btn', $ignoreFields)) && isset($user['password']) ) {
      $password = $user['password'];
      $uppercase = preg_match('@[A-Z]@', $password);
      $lowercase = preg_match('@[a-z]@', $password);
      $number    = preg_match('@[0-9]@', $password);
      // $specialChars = preg_match('@[^\w]@', $password);

      if(!$uppercase || !$lowercase || !$number ) {
        $errors['password'] = "Password should should include at least one upper case letter and one number";
      }
    }
    if ( isset($user['password']) && strlen($user['password']) < 8) {
      $errors['password'] = "Password should be at least 8 characters";
    }
    if ( isset($user['password']) && strlen($user['password']) > 255 ) {
      $errors['password'] = "The password must be shorter than 255 character";
    }

    // in all cases, except login, the two passwords must match
    if ( !in_array('login_btn', $ignoreFields) ) {

      // password and password confirmation must match
      if (   (isset($user['password']) && isset($user['passwordConf'])) 
          && ($user['password'] !== $user['passwordConf']) ) {
        $errors['passwordConf'] = "The two passwords do not match";
      }
    }

    // in all cases, except login, signup and creation, there is an old password
    if (   !in_array('login_btn', $ignoreFields)
        && !in_array('signup_btn', $ignoreFields)
        && !in_array('save_user', $ignoreFields) ) {
      // if old password is not ignored and set, then check it
      if ( !in_array('passwordOld', $ignoreFields) && isset($user['passwordOld']) ) {
        $sql = "SELECT * FROM users WHERE id=? LIMIT 1";
        $oldUser = getSingleRecord($sql, 'i', [$user['user_id']]);
        $prevPasswordHash = $oldUser['password'];
        if (!password_verify($user['passwordOld'], $prevPasswordHash)) {
          $errors['passwordOld'] = "The old password does not match";
        }
      }
      // if old password is not set, then it will be signaled as required below
    }

    // the username, fullname, neptuncode, email should be unique for each user during signup and creation
    if (   in_array('signup_btn', $ignoreFields)
        || in_array('save_user', $ignoreFields) ) {

      $sql = "SELECT * FROM users WHERE username=? OR fullname=? OR neptuncode=? OR email=? LIMIT 1";
      $oldUser = getSingleRecord($sql, 'ssss', [$user['username'], $user['fullname'], $user['neptuncode'], $user['email']]);

      if (!empty($oldUser['username']) && $oldUser['username'] === $user['username']) { // if user exists
        $errors['username'] = "Username already exists";
      }
      if (!empty($oldUser['fullname']) && $oldUser['fullname'] === $user['fullname']) { // if user exists
        $errors['fullname'] = "Fullname already exists";
      }
      if (!empty($oldUser['neptuncode']) && $oldUser['neptuncode'] === $user['neptuncode']) { // if user exists
        $errors['neptuncode'] = "Neptun code already exists";
      }
      if (!empty($oldUser['email']) && $oldUser['email'] === $user['email']) { // if user exists
        $errors['email'] = "Email already exists";
      }
    }

    // during update, user should not change its username, except admin can change the username of others
    if ( in_array('update_user', $ignoreFields) ) {
      // admin may modify the username, check for duplicate
      if(isAdmin($_SESSION['user']['id'])) {
        $sql = "SELECT * FROM users WHERE username=? AND id<>? LIMIT 1";
        $oldUser = getSingleRecord($sql, 'si', [ $user['username'], $user['user_id'] ]);
        if( !empty($oldUser) ) {
          $errors['username'] = "Username already exists";
        }
      } else {
        $sql = "SELECT * FROM users WHERE id=? LIMIT 1";
        $oldUser = getSingleRecord($sql, 'i', [ $user['user_id'] ]);
        if( !empty($oldUser) && $oldUser['username'] !== $user['username'] ) {
          $errors['username'] = "Username cannot be changed by user";
        }
      }

      $sql = "SELECT * FROM users WHERE fullname=? AND id<>? LIMIT 1";
      $oldUser = getSingleRecord($sql, 'si', [ $user['fullname'], $user['user_id'] ]);
      if ( !empty($oldUser) ) {
        $errors['fullname'] = "Fullname already exists";
      }

      $sql = "SELECT * FROM users WHERE neptuncode=? AND id<>? LIMIT 1";
      $oldUser = getSingleRecord($sql, 'si', [ $user['neptuncode'], $user['user_id'] ]);
      if ( !empty($oldUser) ) {
        $errors['neptuncode'] = "Neptun code already exists";
      }

      $sql = "SELECT * FROM users WHERE email=? AND id<>? LIMIT 1";
      $oldUser = getSingleRecord($sql, 'si', [ $user['email'], $user['user_id'] ]);
      if ( !empty($oldUser) ) {
        $errors['email'] = "Email already exists";
      }
    }

    // required fields
    foreach ($user as $key => $value) {
      if (in_array($key, $ignoreFields)) {
        continue;
      }
      if (empty($user[$key])) {
        $errors[$key] = "This field is required";
      }
    }
    return $errors;
  }

  // Accept a role object, validates role and return an array with the error messages
  function validateRole($role, $ignoreFields) {
    global $conn;
    $errors = [];

    foreach ($role as $key => $value) {
      if (in_array($key, $ignoreFields)) {
          continue;
      }
      if (empty($role[$key])) {
        $errors[$key] = "This field is required";
      }
    }
    return $errors;
  }

  
  // Accept a semester object, validates semester and return an array with the error messages
  function validateSemester($semester, $ignoreFields) {
    global $conn;
    $errors = [];

    foreach ($semester as $key => $value) {
      if (in_array($key, $ignoreFields)) {
          continue;
      }
      if (empty($semester[$key])) {
        $errors[$key] = "This field is required";
      }
    }
    return $errors;
  }

  