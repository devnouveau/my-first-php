<?php
  require_once('bookmark_fns.php');
  session_start();

  // 미입력 데이터 공백처리
  if (!isset($_POST['username'])) {
    $_POST['username'] = '';
  } 
  $username = $_POST['username'];
  if (!isset($_POST['passwd'])) {
    $_POST['passwd'] = '';
  } 
  $passwd = $_POST['passwd'];



  if ($username && $passwd) {
    try {
      login($username, $passwd);
      $_SESSION['valid_user'] = $username;
    } catch (Exception $e) {
      // 로그인실패시
      do_html_header('Problem: ');
      echo 'You could not be logged in. <br />';
      do_html_URL('login.php', 'Login');
      do_html_footer();
      exit;
    }
  }

  /** 로그인 성공시 출력 **/
  do_html_header('Home');
  check_valid_user();
  
  // 사용자가 등록한 북마크 가져오기
  if($url_array = get_user_urls($_SESSION['valid_user'])) {
    display_user_urls($url_array);
  }

  display_user_menu();
  do_html_footer();
  /** 로그인 성공시 출력 **/





?>