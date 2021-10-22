<?php
  // create short variable name
  $document_root = $_SERVER['DOCUMENT_ROOT'];
?>
<!DOCTYPE html>
<html>
  <head>
    <title>Bob's Auto Parts - Customer Orders</title>
  </head>
  <body>
    <h1>Bob's Auto Parts</h1>
    <h2>Customer Orders</h2> 
    <?php
      // 데이터 읽기 - 1.파일 열기
      @$fp = fopen("$document_root/orderprocess/orders/orders.txt", 'rb'); // 읽기전용으로 열기 rb
      flock($fp, LOCK_SH); // lock file for reading (acquire a shared lock)

      if (!$fp) { // 예외처리
        echo "<p><strong>No orders pending.<br />
              Please try again later.</strong></p>";
        exit;
      }


      // 데이터 읽기 - 2.데이터 읽기
      while (!feof($fp)) { // feof()으로 파일의 끝인지 검사하며, 파일 끝에 도달할 때까지 루핑  
         $order = fgets($fp); // fgets() -> 줄바꿈문자 (\n)를 만나거나 EOF를 만날 떄까지 읽기 // fgetc()는 한 문자 단위로 읽기
         // fgetss()는 태그제거하여 안전하게 읽기에 사용
         // $order = fgetcsv($fp, 0,"\t"); // fgetcsv()는 데이터에서 구분자로 필드를 구분하여 변수로 재구성한 후 배열로 반환
         echo htmlspecialchars($order)."<br />";
      }
      flock($fp, LOCK_UN); // release read lock


      // 파일포인터 위치 변경
      echo 'Filnal position of the file pointer is '.(ftell($fp)); // 포인터 위치를 바이트 값으로 반환
      echo '<br />';
      rewind($fp); // 포인터를 맨앞으로 
      echo 'After rewind, the position is '.(ftell($fp));
      echo '<br />';
      // fseek(); // 포인터를 특정위치로

      
      // 데이터 읽기 - 3.파일 닫기
      fclose($fp); 


      // =================================================

      // readfile("$document_root/orderprocess/orders/orders.txt"); // 파일열기, 표준출력, 닫기를 한번에 수행
      // fpasethru(fopen("파일경로", 파일모드)); // 파일읽기 성공여부 t/f 반환 
      // $filearray = file("파일경로"); // 파일 데이터의 각 줄을 배열에 담아 반환
      // file_get_contents(); // readfile과 동일하나, 브라우저 출력없이 문자열로 반환

      // fread(); // 임의의 바이트 수만큼 읽기
    ?>
  </body>
</html>