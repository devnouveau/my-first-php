<?php
  session_start();

  $old_user = $_SESSION['valid_user'];

  // 세션 변수해제/ 종료
  unset($_SESSION['valid_user']);
  session_destroy();
?>
<!DOCTYPE html>
<html>
<head>
   <title>Log Out</title>
</head>
<body>
<h1>Log Out</h1>
<?php
  if (!empty($old_user)) { // 정상로그아웃
    echo '<p>You have been logged out.</p>'; 
  } else { // 로그인되어 있지 않은 상태에서 로그아웃 페이지로 온 경우
    echo '<p>You were not logged in, and so have not been logged out.</p>';
  }
?>
<p><a href="authmain.php">Back to Home Page</a></p>

</body>
</html>