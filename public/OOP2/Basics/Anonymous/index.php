<?php
/**
 * Anonymous Classes 
 */
Class A {
  public function foo() {
    return "anonymous";
  }
}


Class B {
  public function create() {
    return new class extends A {}; // 익명클래스
  }
}
$b = new B();
var_dump($b->create()); // 클래스A를 상속하는 익명클래스출력 // object(A@anonymous)#2 (0) { }
var_dump($b->create()->foo()); // 클래스A의 메소드 상속


?>