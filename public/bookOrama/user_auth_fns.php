<?php

require_once('db_fns.php');

function login($username, $password) {
  $conn = db_connect();
  if (!$conn) {
    return 0;
  }

  $result = $conn->query("select * from admin
                         where username='". $conn->real_escape_string($username)."'
                         and password = sha1('". $conn->real_escape_string($password)."')");
  if (!$result) {
     return 0;
  }

  if ($result->num_rows>0) {
     return 1;
  } else {
     return 0;
  }
}

function check_admin_user() {
  if (isset($_SESSION['admin_user'])) {
    return true;
  } else {
    return false;
  }
}

function change_password($username, $old_password, $new_password) {
  if (login($username, $old_password)) {

    if (!($conn = db_connect())) {
      return false;
    }

    $result = $conn->query("update admin
                            set password = sha1('". $conn->real_escape_string($new_password)."')
                            where username = '". $conn->real_escape_string($username) ."'");
    if (!$result) {
      return false;  // not changed
    } else {
      return true;  // changed successfully
    }
  } else {
    return false; // old password was wrong
  }
}


?>
