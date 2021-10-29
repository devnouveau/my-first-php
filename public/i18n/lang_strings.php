<?php

// ============ 지역화 가능한 페이지 구조 만들기 (로케일에 따라 출력할 정보 설정) ============ //

function defineStrings() {
    switch($_SESSION['lang']) { // session에 저장한 lang값에 따라 출력할 내용을 상수로 정의함
        case "en":
            define("WELCOME_TXT", "Welcome");
            define("CHOOSE_TXT", "Choose Language");
        break;

        case "ko":
            define("WELCOME_TXT", "어서오세요");
            define("CHOOSE_TXT", "언어선택");
        break;

        case "ja":
			define("WELCOME_TXT","ようこそ！");
			define("CHOOSE_TXT","言語を選択");
        break;

        default:
            define("WELCOME_TXT", "Welcome");
            define("CHOOSE_TXT", "Choose Language");
        break;
            
    }
}
?>