<?php
echo get_current_user().'<br />'; // 실행 중 스크립트의 소유자 조회
echo date('g:i a, j M Y', getlastmod()).'<br />'; // getlastmod 최근수정일자 타임스탬프

echo '현재 설치된 php에서 사용가능한 함수 목록입니다.<br />';
$extensions = get_loaded_extensions();
foreach ($extensions as $each_ext) {
  echo $each_ext.'<br />';
  echo '<ul>';

  $ext_funcs = get_extension_funcs($each_ext);
  if($ext_funcs) {
    foreach($ext_funcs as $func) {
        echo '<li>'.$func.'</li>';
    }
  } else {
      echo "<li>$each_ext is not a valid extension</li>";
  }
  
  echo '</ul>';
}
?>
