<?php

try  {
  // 예외 발생 가능성있는 코드
  // ...


  throw new Exception("A terrible error has occurred", 42); // 예외 발생시키기
}
catch (Exception $e) {
  echo "Exception ". $e->getCode(). ": ". $e->getMessage()."<br />".
  " in ". $e->getFile(). " on line ". $e->getLine(). "<br />";
  // 이외에도, Exception의 메소드로 
  // getTrace(), getTraceAsString(), :: 예외발생시점까지 호출된 함수들의 기록
  // getPrevious(), __toString()

  echo $e; // __toString()메서드 자동호출(Exception객체의 모든 정보를 출력)
}

?>
