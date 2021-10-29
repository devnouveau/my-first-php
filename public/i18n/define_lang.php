<?php

// ============ 지역화 가능한 페이지 구조 만들기 (locale header 전송) ============ //

// lang값 가져오기 
if ((!isset($_SESSION['lang'])) || (!isset($_GET['lang']))) {
	$_SESSION['lang'] = "en"; // lang값 없으면 기본으로 en설정되도록 함
	$currLang = "en";
} else {
	$currLang = $_GET['lang'];
	$_SESSION['lang'] = $currLang;
}

// lang값에 따라 캐릭터셋과 랭귀지코드 상수 정의
switch($currLang) {  
	case "en":
		define("CHARSET","ISO-8859-1");
		define("LANGCODE", "en");
	break;

	case "ko":
		define("CHARSET","UTF-8");
		define("LANGCODE", "ko");
	break;

	case "ja":
		define("CHARSET","UTF-8");
		define("LANGCODE", "ja");
	break;

	default:
		define("CHARSET","ISO-8859-1");
		define("LANGCODE", "en");
	break;
}


// locale에 따른 헤더 전송
header("Content-Type: text/html;charset=".CHARSET);
header("Content-Language: ".LANGCODE);
?>
