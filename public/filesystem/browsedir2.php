<!DOCTYPE html>
<html>
<head>
   <title>Browse Directories</title>
</head>
<body>
   <h1>Browsing</h1>

<?php

// ============================= 디렉토리 파일내역 출력 (php dir 클래스 사용) ============================= //

  // dir(경로) : 디렉토리클래스 인스턴스 반환
  $dir = dir("./uploads/"); 

  // 디렉토리 클래스의 속성 handle, path 사용
  echo '<p>Handle is '.$dir->handle.'</p>'; 
  echo '<p>Upload directory is '.$dir->path.'</p>';
  echo '<p>Directory Listing:</p><ul>';
  
  // 디렉토리 클래스의 read()메소드로 파일이름 반환
  while(false !== ($file = $dir->read()))
    if($file != "." && $file != "..")
       {
         echo '<li>'.$file.'</li>';
       }
       
  echo '</ul>';
  
  // 디렉토리 클래스의 close()메소드로 디렉토리 닫기
  $dir->close();
?>

</body>
</html>