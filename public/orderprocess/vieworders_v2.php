<?php
  // create short variable name
  $document_root = $_SERVER['DOCUMENT_ROOT'];
?>
<!DOCTYPE html>
<html>
  <head>
    <title>Bob's Auto Parts - Customer Orders</title>

    <style type="text/css">
    table, th, td {
      border-collapse: collapse;
      border: 1px solid black;
      padding: 6px;
    }

    th {
      background: #ccccff;      
    }
    </style>

  </head>
  <body>
    <h1>Bob's Auto Parts</h1>
    <h2>Customer Orders</h2> 

    <?php
      // 파일 읽어서 라인별로 배열에 저장하기
      $orders= file("$document_root/orderprocess/orders/orders.txt");
    
      // 배열 행수 count()로 가져오기
      $number_of_orders = count($orders);
    
      if ($number_of_orders == 0) {
        echo "<p><strong>No orders pending.<br />
              Please try again later.</strong></p>";
      }
    
      echo "<table>\n";
      echo "<tr>
              <th>Order Date</th>
              <th>Tires</th>
              <th>Oil</th>
              <th>Spark Plugs</th>
              <th>Total</th>
              <th>Address</th>
            <tr>";
    
      for ($i=0; $i<$number_of_orders; $i++) {
        // 데이터 한 줄을 탭으로 구분하여 배열에 저장하기
        $line = explode("\t", $orders[$i]);
    
        // 주문부품 갯수 저장
        $line[1] = intval($line[1]); //intval 문자열을 정수로 반환
        $line[2] = intval($line[2]);
        $line[3] = intval($line[3]);
    
        // output each order
        echo "<tr>
              <td>".$line[0]."</td>
              <td style=\"text-align: right;\">".$line[1]."</td>
              <td style=\"text-align: right;\">".$line[2]."</td>    
              <td style=\"text-align: right;\">".$line[3]."</td>
              <td style=\"text-align: right;\">".$line[4]."</td>
              <td>".$line[5]."</td>
          </tr>";
      }    
      echo "</table>";
    ?>
  </body>
</html>