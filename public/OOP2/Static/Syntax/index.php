<?php
/**
 * Static
 */
// static은 객체생성없이도 사용가능!

Class A {
  public static $message = "hello, world";
  public static function foo() {
    return self::$message;
    //retrun $this->message; // static은 $this를 사용할 수 없음 ($this는 인스턴스에 사용)
  }
}

// var_dump(A::foo());
// var_dump(A::$message);

$classname = 'A';

$a = new $classname(); // new A()
var_dump($a->foo()); // 객체로 정적메소드 접근 가능 (권장하지 않음)
var_dump($a->message); // 객체로 정적프로퍼티는 접근 할 수 없음




?>