<!-- 배열을 함수로 제어하기 -->
<h1>Function Style</h1>
<?php
$arrData = array('a','b','c');
array_push($arrData, 'd'); 
foreach($arrData as $item) {
  echo $item.'<br>';
}
var_dump(count($arrData)); 
// 함수 호출시 배열을 일일이 인자로 넘겨주어야 함
?>

<!-- 배열을 객체방식으로 제어하기 -->
<h1>Object Style</h1>
<?php
$objData = new ArrayObject(array('a','b','c'));
$objData->append('d');
foreach($objData as $item) {
  echo $item.'<br>';
}
var_dump($objData->count());
// 각 메소드 호출시 배열을 일일이 인자로 넘겨주지 않아도 됨
?>