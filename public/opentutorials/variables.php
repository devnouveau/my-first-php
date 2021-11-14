<?php
/********** 상수 **********/
define('TITLE', 'PHP Tutorial'); // 상수선언
echo "<p>".TITLE."</p>";
//define('TITLE', 'JAVA Tutorial'); // 오류


/********** 데이터 타입 검사 및 변경 **********/
$a = 100;
echo gettype($a);
settype($a, 'double');
echo '<br />';
echo gettype($a);


/********** 가변변수 **********/
$title = 'subject';
$$title = 'PHP tutorials'; 
// $$title은 $title의 값인 subject를 변수명으로 함.  
// $subject = 'PHP tutorials'; 와 같음
echo "<p>".$subject."</p>"; 

?>