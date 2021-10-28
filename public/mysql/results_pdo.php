<!DOCTYPE html>
<html>
<head>
  <title>Book-O-Rama Search Results</title>
</head>
<body>
  <h1>Book-O-Rama Search Results</h1>
  <?php

    // ================= PDO (PHP Data Objects) ================= //
    // 동일 인터페이스로 서로다른 DB사용가능
    $searchtype=$_POST['searchtype'];
    $searchterm="%{$_POST['searchterm']}%";

    if (!$searchtype || !$searchterm) {
       echo '<p>You have not entered search details.<br/>
       Please go back and try again.</p>';
       exit;
    }

    switch ($searchtype) {
      case 'Title':
      case 'Author':
      case 'ISBN':   
        break;
      default: 
        echo '<p>That is not a valid search type. <br/>
        Please go back and try again.</p>';
        exit; 
    }

    // PDO사용을 위한 변수 세팅
    $user = 'homestead';
    $pass = 'secret';
    $host = 'localhost';
    $db_name = 'books';

    // DSN(Data Source Name)설정
    $dsn = "mysql:host=$host;dbname=$db_name";

    // DB연결
    try {
      $db = new PDO($dsn, $user, $pass); 

      // 쿼리 실행
      $query = "SELECT ISBN, Author, Title, Price FROM Books WHERE $searchtype like :searchterm";  // mysqli라이브러리처럼 파라미터를 ?로 전달하는 것도 가능
      $stmt = $db->prepare($query);  
      $stmt->bindParam(':searchterm', $searchterm);
      $stmt->execute(); 

      // 반환된 행 갯수 출력
      echo "<p>Number of books found: ".$stmt->rowCount()."</p>"; 

      // 결과행출력
      // mysqli라이브러리에서는 $stmt->bind_result("변수", "",...); 이용하여 결과바인딩
      while($result = $stmt->fetch(PDO::FETCH_OBJ)) { // PDO::FETCH_OBJ는 객체형태로 행을 반환하도록 해줌                                              
        echo "<p><strong>Title: ".$result->Title."</strong>";                               
        echo "<br />Author: ".$result->Author;                                              
        echo "<br />ISBN: ".$result->ISBN;                                                  
        echo "<br />Price: \$".number_format($result->Price, 2)."</p>";                                         
      }         

      // DB연결 해제
      $db = NULL;
    } catch (PDOException $e) { // 예외처리
      echo "Error: ".$e->getMessage();
      exit;
    }
  ?>
</body>
</html>
