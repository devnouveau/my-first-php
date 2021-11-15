<?php
class ParentClass {
  function callMethod($param) {
    echo "<h1>Parent{$param}</h1>";
  }
}

class ChildClass extends ParentClass {
  function callMethod($param) { // 메소드 오버라이딩
    parent::callMethod($param); // 부모클래스의 메소드 호출방법 // parent:: 부모클래스를 가리킴
    echo "<h1>Child{$param}</h1>";
  }
}

$obj = new ChildClass();
$obj->callMethod('method');



?>