<?php
// ============================= 외부프로그램실행 (exec() 사용) ============================= //

chdir('./uploads/'); // 디렉토리 이동


echo '<h1>Using exec()</h1>';
echo '<pre>';

// unix 명령어 실행. $result배열로 실행결과 배열을 받음
exec('ls -la', $result);

// windows
// exec('dir', $result);

// 실행결과 출력
foreach ($result as $line)
{
   echo $line.PHP_EOL; 
}

echo '</pre>';
echo '<hr />';

// ============================= 외부프로그램실행 (passthru() 사용) ============================= //
echo '<h1>Using passthru()</h1>';
echo '<pre>';

// unix
passthru('ls -la') ;

// windows
// passthru('dir');

echo '</pre>';
echo '<hr />';

// ============================= 외부프로그램실행 (system() 사용) ============================= //
echo '<h1>Using system()</h1>';
echo '<pre>';

// unix
$result = system('ls -la');

// windows
// $result = system('dir');

echo '</pre>';
echo '<hr />';

// ============================= 외부프로그램실행 (백틱 사이에 명령어 입력) ============================= //
echo '<h1>Using Backticks</h1>';
echo '<pre>';

// unix
$result = `ls -al`; 

// windows 
// $result = `dir`;

echo $result;
echo '</pre>';

// system(ecapeshellcmd(명령어)) : 쉘의 메타문자를 일반문자로 인식되게 함
// system('ls '.escapeshellarg($dir)) : 문자열 앞뒤로 작은 따옴표를 붙여서 하나의 문자열로 인식되게 함

// 환경변수(phpinfo()에서 환경변수 확인가능)사용
getenv("HTTP_REFERER"); //현재 페이지 이동직전에 머문 페이지 URL 반환
// 환경변수 설정
$home = "/home/nobody";
putenv(" HOME=$home ");
?>
