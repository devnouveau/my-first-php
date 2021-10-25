<?php



require_once("page.php");

// relection API사용
$class = new ReflectionClass("Page"); // Page 클래스 정보를 얻기
echo "<pre>".$class."</pre>";

?>
