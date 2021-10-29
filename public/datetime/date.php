<?php
// 아래 날짜함수들은 php.init - date.timezone 표준시간대 사용
echo "[ 표준시간대 함수 ]<br />";
echo time()." > time()<br/>";
echo date('l jS n F Y')." > date()<br/>"; // l 요일 전체명칭, j 날짜(앞에 0붙지 않음), S 날짜서수, n월(앞에 0붙지 않음), F 월이름 전체명칭, Y 연도4자리 숫자
echo date('D dS m M y')." > date()<br/>"; // D 요일 축약명칭, d 날짜(앞에 0붙음), m 월(앞에 0붙음), M 월이름 축약명칭, y 연도 2자리 숫자
// echo strtotime()."strtotime<br/>";



echo "<br />";

// 유닉스 타임스탬프 반환 함수
// 타임스탬프는 1902년부터 2038년 사이의 날짜만을 나타낼 수 있다
echo "[ 유닉스 타임스탬프 반환 함수 ]<br />";
$timestamp = mktime(2, 30, 22, 1, 3); //매개변수 h, m, s, month, day, year 중 하나라도 전달되어야 함. 
echo $timestamp." > 날짜지정<br/>";
$timestamp = time();
echo $timestamp." > 현재시간time()함수<br/>";
$timestamp = date("U");
echo $timestamp." > date()함수<br/>";
$timestamp = mktime(2, 30, 22, 1, 3+72, 2021); // 날짜연산하여 타임스탬프 반환도 가능
echo $timestamp." > 날짜더하기<br/>";

echo "<br />";

// getDate()함수로 날짜, 시간의 각 부분을 배열로 반환
echo "[ 날짜, 시간의 각 부분을 배열로 반환하는 getDate() 함수 ]<br />";
$today = getDate();
print_r($today); // 배열내용 한번에 출력
// echo $today; //  Warning: Array to string conversion // 배열은 echo로 바로 출력시 오류발생

echo "<br /><br />";

// 날짜검사 checkdate()
echo "[ 날짜 유효성 검사하는 checkdate() 함수 ]<br />";
echo "2016.02.29는 ".(checkdate(2, 29, 2016)? "유효한" : "유효하지 않은")."날짜입니다.<br/>";
echo "2017.02.29는 ".(checkdate(2, 29, 2017)? "유효한" : "유효하지 않은")."날짜입니다.<br/>";

echo "<br />";

// 타임스탬프로 형식변경 strftime()
echo "[ 타임스탬프로 형식변경하는 strftime() 함수 ]<br />";
// format, timestamp를 매개변수로 전달. timestamp생략시 현재timestamp로 연산 // 
echo strftime('%A<br/>'); // 요일 전체명칭 
echo strftime('%x<br/>'); // 표준형식 날짜 10/29/21
echo strftime('%X<br/>'); // 표준형식 시간 01:48:53
echo strftime('%c<br/>'); // 표준형식 날짜,시간 Fri Oct 29 01:33:17 2021
echo strftime('%Y<br/>'); // 네 자리수 연도



echo "<br />";


// ================= 날짜 차이 계산하기 ================= //
echo "[ 타임스탬프 값 차이로 생년월일로 나이 계산하기 (타임스탬프가 다루지 않는 연도, 윤년, 썸머타임 등은 고려하지 않음) ]<br />";
// 기준날짜 
$day = 18;
$month = 9;
$year = 1972;

$bdayunix = mktime (0, 0, 0, $month, $day, $year); // 기준시간을 유닉스 타임스탬프로 변환
$nowunix = time(); // 현재시간의 타임스탬프 얻기
$ageunix = $nowunix - $bdayunix; // 시간 차이 계산
$age = floor($ageunix / (365 * 24 * 60 * 60)); // 초를 연도로 변환 

echo 'Current age is '.$age.'.<br/>';



echo "<br />";


// ================= 날짜 차이 계산하기 ================= //
echo "[ mysql함수로 생년월일로 나이 계산하기 ]<br />";
// 생년월일
$day = 18;
$month = 9;
$year = 1972;

// 생년월일을 ISO 포맷으로 변경
$bdayISO = date("c", mktime (0, 0, 0, $month, $day, $year));
echo $bdayISO." > 생일 <br/>";

// use mysql query to calculate an age in days
$db = mysqli_connect('localhost', 'homestead', 'secret');
$res = mysqli_query($db, "select datediff(now(), '$bdayISO')"); // 현재시간과 생일IOS시간 비교하여 일수 차이를 반환
$age = mysqli_fetch_array($res); // 

// convert age in days to age in years (approximately)
echo 'Current age is '.floor($age[0]/365.25).'.';


//마이크로 초 계산
echo number_format(microtime(true), 5, '.', '');




?>