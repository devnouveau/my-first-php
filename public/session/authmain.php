<?php 

// 세션시작
session_start();

// include('../debugging&logging/dump_variables.php');

// 폼데이터 확인
if(isset($_POST['userid']) && isset($_POST['password'])) {
    $userid = $_POST['userid'];
    $password = $_POST['password'];

    // DB연결
    $db_conn = new mysqli('localhost', 'webauth', 'webauth', 'auth');
    if(mysqli_connect_error()) {
        echo 'Connection to database failed:'.mysqli_connect_error();
    }

    $query = "select * from authorized_users where
                name='".$userid."' and password='".$password."'";
    $result = $db_conn->query($query);

    if($result->num_rows) { // db에 등록된 사용자인 경우 세션등록
        $_SESSION['valid_user'] = $userid;
    }
    $db_conn->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Page</title>
    <style type="text/css">
      fieldset {
         width: 50%;
         border: 2px solid #ff0000;
      }
      legend {
         font-weight: bold;
         font-size: 125%;
      }
      label {
         width: 125px;
         float: left;
         text-align: left;
         font-weight: bold;
      }
      input {
         border: 1px solid #000;
         padding: 3px;
      }
      button {
         margin-top: 12px;
      }
    </style>
</head>
<body>
    <h1>Home Page</h1>
    <?php 

        if(isset($_SESSION['valid_user'])) { // 인증된 경우
            echo '<p>You are logged in as: '.$_SESSION['valid_user'].' <br />';
            echo '<a href="logout.php">Log out</a></p>';
        
        } else { // 인증되지 않은 경우 
            if (isset($userid)) {
              echo '<p>Could not log you in.</p>';
            } else {
              echo '<p>You are not logged in.</p>';
            }
            echo '<form action="authmain.php" method="post">';
            echo    '<fieldset>';
            echo        '<legend>Login Now!</legend>';
            echo        '<p><label for="userid">UserID:</label>';
            echo        '<input type="text" name="userid" id="userid" size="30"/></p>';
            echo        '<p><label for="password">Password:</label>';
            echo        '<input type="password" name="password" id="password" size="30"/></p>';    
            echo    '</fieldset>';
            echo    '<button type="submit" name="login">Login</button>';
            echo '</form>';
        }
    ?>

    <p><a href="members_only.php">Go to Members Section</a></p>


</body>
</html>