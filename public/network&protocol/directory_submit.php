<!DOCTYPE html>
<html>
<head>
   <title>Site Submission Results</title>
</head>
<body>
   <h1>Site Submission Results</h1>

<?php

$url = $_POST['url'];
$email = $_POST['email'];

// 호스트/IP 추출
$url = parse_url($url); 
$host = $url['host'];
// gethostbyname() :: hostname으로 ipv4주소 얻기
if (!($ip = gethostbyname($host)))
{
  echo 'Host for URL does not have valid IP address.';
  exit;
} 
echo 'Host ('.$host.') is at IP '.$ip.'<br/>';
 

$email = explode('@', $email);
$emailhost = $email[1];
if (!getmxrr($emailhost, $mxhostsarr))
{  // getmxrr() :: mail exchange record확인(이메일 호스트 정보가 유효한지 체크)
  echo 'Email address is not at valid host.';
  exit;
}

echo 'Email is delivered via: <br/>
<ul>';


// 
for($i=0;$i<count($mxhostsarr);$i++){
    echo '<li>'.$mxhostsarr[$i].'</li>';
}

echo '</ul>';

// If reached here, all ok
echo '<p>All submitted details are ok.</p>';
echo '<p>Thank you for submitting your site. 
      It will be visited by one of our staff members soon.</p>';
// In real case, add to db of waiting sites...
?>
</body>
</html>
