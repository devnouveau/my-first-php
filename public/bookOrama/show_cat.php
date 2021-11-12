<?php
  include ('book_sc_fns.php');
  session_start(); // 세션 시작

  // 선택한 카테고리 이름 받아오기 및 출력
  $catid = $_GET['catid'];
  $name = get_category_name($catid); 
  do_html_header($name); 

  // 카테고리 내 도서정보 받아오기 및 출력
  $book_array = get_books($catid); 
  display_books($book_array);


  // 관리자일 경우 관리자 메뉴와 카테고리 수정버튼 출력
  if(isset($_SESSION['admin_user'])) {
    display_button("index.php", "continue", "Continue Shopping");
    display_button("admin.php", "admin-menu", "Admin Menu");
    display_button("edit_category_form.php?catid=". urlencode($catid),
                   "edit-category", "Edit Category");
  } else { // 아닐경우 메인으로 가는 링크 출력
    display_button("index.php", "continue-shopping", "Continue Shopping");
  }

  do_html_footer();
?>
