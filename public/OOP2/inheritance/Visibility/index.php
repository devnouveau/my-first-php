<?php
/**
 * 가시성 Visibility
 */

class A {
  public $public = 'public';
  protected $protected = 'protected';
  private $private = 'private';
}
$a = new A();
// var_dump($a->private); private에 접근 불가

class B extends A {
  
  private $message = 'hello';
  private static $instance;

  private function __construct() {
    var_dump($this->message); 
  }
  public function foo() {
    return $this->protected; //상속받은 메소드 내에서는 protected 프로퍼티, 메소드에 접근가능
  }

  public static function getInstance() { // 생성자는 private으로 막아두고, 하나의 객체만을 얻을 수 있도록 함. singleton
    return self::$instance ? : self::$instance = new self();
  }
}

// $b = new B(); // 생성자가 private인 경우 객체 생성 불가
// var_dump($b->private);

// 두번 불렀지만 하나의 객체만을 생성함
$b = B::getInstance();
$bb = B::getInstance();

var_dump($bb == $b); // 동일한 인스턴스

?>