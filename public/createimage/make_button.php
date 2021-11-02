<?php
    // 입력값 확인
    $button_text = $_POST['button_text'];
    $button_color = $_POST['button_background'];
    if(empty($button_text) || empty($button_color)) {
        echo '<p>form not filled out correctly</p>';
        exit;
    }

    // 이미지 생성
    //$im = imagecreatefrompng($button_color."-button.png"); // 기존에 있는 이미지 파일로 새로운이미지 생성
    $im = imagecreatefrompng("yeonim.png"); // 기존에 있는 이미지 파일로 새로운이미지 생성
    $width_image = imagesx($im);
    $height_image = imagesy($im);


    // 이미지 네 방향 테두리에서 18픽셀 띄워서 글자 채우기
    $width_image_wo_margins = $width_image - (2 * 18);
    $height_image_wo_margins = $height_image - (2 * 18);

    
    // 폰트위치를 GD2에 알려주기
    putenv('GDFONTPATH=/usr/share/fonts/truetype/dejavu');
    $font_name = 'DejaVuSans';


    // 버튼에 적합한 텍스트의 폰트 크기 찾기 (가장 큰 크기부터 시작해서 줄여가기)
    $font_size = 33;
    do {
        $font_size--;
      
        $bbox = imagettfbbox($font_size, 0, $font_name, $button_text); // 글자의 경계박스
        /* (x, y)
          (6,7)    (4,5)
            ---------
            |       |
            |       |
            |       |
            ---------
          (0,1)    (2,3)  
        */
        $right_text = $bbox[2]; // 우측하단 x좌표
        $left_text = $bbox[0];  // 좌측하단 x좌표
        $width_text = $right_text - $left_text; 
        $height_text = abs($bbox[7] - $bbox[1]);
       
    } while ( 
            $font_size > 8 && // 폰트가 8보다는 커야 함
           ($height_text > $height_image_wo_margins || 
            $width_text > $width_image_wo_margins)
          );
    
    if ($height_text > $height_image_wo_margins ||
         $width_text > $width_image_wo_margins) {
      // no readable font size will fit on button
      echo '<p>Text given will not fit on button.</p>';
    } else {

      // 텍스트를 버튼에 추가할 위치 계산
      $text_x = $width_image / 2.0 - $width_text / 2.0;
      $text_y = $height_image / 2.0 - $height_text / 2.0;
    
      if ($left_text < 0) {
        $text_x += abs($left_text);     // add factor for left overhang
      }
    
      $above_line_text = abs($bbox[7]); // how far above the baseline?
      $text_y += $above_line_text;      // add baseline factor 
      
      $text_y -= 2;  // adjustment factor for shape of our template
    
      $white = imagecolorallocate ($im, 255, 255, 255); // 색상설정
    
      imagettftext ($im, $font_size, 0, $text_x, $text_y, $white, 
                    $font_name, $button_text); // 텍스트 그리기
    
      
      // 브라우저 출력
      header('Content-type: image/png'); 
      imagepng ($im);
    }
    
    // Clean up the resources 
    imagedestroy ($im);

?>