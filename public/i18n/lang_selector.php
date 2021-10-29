<?php
// ============ 지역화 가능한 페이지 구조 만들기 (선택된 언어로 메시지 출력) ============ //
session_start(); // Initialize session data
include 'define_lang.php'; // 로케일별 헤더 전송
include 'lang_strings.php'; // 세션에 세팅된 언어에 따른 메시지 출력
defineStrings();
?>

<!DOCTYPE html>
<html lang="<?php echo LANGCODE; // 세션의 lang값에 따라 결정된 상수LANGCODE를 html헤더값에 설정 ?>">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo WELCOME_TXT;?></title>
    <meta charset="<?php echo CHARSET; // 세션의 lang값에 따라 결정된 상수CHARSET을 html헤더값에 설정 ?>" />
</head>
<body>
    <h1><?php echo WELCOME_TXT; ?></h1>
    <h2><?php echo CHOOSE_TXT; ?></h2>
    <ul>
        <li><a href="<?php echo $_SERVER['PHP_SELF']."?lang=en"; 
            // 링크 클릭시 lang값을 get으로 전달하면서 현재페이지로 이동
            // (include된 define_lang.php에서 get으로 전달받은 lang값을 이용해 헤더를 세팅하고,,
            // lang_strings.php에서 상수값이 정해져서 출력됨) ?>">en</a></li>
        <li><a href="<?php echo $_SERVER['PHP_SELF']."?lang=ko";?>">ko</a></li>
        <li><a href="<?php echo $_SERVER['PHP_SELF']."?lang=ja";?>">ja</a></li>
    </ul>
</body>
</html>