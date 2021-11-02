<?php
  session_start(); // 세션변수 사용 전, session_start() 호출을 통해 객체의 클래스 정의를 포함시켜야 함.
?>
<!DOCTYPE html>
<html>
<head>
   <title>Members Only</title>
</head>
<body>
<h1>Members Only</h1>

<?php
  // 세션변수를 검사하여 인증된 사용자인지 확인
  if (isset($_SESSION['valid_user'])) {
    echo '<p>You are logged in as '.$_SESSION['valid_user'].'</p>';
    echo '<p><em>Members-Only content goes here.</em></p>';
  } else {
    echo '<p>You are not logged in.</p>';
    echo '<p>Only logged in members may see this page.</p>';
  }
?>

<p><a href="authmain.php">Back to Home Page</a></p>

</body>
</html>

