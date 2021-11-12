<?php
include('book_sc_fns.php');

session_start();

if(!isset($_SESSION['cart'])) {
  $_SESSION['cart'] = array();
  $_SESSION['items'] = 0;
  $_SESSION['total_price'] ='0.00';
}

// 새로운 책 선택시(책 상세 페이지에서 카트에 추가버튼 클릭)
@$new = $_GET['new']; // 장바구니에 담은 책의 isbn
if($new) {
  if(isset($_SESSION['cart'][$new])) { // 장바구니에 이미 담겨 있는 책이라면
    $_SESSION['cart'][$new]++; // 책수량 추가
  } else {
    $_SESSION['cart'][$new] = 1; // 책수량 1로 담기
  }
  $_SESSION['total_price'] = calculate_price($_SESSION['cart']);
  $_SESSION['items'] = calculate_items($_SESSION['cart']);
}
// 장바구니에 책을 추가하는 경우(save changes버튼 클릭)
if(isset($_POST['save'])) {
  foreach($_SESSION['cart'] as $isbn => $qty) { 
    if($_POST[$isbn] == 0) {
      unset($_SESSION['cart'][$isbn]);
    } else {
      $_SESSION['cart'][$isbn] = $_POST[$isbn]; // 장바구니에 추가한 특정 책의 수량
    }
  }
  $_SESSION['total_price'] = calculate_price($_SESSION['cart']);
  $_SESSION['items'] = calculate_items($_SESSION['cart']);  
}

// 장바구니 보여주기
do_html_header("your shopping cart");
if($_SESSION['cart'] && (array_count_values($_SESSION['cart']))) { // 세션에 저장되어 있는 장바구니정보가 있으면
  display_cart($_SESSION['cart']);
} else {
  echo "<p>there are no item in your cart</p><hr>";
}


// 계속쇼핑, 결제하기버튼 세팅 
$target = "index.php";
if($new) { // 방금 장바구니에 책을 추가한 경우, continue-shopping버튼을 누르면 주제 페이지로 돌아가도록 함
  $details = get_book_details($new);
  if($details['catid']) {
    $target = "show_cat.php?catid=".urlencode($details['catid']);
  }
}
display_button($target, "continue-shopping", "Continue Shoping");
display_button("checkout.php", "go-to-checkout", "GO To Checkout");



do_html_footer();



?>