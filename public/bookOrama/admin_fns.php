<?php

function display_category_form($category = '') {

  $edit = is_array($category);

?>
  <form method="post"
      action="<?php echo $edit ? 'edit_category.php' : 'insert_category.php'; ?>">
  <table border="0">
  <tr>
    <td>Category Name:</td>
    <td><input type="text" name="catname" size="40" maxlength="40"
          value="<?php echo htmlspecialchars($edit ? $category['catname'] : ''); ?>" /></td>
   </tr>
  <tr>
    <td <?php if (!$edit) { echo "colspan=2";} ?> align="center">
      <?php
         if ($edit) {
            echo "<input type=\"hidden\" name=\"catid\" value=\"". htmlspecialchars($category['catid'])."\" />";
         }
      ?>
      <input type="submit"
       value="<?php echo $edit ? 'Update' : 'Add'; ?> Category" /></form>
     </td>
     <?php
        if ($edit) {
          echo "<td>
                <form method=\"post\" action=\"delete_category.php\">
                <input type=\"hidden\" name=\"catid\" value=\"". htmlspecialchars($category['catid'])."\" />
                <input type=\"submit\" value=\"Delete category\" />
                </form></td>";
       }
     ?>
  </tr>
  </table>
<?php
}

function display_book_form($book = '') {


  $edit = is_array($book);

?>
  <form method="post"
        action="<?php echo $edit ? 'edit_book.php' : 'insert_book.php';?>">
  <table border="0">
  <tr>
    <td>ISBN:</td>
    <td><input type="text" name="isbn"
         value="<?php echo htmlspecialchars($edit ? $book['isbn'] : ''); ?>" /></td>
  </tr>
  <tr>
    <td>Book Title:</td>
    <td><input type="text" name="title"
         value="<?php echo htmlspecialchars($edit ? $book['title'] : ''); ?>" /></td>
  </tr>
  <tr>
    <td>Book Author:</td>
    <td><input type="text" name="author"
         value="<?php echo htmlspecialchars($edit ? $book['author'] : ''); ?>" /></td>
   </tr>
   <tr>
      <td>Category:</td>
      <td><select name="catid">
      <?php
          $cat_array=get_categories();
          foreach ($cat_array as $thiscat) {
               echo "<option value=\"".htmlspecialchars($thiscat['catid'])."\"";
               if (($edit) && ($thiscat['catid'] == $book['catid'])) {
                   echo " selected";
               }
               echo ">".htmlspecialchars($thiscat['catname'])."</option>";
          }
          ?>
          </select>
        </td>
   </tr>
   <tr>
    <td>Price:</td>
    <td><input type="text" name="price"
               value="<?php echo htmlspecialchars($edit ? $book['price'] : ''); ?>" /></td>
   </tr>
   <tr>
     <td>Description:</td>
     <td><textarea rows="3" cols="50"
          name="description"><?php echo htmlspecialchars($edit ? $book['description'] : ''); ?></textarea></td>
    </tr>
    <tr>
      <td <?php if (!$edit) { echo "colspan=2"; }?> align="center">
         <?php
            if ($edit)
             echo "<input type=\"hidden\" name=\"oldisbn\"
                    value=\"".htmlspecialchars($book['isbn'])."\" />";
         ?>
        <input type="submit"
               value="<?php echo $edit ? 'Update' : 'Add'; ?> Book" />
        </form></td>
        <?php
           if ($edit) {
             echo "<td>
                   <form method=\"post\" action=\"delete_book.php\">
                   <input type=\"hidden\" name=\"isbn\"
                    value=\"".htmlspecialchars($book['isbn'])."\" />
                   <input type=\"submit\" value=\"Delete book\"/>
                   </form></td>";
            }
          ?>
         </td>
      </tr>
  </table>
  </form>
<?php
}

function display_password_form() {
?>
   <br />
   <form action="change_password.php" method="post">
   <table width="250" cellpadding="2" cellspacing="0" bgcolor="#cccccc">
   <tr><td>Old password:</td>
       <td><input type="password" name="old_passwd" size="16" maxlength="16" /></td>
   </tr>
   <tr><td>New password:</td>
       <td><input type="password" name="new_passwd" size="16" maxlength="16" /></td>
   </tr>
   <tr><td>Repeat new password:</td>
       <td><input type="password" name="new_passwd2" size="16" maxlength="16" /></td>
   </tr>
   <tr><td colspan=2 align="center"><input type="submit" value="Change password">
   </td></tr>
   </table>
   <br />
<?php
}

function insert_category($catname) {

   $conn = db_connect();

   $query = "select *
             from categories
             where catname='".$conn->real_escape_string($catname)."'";
   $result = $conn->query($query);
   if ((!$result) || ($result->num_rows!=0)) {
     return false;
   }

   $query = "insert into categories 
            (catname)
            values
            ('".$conn->real_escape_string($catname)."')";
   $result = $conn->query($query);
   if (!$result) {
     return false;
   } else {
     return true;
   }
}

function insert_book($isbn, $title, $author, $catid, $price, $description) {

   $conn = db_connect();

   $query = "select *
             from books
             where isbn='".$conn->real_escape_string($isbn)."'";

   $result = $conn->query($query);
   if ((!$result) || ($result->num_rows!=0)) {
     return false;
   }

   $query = "insert into books values
            ('".$conn->real_escape_string($isbn) ."', '". $conn->real_escape_string($author) . 
             "', '". $conn->real_escape_string($title) ."', '". $conn->real_escape_string($catid) . 
              "', '". $conn->real_escape_string($price) ."', '" . $conn->real_escape_string($description) ."')";

   $result = $conn->query($query);
   if (!$result) { echo mysqli_error($conn);
     return false;
   } else {
     return true;
   }
}

function update_category($catid, $catname) {

   $conn = db_connect();

   $query = "update categories
             set catname='".$conn->real_escape_string($catname) ."'
             where catid='".$conn->real_escape_string($catid) ."'";
   $result = @$conn->query($query);
   if (!$result) {
     return false;
   } else {
     return true;
   }
}

function update_book($oldisbn, $isbn, $title, $author, $catid,
                     $price, $description) {

   $conn = db_connect();

   $query = "update books
             set isbn= '".$conn->real_escape_string($isbn)."',
             title = '".$conn->real_escape_string($title)."',
             author = '".$conn->real_escape_string($author)."',
             catid = '".$conn->real_escape_string($catid)."',
             price = '".$conn->real_escape_string($price)."',
             description = '".$conn->real_escape_string($description)."'
             where isbn = '".$conn->real_escape_string($oldisbn)."'";

   $result = @$conn->query($query);
   if (!$result) {
     return false;
   } else {
     return true;
   }
}

function delete_category($catid) {

   $conn = db_connect();

   $query = "select *
             from books
             where catid='".$conn->real_escape_string($catid)."'";

   $result = @$conn->query($query);
   if ((!$result) || (@$result->num_rows > 0)) {
     return false;
   }

   $query = "delete from categories
             where catid='".$conn->real_escape_string($catid)."'";
   $result = @$conn->query($query);
   if (!$result) {
     return false;
   } else {
     return true;
   }
}


function delete_book($isbn) {

   $conn = db_connect();

   $query = "delete from books
             where isbn='".$conn->real_escape_string($isbn)."'";
   $result = @$conn->query($query);
   if (!$result) {
     return false;
   } else {
     return true;
   }
}

?>
