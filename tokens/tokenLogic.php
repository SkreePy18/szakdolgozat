<?php include_once(INCLUDE_PATH . '/logic/validation.php') ?>
<?php include_once(INCLUDE_PATH . '/logic/qrCode.php') ?>
<?php
  // variable declaration. These variables will be used in the semester form
  $opportunity_id = 0;
  $opportunity = -1;
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

  if(isset($_GET['opportunity'])) {
    getTokensOfOpportunity();
  }

  if(isset($_GET['save_token'])) {
    saveFile();
  }

  if(isset($_GET['delete_token'])) {
    deleteToken();
  }




  function getTokens(){
    global $conn;
    $sql = "SELECT * FROM tokens";
    $tokens = getMultipleRecords($sql);
    return $tokens;
  }

  function getTokensOfOpportunity() {
    global $conn, $tokens, $opportunity;
    $opportunity = filter_input(INPUT_GET, "opportunity", FILTER_SANITIZE_NUMBER_INT);
    $sql = "SELECT * FROM tokens WHERE opportunity_id = ?";
    $tokens = getMultipleRecords($sql, 'i', [$opportunity]);
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

    // Token generation - We will generate QR codes by these hexa numbers
    $token = bin2hex(random_bytes(10));

    $sql = "INSERT INTO tokens (token, opportunity_id, user_id, generated_by, expiration_date) VALUES (?, ?, ?, ?, ?)";
    $result = modifyRecord($sql, 'siiis', [$token, $opportunity_id, $user_id, $generated_by, $expiration_date]);
                                    
    if($result) {
      $QRCode = generateQRCode($token);
      if($QRCode){
        $_SESSION['success_msg'] = "QR code has been successfully created";
        $QRCode->saveToFile(__DIR__ . "/qrCodes/" . $token . ".png");
        header("location: " . BASE_URL . "opportunities/opportunityFilter.php");
        exit(0);
      } else {
        $_SESSION['error_msg'] = "Could not create QR code";
      }
    }
  }

  function deleteToken() {
    global $conn, $token, $opportunity;
    $token_data = filter_input_array(INPUT_GET, [
      "delete_token"            => FILTER_SANITIZE_STRING,
      "opportunity"             => FILTER_SANITIZE_NUMBER_INT
    ]);

    $token = $token_data['delete_token'];
    $opportunity_id = $token_data['opportunity'];

    $result = removeToken($token, $opportunity_id);

    if(!$result) {
      $_SESSION['error_msg'] = "You cannot delete this token!";
      header("location: " . BASE_URL . "tokens/tokenList.php" . " ?opportunity=" . $opportunity_id);
      exit(0);
    } else {
      $_SESSION['success_msg'] = "Token has been successfully deleted!";
      header("location: " . BASE_URL . "tokens/tokenList.php" . " ?opportunity=" . $opportunity_id);
      exit(0);
    }
  }

  function removeToken($token, $opportunity_id) {
    $sql = "DELETE FROM `tokens` WHERE token=? AND opportunity_id=?";
    $result = modifyRecord($sql, 'si', [$token, $opportunity_id]);
    
    if(!$result) {
      return false;
    } else {
      $file = __DIR__ . "/qrCodes/" . $token . ".png";
      if(file_exists($file)) {
        unlink($file);
        return true;
      }
    }

    return true;
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

    $sql = "UPDATE tokens SET redeemed='yes' WHERE token=? AND user_id = ?";
    $result = modifyRecord($sql, 'si', [$token, $user_id]);

    if($result) {
      return insertPoints($user_id, $token);
    } else {
      $_SESSION['error_msg'] = "You cannot redeem this token!";
      header("location: " . BASE_URL . "tokens/redeemToken.php");
      exit(0);
    }
  }

  function insertPoints($user_id, $token) {
    $sql = "SELECT * FROM `tokens` WHERE token = ? AND user_id = ?";
    $result = getSingleRecord($sql, 'si', [$token, $user_id]);
    if($result) {
      $sql = "INSERT INTO excellence_points (opportunity_id, user_id) VALUES (?, ?)";
      $insert = modifyRecord($sql, "ii", [$result['opportunity_id'], $result['user_id']]);
      if($insert) {
        $_SESSION['success_msg'] = "You have successfully redeemed the token!";
        header("location: " . BASE_URL . "tokens/redeemToken.php");
        exit(0);
      } else {
        $_SESSION['error_msg'] = "You cannot redeem this token!";
        header("location: " . BASE_URL . "tokens/redeemToken.php");
        exit(0);
      }
    }
  }

  function saveFile() {
      // Get parameters
      // $token = filter_input(INPUT_GET, "save_token", FILTER_SANITIZE_STRING);
      // $filename = $token . ".png";
      // $file = BASE_URL . "tokens/qrCode/".$filename;
      
      // header("Content-Type: image/png");
      // header("Content-Disposition: attachment; filename=".$filename);
      // while (ob_get_level()) {
          // ob_end_clean();
      // }
      // readfile($file);
  }
