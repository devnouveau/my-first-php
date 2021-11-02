<?php 

session_start();

if(isset($_SESSION['session_var'])) {
    echo 'The content of $_SESSION[\'session_var\'] is '
    .$_SESSION['session_var'].'<br />';
} else {
    echo '$_SESSION[\'session_var\'] Not exist!!<br />';
}

session_destroy(); // 세션 ID소멸

?>