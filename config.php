<?php
  include_once('error_handler.php');

  /////////////////////////////////////////////////////////////////////////////
  // start session
  if (!isset($_SESSION)) {
  	session_start();
  }

  // Session timeout duration in seconds
  // Specify value lesser than the PHPs default timeout of 24 minutes
  $timeout = 1800;
  $cookie_domain = '/';
       
  // Check existing timeout variable
  if( isset( $_SESSION[ 'lastaccess' ] ) ) {
    // Time difference since user sent last request
    $duration = time() - intval( $_SESSION[ 'lastaccess' ] );
    // Destroy if last request was sent before the current time minus last request
    if( $duration > $timeout ) {
      // Clear the session
      session_unset();
      // Destroy the session
      session_destroy();
      // Restart the session
      session_start();
    }
  }
  // Set the last request variable
  $_SESSION['lastaccess'] = time();
  // echo '<p>' . print_r($_SESSION) . "duration: ". $duration .'</p>';

  // to avoid session fixation:
  // https://owasp.org/www-community/attacks/Session_fixation
  if (!isset($_SESSION['created'])) {
      $_SESSION['created'] = time();
  } else if (time() - $_SESSION['created'] > 3600) {
      // session started more than 60 minutes ago
      session_regenerate_id(true);    // change session ID for the current session and invalidate old session ID
      $_SESSION['created'] = time();  // update creation time
  }

  /////////////////////////////////////////////////////////////////////////////
  // Table by object name - remove redundancy of functions
  $database_by_object = array(
    'point-type'  => "opportunity_points_type",
    'user'        => 'users',
    'role'        => 'roles',
    'category'    => 'categories',
    'semester'    => 'semesters',
    'opportunity' => 'opportunities',
  );



  /////////////////////////////////////////////////////////////////////////////


  mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
	// connect to database
	$conn = new mysqli("localhost", "root", "", "user-accounts");
	// Check connection
	if ($conn->connect_error) {
	    die("Connection failed: " . $conn->connect_error);
	}

  // switch on transactions
  mysqli_autocommit ($conn, true);

  // define global constants
	define ('ROOT_PATH', realpath(dirname(__FILE__))); // path to the root folder
	define ('INCLUDE_PATH', realpath(dirname(__FILE__) . '/includes' )); // Path to includes folder
	define ('BASE_URL', 'https://localhost/szakdolgozat/'); // the home url of the website
	define ('APP_NAME', "Diploma topic selection web site"); // name of the application

	define ('RANDOM_SECURITY', 'XXXXXXXXXXXX'); // random seed for CSRF token

  function getMultipleRecords($sql, $types = null, $params = []) {
    global $conn;
    $stmt = $conn->prepare($sql);
    if (!empty($types) && !empty($params)) { // parameters must exist before you call bind_param() method
      $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $user;
  }
  function getSingleRecord($sql, $types = null, $params = []) {
    global $conn;
    $stmt = $conn->prepare($sql);
    if (!empty($types) && !empty($params)) { // parameters must exist before you call bind_param() method
      $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    return $user;
  }
  function modifyRecord($sql, $types, $params) {
    global $conn;
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
  }

  // xss mitigation functions
  // If your database is already poisoned or you want to deal with XSS at time of output, OWASP recommends creating a custom wrapper function for echo, and using it EVERYWHERE you output user-supplied values:
  function xssafe($data,$encoding='UTF-8')
  {
    return htmlspecialchars($data,ENT_QUOTES | ENT_HTML401,$encoding);
  }
  function xecho($data)
  {
    echo xssafe($data);
  }

  include_once(INCLUDE_PATH . '/logic/middleware.php');

