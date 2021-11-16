<?php
/**
 * Final
 */
Class A {
  public $message;
  public final function foo() {

  }
}

Class B extends A {
  public function foo() {
    // final 메소드는 오버라이드 할 수 없어 오류 발생
  }
}
 
?>