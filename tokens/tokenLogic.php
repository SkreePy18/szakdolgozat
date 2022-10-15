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


   // ACTION: Fetch data for viewing opportunity
   if (isset($_POST["generate_token"])) {
    generateToken();
  }

  // Action: Click on redeem button
  if(isset($_POST["redeem"])) {
    redeemToken();
  }




  function getTokens(){
    global $conn;
    $sql = "SELECT * FROM tokens";
    $tokens = getMultipleRecords($sql);
    return $tokens;
  }



  function generateToken() {
    $token_data = filter_input_array(INPUT_POST, [
                                      "user_id"           => FILTER_SANITIZE_NUMBER_INT,
                                      "token_type"        => FILTER_SANITIZE_STRING,
                                      "owner_id"          => FILTER_SANITIZE_NUMBER_INT,
                                      "opportunity_id"    => FILTER_SANITIZE_NUMBER_INT,
                                      "expiration_date"   => FILTER_SANITIZE_STRING
                                    ]);

    $token_type       = $token_data["token_type"];
    $opportunity_id   = $token_data["opportunity_id"];
    $expiration_date  = $token_data["expiration_date"];
    $user_id          = $token_data["user_id"];
    $generated_by     = $token_data["owner_id"];
    
    if($token_type == "hexa") {
      // Token generation
      $token = bin2hex(random_bytes(10));

      $sql = "INSERT INTO tokens (token, opportunity_id, user_id, generated_by, expiration_date) VALUES (?, ?, ?, ?, ?)";
      $result = modifyRecord($sql, 'siiis', [$token, $opportunity_id, $user_id, $generated_by, $expiration_date]);

      if($result){
        $_SESSION['success_msg'] = "Opportunity has been successfully published";
        header("location: " . BASE_URL . "tokens/opportunityFilter.php");
        exit(0);
      } else {
        $_SESSION['error_msg'] = "Could not create opportunity";
      }

    } elseif($token_type = "qr") {
      return false;
    }

  }

  function redeemToken() {
    $token_data = filter_input_array(INPUT_POST, [
      "user_id"   => FILTER_SANITIZE_NUMBER_INT,
      "token"     => FILTER_SANITIZE_STRING
    ]);

    $user_id = $token_data["user_id"];
    $token   = $token_data["token"];

    if(!canUserRedeemToken($user_id, $token)) {
      $_SESSION['error_msg'] = "You cannot redeem this token!";
      header("location: " . BASE_URL . "tokens/redeemToken.php");
      exit(0);
    }

    $sql = "UPDATE tokens SET redeemed=1 WHERE token=? AND user_id = ?";
    $result = modifyRecord($sql, 'si', [$token, $user_id]);

    if($result) {
      $_SESSION['success_msg'] = "You have successfully redeemed the token!";
      header("location: " . BASE_URL . "tokens/redeemToken.php");
      exit(0);
    } else {
      $_SESSION['error_msg'] = "You cannot redeem this token!";
      header("location: " . BASE_URL . "tokens/redeemToken.php");
      exit(0);
    }
  }