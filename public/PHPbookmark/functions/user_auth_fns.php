<?php

/* DB접근시 Exception발생가능. try내에서 호출하고 catch내에서 예외처리할 것. */

  
  function register($username, $email, $password) {
    $conn = db_connect();
    
    $result = $conn->query("select * from user where username='".$username."'");

    if(!$result) {
      throw new Exception('Could not excecute query');
    }
    if($result->num_rows>0) {
      throw new Exception('That username is taken - go back and choose another one');
    }

    $result = $conn->query("insert into user values ('".$username."', sha1('".$password."'), '".$email."')");
    if(!$result) {
      // echo mysqli_error($conn); // mysql 에러출력
      throw new Exception('Could not register you in database - please try again later');
    }

    return true;
  }

  function login($username, $password) {
    $conn = db_connect();
    $result = $conn->query("select * from user 
                            where username='".$username."' 
                            and passwd = sha1('".$password."')");
    if (!$result) {
      throw new Exception("Could not log you in");
    }
    if($result->num_rows > 0) {
      return true;
    } else {
      throw new Exception("Could not log you in");
    }

  }

  function check_valid_user() { // 사용자 세션변수 생성여부 확인
    if(isset($_SESSION['valid_user'])) {
      echo "Logged in as ".$_SESSION['valid_user'].".<br />";
    } else {
      do_html_heading('Problem');
      echo 'You are not logged in. <br />';
      do_html_URL('login.php', 'Login');
      do_html_footer();
      exit;
    }
  }



  function change_password($username, $old_password, $new_password) {
    login($username, $old_password); // 기존의 비밀번호가 맞는지 먼저 확인
    
    $conn = db_connect();
    $result = $conn->query("update user
                            set passwd = sha1('".$new_password."')");

    if(!$result) {
      throw new Exception('Password could not be changed');
    } else {
      return true;
    }
  }


  function get_random_word($min_length, $max_length) {
    $word = '';

    //$dictionary = '/usr/share/dict/words'; // 사전파일
    $dictionary = '/home/vagrant/code/studyproject/public/PHPbookmark/scowl-2020.12.07/final/british_variant_2-words.95'; // 사전파일
    $fp = @fopen($dictionary, 'r');
    if(!$fp) {
      return false;
    }
    $size = filesize($dictionary);
  
    // go to a random location in dictionary
    $rand_location = rand(0, $size);
    fseek($fp, $rand_location);
  
    // get the next whole word of the right length in the file
    while ((strlen($word) < $min_length) || (strlen($word)>$max_length) || (strstr($word, "'"))) {
       if (feof($fp)) {
          fseek($fp, 0);        // if at end, go to start
       }
       $word = fgets($fp, 80);  // skip first word as it could be partial
       $word = fgets($fp, 80);  // the potential password
    }
    $word = trim($word); // trim the trailing \n from fgets
    return $word;
  } 


  function reset_password($username) {
    $new_password = get_random_word(6,13);// 사전파일에서 임의의 단어를 얻기
    if($new_password == false) { // 사전파일 사용불가시
      $new_password = "changeMe!";
    }

    $rand_number = rand(0,999); // 0~999사이 랜덤넘버 구하기
    $new_password .= $rand_number; // 패스워드를 약간 더 안전하게 하기 위함


    $conn = db_connect();
    $result = $conn->query("Update user 
                            set passwd = sha1('".$new_password."')
                            where username = '".$username."'");
    if (!$result) {
      throw new Exception('Could not change password.');
    } else {
      return $new_password;
    }

  }


  function notify_password($username, $password) {
    $conn = db_connect();
    $result = $conn->query("select email 
                            from user
                            where username = '".$username."'");
    if(!$result) {
      throw new Exception('Could not find email address.');
    } else if ($result->num_rows == 0) {
      throw new Exception('Could not find email address.');
    } else {
      $row = $result->fetch_object(); // 현재 행 데이터를 객체로 반환
      $email = $row->email;
      $from = "From: support@phpbookmark.com \r\n";
      $mesg = "Your PHPBookmark password has been changed to ".$password."\r\n"
      ."Please change it next time you log in. \r\n";

      if(mail($email, 'PHPBookmark login information', $mesg, $from)) {
        return true;
      } else {
        throw new Exception('Could not send email.');
      }




    }
  }




?>