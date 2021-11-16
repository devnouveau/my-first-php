<?php
/**
 * Exception extends
 */

 // Exception 클래스를 상속
class MyException extends Exception
{
}

try {
  throw new MyException('hello');
} catch (MyException $e) {
  var_dump(MyException::class);
} catch (Exception $e) {
  var_dump(Exception::class);
}

?>