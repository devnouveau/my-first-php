<?php

// ================ 문자열 처리와 정규표현식 ================ //


// trim()으로 앞 뒤 공백문자(\n,\r,\0,space)제거 // ltrim(),rtrim(),chop()
$name = trim($_POST['name']); 
$email = trim($_POST['email']);
$feedback = trim($_POST['feedback']);

// 이메일 입력값에 따라 다르게 처리하기
$email_array = explode('@', $email); 
if(strtolower($email_array[1]) == "bigcustomer.com") {
  $toaddress = "important@example.com";
} else {
  $toaddress = "feedback@example.com";
}

$subject = "Feedback from web site";

// \r\n을 제거하여 이메일 헤더값 구분에 문제가 없도록 함
$mailcontent = "Customer name: ".str_replace("\r\n", "", $name)."\n".
               "Customer email: ".str_replace("\r\n", "",$email)."\n".
               "Customer comments:\n".str_replace("\r\n", "",$feedback)."\n";

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


    echo "<p>".nl2br(htmlspecialchars($feedback))."</p>";
    // nl2br()
    // html에서는 newline \n(공백문자)을 무시함. 
    // 따라서 이를 <br />로 변경해주어야 줄바꿈을 적절하게 출력할 수 있음 

    // htmlspecialchars()
    // &, ", ', <, >을 html엔티티로 변환
    // 기본적으로 ",' 중 "만 변환(flags 매개변수로 변경가능)

    // htmlspecialchars() 이후 nl2br()를 해야 함
    echo "<p>htmlspecialchars() -> nl2br() : ".nl2br(htmlspecialchars($mailcontent))."</p>"; 
    // nl2br()을 먼저하고 htmlspecialchars()적용시 <br />이 문자그대로 출력되게됨
    echo "<p>nl2br() -> htmlspecialchars() : ".htmlspecialchars(nl2br($mailcontent))."</p>"; 




    // printf() : 형식지정하여 출력 , sprintf() : 형식지정하여 반환
    // 변환 명세 타입코드는 검색해볼 것...
    $total = 100.123;
    $total_shipping = 10.456;
    printf("총 금액은 %2\$.2f (총배송료 %1\$.2f)", $total_shipping, $total); // 숫자\$ 기호를 이용해 인자의 위치 지정가능

    // strtoupper(), strtolower(), ucfirst(), ucwords()
    // explode() : 구분자로 문자열 분리하여 배열로 반환, implode()/join() : 배열값들을 구분자로 결합하여 문자열로 반환
    
    echo "<br /><br />";

    // strtok() : 문자열에서 한 번에 하나의 조각을 가져옴
    $token = strtok($feedback, " "); // 문자열, 구분자 전달시 포인터 초기화
    while ($token != "") {
      echo $token."<br />";
      $token = strtok(" "); // 구분자만 전달시 내부 포인터 유지 (다음 토큰을 읽을 수 있게 함)
    }
    
    //substr(문자열, 시작위치, +반환할문자갯수 or -끝에서부터 제외시킬 문자갯수)

    ?>



  </body>
</html>