<?php

define('DEVPANEL_TOKEN_LEN', 50);
define('DEVPANEL_DIR', "/opt/webenabled");

function dp_has_valid_token($vhost, $app, $token_str) {
  $ret = false;
  $token_file = sprintf("%s/var/tokens/%s.%s.%s", DEVPANEL_DIR, $vhost, $app, $token_str);

  /* open file for read/write, fail if doesn't exist */
  $token_fd = fopen($token_file, 'r');
  if (!$token_fd) {
    return false;
  }

  $ret =
    /* try to acquire exclusive lock, fail otherwise */
    flock($token_fd, LOCK_EX | LOCK_NB) &&
    /* mtime == 0 means the token has been already used */
    (filemtime($token_file) != 0) &&
    /* touch the file to mark it as used */
    touch($token_file, 0);

  /* closing the file releases the lock */
  fclose($token_fd);
  return $ret;
}

function dp_is_already_logged_to_app($app) {
  ini_get('session.auto_start') || session_start();

  if(!empty($_SESSION) && !empty($_SESSION['app']) && $_SESSION['app'] == $app) {
    return true;
  } else {
    return false;
  }
}

function dp_derive_gen_vhost() {
  $user_info = posix_getpwuid(posix_geteuid());

  if(strlen($user_info['name']) > 2 && substr($user_info['name'], 0, 2) == "w_") {
    $vhost = substr($user_info['name'], 2);
  } else {
    $vhost = $user_info['name'];
  }

  return $vhost;
}

function dp_get_app_token_from_url() {
  if(empty($_SERVER['PATH_INFO'])) {
    return false;
  }

  $token = trim($_SERVER['PATH_INFO'], "/");

  if(strlen($token) == DEVPANEL_TOKEN_LEN && preg_match('/^[A-Za-z0-9]+$/', $token)) {
    return $token;
  } else {
    return false;
  }
}

function dp_get_token_from_params() {
  if(!isset($_GET['token'])) {
    return false;
  }

  $token = $_GET['token'];

  if(strlen($token) == DEVPANEL_TOKEN_LEN && preg_match('/^[A-Za-z0-9]+$/', $token)) {
    return $token;
  } else {
    return false;
  }
}

function dp_start_app_session($vhost, $app, $token) {
  session_start();

  session_cache_expire(120);
  session_cache_limiter("private");
  $_SESSION["app"]   = $app;
  $_SESSION["token"] = $token;

  session_write_close();
}

?>
