<?php

// 이미지 캔버스 설정
$height = 200;
$width = 500;
$im = imagecreatetruecolor($width, $height); // 이미지 식별자 생성
$white = imagecolorallocate($im, 255, 255, 255); // $im에 색상할당
$blue = imagecolorallocate($im, 0, 0, 255); 

// 이미지 그리기
// ( 좌표값은 좌측상단모서리에서 x = 0, y = 0으로 시작
//  우측하단 모서리의 좌표는 x = $width, y = $height와 동일함 )
imagefill($im, 0, 0, $blue); // 색상채우기
imageline($im, 0, 0, $width, $height, $white); // 라인그리기
imagestring($im, 4, 50, 150, 'Sales', $white); // 이미지에 텍스트 추가 // 2번째 매개변수 폰트세트 마다 사용함수가 다름. 

// 이미지 출력
header('Content-type: image/png'); // 이미지 출력위한 mime타입 지정
imagepng($im); // 이미지 출력 - 브라우저
imagepng($im, "sampleimg.png"); // 이미지 출력 - 파일

// 리소스 해제
imagedestroy($im);


?>