<?php
  
  require_once('bookmark_fns.php');
  session_start();
  $old_user = $_SESSION['valid_user'];


  unset($_SESSION['valid_user']);
  $result_dest = session_destroy();



  /* 로그아웃 결과에 따른 화면출력 */
  do_html_header('Logging out');

  if (!empty($old_user)) { // 로그인된 상태에서 로그아웃 시도시
    if($result_dest) { // session destroy 성공적으로 이뤄진 경우
      echo 'Logged Out.<br/>';
      do_html_url('login.php', 'Login');
    } else {
      echo 'Could not log you out.<br />';
    }
  } else { // 로그인되지 않은 상태에서 로그아웃 시도시
    echo 'You were not logged in, and so have not been logged out.<br>';
    do_html_url('login.php', 'Login');
  }

  do_html_footer();


?>