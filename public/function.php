<?php
// ============================= scope =============================
// 함수 안에서 선언된 지역변수 - function scope
// 함수 밖에서 선언된 전역변수 - global scope : but 함수 내부에서 공유불가
// 슈퍼글로벌 변수 - 함수안팎에서 사용가능

// unset() 호출하여 변수 삭제 가능


function testlocalvar() {
    //echo "함수내부에서 지역변수 값 할당 전".$var1."<br />"; // undefined
    $var1 = 2;
    echo '함수내부에서 지역변수 값 할당 후'.$var1."<br />"; // 2
}

$var1 = 1;
testlocalvar();
echo '함수외부에서 전역변수의 값 할당 후'.$var1."<br />"; // 1


// 함수 내부에서 전역변수 생성
function testglobalvarinfunc() {
    global $var2;
    $var2 = 'contents';
    echo "함수내부에서 생성된 전역변수를 함수내부에서 출력 ".$var2."<br />"; // contents
}
testglobalvarinfunc();
echo "함수내부에서 생성된 전역변수를 함수외부에서 출력 ".$var2."<br />"; // contents



// ============================= pass by value / pass by reference =============================
// pass by value : 함수 외부의 변수를 함수에 값으로 전달시, 새로운 변수로 전달되기 때문에 함수 내부에서 값을 변경해도 외부의 변수는 값 변화가 없다.
echo "<br />===== pass by value =====<br />";

function increment($value, $amount = 1) {
    $value = $value + $amount; // 11
} 
$value = 10;
increment($value);
echo $value.'<br />'; // 10

echo "<br />===== pass by reference =====<br />";
// pass by reference : 함수외부의 변수의 참조를 함수에 전달시, 해당 참조변수를 변경하면 원래 변수값도 변경되어 외부 변수 값이 변한 걸 확인 할 수 있다.
function increment2(&$val, $add = 1) {
    $val = $val + $add; // 11
}
$a = 10;
echo $a.'<br />'; // 10
increment2($a);
echo $a.'<br />'; // 11


// ============================= 재귀함수 =============================
echo "<br />===== recursion =====<br />";
function reverse_r($str) {
    if (strlen($str)>0) {
      reverse_r(substr($str, 1)); // 재귀함수
    }
    echo substr($str, 0, 1);
    return;
 }
 
 function reverse_i($str) {
    for ($i=1; $i<=strlen($str); $i++) {
      echo substr($str, -$i, 1);
    }
    return;
 }
 
 reverse_r('Hello');
 echo '<br />';
 reverse_i('Hello');


 // ============================= 익명함수(클로져 closure) =============================
 echo "<br />===== 익명함수(클로져 closure) -콜백 =====<br />";
 // 콜백으로 많이 사용
 $array = array(1,2,3,4);
 array_walk($array, function($value) { echo "$value <br/>"; });  
 
 echo "<br />===== 익명함수(클로져 closure) -변수저장 =====<br />";
 // 변수에 저장 가능
 $printer = function($value) { echo "$value <br/>"; }; // 
 $printer('Hello');


 echo "<br />===== 익명함수(클로져 closure) - 전역범위의 변수 액세스 =====<br />";
 $printerclosure =  function($value){ echo "$value \t"; };

 $products = [ 'Tires' => 100, 
               'Oil' => 10,
               'Spark Plugs' => 4 ]; 
 
 $markup = 0.20;
 
 // &$val2는 $products 각 요소들의 참조를 매개변수로 받음 ( $products 요소들의 값이 변경됨 )
 // use ($markup)은 전역범위 변수를 익명함수 내부에서 사용한다는 의미
 $apply = function(&$val2) use ($markup) { 
            $val2 = $val2 * (1+$markup);
          };
 
 array_walk($products, $apply);
 array_walk($products, $printerclosure); // 120 12 4.8

?>