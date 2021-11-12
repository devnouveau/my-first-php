### 첫 번째 방식
- 조회결과를 statement객체에 저장
- 결과컬럼을 개별변수에 바인딩
- db->prepare(), stmt = db->prepare(), stmt->bind_param(), stmt->execute(), 
  stmt->store_result(), stmt->fetch()
```php
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
```

### 두 번째 방식
- 조회결과를 statement객체의 get_result()로 받아서 별도의 결과 변수에 저장
- 결과변수에 저장된 데이터를 fetch_assoc()fh 접근하여 행 별로 key,value를 받아옴
- $db->prepare(), $stmt->bind_param(), 
  $stmt->execute(), $result = $stmt->get_result()
  $row = $result->fetch_assoc(),
  $newarr = []; 
  while($row = $result->fetch_assoc()) { $row['column'] = 'self'; $newarr[] = $row; }
```php
// =============== DB연결 =============== //
$db = mysqli_connect('localhost', 'chat_user', 'chat_password', 'chat');
if (mysqli_connect_errno()) {
   echo '<p>Error: Could not connect to database.<br/>
   Please try again later.</p>';
   exit;
}

// =============== 쿼리 실행 =============== //
try {
  $query = "SELECT * FROM chatlog WHERE 
            date_created >= ?";
  $stmt = $db->prepare($query);
  $stmt->bind_param('s', $lastPoll);  
  $stmt->execute();
  $stmt->bind_result($id, $message, $session_id, $date_created);
  $result = $stmt->get_result(); //// $stmt->store_result()과 달리, 별도의 result set을 반환함

// =============== 쿼리 실행 결과출력 =============== //

  $newChats = [];
  while($chat = $result->fetch_assoc()) { //// while($stmt->fetch()){}와 다르게, key(컬럼명)-value(컬럼값)로 이루어진 값들의 array를 반환함
      
      // DB에서 조회한 데이터를 가공
      if($session_id == $chat['sent_by']) {
        $chat['sent_by'] = 'self';
      } else {
        $chat['sent_by'] = 'other';
      }
    
      $newChats[] = $chat;
  }

} catch (Exception $e) {
}
```

### 세 번째 방식
- statement객체 사용 X
$result = $conn->query()
while($row = $result->fetch_assoc()){...} // array로 반환




    $row = $result->fetch_object(); // object로 반환



    ```php
    $conn = db_connect();
    $query = "select * from books where catid = '".$conn->real_escape_string($catid)."'";
    $result = @$conn->query($query);
    if (!$result) {
      return false;
    }
    $num_books = @$result->num_rows;
    if ($num_books == 0) {
       return false;
    }
    $result = db_result_to_array($result);
    return $result;
    ```

