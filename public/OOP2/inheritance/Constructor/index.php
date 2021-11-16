<?php
/**
 * Construnctor, Destructor
 */
Class A {
  public function __construct() {
    var_dump(__METHOD__); 
  }
  
  public function __destruct() { // 메모리 관리를 위해서 소멸자로 소멸시켜줌
    var_dump(__METHOD__); 
  }

}
$a = new A();
unset($a);


/**
 * Counstructor Parameter
 */
Class B {
  public $message;
  public function __construct($message) {
    $this->message = $message;
  }
}
$b = new B("hello");


/**
 * Inherit
 */
Class C extends A {
  // 자식에 생성자 없으면 부모클래스의 생성자를 호출함
  // 부모클래스의 생성자를 사용할 것을 권장
  public function __construct() {
    parent::__construct();   
  }
  public function __destruct() {
    parent::__destruct();   
  }
}

$c = new C();


?>