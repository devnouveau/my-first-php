<?php
/**
 * Magic Method: Methods
 */
// 없는 함수를 만들고 호출할 때
// 여러 함수 내용들이 비슷할 때
class A 
{
  // 정의되지 않은 메소드 호출시 대신 호출되는 __call()
  public function __call($name, $args) 
  {
    var_dump($name, $args);
  }
  // 정적메소드 호출시 __callStatic()
  public static function __callStatic($name, $args)
  {
    var_dump($name, $args);
  }
  public function __invoke(...$args)
  {
    var_dump($args);
  }
}

$a = new A();
$a->foo('Hello, world'); // __call() 호출
echo "<p>==================</p>";
A::foo(); // __callStatic() 호출
echo "<p>==================</p>";
$a('hello', 'bye'); // __invoke() 호출
echo "<p>==================</p>";


?>