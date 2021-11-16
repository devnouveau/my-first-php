<?php
/**
 * Class Abstraction
 */
// 구현부, 미구현부가 혼재
// 최상위클래스는 객체 생성 x. 자식클래스만으로 구현하기를 원할 때 사용

function foo(A $a) {
  return $a->foo();
}

abstract class A {
  protected $message = 'hello';
  public function sayHello() {
    return $this->message;
  }
  abstract public function foo(); // 자식클래스에서 구현해주어야 하는 추상메소드
}
// 객체 생성 불가


class B extends A {
  public function foo() { // 추상클래스 구현
    return __CLASS__;
  }
}

$b = new B();
var_dump($b->foo()); // B
var_dump(foo($b)); // B



?>