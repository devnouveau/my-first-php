<!DOCTYPE html>
<html>
<head>
  <title>Uploading...</title>
</head>
<body>
   <h1>Uploading File...</h1>

<?php

   // ==================== 업로드 파일 처리 스크립트 ==================== //
   // 업로드 파일 정보는 슈퍼글로벌 배열 $_FILES에 저장됨.


/*
  // 복수의 파일을 업로드 하는 경우... 
  // file input의 name을 the_files[]로 하여 html에서 파일 넘겨받기
  echo count($_FILES['the_files']['name'])."<br />";
  for($i=0; $i<count($_FILES['the_files']['name']); $i++) {
  }
*/
/*   
  // 복수파일 업로드 정보 확인
  foreach($_FILES['the_files'] as $key=>$value) {
   echo "<br /> key: ".$key."<br/>";
   foreach($value as $val) {
      echo "value: ".$val."<br/>";
   }
   echo "<br /><br />";
  }
*/ 
   
  if ($_FILES['the_file']['error'] > 0)
  {
    echo 'Problem: ';
    switch ($_FILES['the_file']['error'])  
    { // 1. 파일업로드 오류처리
      case 1:  
         echo 'File exceeded upload_max_filesize.';
         break;
      case 2:  
         echo 'File exceeded max_file_size.';
         break;
      case 3:  
         echo 'File only partially uploaded.';
         break;
      case 4:  
         echo 'No file uploaded.';
         break;
      case 6:  
         echo 'Cannot upload file: No temp directory specified.';
         break;
      case 7:  
         echo 'Upload failed: Cannot write to disk.';
         break;
    }
    exit;
  }

  // 2. 업로드 가능한 파일타입 제한 (mime type 검사)
  if ($_FILES['the_file']['type'] != 'image/jpeg')
  {
    echo '업로드 가능한 파일이 아닙니다.';
    exit;
  }

  // 파일을 저장할 곳 지정하여 업로드 처리 
  $uploaded_file = './uploads/'.$_FILES['the_file']['name']; // 3. 파일을 저장할 경로지정 // 해당 디렉토리가 존재하지 않으면 오류발생함

  if (is_uploaded_file($_FILES['the_file']['tmp_name'])) // 4. 임시저장경로(php.ini에서 설정)에 파일이 저장되어 있는지 확인
  {
     if (!move_uploaded_file($_FILES['the_file']['tmp_name'], $uploaded_file)) // 5. 임시저장경로에 저장된 파일을 새로운 경로로 이동 (임시저장 파일은 삭제됨)
     {
        echo 'Problem: Could not move file to destination directory.';
        exit; // 스크립트 중단 
     }
  }
  else
  {
    echo 'Problem: Possible file upload attack. Filename: ';
    echo $_FILES['the_file']['name'];
    exit;
  }

  echo 'File uploaded successfully.';


  // 업로드한 파일 출력
  echo '<p>You uploaded the following image:<br/>';
  echo '<img src="./uploads/'.$_FILES['the_file']['name'].'"/>';
?>
</body>
</html>
