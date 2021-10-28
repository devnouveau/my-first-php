<!DOCTYPE html>
<html>
<head>
   <title>Browse Directories</title>
</head>
<body>
   <h1>Browsing</h1>

<?php
// ============================= 디렉토리 파일내역 출력 (파일이름 정렬) ============================= //

$dir = './uploads/';
// scandir(경로) : 파일이름을 배열로 반환(실패시 false반환)
// 알파벳 순 정렬(오름차순 기본. 두번째 매개변수 0이 아닌 다른 값일 경우 내림차순)
$files1 = scandir($dir); 
$files2 = scandir($dir, 1);

echo '<p>Upload directory is '.$dir.'</p>';
echo '<p>Directory Listing in alphabetical order, ascending:</p><ul>';

foreach($files1 as $file)
{
   if ($file != "." && $file != "..")
   {
     echo '<li>'.$file.'</li>';
   }
}

echo '</ul>';

echo '<p>Upload directory is '.$dir.'</p>';
echo '<p>Directory Listing in alphabetical, descending:</p><ul>';

foreach($files2 as $file)
{
   if ($file != "." && $file != "..")
   {
     echo '<li>'.$file.'</li>';
   }
}

echo '</ul>';

?>
</body>
</html>
