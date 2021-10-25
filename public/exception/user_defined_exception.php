<?php

class myException extends Exception 
{
  function __toString() //오버라이딩
  {
       return "<strong>Exception ".$this->getCode()
       ."</strong>: ".$this->getMessage()."<br />"
       ."in ".$this->getFile()." on line ".$this->getLine()."<br/>";
   }
}

try
{
  throw new myException("A terrible error has occurred", 42);
}
catch (myException $m)
{
   echo $m; // __toString() 자동으로 출력
}

?>
