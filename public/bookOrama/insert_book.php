<?php
  require_once('book_sc_fns.php');
  session_start();

  do_html_header("addmin a book");
  if(check_admin_user()) {
    if(filled_out($_POST)) {
      $isbn = $_POST['isbn'];
      $title = $_POST['title'];
      $author = $_POST['author'];
      $catid = $_POST['catid'];
      $price = $_POST['price'];
      $description = $_POST['description'];

      if(insert_book($isbn, $title, $author, $catid, $price, $description)) {
        echo "<p>Book <em>".htmlspecialchars($title)."</em> was added to the DB</p>";
      } else {
        echo "<p>could not be added to DB</p>";
      }
    } else {
      echo "<p>you have not filled out the form</p>";
    }
    do_html_url("admin.php", "Back to admin menu");
  } else {
    echo "<p>not authorized</p>";
  }
  do_html_footer();


?>