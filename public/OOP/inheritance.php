<?php
class ParentClass {
  public $_public = '<h1>public</h1>';
  protected $_protected = '<h1>protected</h1>';
  private $_private = '<h1>private</h1>'; // 부모만 접근가능
}
class ChildClass extends ParentClass {
  function callPublic() {
    echo $this->_public;
  }
  function callProtected(){
    echo $this->_protected; 
  }
  function callPrivate(){
    echo $this->_private; 
  }
}

$obj = new ChildClass();
echo "<p>프로퍼티접근</p>";
echo $obj->_public; // 자식에게 없으므로 부모에게서 탐색하여 접근가능
//echo $obj->_private; // 접근 불가
//echo $obj->_protected; // 인스턴스가 protected멤버변수에 직접접근은 불가

echo "<p>메소드호출로 프로퍼티접근</p>";
echo $obj->callPublic(); 
echo $obj->callProtected(); // 상속한 자식이 접근가능
//echo $obj->callPrivate(); // 자식이 부모의 private 멤버에 접근 불가

?>