<?php
// ================== 외부코드 삽입하기 ================== //
// 1. require(), include() : 다른 파일을 현재 파일에 삽입
// 오류발생시, require()는 스크립트 중단. include는 계속 실행(경고만 발생)
// require_once(),include_once() : 함수 중복정의 방지로 사용. 성능은 require보다 떨어짐

// 2. 일반 텍스트나HTML만을 포함시킬 경우 require()대신 readfile()사용가능

// 3. php.ini에서 auto_prepend_file, auto_appen_file을 사용하여 
// 모든 페이지가 특정 파일을 참조하도록 함(include와 동일한 방법으로 추가됨)

//4. 웹서버에서 설정

  echo 'This is the main file.<br />';
  require('reusable.php'); 
  echo 'The script will end now.<br />';
?>

