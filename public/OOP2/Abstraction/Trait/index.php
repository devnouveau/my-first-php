<?php
/**
 * Trait
 */
trait A {
  // abastact메소드, 구현메소드, private멤버 가질 수 있음
  private $message = 'hello';
  public function sayHello(){
   return $this->message;
  }
  abstract public function foo();
}

trait AA {
  public function sayHello() {
    return __TRAIT__;
  }
}

// 트레이트 확장
// trait AAA {
//   use A, AA {
//     A::sayHello insteadOf AA; // 동일한 메소드 명이 있는 경우
//     A::sayHello as protected h; // 별칭 지정 // h();로는 접근제한됨
//   }
// }



class B {
  use A, AA { // trait 사용
    A::sayHello insteadOf AA; // 동일한 메소드 명이 있는 경우
    A::sayHello as protected h; // 별칭 지정 // h();로는 접근제한됨
  }
  public function foo() {
    return __CLASS__;
  }
}

$b = new B();
var_dump($b->sayHello());




echo "<p>=========================</p>";

class C {
  private $message = 'hello';
  public function sayHello() {
    return $this->message;
  }
}
trait D {
  public function sayHello() {
    return __TRAIT__;
  }
}
// 우선순위 높은 순 : 재정의 - 트레이트 - 상속
class E extends C { // 상속
  use D; // 트레이트
  // public function sayHello() { // 재정의
  //   return __CLASS__;
  // }
}

$e = new E();
var_dump($e->sayHello());

?>