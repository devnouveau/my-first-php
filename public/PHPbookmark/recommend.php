<?php
  require_once('bookmark_fns.php');
  session_start();
  do_html_header('Recommending URLs');
  try   {
    check_valid_user();
    $urls = recommend_urls($_SESSION['valid_user']);
    display_recommended_urls($urls);
  }
  catch(Exception $e)   {
    echo $e->getMessage();
  }
  display_user_menu();
  do_html_footer();
?>

select distinct(b2.username)
              from bookmark b1, bookmark b2
		          where b1.username='noname'
                and b1.username != b2.username
                and b1.bm_URL = b2.bm_URL