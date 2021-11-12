<?php
  function calculate_shipping_cost() {
    // as we are shipping products all over the world
    // via teleportation, shipping is fixed
    return 20.00;
  }

  // db에 저장되어 있는 카테고리 정보들 가져오기
  function get_categories() {
    $conn = db_connect();
    $query = "select catid, catname from categories";
    $result = @$conn->query($query);
    if(!$result) {
      return false;
    }
    $num_cats = @$result->num_rows;
    if($num_cats == 0) {
      return false;
    }
    $result = db_result_to_array($result);
    return $result;
  }

  // 사용자가 클릭한 카테고리의 이름을 db에서 가져옴
  function get_category_name($catid) {
    $conn = db_connect();
    $query = "select catname from categories
              where catid = '".$conn->real_escape_string($catid)."'";
    $result = @$conn->query($query);
    if(!$result) {
      return false;
    }
    $num_cats = @$result->num_rows;
    if($num_cats == 0) {
      return false;
    }
    $row = $result->fetch_object();
    return $row->catname;
  }



  // 특정 카테고리에 속한 도서목록가져오기
  function get_books($catid) {
    if ((!$catid) || ($catid == '')) {
      return false;
    }
 
    $conn = db_connect();
    $query = "select * from books where catid = '".$conn->real_escape_string($catid)."'";
    $result = @$conn->query($query);
    if (!$result) {
      return false;
    }
    $num_books = @$result->num_rows;
    if ($num_books == 0) {
       return false;
    }
    $result = db_result_to_array($result);
    return $result;
 }
 
 // 특정 도서의 정보가져오기
 function get_book_details($isbn) {
   if ((!$isbn) || ($isbn=='')) {
      return false;
   }
   $conn = db_connect();
   $query = "select * from books where isbn='".$conn->real_escape_string($isbn)."'";
   $result = @$conn->query($query);
   if (!$result) {
      return false;
   }
   $result = @$result->fetch_assoc();
   return $result;
 }
 


  // 장바구니에 있는 책의 금액합계
  function calculate_price($cart) {
    $price = 0.0;
    if(is_array($cart)) {
      $conn = db_connect();
      foreach($cart as $isbn=> $qty) {
        $query = "select price from books
                  where isbn ='".$conn->real_escape_string($isbn)."'";
        $result = $conn->query($query);
        if($result) {
          $item = $result->fetch_object(); // db데이터를 오브젝트로
          $item_price = $item->price; // 단가 가져오기
          $price +=$item_price*$qty; // 총계에 더하기
        }
      }
    }
    return $price;
  }

  // 장바구니에 있는 책의 수량
  function calculate_items($cart) {
    $items = 0;
    if(is_array($cart)) {
      foreach($cart as $isbn=>$qty) {
        $items += $qty;
      }
    }
    return $items;
  }


?>