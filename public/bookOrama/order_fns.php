<?php
function process_card($card_details) {
  // connect to payment gateway or
  // use gpg to encrypt and mail or
  // store in DB if you really want to

  return true;
}

function insert_order($order_details) {
  extract($order_details); //extract()로 추출한 변수를 별도의 변수에 저장

  if(!$ship_name  && !$ship_address && !$ship_city && !$ship_state && 
      !$ship_zip && !$ship_country) { //배송지 정보 생략시 
    // 입력받은 사용자 정보를 배송지 정보 변수에 저장
    $ship_name = $name;
    $ship_address = $address;
    $ship_city= $city;
    $ship_state = $state;
    $ship_zip = $zip;
    $ship_country = $country;
  }

  // 배송지 정보가 등록되어 있는지 조회
  $conn = db_connect();
  $conn->autocommit(FALSE); // 오토커밋 비활성화 (다건의 DB변경이 끝난 후 한 번에 커밋할 것)
  $query = "select customerid from customers where
            name='".$conn->real_escape_string($name)."'".
            " and address='".$conn->real_escape_string($address)."'".
            " and city='".$conn->real_escape_string($city)."'".
            " and state='".$conn->real_escape_string($state)."'".
            " and zip='".$conn->real_escape_string($zip)."'".
            " and country='".$conn->real_escape_string($country)."'";
  $result = $conn->query($query);


  if($result->num_rows>0) { // 구매자 아이디 가져오기
    $customer = $result->fetch_object();
    $customerid = $customer->customerid;
  } else { // 입력한 배송지정보를 조회할 수 없는 경우 새로 삽입
    $query = "insert into customers 
              (name, address, city, state, zip, country)
              values
              ('".$conn->real_escape_string($name)."', '".
              $conn->real_escape_string($address)."', '".
              $conn->real_escape_string($city)."', '".
              $conn->real_escape_string($state)."', '".
              $conn->real_escape_string($zip)."', '".
              $conn->real_escape_string($country)."')";
    $result = $conn->query($query);
    if(!$result) { echo "1".mysqli_error($conn);
      return false;
    }
  }


    // 주문정보 db삽입
    $customerid = $conn->insert_id; // mysqli객체에 저장된 쿼리결과에서 작성자 id가져오기
    $date = date("Y-m-d");
    $query = "insert into orders 
              (customerid, amount, date, ship_name, 
                ship_address, ship_state, ship_zip, ship_country)
              values 
              ('".$conn->real_escape_string($customerid)."', ".
              "'".$conn->real_escape_string($_SESSION['total_price'])."', ".
              "'".$conn->real_escape_string($date)."', ".
              "'".$conn->real_escape_string($name)."', ".
              "'".$conn->real_escape_string($address)."', ".
              "'".$conn->real_escape_string($state)."', ".
              "'".$conn->real_escape_string($zip)."', ".
              "'".$conn->real_escape_string($country)."')";
    $result = $conn->query($query);
    if(!$result) {echo "2".mysqli_error($conn);
      return false;
    }

    // 삽입한 주문건id 가져오기
    $query = "select orderid 
              from orders 
              where 
              customerid = '".$conn->real_escape_string($customerid)."'".
              //" and amount > (".(float)$_SESSION['total_price'] ."-.001)".
              //" and amount < (". (float)$_SESSION['total_price']."+.001)".
              " and date = STR_TO_DATE('".$conn->real_escape_string($date)."', '%Y-%m-%d')".
              //" and order_status = 'PARTIAL'".
              " and ship_name = '".$conn->real_escape_string($ship_name)."'".
              " and ship_address = '".$conn->real_escape_string($ship_address)."'".
              " and ship_state = '".$conn->real_escape_string($ship_state)."'".
              " and ship_zip = '".$conn->real_escape_string($ship_zip)."'".
              " and ship_country = '".$conn->real_escape_string($ship_country)."'";
      $result = $conn->query($query);
      if($result->num_rows>0) {
        $order = $result->fetch_object();
        $orderid = $order->orderid;
      } else {echo "3".mysqli_error($conn);
        return false;
      }

      // 주문상세 데이터 삽입
      foreach($_SESSION['cart'] as $isbn=> $quantity) {
        $detail = get_book_details($isbn);
        // 동일한 주문건 삭제
        $query = "delete from order_items where
                  orderid = '".$conn->real_escape_string($orderid).
                  " and isbn = '".$conn->real_escape_string($isbn)."'";
        $result = $conn->query($query);
        
        $query = "insert into order_items values 
                  ('".$conn->real_escape_string($orderid).
                  "', '".$conn->real_escape_string($isbn).
                  "', '".$conn->real_escape_string($detail['price']).
                  "', '".$conn->real_escape_string($quantity)."')";
        $result = $conn->query($query);
        if(!$result) {echo "4".mysqli_error($conn);
          return false;
        }
      }

      // 모든 변경사항이 완료된 후 커밋처리
      $conn->commit();
      $conn->autocommit(TRUE); // 다시 오토커밋 설정
      return $orderid;
}

?>
