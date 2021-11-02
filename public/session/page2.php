<?php 

// 세션변수 사용 전, session_start() 호출을 통해 객체의 클래스 정의를 포함시켜야 함.
session_start();

// 세션변수 사용
echo 'The content of $_SESSION[\'session_var\'] is '
        .$_SESSION['session_var'].'<br />';

        
// 세션변수 해제/세션 종료
unset($_SESSION['session_var']);
// 세션 배열 전체를 설정 해제하면 안됨. 세션을 사용할 수 없게 됨.

// 모든 세션 변수를 한 번에 설정 해제시 
// $_SESSION = array();
// session_destroy();//세션 ID소멸

?>

<p><a href="page3.php">Next page</a></p>