<?php
  phpinfo();
// session_start();

  // dump_array()의 반환값 html 주석으로 출력하기 
  echo "\n<!-- BEGIN VARIABLE DUMP -->\n\n";

  echo "<!-- BEGIN GET VARS -->\n";
  echo "<!-- ".dump_array($_GET)." -->\n";

  echo "<!-- BEGIN POST VARS -->\n";
  echo "<!-- ".dump_array($_POST)." -->\n";

  echo "<!-- BEGIN SESSION VARS -->\n";
  echo "<!-- ".dump_array($_SESSION)." -->\n";

  echo "<!-- BEGIN COOKIE VARS -->\n";
  echo "<!-- ".dump_array($_COOKIE)." -->\n";

  echo "\n<!-- END VARIABLE DUMP -->\n";




function dump_array($array) { 

  if(is_array($array)) {

    $size = count($array);
    $string = "";

    if($size) {
      $count = 0;
      $string .= "{ ";
      foreach($array as $var => $value) {
        $string .= $var." = ".$value;
        if($count++ < ($size-1)) {
          $string .= ", ";
        }
      }
      $string .= " }";
    }
    return $string;
  } else {
    return $array;
  }

}

?>
