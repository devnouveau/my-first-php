<!DOCTYPE html>
<html>
<head>
  <title>File Details</title>
</head>
<body>
<?php

// ============================= 파일상세내용 출력 ============================= //
  
  if (!isset($_GET['file'])) 
  {  // browsedir에서 링크주소로 넘긴 file경로 파라미터값 확인
     echo "You have not specified a file name.";
  } 
  else { 
     $uploads_dir = './uploads/';
     $the_file = basename($_GET['file']); // 경로에서 디렉토리 이름 제거하고 파일이름만 반환

     $safe_file = $uploads_dir.$the_file; 

     echo '<h1>Details of File: '.$the_file.'</h1>';

     echo '<h2>File Data</h2>';
     echo 'File Last Accessed: '.date('j F Y H:i', fileatime($safe_file)).'<br/>'; 
     echo 'File Last Modified: '.date('j F Y H:i', filemtime($safe_file)).'<br/>';

     /*

     // 윈도우에서 정상 작동하지 않을 수 있음
	 $user = posix_getpwuid(fileowner($safe_file));
     echo 'File Owner: '.$user['name'].'<br/>';
  
     $group = posix_getgrgid(filegroup($safe_file));
     echo 'File Group: '.$group['name'].'<br/>';
	 */

    // 아래 함수대신 stat()함수 하나로 한 번에 조회하는 것도 가능
    // clearstatcache(); // 캐시에 저장된 이전결과 지우기
     echo 'File Permissions: '.decoct(fileperms($safe_file)).'<br/>';
     echo 'File Type: '.filetype($safe_file).'<br/>';
     echo 'File Size: '.filesize($safe_file).' bytes<br>';

     echo '<h2>File Tests</h2>';
     echo 'is_dir: '.(is_dir($safe_file)? 'true' : 'false').'<br/>';
     echo 'is_executable: '.(is_executable($safe_file)? 'true' : 'false').'<br/>';
     echo 'is_file: '.(is_file($safe_file)? 'true' : 'false').'<br/>';
     echo 'is_link: '.(is_link($safe_file)? 'true' : 'false').'<br/>';
     echo 'is_readable: '.(is_readable($safe_file)? 'true' : 'false').'<br/>';
     echo 'is_writable: '.(is_writable($safe_file)? 'true' : 'false').'<br/>';
  }
?>
</body>
</html>
