<?php
/**
 * Interface
 */

// private, protected 멤버를 가질 수 없음 
// 기능적인 부분을 구현 할 때 사용

function foo(A $a) {
  return $a->foo();
}

interface A { 
  public function foo(); // 반드시 구현해야 함 
}

interface AA extends A { //  인터페이스 확장 // AA 구현시 A, AA의 메소드를 모두 구현해야 함
  public function sayHello();
}

class B implements AA { // 구현클래스
  public function sayHello() { // AA의 메소드 구현
    return 'hello';
  }
  public function foo() { // A의 메소드 구현
    return __CLASS__;
  }
}

$b = new B();
var_dump(foo($b)); // B

?>