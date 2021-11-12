<?php

  include ('book_sc_fns.php');
  session_start();

  do_html_header("Checkout");

  $name = $_POST['name'];
  $address = $_POST['address'];
  $city = $_POST['city'];
  $zip = $_POST['zip'];
  $country = $_POST['country'];

  // 주문에 필요한 정보가 입력되어 있다면
  if (($_SESSION['cart']) && ($name) && ($address) && ($city) && ($zip) && ($country)) {
    if(insert_order($_POST) != false ) { // db에 성공적으로 주문정보를 등록했다면
      
      display_cart($_SESSION['cart'], false, 0); // 이미지 출력과 수량변경버튼 없이 장바구니정보 출력

      display_shipping(calculate_shipping_cost());

      //get credit card details
      display_card_form($name);

      display_button("show_cart.php", "continue-shopping", "Continue Shopping");
    } else {
      echo "<p>Could not store data, please try again.</p>";
      display_button('checkout.php', 'back', 'Back');
    }
  } else {
    echo "<p>You did not fill in all the fields, please try again.</p><hr />";
    display_button('checkout.php', 'back', 'Back');
  }

  do_html_footer();
?>
