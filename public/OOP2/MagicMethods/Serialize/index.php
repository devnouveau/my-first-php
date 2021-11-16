<?php
/**
 * Magic Method: Serialize
 */

/************ 직렬화 방식 1 ************/
class A
{
  private $message = 'hello';
  
  public function __sleep()
  {
    return ['message']; // property를 배열로 serialize해서 리턴
  }

  public function __wakeup()
  {
    var_dump(__METHOD__); // unserialize
  }
}

$a = new A();

$serialized = serialize($a); // __sleep() 함수 호출됨
var_dump($serialized); 

var_dump(unserialize($serialized)); // __wakeup() 함수 호출




/************ 직렬화 방식 2 ************/
class B implements Serializable
{
  private $message = 'hello';
  public function serialize() 
  {
    return serialize($this->message);
  }
  public function unserialize($serialized)
  {
    $this->message = unserialize($serialized);
  }
}

$b = new B();
$serialized = serialize($b);
var_dump(unserialize($serialized))

// serialize 할 수 있음을 알려줌



?>