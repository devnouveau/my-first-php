<!DOCTYPE html>
<html>
<head>
   <title>Browse Directories</title>
</head>
<body>
   <h1>Browsing</h1>

<?php
// ============================= 디렉토리 파일내역 출력 (opendir(), closedir(), readdir()) ============================= //
  $current_dir = './uploads/';
  $dir = opendir($current_dir); // opendir(경로) : 특정 경로의 디렉토리 열기

  echo '<p>Upload directory is '.$current_dir.'</p>';
  echo '<p>Directory Listing:</p><ul>';
  
  while(false !== ($file = readdir($dir))) 
  { // readdir(오픈된 directory 리소스) : 디렉토리 핸들을 반환. 파일이름을 읽어오고, 더 이상 읽을 파일이 없으면 false반환
                                                // (0을 반환할 수 있으므로 반드시 boolean false와 비교해야 함)
                                                // rewinddir($dir)함수 :: 디렉터리 맨 앞의 파일 위치로 디렉터리 핸들 재설정
    if($file != "." && $file != "..")
       { // 파일이름 출력 (현재디렉토리, 부모디렉토리 의미하는 ., ..은 출력되지 않게)
        //  echo '<li>'.$file.'</li>';
         echo '<li><a href="filedetails.php?file='.$file.'">'.$file.'</a></li>';
       }
  }
  echo '</ul>';
  closedir($dir); // 디렉토리 닫기
?>

</body>
</html>