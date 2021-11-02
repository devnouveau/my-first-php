<?php 

// 세션시작
session_start(); 
// 세션검사 후 없으면 생성하여 $_SESSION배열에 액세스하게 해줌.
// 세션 존재시 기존 세션변수들을 $_SESSION배열에 로드

// 세션변수 등록
$_SESSION['session_var'] = "Hello world!";
// 세션을 종료하거나 직접 세션변수를 unset할 때까지 유지관리됨.

// 세션변수 사용
echo 'The content of '.$_SESSION['session_var'].' is '
                        .$_SESSION['session_var'].'<br />';

?>
<p>
    <a href="page2.php">Next page</a>
</p>