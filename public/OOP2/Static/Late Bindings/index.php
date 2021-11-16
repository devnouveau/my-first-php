<?php
// 늦은 정적 바인딩
// self::와 상수 __CLASS__ 메소드가 어디에 정의되어 있는가에 따라 값이 결정됨
// static::은 정의된 클래스를 컴파일 시간에 결정할 수 없고, 프로그램 실행시 전달되는 정보로 결정 (늦은 정적 바인딩)

Class A {
  public static function foo() {
    static::who(); 
  }
  public static function who() {
    var_dump('A 클래스 who()');
    var_dump(__CLASS__);
  }
}
Class B extends A {
  public static function test() {
    A::foo(); // A 클래스 who() 호출 
    // parent::foo(); //B 클래스 who() 호출
    // self::foo(); //B 클래스 who() 호출
  }
  public static function who() {
    var_dump('B 클래스 who()');
    var_dump(__CLASS__);
  }
}

$b = new B();
$b->test(); // static var_dump(__CLASS__) 이므로 객체자신을 가리킴?

?>