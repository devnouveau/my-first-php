<?php

  require_once('bookmark_fns.php');
  
  $email = $_POST['email'];
  $username = $_POST['username'];
  $passwd = $_POST['passwd'];
  $passwd2 = $_POST['passwd2'];

  session_start(); // 등록 후 세션변수저장을 위함

  try {

    // 입력값 유효성 검사 (서버/클라이언트 양측에서 검사되어야 함)
    if(!filled_out($_POST)) {
      throw new Exception('You have not filled the form out correctly');
    }
    if(!valid_email($email)) {
      throw new Exception('That is not a valid email address.');
    } 
    if($passwd != $passwd2) {
      throw new Exception('password not match');
    }
    if(strlen($passwd) < 6 || (strlen($passwd)) > 16) {
      throw new Exception('password must be 6~16 characters');
    }

    // DB등록처리
    register($username, $email, $passwd); // 예외발생가능


    // 등록 성공 후 
    $_SESSION['valid_user'] = $username; // 세션변수 저장
    // 결과 출력
    do_html_header('Registration success');
    echo 'your registraion was successsful. go to mebers page to start setting up your bookmarks!';
    do_html_url('member.php', 'go to members page');
    do_html_footer();

  } catch (Exception $e) {
    // 예외발생으로 등록실패시 출력
    do_html_header('problem:');
    echo $e->getMessage();
    do_html_footer();
    exit;
  }


?>