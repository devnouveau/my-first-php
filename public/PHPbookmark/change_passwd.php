<?php 
require_once('bookmark_fns.php');
session_start();
do_html_header('Changing password');

$old_passwd = $_POST['old_passwd'];
$new_passwd = $_POST['new_passwd'];
$new_passwd2 = $_POST['new_passwd2'];

try {
  check_valid_user(); // 로그인여부부터 확인
  
  // 입력값 유효성 체크
  if(!filled_out($_POST)) {
    throw new Exception('You have not filled out the form completely.');
  }
  if($new_passwd != $new_passwd2) {
    throw new Exception('Passwords entered were not the same');
  }
  if((strlen($new_passwd) > 16) || (strlen($new_passwd) < 6)) {
    throw new Exception('Password must be 6 ~ 16 characters.');
  }


  // DB값 변경(예외발생가능)
  change_password($_SESSION['valid_user'], $old_passwd, $new_passwd);
  echo 'Password changed';

} catch (Exception $e) {
  echo $e->getMessage();
}

display_user_menu();
do_html_footer();


?>