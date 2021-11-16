<?php
/**
 * Magic Method: Property
 */
class A
{
  private $message;
  public function __isset($name)
  {
    return isset($this->$name); 
  }
  public function __unset($name)
  {
    unset($this->$name);
  }
 
  public function __set($name, $value)
  {
    $this->$name = $value;
  }
  public function __get($name)
  {
    return $this->$name;
  }
}
$a = new A();
isset($a->message);
var_dump($a->message);

$a->message = 'hello';


?>