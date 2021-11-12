<?php

  include ('book_sc_fns.php');

  session_start();

  do_html_header("Checkout");

  if(($_SESSION['cart']) && (array_count_values($_SESSION['cart']))) { // 카트에 담긴 것이 있는지 확인
    display_cart($_SESSION['cart'], false, 0); 
    display_checkout_form();
  } else {
    echo "<p>There are no items in your cart</p>";
  }

  display_button("show_cart.php", "continue-shopping", "Continue Shopping");

  do_html_footer();
?>
