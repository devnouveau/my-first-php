<?php
// db연결
function db_connect() {
   $result = new mysqli('localhost', 'book_sc', 'password', 'book_sc');
   if (!$result) {
      return false;
   }
   $result->autocommit(TRUE);
   return $result;
}

// mysqli결과를 associative array로 반환
function db_result_to_array($result) {
   $res_array = array();

   for ($count = 0; $row = $result->fetch_assoc(); $count++) {
      $res_array[$count] = $row;
   }
   return $res_array;
}


?>
