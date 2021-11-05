
<?php


function do_html_header($title) { // 표준화된 html헤더 출력
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $title; ?></title>
  <style>
      body { font-family: Arial, Helvetica, sans-serif; font-size: 13px; }
      li, td { font-family: Arial, Helvetica, sans-serif; font-size: 13px; }
      hr { color: #3333cc; }
      a { color: #000; }
      div.formblock { background: #ccc; width: 300px; padding: 6px; border: 1px solid #000; }
  </style>
</head>
<body>
  <div>
    <img src="./images/bookmark.gif" alt="PHPbookmark logo" 
      height="55" width="57" style="float:left;" padding-right:6px;=""/>
    <h1>PHPbookmark</h1>
  </div>
  <hr />
  <?php
  if($title) {
    do_html_heading($title);
  }
}


function do_html_footer() {
?>
</body>
</html>
<?php
}


function do_html_heading($heading) {
?>
  <h2><?php echo $heading;?></h2>
<?php
}


function do_html_URL($url, $name) {
?>
  <br><a href="/PHPbookmark/<?php echo $url;?>"><?php echo $name;?></a><br>
<?php
}


function display_site_info() {
?>
  <ul>
    <li>Store your bookmarks online with us!</li>
    <li>See what other users use!</li>
    <li>Share your favorite links with others!</li>
  </ul>
<?php
}

function display_login_form() {
?>
  <p><a href="/PHPbookmark/register_form.php">Not a member?</a></p>
  <form method="post" action="/PHPbookmark/member.php">

  <div class="formblock">
    <h2>Members Log In Here</h2>

    <p><label for="username">Username:</label><br/>
    <input type="text" name="username" id="username" /></p>

    <p><label for="passwd">Password:</label><br/>
    <input type="password" name="passwd" id="passwd" /></p>

    <button type="submit">Log In</button>

    <p><a href="/PHPbookmark/forgot_form.php">Forgot your password?</a></p>
  </div>

 </form>
<?php
}

function display_registration_form() {
?>
 <form method="post" action="/PHPbookmark/register_new.php">

 <div class="formblock">
    <h2>Register Now</h2>

    <p><label for="email">Email Address:</label><br/>
    <input type="email" name="email" id="email" 
      size="30" maxlength="100" required /></p>

    <p><label for="username">Preferred Username <br>(max 16 chars):</label><br/>
    <input type="text" name="username" id="username" 
      size="16" maxlength="16" required /></p>

    <p><label for="passwd">Password <br>(between 6 and 16 chars):</label><br/>
    <input type="password" name="passwd" id="passwd" 
      size="16" maxlength="16" required /></p>

    <p><label for="passwd2">Confirm Password:</label><br/>
    <input type="password" name="passwd2" id="passwd2" 
      size="16" maxlength="16" required /></p>


    <button type="submit">Register</button>

   </div>

  </form>
<?php

}

function display_user_urls($url_array) { // 사용자가 등록한 북마크출력
  global $bm_table;
  $bm_table = true;
?>
  <br>
  <form name="bm_table" action="/PHPbookmark/delete_bms.php" method="post">
  <table width="300" cellpadding="2" cellspacing="0">
  <?php
  $color = "#cccccc";
  echo "<tr bgcolor=\"".$color."\"><td><strong>Bookmark</strong></td>";
  echo "<td><strong>Delete?</strong></td></tr>";
  if (is_array($url_array) && count($url_array) > 0) {
    foreach ($url_array as $url) { 
      if ($color == "#cccccc") {
        $color = "#ffffff";
      } else {
        $color = "#cccccc";
      }
      // 사용자가 입력한 값을 출력시 반드시 htmlspecialchars()함수를 이용해 html엔티티변환을 해줄 것.
      echo "<tr bgcolor=\"".$color."\"><td><a href=\"".$url."\">".htmlspecialchars($url)."</a></td>
            <td><input type=\"checkbox\" name=\"del_me[]\" 
                value=\"".$url."\"></td>
            </tr>";
    }
  } else {
    echo "<tr><td>No bookmarks on record</td></tr>";
  }
?>
  </table>
  </form>
<?php
}

function display_user_menu() {
?>
<hr>
<a href="/PHPbookmark/member.php">Home</a> &nbsp;|&nbsp;
<a href="/PHPbookmark/add_bm_form.php">Add BM</a> &nbsp;|&nbsp;
<?php
  global $bm_table;
  if ($bm_table == true) { // 북마크 데이터가 저장되어 있는 경우에만 삭제링크 활성화
    echo "<a href=\"#\" onClick=\"bm_table.submit();\">Delete BM</a> &nbsp;|&nbsp;"; //  bm_table.submit(); name이 bm_table인 form을 submit
  } else {
    echo "<span style=\"color: #cccccc\">Delete BM</span> &nbsp;|&nbsp;";
  }
?>
<a href="/PHPbookmark/change_passwd_form.php">Change password</a><br>
<a href="/PHPbookmark/recommend.php">Recommend URLs to me</a> &nbsp;|&nbsp;
<a href="/PHPbookmark/logout.php">Logout</a>
<hr>

<?php
}

function display_add_bm_form() {
?>
<form name="bm_table" action="/PHPbookmark/add_bms.php" method="post">

 <div class="formblock">
    <h2>New Bookmark</h2>

    <p>
    <input type="text" name="new_url" id="new_url" 
      size="40"  maxlength="255" value="http://" required /></p>

    <button type="submit">Add Bookmark</button>

   </div>

</form>
<?php
}

function display_password_form() {
?>
   <br>
   <form action="/PHPbookmark/change_passwd.php" method="post">

 <div class="formblock">
    <h2>Change Password</h2>

    <p><label for="old_passwd">Old Password:</label><br/>
    <input type="password" name="old_passwd" id="old_passwd" 
      size="16" maxlength="16" required /></p>

    <p><label for="passwd2">New Password:</label><br/>
    <input type="password" name="new_passwd" id="new_passwd" 
      size="16" maxlength="16" required /></p>

    <p><label for="passwd2">Repeat New Password:</label><br/>
    <input type="password" name="new_passwd2" id="new_passwd2" 
      size="16" maxlength="16" required /></p>


    <button type="submit">Change Password</button>

   </div>
   <br>
<?php
}

function display_forgot_form() {
?>
 <br>
 <form action="/PHPbookmark/forgot_passwd.php" method="post">

 <div class="formblock">
    <h2>Forgot Your Password?</h2>

    <p><label for="username">Enter Your Username:</label><br/>
    <input type="text" name="username" id="username" 
      size="16" maxlength="16" required /></p>

    <button type="submit">Change Password</button>

   </div>
   <br>
<?php
}

function display_recommended_urls($url_array) {
?>
  <br>
  <table width="300" cellpadding="2" cellspacing="0">
<?php
  $color = "#cccccc";
  echo "<tr bgcolor=\"".$color."\">
        <td><strong>Recommendations</strong></td></tr>";
  if ((is_array($url_array)) && (count($url_array)>0)) {
    foreach ($url_array as $url) {
      if ($color == "#cccccc") {
        $color = "#ffffff";
      } else {
        $color = "#cccccc";
      }
      echo "<tr bgcolor=\"".$color."\">
            <td><a href=\"".$url."\">".htmlspecialchars($url)."</a></td></tr>";
    }
  } else {
    echo "<tr><td>No recommendations for you today.</td></tr>";
  }
?>
  </table>
<?php
}

?>



