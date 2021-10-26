<!DOCTYPE html>
<html>
<head>
  <title>Book-O-Rama Book Entry Results</title>
</head>
<body>
  <h1>Book-O-Rama Book Entry Results</h1>
  <?php

    // ================= 입력값 유효성 검사 ================= //
    if (!isset($_POST['ISBN']) || !isset($_POST['Author']) 
         || !isset($_POST['Title']) || !isset($_POST['Price'])) {
       echo "<p>You have not entered all the required details.<br />
             Please go back and try again.</p>";
       exit;
    }

    // create short variable names
    $isbn=$_POST['ISBN'];
    $author=$_POST['Author'];
    $title=$_POST['Title'];
    $price=$_POST['Price'];
    $price = doubleval($price);


    // ================= DB연결 ================= //
    @$db = new mysqli('localhost', 'homestead', 'secret', 'books');

    if (mysqli_connect_errno()) {
       echo "<p>Error: Could not connect to database.<br/>
             Please try again later.</p>";
       exit;
    }

    // ================= 데이터 삽입 ================= //
    $query = "INSERT INTO Books VALUES (?, ?, ?, ?)";
    $stmt = $db->prepare($query);
    $stmt->bind_param('sssd', $isbn, $author, $title, $price); // 파라미터-변수 바인딩 (첫번째 매개변수는 데이터타입의 조합)
    $stmt->execute();


    // 결과확인
    if ($stmt->affected_rows > 0) {
        echo  "<p>Book inserted into the database.</p>";
    } else {
        echo "<p>An error has occurred.<br/>
              The item was not added.</p>";
    }
  
    // ================= db연결 종료 ================= //
    $db->close();
  ?>
</body>
</html>
