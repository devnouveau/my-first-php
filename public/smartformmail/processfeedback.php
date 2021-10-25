<?php

// ================ 문자열 처리와 정규표현식 ================ //


// trim()으로 앞 뒤 공백문자(\n,\r,\0,space)제거 // ltrim(),rtrim(),chop()
$name = trim($_POST['name']); 
$email = trim($_POST['email']);
$feedback = trim($_POST['feedback']);

if(strlen($email) < 6) { // 입력값 길이 유효성 검사
  echo 'that eamil address is not valid';
  exit;
}

// ================ 정규표현식 ================ //
// 이메일형식검사
//preg_match(): 일치시 1, 일치x시 0, 에러발생 false반환.
if (preg_match('/^[a-zA-Z0-9_\-\.]+@[a-zA-Z0-9\-]+\.[a-zA-Z0-9\-\.]+$/', $email) === 0) { 
  echo "invalid email address";
  exit;
}
$toaddress = "feedback@example.com";
if (preg_match('/shop|customer service|retail/', $feedback)) {
  $toaddress = "retail@example.com";
}
if (preg_match('/bigcustomer\.com/', $email)) {
  $toaddress = 'bob@example.com';
}

// 이메일 입력값에 따라 다르게 처리하기
/*
$email_array = explode('@', $email); 
if(strtolower($email_array[1]) == "bigcustomer.com") {
  $toaddress = "important@example.com";
} else {
  $toaddress = "feedback@example.com";
}
*/
echo "[ preg_split ]<br />";
$addressarr = preg_split('/\.|@/', $toaddress); // preg_split() 정규표현식으로 문자열 분리하여 배열로 반환
foreach($addressarr as $key => $value) {
  echo $key.",".$value."<br />";
}


$subject = "Feedback from web site";

// \r\n을 제거하여 이메일 헤더값 구분에 문제가 없도록 함
$mailcontent = "Customer name: ".str_replace("\r\n", "", $name)."\n".
               "Customer email: ".str_replace("\r\n", "",$email)."\n".
               "Customer comments:\n".str_replace("\r\n", "",$feedback)."\n".
               "toaddress: ".$toaddress."\n";
$fromaddress = "From: webserver@example.com";

mail($toaddress, $subject, $mailcontent, $fromaddress);

?>
<!DOCTYPE html>
<html>
  <head>
    <title>Bob's Auto Parts - Feedback Submitted</title>
  </head>
  <body>

    <h1>Feedback submitted</h1>
    <p>Your feedback (shown  below) has been sent.</p>
    <?php 

    echo "[ nl2br ]<br />";
    echo "<p>".nl2br(htmlspecialchars($feedback))."</p>";
    // nl2br()
    // html에서는 newline \n(공백문자)을 무시함. 
    // 따라서 이를 <br />로 변경해주어야 줄바꿈을 적절하게 출력할 수 있음 

    // htmlspecialchars()
    // &, ", ', <, >을 html엔티티로 변환
    // 기본적으로 ",' 중 "만 변환(flags 매개변수로 변경가능)

    // htmlspecialchars() 이후 nl2br()를 해야 함
    echo "<p>htmlspecialchars() -> nl2br() : </p><p>".nl2br(htmlspecialchars($mailcontent))."</p>"; 
    // nl2br()을 먼저하고 htmlspecialchars()적용시 <br />이 문자그대로 출력되게됨
    echo "<p>nl2br() -> htmlspecialchars() : </p><p>".htmlspecialchars(nl2br($mailcontent))."</p>"; 



    echo "[ printf ]<br />";
    // printf() : 형식지정하여 출력 , sprintf() : 형식지정하여 반환
    // 변환 명세 타입코드는 검색해볼 것...
    $total = 100.123;
    $total_shipping = 10.456;
    printf("총 금액은 %2\$.2f (총배송료 %1\$.2f)", $total_shipping, $total); // 숫자\$ 기호를 이용해 인자의 위치 지정가능

    // strtoupper(), strtolower(), ucfirst(), ucwords()
    // explode() : 구분자로 문자열 분리하여 배열로 반환, implode()/join() : 배열값들을 구분자로 결합하여 문자열로 반환
    
    echo "<br /><br />";

    echo "[ strtok ]<br />";
    // strtok() : 문자열에서 한 번에 하나의 조각을 가져옴
    $token = strtok($feedback, " "); // 문자열, 구분자 전달시 포인터 초기화
    while ($token != "") {
      echo $token."<br />";
      $token = strtok(" "); // 구분자만 전달시 내부 포인터 유지 (다음 토큰을 읽을 수 있게 함)
    }
    
    //substr(문자열, 시작위치, +반환할문자갯수 or -끝에서부터 제외시킬 문자갯수)

    
    // strstr(검색대상문자열, 검색문자열, 일치하는 문자열 앞의 문자열만 반환할 것인지 여부) 
    // 일치 문자열(여러개일 경우 첫번째 일치문자열)부터 끝까지 반환
    // stristr() : 대소문자 무시하고 찾기, strrchr() : 마지막 일치 문자열부터 끝까지 반환
    /*
    if (strstr($feedback, 'shop')) { 
      $toaddress = 'retail@example.com';
    } else if (strstr($feedback, 'delivery')) {
      $toaddress = 'fulfillment@example.com';
    } else if (strstr($feedback, 'bill')) {
      $toaddress = 'accounts@example.com';
    }
    */
    echo "<br /><br />";

    echo "[ strpos ]<br />";
    // strpos()/strrpos() : 첫번째/마지막 일치 문자열의 위치를 숫자로 반환 (strstr()보다 속도 빠름. 권장)
    $test = "Hello world";
    $result = strpos($test, "H");
    if($result === false) { // 일치 문자열 없을 경우 false반환. false는 0과 동일하므로 위치를 나타내는 0과 혼동되지 않도록 반환값 검사 필요
      echo "Not found";
    } else {
      echo "Found at position ".$result;
    }

    echo "[ str_replace / substr_replace ]<br />";
    $offcolor = array("fucking","shit");
    $feedback = str_replace($offcolor, '%!@*#', $feedback);  // $feedback에서 $offcolor 요소 값에 해당하는 부분을 '%!@*#'로 대체
    echo "<br />$feedback";

    $test = substr_replace($test, 'X', -2, 1); // 특정위치(숫자) 문자열 대체// Hello worXd
    echo "<br />$test";



    ?>



  </body>
</html>