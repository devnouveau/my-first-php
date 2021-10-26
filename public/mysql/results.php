<!DOCTYPE html>
<html>
<head>
  <title>Book-O-Rama Search Results</title>
</head>
<body>
  <h1>Book-O-Rama Search Results</h1>
  <?php
    // create short variable names
    $searchtype=$_POST['searchtype'];
    $searchterm="%{$_POST['searchterm']}%"; // trim($_POST['searchterm'])


    // =============== 입력값 유효성 검사 =============== //
    if (!$searchtype || !$searchterm) {
       echo '<p>You have not entered search details.<br/>
       Please go back and try again.</p>';
       exit;
    }
    // whitelist the searchtype
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

    // =============== DB 연결 =============== //
    $db = new mysqli('localhost', 'homestead', 'secret', 'books'); // host, user, pw, dbname // mysqli_connect()함수(절차지향 방식) 호출로도 가능
    if (mysqli_connect_errno()) { // 정상일 경우 0반환
       echo '<p>Error: Could not connect to database.<br/>
       Please try again later.</p>';
       exit; // 오류발생시 스크립트 종료
    }
    // db선택 :: $db->select_db(dbname); 혹은 mysqli_selct_db(db리소스, db이름); 


    // =============== 쿼리 실행 =============== //
    $query = "SELECT ISBN, Author, Title, Price FROM Books WHERE $searchtype like ?"; 
    // ? 파라미터 자리에 $searchterm 변수를 바로 넘길 경우 SQL injection 위험

    // 각 함수는 절차지향함수로도 대체가능
    $stmt = $db->prepare($query); // 쿼리실행에 사용될 statment객체/리소스 생성
      //(prepared문 :: 동일한 쿼리를 서로 다른 매개변수를 대입해 실행가능) 
    $stmt->bind_param('s', $searchterm);  // statement에 파라미터 바인딩
    $stmt->execute(); // 쿼리 실행
    $stmt->store_result(); // 결과 셋의 모든 행을 버퍼에 넣기
  

    // =============== 쿼리 실행결과 출력=============== //
    $stmt->bind_result($isbn, $author, $title, $price); // 쿼리실행결과를 변수에 바인딩

    echo "<p>Number of books found: ".$stmt->num_rows."</p>"; // 결과셋 저장시 stmt객체의 멤버변수에 저장된 rows수를 출력 

    while($stmt->fetch()) { // 저장된 결과의 한 행을 바인딩변수에 넣어줌 (다시 호출하면 다음 행 데이터를 바인딩변수에 넣어줌)
      echo "<p><strong>Title: ".$title."</strong>";
      echo "<br />Author: ".$author;
      echo "<br />ISBN: ".$isbn;
      echo "<br />Price: \$".number_format($price,2)."</p>";
    }

    // =============== 자원반환 =============== //
    $stmt->free_result(); // 메모리에서 저장된 결과 제거
    $db->close(); // DB연결 해제
  ?>
</body>
</html>
