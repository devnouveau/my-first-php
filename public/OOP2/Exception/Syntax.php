<?php
/**
 * Exception
 */
try {
  // throw new Exception('hello');
} catch (Exception $e) {
  var_dump($e->getMessage());
} finally {
  var_dump('Finally');
}

// 핸들러 이용하기
set_error_handler(function($errno, $errstr) {
  throw new Exception($errstr, $errno);
});

set_exception_handler(fn (Exception $e) => var_dump($e->getMessage()));


/**
 * Error
 */

 // Fatal에러 잡기
try {
  new MyClass();
} catch (Error $e) {
  var_dump($e->getMessage());
}

?>