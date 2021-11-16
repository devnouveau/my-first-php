<?php
/**
 * 프로퍼티와 메소드
 */
class A {
  public $message = 'Hello, world';

  public function foo() {
    return $this->message;
  }
}
$a = new A();
var_dump($a->foo());


/**
 * 상속
 */
 class B extends A {
 }
 $b = new B();
 var_dump($b->foo());


/**
 * 함수에 객체를 파라미터로 전달
 */
function foo(A $a) {
  return $a->foo();
}
var_dump(foo($b)); // 부모클래스 A를 상속하는 자식클래스를 전달할 수도 있음

echo "<p>============</p>";
/**
 * context 
 */
class C extends A {
  public function foo() {
    // new D();로  호출했을 때 
    return new self(); // 클래스 자신을 가리킴 // C 
    // return new static(); // D 
    // return new parent(); // A
  }
}

class D extends C {
}
$d = new D();
var_dump($d->foo());

echo "<p>============</p>";
/**
 * Constants
 */
Class E {
  const MESSAGE = 'HELLO WORLD';

  public function getConstants(){
    return self::MESSAGE;
  }

  public function getClassName() {
    return __CLASS__;
  }
}
$e = new E();
var_dump($e->getConstants());
var_dump(E::MESSAGE);
var_dump($e->getClassName());


echo "<p>============</p>";
/**
 * isntanceof
 */
$d = new D();
var_dump($d instanceof A); // 조상클래스 상속받았기 때문에 true
var_dump($d instanceof B); // 상속 x -> false


?>