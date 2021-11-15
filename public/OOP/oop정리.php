
[ php ]

/*************** 클래스 선언 **************/ 
class classname {
  private $attribute;
  function operation() {
  };
  function __construct();
  __get() {
  return $this->$name;
  }
  __set($name, $value) {
   $this->$name = $value;
  }
}

/*************** 인스턴스 생성, 속성/메소드 접근 **************/ 
$a = new classname();
$a->attribute; //자동으로 __get()함수 호출
$a->attribute = 5; //자동으로 __set()함수 호출
$a->operation();


/*************** $this, self **************/ 
class Person {
  private static $count = 0;
  private $name;
  function construnct($name) {
    $this->name = $name; // $this는 인스턴스에 대한 자기사신
    self::$count = self::$count+1; // self는 클래스에 대한 자기자신
  }
  function enter() {
    echo "<h1>Enter".$this->name." ".self::$count."</h1>"; // 인스턴스변수->메소드 , 클래스::클래스멤버
  }
}



/*************** 상속 **************/ 
class A {  // final class A일 경우 : 상속불가
  public attribute = 1; // 상속가능
  private $attribute1; // private키워드 : 상속불가
  function operation() { // 상속가능
  };
  final function operation1() { // final키워드 : 오버라이딩 불가
  };
  function __construct();
  __get() {
  return $this->$name;
  }
  __set($name, $value) {
   $this->$name = $value;
  }
}

class B extends A {
  public attribute = 2; // 속성 오버라이딩
  private $attribute2;
  function operation() { // 메소드 오버라이딩
  };
  function operation2() { // 부모클래스에 없는 메소드 추가
  };
  function __construct();
  __get() {
  return $this->$name;
  }
  __set($name, $value) {
   $this->$name = $value;
  }
}

// 자식클래스에서 부모클래스의 메소드 호출방법
class ParentClass {
  function callMethod($param) {
    echo "<h1>Parent{$param}</h1>";
  }
}
class ChildClass extends ParentClass {
  function callMethod($param) {
    parent::callMethod($param);  // parent:: 부모클래스를 가리킴
    echo "<h1>Child{$param}</h1>";
  }
}
$obj = new ChildClass();
$obj->callMethod('method');


// 상속 가시성 제어 (private/protected)
private멤버 상속불가, 클래스외부 사용불가
protected멤버 상속가능, 클래스외부 사용불가

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
    echo $this->_protected;  // protected 멤버에 상속한 자식이 접근가능
  }
  function callPrivate(){
    echo $this->_private; // private 멤버에 상속한 자식이 접근불가
  }
}

$obj = new ChildClass();
echo "<p>프로퍼티접근</p>";
echo $obj->_public; // 자식에게 없으므로 부모에게서 탐색하여 접근가능
//echo $obj->_private; // private프로퍼티는 클래스외부에서 사용불가
//echo $obj->_protected; // protected프로퍼티는 클래스외부에서 사용불가

echo "<p>메소드호출로 프로퍼티접근</p>";
echo $obj->callPublic(); 
echo $obj->callProtected(); 
//echo $obj->callPrivate(); 


/*************** 인터페이스 interface **************/ 
// 우회적으로 다중상속 구현
// 반드시 구현해야 할 메소드를 선언함(메소드 세부내용은 정의하지 않음)
// 인터페이스를 implements하는 클래스는 반드시 인터페이스에서 선언된 메소드를 모두 구현해야 함



/*************** 추상클래스 abstract **************/ 




/*************** 트레이트 trait **************/ 
// 메서드 시그니처만 지정하는 인터페이스와 달리, 트레이트는 메서드 구현부도 포함
// 여러 클래스에서 재사용될 수 있는 메서드를 모아둘 수 있음. 단일클래스 다중트레이트결합 가능. 트레이트 간 상속가능
// user keyword로 사용
// .. instead of ..
//



/*************** 클래스 상수 **************/ 
class A {
  const pi = 3.14159;
}
echo "pi 값은 ".Math::pi;


/*************** static 클래스멤버  **************/ 
// 참조하는 현재의 객체가 없으므로 this키워드 사용불가
class A {
  static function squared($input) { // static이 붙은 메소드, 프로퍼티는 모든 인스턴스가 공유할 수 있음.
    return $input * $input;
  }
echo A::squared;


/*************** 클래스 타입, 힌트  **************/ 
- instanceof 연산자
- 타입힌트 : 함수에 매개변수 전달시 타입을 검사하도록 사전에 힌팅
function check_hint(B $classname) {} // 매개변수가 classname이나, 이를 상속하는 클래스의 인스턴스가 아닌 경우 오류 발생





/*************** 네임스페이스  **************/ 
// 클래스, 함수, 인터페이스, 상수를 분류하여 모아둠
- orders.php 네임스페이스 정의
namespace orders;
class order {...}
class orderItem {...}
- 네임스페이스의 클래스 사용시 1. (전체경로 네임스페이스)
include 'orders.php';
$myOrder = new orders\order(); // orders네임스페이스의 order클래스 사용 
- 네임스페이스의 클래스 사용시 2. 현재 네임스페이스 탐색(함수,상수는 없으면 전역 네임스페이스에서 탐색)
namespace orders;
include 'orders.php'; // 현재 네임스페이스
$myOrder = new order(); // 현재 네임스페이스를 탐색하는 경우 네임스페이스 생략




// 서브 네임스페이스
- 정의
namespace bob\html\page; 
class Page {}
namespace kim\html\page; 
class Page {}

- 사용
-- 네임스페이스 클래스 사용
$services = new bob\html\page\Page(); // 서브경로포함
$services = new html\page\Page(); // 현재 bob 네임스페이스에 있는 경우, 해당네임스페이스경로 생략

-- 동일한 클래스명이지만, 서로다른 네임스페이스에 속한 경우
$servicesBob = new bob\html\page\Page(); 
$servicesKim = new kim\html\page\Page();


-- 전역 네임스페이스 사용
$services = new \Page();

-- 네임스페이스 불러와서 사용하기 
use bob\html\page; 
$services = new page\Page();

use bob\html\page\Page; // use 네임스페이스\클래스명;
$services = new Page(); // 클래스명으로 바로 사용가능



-- 네임스페이스 별칭 사용
use bob\html\page as www;
$services = new www\Page();


-- 네임스페이스는 클래스 뿐 아니라 상수, 함수에 대해서도 사용가능





