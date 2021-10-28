<!DOCTYPE html>
<html>
<head>
   <title>Mirror Update</title>
</head>
<body>
   <h1>Mirror Update</h1>

<?php
$host = 'apache.cs.utah.edu';
$user = 'anonymous';
$password = 'chowbiz715@gmail.com';
$remotefile = '/apache.org/httpd/httpd-2.4.51.tar.gz'; // 존재하는 경로/파일이어야 함.
$localfile = './httpd-2.4.51.tar.gz';

// ftp연결 / ftp 스트림 얻기
$conn = ftp_connect($host);
if (!$conn)
{
  echo 'Error: Could not connect to '.$host;
  exit;
}
echo 'Connected to '.$host.'<br />';

// 호스트에 ftp로그인 
$result = @ftp_login($conn, $user, $pass);
if (!$result)
{
  echo 'Error: Could not log in as '.$user;
  ftp_quit($conn);
  exit;
}
echo 'Logged in as '.$user.'<br />';

// ftp패시브모드 설정
ftp_pasv($conn, true); 

// 로컬파일의 파일변경시간 확인
echo 'Checking file time...<br />';
if (file_exists($localfile))
{
  $localtime = filemtime($localfile);
  echo 'Local file last updated ';
  echo date('G:i j-M-Y', $localtime);
  echo '<br />';
}
else
{
  $localtime = 0;
}

// 리모트 파일의 파일변경시간 확인
$remotetime = ftp_mdtm($conn, $remotefile);
if (!($remotetime >= 0))
{
   // This doesn't mean the file's not there, server may not support mod time
   echo 'Can\'t access remote file time.<br />';
   $remotetime = $localtime+1;  // make sure of an update
}
else
{
  echo 'Remote file last updated ';
  echo date('G:i j-M-Y', $remotetime);
  echo '<br />';
}





// set_time_limit() :: 특정스크립트 최대 실행시간 변경가능 (타임아웃 방지)

// ftp_size() :: 원격지 서버 파일크기를 바이트로 반환 (파일 전송 최대 실행시간 계산시 유용)
// echo "file size : ".ftp_size($conn, $remotefile)."bytes <br />";

// ftp_nlist() :: 원격지 ftp서버 디렉터리의 파일 내역 확인
echo "[ file list ]<br />";
$listing = ftp_nlist($conn, dirname($remotefile));
foreach($listing as $filename) {
  echo $filename.'<br />';
}
echo "======================<br />";


// 로컬 파일의 업데이트가 필요한지 확인 (로컬파일의 변경시간이 리모트파일의 변경시간 이전인 경우 업데이트)
if (!($remotetime > $localtime))
{
   echo 'Local copy is up to date.<br />';
   exit;
}

// download file
echo 'Getting file from server...<br />';
$fp = fopen($localfile, 'wb'); // 로컬파일을 바이너리쓰기모드로 열기 

if (!$success = ftp_fget($conn, $fp, $remotefile, FTP_BINARY))
//if (!$success = ftp_get($conn, $localfile, $remotefile, FTP_BINARY)) // 파일 업로드시 ftp_fput(), ftp_put() 사용
{
  echo 'Error: Could not download file.';
  // 파일닫기/ ftp연결종료
  fclose($fp);
  ftp_quit($conn); //ftp_close()동일
  exit;
}
// 파일닫기/ ftp연결종료
fclose($fp);
echo 'File downloaded successfully.';
ftp_close($conn);






?>
</body>
</html>
