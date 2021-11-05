<?php 
  require_once("bookmark_fns.php");

  do_html_header('Resetting password');

  $username = $_POST['username'];

  try {
    $password = reset_password($username); // 패스워드초기화
    notify_password($username, $password); // 초기화한 패스워드 전송
    echo 'Your new password has been emailed to you.<br>';
  } catch (Exception $e) {
    echo 'Your password could not be changed';
  }
  do_html_URL('login.php', 'Login');
  do_html_footer();
?>