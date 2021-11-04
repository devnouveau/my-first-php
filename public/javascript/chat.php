<?php
session_start(); // 세션생성
ob_start(); // 출력 버퍼링 시작
header("Content-type: application/json");  // JSON데이터로 응답함을 클라이언트에 알리기

date_default_timezone_set('UTC'); // 서버와 시간대 일치 시키기


// DB연결
$db = mysqli_connect('localhost', 'chat_user', 'chat_password', 'chat');
if (mysqli_connect_errno()) {
   echo '<p>Error: Could not connect to database.<br/>
   Please try again later.</p>';
   exit;
}

try {
 
    $currentTime = time();
    $session_id = session_id(); // 전송된 메시지를 현재 사용자가 보낸 것인지 확인하기 위함
    
    $lastPoll = isset($_SESSION['last_poll']) ? 
                      $_SESSION['last_poll'] : $currentTime; // 마지막 폴링 시간
    
    $action = isset($_SERVER['REQUEST_METHOD']) && 
              ($_SERVER['REQUEST_METHOD'] == 'POST') ? 
              'send' : 'poll';

    switch($action) {   
        case 'poll': // get요청(poll)시 미확인 메시지 출력

           $query = "SELECT * FROM chatlog WHERE 
                     date_created >= ?";

           $stmt = $db->prepare($query);
           $stmt->bind_param('s', $lastPoll);  
           $stmt->execute();
           $stmt->bind_result($id, $message, $session_id, $date_created);
           $result = $stmt->get_result(); //// $stmt->store_result()과 달리, 별도의 result set을 반환함

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

           $_SESSION['last_poll'] = $currentTime; // 폴링시간을 세션에 저장해 둠

           print json_encode([ // json으로 인코딩하여 출력
               'success' => true,
               'messages' => $newChats
           ]);
           exit;

        case 'send': // post요청(send)시 전송할 메시지를 받아 DB에 추가

            $message = isset($_POST['message']) ? $_POST['message'] : ''; 
            $message = strip_tags($message);

            $query = "INSERT INTO chatlog (message, sent_by, date_created) 
                      VALUES(?, ?, ?)";

            $stmt = $db->prepare($query);
            $stmt->bind_param('ssi', $message, $session_id, $currentTime); 
            $stmt->execute(); 

            print json_encode(['success' => true]);
            exit;
    }
} catch(\Exception $e) {
    print json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
    
}
