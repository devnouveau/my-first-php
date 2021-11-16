<?php
// 변수의 메모리공간에 이름을 붙임


/**
 * References
 */
$message = 'hello init';
$sayhello =& $message; // $sayhello가 $message를 참조하게 됨

$sayhello = 'who are you';
var_dump($message); // who are you








/**
 * Functions and Methods
 */

 // 글로벌변수를 참조
function foo() 
{
  // global $message; // 
  $message =& $GLOBALS['message']; //$message를 함수 내부 뿐 아니라 글로벌변수로 사용할 수 있게 해줌 (위와같은 효과)
  $message = 'Bye'; 
}
foo();
var_dump($message); // Bye


// 참조를 파라미터로 전달
function foo2(&$message) 
{
  $message = 'hello ref param';
}
foo2($message);
var_dump($message); //hello로 변경됨



// 참조를 리턴하기 (권장하지 않음)
class MyClass 
{
  public $message = 'hello ref return';
  public function &getMessage()
  {
    return $this->message; // 참조를 리턴
  }
}
$myclass = new MyClass();
$sayhello =& $myclass->getMessage(); // $sayhello는 $myclass의 &$message도 참조하게 됨
$sayhello = 'Bye';

var_dump($myclass->message); // $myclass의 $message도 Bye로 변경된 것을 확인 가능


/**
 * Unset
 */
$sayhello =& $message;
unset($sayHello); // $sayHello가  $message를 참조하지 않도록 참조해제

var_dump($message); // 다시 hello ref param이 됨


?>