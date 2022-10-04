<?php 

function getClientFingerprint()
{
    $sid = RANDOM_SECURITY;
    if (isset($_SERVER['HTTP_USER_AGENT'])) {
        $sid .= $_SERVER['HTTP_USER_AGENT'];
    }
    if (isset($_SERVER['HTTP_ACCEPT'])) {
        $sid .= $_SERVER['HTTP_ACCEPT'];
    }
    if (isset($_SERVER['HTTP_ACCEPT_ENCODING'])) {
        $sid .= $_SERVER['HTTP_ACCEPT_ENCODING'];
    }
    if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        $sid .= $_SERVER['HTTP_ACCEPT_LANGUAGE'];
    }
    if (isset($_SERVER['HTTP_DNT'])) {
        $sid .= $_SERVER['HTTP_DNT'];
    }
    if (isset($_SERVER['HTTP_UPGRADE_INSECURE_REQUESTS'])) {
        $sid .= $_SERVER['HTTP_UPGRADE_INSECURE_REQUESTS'];
    }
    return md5($sid);
}

function getPasswordHash($password)
{
    return password_hash($password, PASSWORD_DEFAULT);
}

function checkPassword($password, $hash)
{
    return password_verify($password, $hash);
}

function getPlainCSRFToken()
{
    $inc = get_included_files();
    return $inc[0].session_id().RANDOM_SECURITY.getClientFingerprint();
}

function checkCSRFToken($token)
{
    return checkPassword(getPlainCSRFToken(), $token);
}

function getCSRFToken()
{
    return getPasswordHash(getPlainCSRFToken());
}

// echo getCSRFTokenField(); 
function getCSRFTokenField()
{
    return '<input type="hidden" name="csrf_token" id="csrf_token" value="'.getCSRFToken().'" />';
}

if (!empty($_POST) && (empty($_POST['csrf_token']) || !checkCSRFToken($_POST['csrf_token']))) {
  echo "<pre>CSRF error</pre>";
  exit();
}


