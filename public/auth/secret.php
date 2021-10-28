<!DOCTYPE html>
<html>
<head>
   <title>Secret Page</title>
</head>
<body>

<?php
// ====================== 접근제어 구현 ====================== //

  if ((!isset($_POST['name'])) || (!isset($_POST['password']))) { // 유저명이나 패스워드 입력되지 않은 경우 입력폼을 노출함
?>
    <h1>Please Log In</h1> 
    <p>This page is secret.</p>
    <form method="post" action="secret.php">
    <p><label for="name">Username:</label> 
    <input type="text" name="name" id="name" size="15" /></p>
    <p><label for="password">Password:</label> 
    <input type="password" name="password" id="password" size="15" /></p>
    <button type="submit" name="submit">Log In</button>    
    </form>
<?php
  } else if(($_POST['name']=='user') && ($_POST['password']=='pass')) { // 유저명과 패스워드 일치
    echo '<h1>인증성공!</h1>
          <p>접근제한 정보 확인 가능!</p>';
    // 비밀번호 암/복호화
    // 암호화 함수 crypt(), md5(), sha1(), sha256()
    // password_hash(pw, 해시 알고리즘)  //salt도 생성. password_verify(입력받은 pw, db에 저장된 hash)
    // :: PASSWORD_DEFAULT(bcrypt알고리즘), PASSWORD_BCRYPT(crypt_blowfish 알고리즘) : 60자 해시반환. pw해시열 크기 255권장
  } else {
    echo '<h1>인증실패!</h1>
          <p>현재 페이지 접근 불가!</p>';
  }
?>
</body>
</html>